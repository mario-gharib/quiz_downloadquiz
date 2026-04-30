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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Quiz report implementation for quiz_downloadquiz.
 *
 * @package   quiz_downloadquiz
 * @copyright 2026 Center for Digital Innovation and Artificial Intelligence <moodle.cinia@usj.edu.lb>
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_quiz\local\reports\report_base;

/**
 * Quiz report implementation.
 */
class quiz_downloadquiz_report extends report_base {
    /**
     * Display the report page or process the PDF request.
     *
     * @param \stdClass $quiz The quiz record.
     * @param \cm_info $cm The course module.
     * @param \stdClass $course The course record.
     * @return bool
     */
    public function display($quiz, $cm, $course): bool {
        global $DB, $OUTPUT, $PAGE, $USER;

        $context = \context_module::instance($cm->id);

        \quiz_downloadquiz\local\access_manager::validate_base($course, $cm, $quiz, $context);

        $download = optional_param('download', 0, PARAM_BOOL);
        if ($download) {
            $this->process_pdf_request($quiz, $cm, $course, $context);
            return true;
        }

        $this->print_header_and_tabs($cm, $course, $quiz, 'downloadquiz');

        $PAGE->set_title(format_string($quiz->name) . ' - ' . get_string('pluginname', 'quiz_downloadquiz'));
        $PAGE->set_heading(format_string($course->fullname));

        echo $OUTPUT->notification(
            get_string('disclaimer', 'quiz_downloadquiz'),
            \core\output\notification::NOTIFY_ERROR,
        );

        $grantmanager = new \quiz_downloadquiz\local\grant_manager();
        $activegrant = $grantmanager->get_active_grant_for_user((int)$USER->id);

        if ($activegrant !== null) {
            echo $OUTPUT->notification(
                get_string('usergranttimeleft', 'quiz_downloadquiz', [
                    'remaining' => \quiz_downloadquiz\local\grant_manager::format_remaining_time(
                        (int)$activegrant->timeexpires
                    ),
                    'expireson' => userdate((int)$activegrant->timeexpires),
                ]),
                \core\output\notification::NOTIFY_INFO
            );
        }

        echo $OUTPUT->box(
            get_string('reportintro', 'quiz_downloadquiz'),
            'generalbox quiz-downloadquiz-intro'
        );

        echo $OUTPUT->notification(
            get_string('pdfkeywarning', 'quiz_downloadquiz'),
            'downloadquiz-warning'
        );

        echo $this->render_download_form($cm);

        return true;
    }

    /**
     * Process the PDF generation request.
     *
     * @param \stdClass $quiz The quiz record.
     * @param \cm_info $cm The course module.
     * @param \stdClass $course The course record.
     * @param \context_module $context The module context.
     * @return void
     */
    private function process_pdf_request(
        \stdClass $quiz,
        \cm_info $cm,
        \stdClass $course,
        \context_module $context
    ): void {
        global $DB, $USER;

        require_sesskey();

        \quiz_downloadquiz\local\access_manager::validate_base($course, $cm, $quiz, $context);

        $pdfkey = trim(optional_param('pdfkey', '', PARAM_ALPHANUMEXT));

        $this->validate_pdf_key($pdfkey);

        $extractor = new \quiz_downloadquiz\local\question_extractor();
        $data = $extractor->build_export_data($quiz, $cm, $course, $context);

        if (empty($data['questions'])) {
            throw new \moodle_exception('errornoquestions', 'quiz_downloadquiz');
        }

        $data['userfullname'] = fullname($USER);
        $data['useremail'] = $USER->email;
        $data['servertimezone'] = date_default_timezone_get();
        $data['navigationmethod'] = $this->resolve_navigation_method($quiz);
        $data['gradetopass'] = $this->resolve_grade_to_pass($DB, $quiz, $course);
        $data['attemptsallowed'] = $this->resolve_attempts_allowed($quiz);
        $data['assessmentcomposition'] = $this->build_assessment_composition($data['questions']);
        $data['pdfpassword'] = $pdfkey;

        $this->trigger_quiz_downloaded_event($quiz, $cm, $course, $context);

        $pdfbuilder = new \quiz_downloadquiz\local\pdf_builder();
        $pdfcontent = $pdfbuilder->build_pdf_content($data);

        $filename = $this->build_pdf_filename($quiz, $USER);
        $temppath = $this->write_pdf_to_temp_file($filename, $pdfcontent);

        $sent = $this->send_pdf_by_email($USER, $quiz, $course, $pdfkey, $temppath, $filename);

        @unlink($temppath);

        if (!$sent) {
            throw new \moodle_exception('errorpdfemailsend', 'quiz_downloadquiz');
        }

        redirect(
            new \moodle_url('/mod/quiz/report.php', [
                'id' => $cm->id,
                'mode' => 'downloadquiz',
            ]),
            get_string('pdfemailsent', 'quiz_downloadquiz', s($USER->email)),
            null,
            \core\output\notification::NOTIFY_SUCCESS
        );
    }

