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
 * Uninstall steps for quiz_downloadquiz.
 *
 * @package     quiz_downloadquiz
 * @copyright   2026 Center for Digital Innovation and Artificial Intelligence
 * @author      Center for Digital Innovation and Artificial Intelligence
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/lib/accesslib.php');

/**
 * Pre-uninstall hook for quiz_downloadquiz.
 *
 * Deletes the plugin-specific timed-access role if it exists.
 *
 * @return bool
 */
function xmldb_quiz_downloadquiz_uninstall(): bool {
    global $DB;

    $shortname = 'downloadquizaccess';

    $role = $DB->get_record('role', ['shortname' => $shortname], '*', IGNORE_MISSING);
    if (!$role) {
        return true;
    }

    // Remove all assignments of this role everywhere in Moodle.
    role_unassign_all(['roleid' => (int) $role->id]);

    // Delete any role capability overrides and related metadata.
    delete_role((int) $role->id);

    // Clear plugin config reference if still present before plugin removal completes.
    unset_config('grantroleid', 'quiz_downloadquiz');

    accesslib_clear_all_caches(true);

    return true;
}
