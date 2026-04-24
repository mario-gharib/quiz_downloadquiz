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
 * Event triggered when a quiz PDF is generated from the downloadquiz report.
 *
 * @package     quiz_downloadquiz
 * @copyright   2026 Center for Digital Innovation and Artificial Intelligence
 * @author      Center for Digital Innovation and Artificial Intelligence
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace quiz_downloadquiz\event;

/**
 * Event triggered when a user downloads the quiz answer-key PDF.
 */
final class quiz_downloaded extends \core\event\base {
    /**
     * Extra event data key for the quiz name.
     */
    private const OTHER_QUIZNAME = 'quizname';

    /**
     * Extra event data key for the quiz instance id.
     */
    private const OTHER_QUIZINSTANCEID = 'quizinstanceid';

    /**
     * Extra event data key for the course module id.
     */
    private const OTHER_CMID = 'cmid';

    /**
     * Initialise event metadata.
     *
     * @return void
     */
    protected function init(): void {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_TEACHING;
        $this->data['objecttable'] = 'quiz';
    }

    /**
     * Return the localized event name.
     *
     * @return string
     */
    public static function get_name(): string {
        return get_string('eventquizdownloaded', 'quiz_downloadquiz');
    }

    /**
     * Return a human-readable event description for logs.
     *
     * @return string
     */
    public function get_description(): string {
        $quizname = (string) ($this->other[self::OTHER_QUIZNAME] ?? '');
        $quizinstanceid = (int) ($this->other[self::OTHER_QUIZINSTANCEID] ?? $this->objectid);
        $cmid = (int) ($this->other[self::OTHER_CMID] ?? 0);

        return "The user with id '{$this->userid}' downloaded the quiz '{$quizname}' " .
            "with quiz instance id '{$quizinstanceid}' and course module id '{$cmid}' " .
            "for the course with id '{$this->courseid}'.";
    }

    /**
     * Return the event URL.
     *
     * @return \moodle_url
     */
    public function get_url(): \moodle_url {
        return new \moodle_url('/mod/quiz/report.php', [
            'id' => $this->contextinstanceid,
            'mode' => 'downloadquiz',
        ]);
    }

    /**
     * Validate required custom event data.
     *
     * @return void
     */
    protected function validate_data(): void {
        parent::validate_data();

        $this->validate_other_key_exists(self::OTHER_QUIZNAME);
        $this->validate_other_key_exists(self::OTHER_QUIZINSTANCEID);
        $this->validate_other_key_exists(self::OTHER_CMID);
    }

    /**
     * Ensure a required key exists in the event "other" payload.
     *
     * @param string $key
     * @return void
     */
    private function validate_other_key_exists(string $key): void {
        if (!array_key_exists($key, $this->other)) {
            throw new \coding_exception("The '{$key}' value must be set in other.");
        }
    }
}
