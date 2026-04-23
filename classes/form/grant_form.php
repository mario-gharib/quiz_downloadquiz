<?php
// This file is part of Moodle - https://moodle.org/.
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

/**
 * Grant management form for quiz_downloadquiz.
 *
 * @package     quiz_downloadquiz
 * @copyright   2026 Center for Digital Innovation and Artificial Intelligence
 * @author      Center for Digital Innovation and Artificial Intelligence
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace quiz_downloadquiz\form;

defined('MOODLE_INTERNAL') || die();

require_once($GLOBALS['CFG']->libdir . '/formslib.php');

/**
 * Form for creating or updating timed access grants.
 */
final class grant_form extends \moodleform {

    /**
     * Define the form elements.
     *
     * @return void
     */
    public function definition(): void {
        $mform = $this->_form;

        $this->add_user_email_element($mform);
        $this->add_expiry_time_element($mform);

        $this->add_action_buttons(false, get_string('savegrant', 'quiz_downloadquiz'));
    }

    /**
     * Validate submitted form data.
     *
     * @param array $data
     * @param array $files
     * @return array
     */
    public function validation($data, $files): array {
        global $DB;

        $errors = parent::validation($data, $files);

        $timeexpires = isset($data['timeexpires']) ? (int) $data['timeexpires'] : 0;
        if ($timeexpires <= time()) {
            $errors['timeexpires'] = get_string('errorexpiryinvalid', 'quiz_downloadquiz');
        }

        $useremail = '';
        if (!empty($data['useremail'])) {
            $useremail = trim(\core_text::strtolower((string) $data['useremail']));
        }

        if ($useremail !== '' && !$DB->record_exists('user', [
            'email' => $useremail,
            'deleted' => 0,
        ])) {
            $errors['useremail'] = get_string('errorusernotfound', 'quiz_downloadquiz');
        }

        return $errors;
    }

    /**
     * Add the user email field.
     *
     * @param \MoodleQuickForm $mform
     * @return void
     */
    private function add_user_email_element(\MoodleQuickForm $mform): void {
        $mform->addElement(
            'text',
            'useremail',
            get_string('useremail', 'quiz_downloadquiz'),
            ['size' => 50]
        );
        $mform->setType('useremail', PARAM_EMAIL);
        $mform->addRule('useremail', null, 'required', null, 'client');
        $mform->addRule('useremail', null, 'email', null, 'client');
    }

    /**
     * Add the expiry datetime selector.
     *
     * @param \MoodleQuickForm $mform
     * @return void
     */
    private function add_expiry_time_element(\MoodleQuickForm $mform): void {
        $mform->addElement(
            'date_time_selector',
            'timeexpires',
            get_string('expirestime', 'quiz_downloadquiz'),
            ['optional' => false]
        );
        $mform->addRule('timeexpires', null, 'required', null, 'client');
        $mform->setDefault('timeexpires', time() + DAYSECS);
    }
}