    /**
     * Validate the entered PDF key.
     *
     * @param string $pdfkey The submitted PDF key.
     * @return void
     */
    private function validate_pdf_key(string $pdfkey): void {
        if ($pdfkey === '') {
            throw new \moodle_exception('errorpdfkeyrequired', 'quiz_downloadquiz');
        }

        // Minimum length check.
        if (\core_text::strlen($pdfkey) < 6) {
            throw new \moodle_exception('errorpdfkeylength', 'quiz_downloadquiz');
        }

        // At least one uppercase letter.
        if (!preg_match('/[A-Z]/', $pdfkey)) {
            throw new \moodle_exception('errorpdfkeylength', 'quiz_downloadquiz');
        }

        // At least one digit.
        if (!preg_match('/[0-9]/', $pdfkey)) {
            throw new \moodle_exception('errorpdfkeylength', 'quiz_downloadquiz');
        }
    }

    /**
     * Resolve the quiz navigation method label.
     *
     * @param \stdClass $quiz The quiz record.
     * @return string
     */
    private function resolve_navigation_method(\stdClass $quiz): string {
        if (isset($quiz->navmethod) && $quiz->navmethod === 'seq') {
            return get_string('navmethod_sequential', 'quiz');
        }

        return get_string('navmethod_free', 'quiz');
    }

    /**
     * Resolve the grade to pass value.
     *
     * @param \moodle_database $db The Moodle database instance.
     * @param \stdClass $quiz The quiz record.
     * @param \stdClass $course The course record.
     * @return string
     */
    private function resolve_grade_to_pass(
        \moodle_database $db,
        \stdClass $quiz,
        \stdClass $course
    ): string {
        $gradeitem = $db->get_record(
            'grade_items',
            [
                'itemtype' => 'mod',
                'itemmodule' => 'quiz',
                'iteminstance' => (int)$quiz->id,
                'courseid' => (int)$course->id,
            ],
            'gradepass',
            IGNORE_MISSING
        );

        if ($gradeitem && $gradeitem->gradepass !== null) {
            return format_float((float)$gradeitem->gradepass, 2);
        }

        return get_string('notdefined', 'quiz_downloadquiz');
    }

    /**
     * Resolve the attempts allowed label.
     *
     * @param \stdClass $quiz The quiz record.
     * @return string
     */
    private function resolve_attempts_allowed(\stdClass $quiz): string {
        if ((int)$quiz->attempts === 0) {
            return get_string('unlimitedattempts', 'quiz_downloadquiz');
        }

        return (string)(int)$quiz->attempts;
    }

    /**
     * Build assessment composition metadata from question data.
     *
     * @param array $questions Exported question data.
     * @return array
     */
    private function build_assessment_composition(array $questions): array {
        $composition = [
            'totalquestions' => 0,
            'types' => [],
            'totalmarks' => 0.0,
        ];

        foreach ($questions as $question) {
            $qtype = (string)($question['qtype'] ?? 'unknown');
            $maxmark = (float)($question['maxmark'] ?? 0.0);

            $composition['totalquestions']++;
            $composition['totalmarks'] += $maxmark;

            if (!array_key_exists($qtype, $composition['types'])) {
                $composition['types'][$qtype] = [
                    'count' => 0,
                    'marks' => 0.0,
                ];
            }

            $composition['types'][$qtype]['count']++;
            $composition['types'][$qtype]['marks'] += $maxmark;
        }

        return $composition;
    }

    /**
     * Trigger the quiz downloaded event.
     *
     * @param \stdClass $quiz The quiz record.
     * @param \cm_info $cm The course module.
     * @param \stdClass $course The course record.
     * @param \context_module $context The module context.
     * @return void
     */
    private function trigger_quiz_downloaded_event(
        \stdClass $quiz,
        \cm_info $cm,
        \stdClass $course,
        \context_module $context
    ): void {
        $event = \quiz_downloadquiz\event\quiz_downloaded::create([
            'context' => $context,
            'objectid' => (int)$quiz->id,
            'courseid' => (int)$course->id,
            'other' => [
                'quizname' => $quiz->name,
                'quizinstanceid' => (int)$quiz->id,
                'cmid' => (int)$cm->id,
            ],
        ]);

        $event->trigger();
    }

    /**
     * Build the generated PDF filename.
     *
     * @param \stdClass $quiz The quiz record.
     * @param \stdClass $user The current user.
     * @return string
     */
    private function build_pdf_filename(\stdClass $quiz, \stdClass $user): string {
        $fullname = preg_replace('/\s+/', '_', clean_filename(fullname($user)));
        $quizname = preg_replace('/\s+/', '_', clean_filename((string)$quiz->name));
        $timestamp = userdate(time(), '%Y%m%d%H%M');

        return clean_filename($timestamp . '-' . $quizname . '-' . $fullname . '-Confidential.pdf');
    }

