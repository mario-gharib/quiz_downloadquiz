<?php
// This file is part of Moodle - https://moodle.org/.
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

/**
 * Timed grant manager for quiz_downloadquiz.
 *
 * @package     quiz_downloadquiz
 * @copyright   2026 Center for Digital Innovation and Artificial Intelligence
 * @author      Center for Digital Innovation and Artificial Intelligence
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace quiz_downloadquiz\local;

defined('MOODLE_INTERNAL') || die();

/**
 * Manages timed user grants for the download feature.
 */
final class grant_manager {

    /**
     * Grant storage table.
     */
    private const TABLE = 'quiz_downloadquiz_grants';

    /**
     * Plugin config key for the timed-access role id.
     */
    private const ROLE_CONFIG_KEY = 'grantroleid';

    /**
     * Moodle database instance.
     *
     * @var \moodle_database
     */
    private \moodle_database $db;

    /**
     * Constructor.
     */
    public function __construct() {
        global $DB;
        $this->db = $DB;
    }

    /**
     * Get an active Moodle user by email address.
     *
     * @param string $email
     * @return \stdClass|false
     */
    public function get_user_by_email(string $email) {
        $email = trim(\core_text::strtolower($email));

        if ($email === '') {
            return false;
        }

        return $this->db->get_record('user', [
            'email' => $email,
            'deleted' => 0,
        ]);
    }

    /**
     * Create a new timed grant for one user.
     *
     * Existing active grants for the same user are disabled first so that the
     * inserted grant becomes the only active grant in effect.
     *
     * @param int $userid
     * @param int $timeexpires
     * @param int $grantedby
     * @return int
     */
    public function create_grant(int $userid, int $timeexpires, int $grantedby): int {
        $this->validate_expiry_time($timeexpires);

        $roleid = $this->get_configured_roleid();
        if ($roleid <= 0) {
            throw new \moodle_exception('errornoroleselected', 'quiz_downloadquiz');
        }

        $now = time();

        $this->disable_active_grants_for_user($userid, $now);

        $record = (object) [
            'userid' => $userid,
            'grantedby' => $grantedby,
            'timegranted' => $now,
            'timeexpires' => $timeexpires,
            'enabled' => 1,
            'timecreated' => $now,
            'timemodified' => $now,
        ];

        $grantid = $this->db->insert_record(self::TABLE, $record);

        $this->assign_role_to_user($userid, $roleid);

        return (int) $grantid;
    }

    /**
     * Revoke all active grants for one user and remove the timed-access role.
     *
     * Historical grant rows remain stored.
     *
     * @param int $userid
     * @return void
     */
    public function revoke_grant(int $userid): void {
        $now = time();

        $this->disable_active_grants_for_user($userid, $now);

        $roleid = $this->get_configured_roleid();
        if ($roleid > 0) {
            $this->unassign_role_from_user($userid, $roleid);
        }
    }

    /**
     * Disable expired grants and remove the timed-access role when no active
     * unexpired grant remains for the affected user.
     *
     * @return void
     */
    public function disable_expired_grants(): void {
        $now = time();

        $expiredrecords = $this->db->get_records_select(
            self::TABLE,
            'enabled = 1 AND timeexpires <= :now',
            ['now' => $now]
        );

        if (empty($expiredrecords)) {
            return;
        }

        $roleid = $this->get_configured_roleid();
        $affecteduserids = [];

        foreach ($expiredrecords as $record) {
            $record->enabled = 0;
            $record->timemodified = $now;
            $this->db->update_record(self::TABLE, $record);

            $affecteduserids[(int) $record->userid] = (int) $record->userid;
        }

        if ($roleid <= 0) {
            return;
        }

        foreach ($affecteduserids as $userid) {
            if (!$this->has_enabled_unexpired_grant($userid, $now)) {
                $this->unassign_role_from_user($userid, $roleid);
            }
        }
    }

    /**
     * Check whether a user currently has an active grant.
     *
     * @param int $userid
     * @return bool
     */
    public function user_has_active_grant(int $userid): bool {
        $this->disable_expired_grants();

        return $this->db->record_exists_select(
            self::TABLE,
            'userid = :userid AND enabled = 1 AND timeexpires > :now',
            [
                'userid' => $userid,
                'now' => time(),
            ]
        );
    }

    /**
     * Get the current active grant for one user.
     *
     * @param int $userid
     * @return \stdClass|null
     */
    public function get_active_grant_for_user(int $userid): ?\stdClass {
        $this->disable_expired_grants();

        $sql = "SELECT g.id,
                       g.userid,
                       g.grantedby,
                       g.timegranted,
                       g.timeexpires,
                       g.enabled
                  FROM {" . self::TABLE . "} g
                 WHERE g.userid = :userid
                   AND g.enabled = 1
                   AND g.timeexpires > :now
              ORDER BY g.timeexpires ASC, g.id ASC";

        $record = $this->db->get_record_sql($sql, [
            'userid' => $userid,
            'now' => time(),
        ]);

        return $record ?: null;
    }

