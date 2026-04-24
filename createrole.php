<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Create or repair the required timed-access role for quiz_downloadquiz.
 *
 * @package     quiz_downloadquiz
 * @copyright   2026 Center for Digital Innovation and Artificial Intelligence
 * @author      Center for Digital Innovation and Artificial Intelligence
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../../config.php');
require_once($CFG->libdir . '/adminlib.php');

require_login();

$systemcontext = context_system::instance();
require_capability('moodle/role:manage', $systemcontext);

$PAGE->set_context($systemcontext);
$PAGE->set_url(new moodle_url('/mod/quiz/report/downloadquiz/createrole.php'));
$PAGE->set_pagelayout('admin');
$PAGE->set_title(get_string('createrequiredrole', 'quiz_downloadquiz'));
$PAGE->set_heading(get_string('createrequiredrole', 'quiz_downloadquiz'));

$roledefinition = get_required_role_definition();
$returnurl = new moodle_url('/admin/settings.php', ['section' => 'modsettingsquizcatdownloadquiz']);

$grantmanager = new \quiz_downloadquiz\local\grant_manager();
$existingrole = $DB->get_record('role', ['shortname' => $roledefinition->shortname], '*', IGNORE_MISSING);

if ($existingrole) {
    update_required_role($existingrole, $roledefinition, $systemcontext, $grantmanager);

    redirect(
        $returnurl,
        get_string('requiredroleexists', 'quiz_downloadquiz'),
        null,
        \core\output\notification::NOTIFY_SUCCESS
    );
}

create_required_role($roledefinition, $systemcontext, $grantmanager);

redirect(
    $returnurl,
    get_string('requiredrolecreated', 'quiz_downloadquiz'),
    null,
    \core\output\notification::NOTIFY_SUCCESS
);

/**
 * Return the required role definition for timed access.
 *
 * @return stdClass
 */
function get_required_role_definition(): stdClass {
    $definition = new stdClass();
    $definition->shortname = 'downloadquizaccess';
    $definition->name = 'Download Quiz PDF Access';
    $definition->description = 'Role for timed access to the Download quiz with answers report.';

    return $definition;
}

/**
 * Create the required role and apply all mandatory configuration.
 *
 * @param stdClass $definition
 * @param context_system $systemcontext
 * @param \quiz_downloadquiz\local\grant_manager $grantmanager
 * @return void
 */
function create_required_role(
    stdClass $definition,
    context_system $systemcontext,
    \quiz_downloadquiz\local\grant_manager $grantmanager
): void {
    $roleid = create_role(
        $definition->name,
        $definition->shortname,
        $definition->description
    );

    configure_required_role($roleid, $systemcontext, $grantmanager);
}

/**
 * Update the required role and re-apply all mandatory configuration.
 *
 * @param stdClass $existingrole
 * @param stdClass $definition
 * @param context_system $systemcontext
 * @param \quiz_downloadquiz\local\grant_manager $grantmanager
 * @return void
 */
function update_required_role(
    stdClass $existingrole,
    stdClass $definition,
    context_system $systemcontext,
    \quiz_downloadquiz\local\grant_manager $grantmanager
): void {
    global $DB;

    $record = new stdClass();
    $record->id = (int)$existingrole->id;
    $record->name = $definition->name;
    $record->shortname = $definition->shortname;
    $record->description = $definition->description;

    $DB->update_record('role', $record);

    configure_required_role((int)$existingrole->id, $systemcontext, $grantmanager);
}

/**
 * Apply the required configuration to the timed-access role.
 *
 * @param int $roleid
 * @param context_system $systemcontext
 * @param \quiz_downloadquiz\local\grant_manager $grantmanager
 * @return void
 */
function configure_required_role(
    int $roleid,
    context_system $systemcontext,
    \quiz_downloadquiz\local\grant_manager $grantmanager
): void {
    set_role_contextlevels($roleid, [CONTEXT_SYSTEM]);

    assign_capability(
        'quiz/downloadquiz:view',
        CAP_ALLOW,
        $roleid,
        $systemcontext->id,
        true
    );

    ensure_manager_can_assign_role($roleid);
    set_config('grantroleid', $roleid, 'quiz_downloadquiz');

    $grantmanager->resync_active_grants_to_role($roleid);

    accesslib_clear_all_caches(true);
}

/**
 * Ensure the manager role can assign the timed-access role.
 *
 * @param int $targetroleid
 * @return void
 */
function ensure_manager_can_assign_role(int $targetroleid): void {
    global $DB;

    if ($targetroleid <= 0) {
        return;
    }

    $managerrole = $DB->get_record('role', ['shortname' => 'manager'], '*', IGNORE_MISSING);
    if (!$managerrole) {
        return;
    }

    $exists = $DB->record_exists('role_allow_assign', [
        'roleid' => (int)$managerrole->id,
        'allowassign' => $targetroleid,
    ]);

    if ($exists) {
        return;
    }

    $record = new stdClass();
    $record->roleid = (int)$managerrole->id;
    $record->allowassign = $targetroleid;

    $DB->insert_record('role_allow_assign', $record);
}
