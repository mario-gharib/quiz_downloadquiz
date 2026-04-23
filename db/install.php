<?php
// This file is part of Moodle - https://moodle.org/.
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

/**
 * Install steps for quiz_downloadquiz.
 *
 * @package     quiz_downloadquiz
 * @copyright   2026 Center for Digital Innovation and Artificial Intelligence
 * @author      Center for Digital Innovation and Artificial Intelligence
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Install the quiz report record for the plugin.
 *
 * @return bool
 */
function xmldb_quiz_downloadquiz_install(): bool {
    global $DB;

    $record = $DB->get_record('quiz_reports', ['name' => 'downloadquiz'], '*', IGNORE_MISSING);

    if ($record) {
        $record->capability = 'quiz/downloadquiz:view';
        $record->displayorder = isset($record->displayorder) ? (int)$record->displayorder : 0;
        $DB->update_record('quiz_reports', $record);

        return true;
    }

    $record = new stdClass();
    $record->name = 'downloadquiz';
    $record->displayorder = 0;
    $record->capability = 'quiz/downloadquiz:view';

    $DB->insert_record('quiz_reports', $record);

    return true;
}