    /**
     * Get all current active grants.
     *
     * @return array
     */
    public function get_current_grants(): array {
        $this->disable_expired_grants();

        $sql = "SELECT g.id,
                       g.userid,
                       g.timegranted,
                       g.timeexpires,
                       u.firstname,
                       u.lastname,
                       u.email AS useremail,
                       CONCAT(u.firstname, ' ', u.lastname) AS userfullname,
                       CONCAT(gu.firstname, ' ', gu.lastname) AS grantedbyname
                  FROM {" . self::TABLE . "} g
                  JOIN {user} u
                    ON u.id = g.userid
                  JOIN {user} gu
                    ON gu.id = g.grantedby
                 WHERE g.enabled = 1
                   AND g.timeexpires > :now
              ORDER BY g.timeexpires ASC, g.id ASC";

        return array_values($this->db->get_records_sql($sql, ['now' => time()]));
    }

    /**
     * Get all grants, including inactive and expired records.
     *
     * @return array
     */
    public function get_all_grants(): array {
        $sql = "SELECT g.id,
                       g.userid,
                       g.grantedby,
                       g.timegranted,
                       g.timeexpires,
                       g.enabled,
                       g.timecreated,
                       g.timemodified,
                       u.firstname,
                       u.lastname,
                       u.email AS useremail,
                       CONCAT(u.firstname, ' ', u.lastname) AS userfullname,
                       CONCAT(gu.firstname, ' ', gu.lastname) AS grantedbyname
                  FROM {" . self::TABLE . "} g
                  JOIN {user} u
                    ON u.id = g.userid
                  JOIN {user} gu
                    ON gu.id = g.grantedby
              ORDER BY g.timegranted DESC, g.id DESC";

        return array_values($this->db->get_records_sql($sql));
    }

    /**
     * Reassign the configured timed-access role to all users who currently have
     * an active grant.
     *
     * This is required after role deletion and recreation, or when assignments
     * need repair.
     *
     * @param int $roleid
     * @return void
     */
    public function resync_active_grants_to_role(int $roleid): void {
        if ($roleid <= 0) {
            return;
        }

        $this->disable_expired_grants();

        $sql = "SELECT DISTINCT userid
                  FROM {" . self::TABLE . "}
                 WHERE enabled = 1
                   AND timeexpires > :now";

        $records = $this->db->get_records_sql($sql, ['now' => time()]);
        if (empty($records)) {
            return;
        }

        foreach ($records as $record) {
            $this->assign_role_to_user((int) $record->userid, $roleid);
        }
    }

    /**
     * Format remaining time until expiry.
     *
     * @param int $timeexpires
     * @return string
     */
    public static function format_remaining_time(int $timeexpires): string {
        $remaining = $timeexpires - time();

        if ($remaining <= 0) {
            return get_string('expired', 'quiz_downloadquiz');
        }

        return format_time($remaining);
    }

    /**
     * Validate that the expiry time is in the future.
     *
     * @param int $timeexpires
     * @return void
     */
    private function validate_expiry_time(int $timeexpires): void {
        if ($timeexpires <= time()) {
            throw new \invalid_parameter_exception('Expiry time must be in the future.');
        }
    }

    /**
     * Disable all active grants for one user.
     *
     * @param int $userid
     * @param int|null $timemodified
     * @return void
     */
    private function disable_active_grants_for_user(int $userid, ?int $timemodified = null): void {
        $timemodified = $timemodified ?? time();

        $records = $this->db->get_records_select(
            self::TABLE,
            'userid = :userid AND enabled = 1',
            ['userid' => $userid]
        );

        foreach ($records as $record) {
            $record->enabled = 0;
            $record->timemodified = $timemodified;
            $this->db->update_record(self::TABLE, $record);
        }
    }

    /**
     * Check whether the user has an enabled and unexpired grant.
     *
     * @param int $userid
     * @param int|null $now
     * @return bool
     */
    private function has_enabled_unexpired_grant(int $userid, ?int $now = null): bool {
        $now = $now ?? time();

        return $this->db->record_exists_select(
            self::TABLE,
            'userid = :userid AND enabled = 1 AND timeexpires > :now',
            [
                'userid' => $userid,
                'now' => $now,
            ]
        );
    }

    /**
     * Get the configured timed-access role id from plugin configuration.
     *
     * @return int
     */
    private function get_configured_roleid(): int {
        return (int) get_config('quiz_downloadquiz', self::ROLE_CONFIG_KEY);
    }

    /**
     * Assign the timed-access role to a user at system context.
     *
     * @param int $userid
     * @param int $roleid
     * @return void
     */
    private function assign_role_to_user(int $userid, int $roleid): void {
        $systemcontext = \context_system::instance();

        if (user_has_role_assignment($userid, $roleid, $systemcontext->id)) {
            return;
        }

        role_assign($roleid, $userid, $systemcontext->id);
        mark_user_dirty($userid);
    }

    /**
     * Remove the timed-access role from a user at system context.
     *
     * @param int $userid
     * @param int $roleid
     * @return void
     */
    private function unassign_role_from_user(int $userid, int $roleid): void {
        $systemcontext = \context_system::instance();

        if (!user_has_role_assignment($userid, $roleid, $systemcontext->id)) {
            return;
        }

        role_unassign($roleid, $userid, $systemcontext->id);
        mark_user_dirty($userid);
    }
}