    /**
     * Write generated PDF content to a temporary file.
     *
     * @param string $filename The output filename.
     * @param string $pdfcontent The binary PDF content.
     * @return string
     */
    private function write_pdf_to_temp_file(string $filename, string $pdfcontent): string {
        $tempdir = make_request_directory();
        $temppath = $tempdir . '/' . $filename;

        if (file_put_contents($temppath, $pdfcontent) === false) {
            throw new \moodle_exception('errorpdfgeneration', 'quiz_downloadquiz');
        }

        return $temppath;
    }

    /**
     * Send the generated PDF to the current user by email.
     *
     * @param \stdClass $user The recipient user.
     * @param \stdClass $quiz The quiz record.
     * @param \stdClass $course The course record.
     * @param string $pdfkey The PDF password.
     * @param string $temppath The temporary file path.
     * @param string $filename The attachment filename.
     * @return bool
     */
    private function send_pdf_by_email(
        \stdClass $user,
        \stdClass $quiz,
        \stdClass $course,
        string $pdfkey,
        string $temppath,
        string $filename
    ): bool {
        $subject = get_string('emailsubject', 'quiz_downloadquiz', (object)[
            'quizname' => format_string($quiz->name),
            'filename' => $filename,
        ]);

        $messagetext = get_string('emailbodytext', 'quiz_downloadquiz', (object)[
            'fullname' => fullname($user),
            'quizname' => format_string($quiz->name),
            'coursename' => format_string($course->fullname),
            'pdfkey' => $pdfkey,
            'generateddate' => userdate(time()),
        ]);

        $isrtl = right_to_left();
        $currentlang = current_language();

        if ($isrtl) {
            $messagehtml = \html_writer::tag(
                'div',
                text_to_html($messagetext, false, false, true),
                [
                    'dir' => 'rtl',
                    'lang' => $currentlang,
                    'style' => 'direction: rtl; text-align: right; unicode-bidi: embed; font-family: Tahoma, Arial, sans-serif;',
                ]
            );
        } else {
            $messagehtml = \html_writer::tag(
                'div',
                text_to_html($messagetext, false, false, true),
                [
                    'dir' => 'ltr',
                    'lang' => $currentlang,
                    'style' => 'direction: ltr; text-align: left;',
                ]
            );
        }

        $fromuser = \core_user::get_support_user();

        return email_to_user(
            $user,
            $fromuser,
            $subject,
            $messagetext,
            $messagehtml,
            $temppath,
            $filename,
            true
        );
    }

    /**
     * Render the download form.
     *
     * @param \cm_info $cm The course module.
     * @return string
     */
    private function render_download_form(\cm_info $cm): string {
        global $OUTPUT, $PAGE;

        $requiredicon = $OUTPUT->pix_icon(
            'req',
            get_string('required'),
            'core',
            ['class' => 'req']
        );

        $showiconurl = $OUTPUT->image_url('t/show')->out(false);
        $hideiconurl = $OUTPUT->image_url('t/hide')->out(false);

        $templatedata = [
            'actionurl' => (new \moodle_url('/mod/quiz/report.php'))->out(false),
            'cmid' => $cm->id,
            'sesskey' => sesskey(),
            'label' => get_string('pdfkey', 'quiz_downloadquiz') .
                \html_writer::span($requiredicon, 'ms-1'),
            'placeholder' => get_string('pdfkeyplaceholder', 'quiz_downloadquiz'),
            'generatelabel' => get_string('generatepdfkey', 'quiz_downloadquiz'),
            'showlabel' => get_string('showpdfkey', 'quiz_downloadquiz'),
            'hidelabel' => get_string('hidepdfkey', 'quiz_downloadquiz'),
            'downloadlabel' => get_string('downloadpdf', 'quiz_downloadquiz'),
            'generateicon' => $OUTPUT->pix_icon('t/reload', get_string('generatepdfkey', 'quiz_downloadquiz')),
            'showicon' => $OUTPUT->pix_icon('t/show', get_string('showpdfkey', 'quiz_downloadquiz')),
        ];

        $PAGE->requires->js_call_amd('quiz_downloadquiz/downloadform', 'init', [[
            'showIcon' => $showiconurl,
            'hideIcon' => $hideiconurl,
            'showLabel' => get_string('showpdfkey', 'quiz_downloadquiz'),
            'hideLabel' => get_string('hidepdfkey', 'quiz_downloadquiz'),
            'requiredError' => get_string('errorpdfkeyrequiredclient', 'quiz_downloadquiz'),
            'lengthError' => get_string('errorpdfkeylength', 'quiz_downloadquiz'),
        ]]);

        return $OUTPUT->render_from_template('quiz_downloadquiz/download_form', $templatedata);
    }
}
