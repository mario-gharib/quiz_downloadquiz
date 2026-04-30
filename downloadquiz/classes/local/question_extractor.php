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
 * Extract quiz questions and answer-key data.
 *
 * @package   quiz_downloadquiz
 * @copyright 2026 Center for Digital Innovation and Artificial Intelligence <moodle.cinia@usj.edu.lb>
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace quiz_downloadquiz\local;

/**
 * Extracts exportable quiz structure and answer data.
 */
final class question_extractor {
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
     * Build the full export payload.
     *
     * @param \stdClass $quiz
     * @param \cm_info $cm
     * @param \stdClass $course
     * @param \context_module $context
     * @return array
     */
    public function build_export_data(
        \stdClass $quiz,
        \cm_info $cm,
        \stdClass $course,
        \context_module $context
    ): array {
        $questions = $this->get_quiz_questions((int) $quiz->id, $context);

        return [
            'coursefullname' => format_string($course->fullname, true, ['context' => $context]),
            'quizname' => format_string($quiz->name, true, ['context' => $context]),
            'quizintro' => $this->format_quiz_intro($quiz, $context),
            'generatedat' => userdate(time()),
            'quizstartdate' => $this->format_optional_date($quiz->timeopen ?? 0),
            'quizenddate' => $this->format_optional_date($quiz->timeclose ?? 0),
            'timelimit' => $this->format_time_limit($quiz),
            'quizmaximumgrade' => $this->format_quiz_maximum_grade($quiz),
            'shufflewithinquestions' => $this->format_yes_no(!empty($quiz->shuffleanswers)),
            'quizpassword' => $this->format_yes_no(!empty($quiz->password)),
            'blockconcurrentconnections' => $this->resolve_block_concurrent_connections_value($quiz),
            'networkrestriction' => $this->format_yes_no(!empty($quiz->subnet)),
            'safeexambrowser' => $this->resolve_safe_exam_browser_value($quiz),
            'questions' => $questions,
        ];
    }

    /**
     * Format quiz introduction for PDF.
     *
     * @param \stdClass $quiz
     * @param \context_module $context
     * @return string
     */
    private function format_quiz_intro(\stdClass $quiz, \context_module $context): string {
        if (empty($quiz->intro)) {
            return '';
        }

        return html_helper::normalise_for_pdf(
            format_text(
                $quiz->intro,
                $quiz->introformat,
                [
                    'context' => $context,
                    'para' => false,
                    'overflowdiv' => true,
                ]
            )
        );
    }

    /**
     * Format an optional Unix timestamp.
     *
     * @param int $timestamp
     * @return string
     */
    private function format_optional_date(int $timestamp): string {
        if ($timestamp <= 0) {
            return get_string('notset', 'quiz_downloadquiz');
        }

        return userdate($timestamp);
    }

    /**
     * Format quiz time limit.
     *
     * @param \stdClass $quiz
     * @return string
     */
    private function format_time_limit(\stdClass $quiz): string {
        if (empty($quiz->timelimit)) {
            return get_string('notset', 'quiz_downloadquiz');
        }

        return format_time((int) $quiz->timelimit);
    }

    /**
     * Format quiz maximum grade.
     *
     * @param \stdClass $quiz
     * @return string
     */
    private function format_quiz_maximum_grade(\stdClass $quiz): string {
        if (!isset($quiz->grade)) {
            return get_string('notset', 'quiz_downloadquiz');
        }

        return format_float((float) $quiz->grade, 2);
    }

    /**
     * Format a boolean as a localized yes/no string.
     *
     * @param bool $value
     * @return string
     */
    private function format_yes_no(bool $value): string {
        return $value
            ? get_string('yes', 'quiz_downloadquiz')
            : get_string('no', 'quiz_downloadquiz');
    }

    /**
     * Resolve Safe Exam Browser status for metadata.
     *
     * @param \stdClass $quiz
     * @return string
     */
    private function resolve_safe_exam_browser_value(\stdClass $quiz): string {
        if (!empty($quiz->browsersecurity) && $quiz->browsersecurity !== '-') {
            return get_string('yes', 'quiz_downloadquiz');
        }

        $dbman = $this->db->get_manager();
        if (
                $dbman->table_exists('quizaccess_seb_quizsettings') &&
                $this->db->record_exists(
                    'quizaccess_seb_quizsettings',
                    [
                        'quizid' => (int) $quiz->id,
                    ]
                )
        ) {
            return get_string('yes', 'quiz_downloadquiz');
        }

        return get_string('no', 'quiz_downloadquiz');
    }

    /**
     * Resolve block concurrent connections status for metadata.
     *
     * @param \stdClass $quiz
     * @return string
     */
    private function resolve_block_concurrent_connections_value(\stdClass $quiz): string {
        if (!empty($quiz->subnet)) {
            return get_string('yes', 'quiz_downloadquiz');
        }

        $dbman = $this->db->get_manager();
        if (
                $dbman->table_exists('quizaccess_onesession') &&
                $this->db->record_exists(
                    'quizaccess_onesession',
                    [
                        'quizid' => (int) $quiz->id,
                    ]
                )
        ) {
            return get_string('yes', 'quiz_downloadquiz');
        }

        return get_string('no', 'quiz_downloadquiz');
    }

    /**
     * Get quiz questions in slot order, resolved to the referenced version.
     *
     * @param int $quizid
     * @param \context_module $context
     * @return array
     */
    private function get_quiz_questions(int $quizid, \context_module $context): array {
        $sql = "SELECT
                    qs.id AS slotid,
                    qs.slot,
                    qs.page,
                    qs.maxmark,
                    q.id AS questionid,
                    q.name,
                    q.qtype,
                    q.questiontext,
                    q.questiontextformat,
                    qc.contextid AS questioncontextid,
                    qsec.id AS sectionid,
                    qsec.heading AS sectionheading,
                    qsec.shufflequestions AS sectionshuffle
                  FROM {quiz_slots} qs
                  JOIN {quiz_sections} qsec
                    ON qsec.quizid = qs.quizid
                   AND qs.slot >= qsec.firstslot
                   AND qs.slot < COALESCE(
                        (
                            SELECT MIN(qsec2.firstslot)
                              FROM {quiz_sections} qsec2
                             WHERE qsec2.quizid = qsec.quizid
                               AND qsec2.firstslot > qsec.firstslot
                        ),
                        999999
                   )
                  JOIN {question_references} qr
                    ON qr.component = 'mod_quiz'
                   AND qr.questionarea = 'slot'
                   AND qr.itemid = qs.id
                  JOIN {question_bank_entries} qbe
                    ON qbe.id = qr.questionbankentryid
                  JOIN {question_versions} qv
                    ON qv.questionbankentryid = qbe.id
                  JOIN {question} q
                    ON q.id = qv.questionid
                  JOIN {question_categories} qc
                    ON qc.id = qbe.questioncategoryid
                 WHERE qs.quizid = :quizid
                   AND qv.version = COALESCE(
                        qr.version,
                        (
                            SELECT MAX(qv2.version)
                              FROM {question_versions} qv2
                             WHERE qv2.questionbankentryid = qbe.id
                        )
                   )
              ORDER BY qs.slot ASC";

        $records = $this->db->get_records_sql($sql, ['quizid' => $quizid]);

        $questions = [];
        foreach ($records as $record) {
            $questions[] = $this->normalise_question($record, $context);
        }

        return $questions;
    }

    /**
     * Convert one raw question record into exportable structure.
     *
     * @param \stdClass $record
     * @param \context_module $context
     * @return array
     */
    private function normalise_question(\stdClass $record, \context_module $context): array {
        $questioncontext = \context::instance_by_id((int) $record->questioncontextid, MUST_EXIST);

        $rewrittenquestiontext = file_rewrite_pluginfile_urls(
            (string) $record->questiontext,
            'pluginfile.php',
            $questioncontext->id,
            'question',
            'questiontext',
            (int) $record->questionid
        );

        $renderedquestionhtml = format_text(
            $rewrittenquestiontext,
            (int) $record->questiontextformat,
            [
                'context' => $questioncontext,
                'para' => false,
                'overflowdiv' => true,
            ]
        );

        $data = [
            'slot' => (int) $record->slot,
            'page' => (int) $record->page,
            'maxmark' => (float) $record->maxmark,
            'questionid' => (int) $record->questionid,
            'questioncontextid' => (int) $record->questioncontextid,
            'sectionid' => isset($record->sectionid) ? (int) $record->sectionid : 0,
            'sectiontitle' => trim((string) ($record->sectionheading ?? '')),
            'sectionshuffle' => !empty($record->sectionshuffle)
                ? get_string('yes', 'quiz_downloadquiz')
                : get_string('no', 'quiz_downloadquiz'),
            'name' => format_string($record->name, true, ['context' => $questioncontext]),
            'qtype' => (string) $record->qtype,
            'questionhtml' => html_helper::normalise_for_pdf($renderedquestionhtml),
            'rawquestiontext' => (string) $record->questiontext,
            'questionimages' => $this->extract_image_sources($renderedquestionhtml),
            'answers' => [],
            'answerlabel' => get_string('correctanswer', 'quiz_downloadquiz'),
            'notes' => [],
        ];

        $data = $this->extract_question_answers($data, (int) $record->questionid, $context);

        if (empty($data['answers']) && empty($data['notes'])) {
            $data['notes'][] = get_string('noansweravailable', 'quiz_downloadquiz');
        }

        return $data;
    }

    /**
     * Extract answer data according to the question type.
     *
     * @param array $data
     * @param int $questionid
     * @param \context_module $context
     * @return array
     */
    private function extract_question_answers(array $data, int $questionid, \context_module $context): array {
        switch ($data['qtype']) {
            case 'multichoice':
                return $this->extract_multichoice($data, $questionid);

            case 'truefalse':
                return $this->extract_truefalse($data, $questionid);

            case 'shortanswer':
                return $this->extract_shortanswer($data, $questionid);

            case 'match':
                return $this->extract_match($data, $questionid);

            case 'numerical':
                return $this->extract_numerical($data, $questionid);

            case 'calculated':
                return $this->extract_calculated($data, $questionid);

            case 'calculatedmulti':
                return $this->extract_calculatedmulti($data, $questionid);

            case 'calculatedsimple':
                return $this->extract_calculatedsimple($data, $questionid);

            case 'multianswer':
                return $this->extract_cloze($data);

            case 'ddwtos':
                return $this->extract_ddwtos($data, $questionid);

            case 'ddmarker':
                return $this->extract_ddmarker($data, $questionid);

            case 'ddimageortext':
                return $this->extract_ddimageortext($data, $questionid);

            case 'ordering':
                return $this->extract_ordering($data, $questionid);

            case 'gapselect':
                return $this->extract_gapselect($data, $questionid);

            case 'essay':
                $data['notes'][] = get_string('fallbackessay', 'quiz_downloadquiz');
                return $data;

            case 'description':
                $data['notes'][] = get_string('fallbackdescription', 'quiz_downloadquiz');
                return $data;

            case 'random':
                $data['notes'][] = get_string('unsupportedrandom', 'quiz_downloadquiz');
                return $data;

            default:
                $data['notes'][] = get_string('unsupportedqtype', 'quiz_downloadquiz');
                return $data;
        }
    }

    /**
     * Format question-related HTML after rewriting Moodle file URLs.
     *
     * @param string $text
     * @param int $format
     * @param int $questioncontextid
     * @param string $filearea
     * @param int $itemid
     * @return string
     */
    private function format_question_html(
        string $text,
        int $format,
        int $questioncontextid,
        string $filearea,
        int $itemid
    ): string {
        $questioncontext = \context::instance_by_id($questioncontextid, MUST_EXIST);

        $rewritten = file_rewrite_pluginfile_urls(
            $text,
            'pluginfile.php',
            $questioncontext->id,
            'question',
            $filearea,
            $itemid
        );

        return html_helper::normalise_for_pdf(
            format_text(
                $rewritten,
                $format,
                [
                    'context' => $questioncontext,
                    'para' => false,
                    'overflowdiv' => true,
                ]
            )
        );
    }

    /**
     * Extract multichoice answers.
     *
     * @param array $data
     * @param int $questionid
     * @return array
     */
    private function extract_multichoice(array $data, int $questionid): array {
        $sql = "SELECT qa.id, qa.answer, qa.answerformat, qa.fraction, qmo.single, qmo.shuffleanswers
                  FROM {question_answers} qa
                  JOIN {qtype_multichoice_options} qmo
                    ON qmo.questionid = qa.question
                 WHERE qa.question = :questionid
              ORDER BY qa.id ASC";

        $rows = $this->db->get_records_sql($sql, ['questionid' => $questionid]);
        if (empty($rows)) {
            $data['notes'][] = get_string('noansweravailable', 'quiz_downloadquiz');
            return $data;
        }

        $single = null;
        $shuffleanswers = 0;
        $maxfraction = null;

        foreach ($rows as $row) {
            $single = (int) $row->single;
            $shuffleanswers = isset($row->shuffleanswers) ? (int) $row->shuffleanswers : 0;

            if ($maxfraction === null || (float) $row->fraction > $maxfraction) {
                $maxfraction = (float) $row->fraction;
            }
        }

        $data['ismultiple'] = !$single;
        $data['notes'][] = get_string(
            'mcqanswertype',
            'quiz_downloadquiz',
            $single
                ? get_string('mcqsingle', 'quiz_downloadquiz')
                : get_string('mcqmultiple', 'quiz_downloadquiz')
        );
        $data['notes'][] = get_string(
            'shufflechoicesstatus',
            'quiz_downloadquiz',
            $this->format_yes_no(!empty($shuffleanswers))
        );

        foreach ($rows as $row) {
            $fraction = (float) $row->fraction;

            $data['answers'][] = [
                'label' => $this->format_question_html(
                    (string) $row->answer,
                    (int) $row->answerformat,
                    (int) $data['questioncontextid'],
                    'answer',
                    (int) $row->id
                ),
                'iscorrect' => ($fraction > 0.0),
                'ispartial' => ($fraction > 0.0 && $fraction < $maxfraction),
                'fraction' => $fraction,
            ];
        }

        $correctcount = count(array_filter($data['answers'], static function (array $item): bool {
            return !empty($item['iscorrect']);
        }));

        if ($correctcount > 1) {
            $data['answerlabel'] = get_string('correctanswers', 'quiz_downloadquiz');
            $data['notes'][] = get_string('fallbackmultianswer', 'quiz_downloadquiz');
        }

        return $data;
    }

    /**
     * Extract true/false answer.
     *
     * @param array $data
     * @param int $questionid
     * @return array
     */
    private function extract_truefalse(array $data, int $questionid): array {
        $sql = "SELECT qa.id, qa.answer, qa.answerformat, qa.fraction
                  FROM {question_answers} qa
                 WHERE qa.question = :questionid
              ORDER BY qa.id ASC";

        $rows = $this->db->get_records_sql($sql, ['questionid' => $questionid]);

        foreach ($rows as $row) {
            $raw = strtolower(trim(strip_tags((string) $row->answer)));

            if ($raw === 'true' || $raw === '1') {
                $label = get_string('true', 'quiz_downloadquiz');
            } else if ($raw === 'false' || $raw === '0') {
                $label = get_string('false', 'quiz_downloadquiz');
            } else {
                // Fallback (very rare).
                $label = $this->format_question_html(
                    (string) $row->answer,
                    (int) $row->answerformat,
                    (int) $data['questioncontextid'],
                    'answer',
                    (int) $row->id
                );
            }

            $data['answers'][] = [
                'label' => $label,
                'iscorrect' => ((float) $row->fraction > 0.0),
                'fraction' => (float) $row->fraction,
            ];
        }

        if (empty($data['answers'])) {
            $data['notes'][] = get_string('noansweravailable', 'quiz_downloadquiz');
        }

        return $data;
    }

    /**
     * Extract shortanswer accepted answers.
     *
     * @param array $data
     * @param int $questionid
     * @return array
     */
    private function extract_shortanswer(array $data, int $questionid): array {
        $sql = "SELECT qa.id, qa.answer, qa.answerformat, qa.fraction
                  FROM {question_answers} qa
                 WHERE qa.question = :questionid
                   AND qa.fraction > 0
              ORDER BY qa.id ASC";

        $rows = $this->db->get_records_sql($sql, ['questionid' => $questionid]);
        $data['answerlabel'] = get_string('acceptedanswers', 'quiz_downloadquiz');

        foreach ($rows as $row) {
            $data['answers'][] = [
                'label' => $this->format_question_html(
                    (string) $row->answer,
                    (int) $row->answerformat,
                    (int) $data['questioncontextid'],
                    'answer',
                    (int) $row->id
                ),
                'iscorrect' => true,
                'fraction' => (float) $row->fraction,
            ];
        }

        if (count($data['answers']) > 1) {
            $data['notes'][] = get_string('fallbackmultianswer', 'quiz_downloadquiz');
        }

        if (empty($data['answers'])) {
            $data['notes'][] = get_string('noansweravailable', 'quiz_downloadquiz');
        }

        return $data;
    }

    /**
     * Extract matching pairs.
     *
     * @param array $data
     * @param int $questionid
     * @return array
     */
    private function extract_match(array $data, int $questionid): array {
        $sql = "SELECT id, questiontext, questiontextformat, answertext
                  FROM {qtype_match_subquestions}
                 WHERE questionid = :questionid
                   AND answertext IS NOT NULL
                   AND answertext <> ''
              ORDER BY id ASC";

        $rows = $this->db->get_records_sql($sql, ['questionid' => $questionid]);
        $data['answerlabel'] = get_string('matchingpairs', 'quiz_downloadquiz');

        $matchoptions = $this->db->get_record(
            'qtype_match_options',
            ['questionid' => $questionid],
            'shuffleanswers',
            IGNORE_MISSING
        );

        $data['notes'][] = get_string(
            'shufflechoicesstatus',
            'quiz_downloadquiz',
            $this->format_yes_no($matchoptions && !empty($matchoptions->shuffleanswers))
        );

        foreach ($rows as $row) {
            $left = $this->format_question_html(
                (string) $row->questiontext,
                (int) $row->questiontextformat,
                (int) $data['questioncontextid'],
                'subquestion',
                (int) $row->id
            );

            $data['answers'][] = [
                'label' => $left . ' → ' . s((string) $row->answertext),
                'iscorrect' => true,
            ];
        }

        if (empty($data['answers'])) {
            $data['notes'][] = get_string('noansweravailable', 'quiz_downloadquiz');
        }

        return $data;
    }

    /**
     * Extract numerical accepted answers.
     *
     * @param array $data
     * @param int $questionid
     * @return array
     */
    private function extract_numerical(array $data, int $questionid): array {
        $sql = "SELECT qa.id, qa.answer, qa.answerformat, qa.fraction
                  FROM {question_answers} qa
                 WHERE qa.question = :questionid
                   AND qa.fraction > 0
              ORDER BY qa.id ASC";

        $rows = $this->db->get_records_sql($sql, ['questionid' => $questionid]);
        $data['answerlabel'] = get_string('acceptedanswers', 'quiz_downloadquiz');

        foreach ($rows as $row) {
            $data['answers'][] = [
                'label' => $this->format_question_html(
                    (string) $row->answer,
                    (int) $row->answerformat,
                    (int) $data['questioncontextid'],
                    'answer',
                    (int) $row->id
                ),
                'iscorrect' => true,
                'fraction' => (float) $row->fraction,
            ];
        }

        if (count($data['answers']) > 1) {
            $data['notes'][] = get_string('fallbackmultianswer', 'quiz_downloadquiz');
        }

        if (empty($data['answers'])) {
            $data['notes'][] = get_string('noansweravailable', 'quiz_downloadquiz');
        }

        return $data;
    }

    /**
     * Extract calculated accepted answers.
     *
     * @param array $data
     * @param int $questionid
     * @return array
     */
    private function extract_calculated(array $data, int $questionid): array {
        $sql = "SELECT qa.id, qa.answer, qa.answerformat, qa.fraction
                  FROM {question_answers} qa
                 WHERE qa.question = :questionid
                   AND qa.fraction > 0
              ORDER BY qa.id ASC";

        $rows = $this->db->get_records_sql($sql, ['questionid' => $questionid]);
        $data['answerlabel'] = get_string('acceptedanswers', 'quiz_downloadquiz');

        foreach ($rows as $row) {
            $data['answers'][] = [
                'label' => $this->format_question_html(
                    (string) $row->answer,
                    (int) $row->answerformat,
                    (int) $data['questioncontextid'],
                    'answer',
                    (int) $row->id
                ),
                'iscorrect' => true,
                'fraction' => (float) $row->fraction,
            ];
        }

        if (count($data['answers']) > 1) {
            $data['notes'][] = get_string('fallbackmultianswer', 'quiz_downloadquiz');
        }

        if (empty($data['answers'])) {
            $data['notes'][] = get_string('noansweravailable', 'quiz_downloadquiz');
        }

        return $data;
    }

    /**
     * Extract calculated multichoice answers.
     *
     * @param array $data
     * @param int $questionid
     * @return array
     */
    private function extract_calculatedmulti(array $data, int $questionid): array {
        $sql = "SELECT qa.id, qa.answer, qa.answerformat, qa.fraction
                  FROM {question_answers} qa
                 WHERE qa.question = :questionid
              ORDER BY qa.id ASC";

        $rows = $this->db->get_records_sql($sql, ['questionid' => $questionid]);

        $options = $this->db->get_record(
            'question_calculated_options',
            ['question' => $questionid],
            'single, shuffleanswers',
            IGNORE_MISSING
        );

        $single = ($options && isset($options->single)) ? (int) $options->single : 1;
        $shuffleanswers = ($options && isset($options->shuffleanswers)) ? (int) $options->shuffleanswers : 0;

        $data['ismultiple'] = !$single;
        $data['answerlabel'] = get_string('correctanswer', 'quiz_downloadquiz');
        $data['notes'][] = get_string(
            'mcqanswertype',
            'quiz_downloadquiz',
            $single
                ? get_string('mcqsingle', 'quiz_downloadquiz')
                : get_string('mcqmultiple', 'quiz_downloadquiz')
        );
        $data['notes'][] = get_string(
            'shufflechoicesstatus',
            'quiz_downloadquiz',
            $this->format_yes_no(!empty($shuffleanswers))
        );

        foreach ($rows as $row) {
            $fraction = (float) $row->fraction;

            $data['answers'][] = [
                'label' => $this->format_question_html(
                    (string) $row->answer,
                    (int) $row->answerformat,
                    (int) $data['questioncontextid'],
                    'answer',
                    (int) $row->id
                ),
                'iscorrect' => ($fraction > 0.0),
                'ispartial' => false,
                'fraction' => $fraction,
            ];
        }

        $correctcount = count(array_filter($data['answers'], static function (array $item): bool {
            return !empty($item['iscorrect']);
        }));

        if ($correctcount > 1) {
            $data['answerlabel'] = get_string('correctanswers', 'quiz_downloadquiz');
            $data['notes'][] = get_string('fallbackmultianswer', 'quiz_downloadquiz');
        }

        if (empty($data['answers'])) {
            $data['notes'][] = get_string('noansweravailable', 'quiz_downloadquiz');
        }

        return $data;
    }

    /**
     * Extract calculated simple accepted answers.
     *
     * @param array $data
     * @param int $questionid
     * @return array
     */
    private function extract_calculatedsimple(array $data, int $questionid): array {
        $sql = "SELECT qa.id, qa.answer, qa.answerformat, qa.fraction
                  FROM {question_answers} qa
                 WHERE qa.question = :questionid
                   AND qa.fraction > 0
              ORDER BY qa.id ASC";

        $rows = $this->db->get_records_sql($sql, ['questionid' => $questionid]);
        $data['answerlabel'] = get_string('acceptedanswers', 'quiz_downloadquiz');

        foreach ($rows as $row) {
            $data['answers'][] = [
                'label' => $this->format_question_html(
                    (string) $row->answer,
                    (int) $row->answerformat,
                    (int) $data['questioncontextid'],
                    'answer',
                    (int) $row->id
                ),
                'iscorrect' => true,
                'fraction' => (float) $row->fraction,
            ];
        }

        if (count($data['answers']) > 1) {
            $data['notes'][] = get_string('fallbackmultianswer', 'quiz_downloadquiz');
        }

        if (empty($data['answers'])) {
            $data['notes'][] = get_string('noansweravailable', 'quiz_downloadquiz');
        }

        return $data;
    }

    /**
     * Extract Cloze (Embedded answers) question.
     *
     * @param array $data
     * @return array
     */
    private function extract_cloze(array $data): array {
        $data['answers'] = [];
        $data['answerlabel'] = null;

        $multianswer = $this->db->get_record(
            'question_multianswer',
            ['question' => (int) $data['questionid']],
            'sequence',
            IGNORE_MISSING
        );

        if (!$multianswer || empty($multianswer->sequence)) {
            return $data;
        }

        $ids = array_filter(array_map('trim', explode(',', (string) $multianswer->sequence)));
        if (empty($ids)) {
            return $data;
        }

        [$insql, $params] = $this->db->get_in_or_equal(
            array_map('intval', $ids),
            SQL_PARAMS_QM
        );

        $records = $this->db->get_records_sql(
            "SELECT id, qtype, questiontext
               FROM {question}
              WHERE id $insql",
            $params
        );

        if (empty($records)) {
            return $data;
        }

        $data['notes'][] = get_string('clozefields', 'quiz_downloadquiz');

        $position = 1;
        foreach ($ids as $id) {
            $id = (int) $id;

            if (empty($records[$id])) {
                $position++;
                continue;
            }

            $definition = html_entity_decode((string) $records[$id]->questiontext, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $definition = trim(preg_replace('/\s+/', ' ', strip_tags($definition)));

            if ($definition !== '') {
                $data['notes'][] = '{#' . $position . '} ' . $definition;
            }

            $position++;
        }

        return $data;
    }

    /**
     * Extract Drag-and-drop into text answers.
     *
     * @param array $data
     * @param int $questionid
     * @return array
     */
    private function extract_ddwtos(array $data, int $questionid): array {
        $data['answers'] = [];
        $data['answerlabel'] = get_string('correctanswers', 'quiz_downloadquiz');

        $sql = "SELECT qa.id, qa.answer, qa.answerformat
                  FROM {question_answers} qa
                 WHERE qa.question = :questionid
              ORDER BY qa.id ASC";

        $rows = $this->db->get_records_sql($sql, ['questionid' => $questionid]);
        if (empty($rows)) {
            $data['notes'][] = get_string('noansweravailable', 'quiz_downloadquiz');
            return $data;
        }

        $choices = [];
        $index = 1;

        foreach ($rows as $row) {
            $choices[$index] = $this->format_question_html(
                (string) $row->answer,
                (int) $row->answerformat,
                (int) $data['questioncontextid'],
                'answer',
                (int) $row->id
            );
            $index++;
        }

        $questiontext = html_entity_decode((string) ($data['rawquestiontext'] ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        if (!preg_match_all('/\[\[(\d+)\]\]/', $questiontext, $matches)) {
            $data['notes'][] = get_string('noansweravailable', 'quiz_downloadquiz');
            return $data;
        }

        $blank = 1;
        foreach ($matches[1] as $choiceindex) {
            $choiceindex = (int) $choiceindex;

            if (empty($choices[$choiceindex])) {
                $data['notes'][] = '[[' . $blank . ']]: ' . get_string('noansweravailable', 'quiz_downloadquiz');
            } else {
                $data['answers'][] = [
                    'label' => '[[' . $blank . ']] → ' . $choices[$choiceindex],
                    'iscorrect' => true,
                ];
            }

            $blank++;
        }

        if (empty($data['answers'])) {
            $data['notes'][] = get_string('noansweravailable', 'quiz_downloadquiz');
        }

        return $data;
    }

    /**
     * Resolve ddmarker background image URL.
     *
     * @param int $questionid
     * @param int $contextid
     * @return string|null
     */
    private function resolve_ddmarker_background_image(int $questionid, int $contextid): ?string {
        $fs = get_file_storage();

        $files = $fs->get_area_files(
            $contextid,
            'qtype_ddmarker',
            'bgimage',
            $questionid,
            'itemid, filepath, filename',
            false
        );

        if (empty($files)) {
            return null;
        }

        $file = reset($files);

        return \moodle_url::make_pluginfile_url(
            $file->get_contextid(),
            $file->get_component(),
            $file->get_filearea(),
            $file->get_itemid(),
            $file->get_filepath(),
            $file->get_filename()
        )->out(false);
    }

    /**
     * Extract drag-and-drop markers as overlay-ready data.
     *
     * @param array $data
     * @param int $questionid
     * @return array
     */
    private function extract_ddmarker(array $data, int $questionid): array {
        $data['answers'] = [];
        $data['answerlabel'] = null;
        $data['notes'] = [];
        $data['ddmarkeroverlay'] = [
            'backgroundimage' => null,
            'drops' => [],
        ];

        $drags = $this->db->get_records('qtype_ddmarker_drags', ['questionid' => $questionid], 'no ASC');
        $drops = $this->db->get_records('qtype_ddmarker_drops', ['questionid' => $questionid], 'no ASC');

        if (empty($drags) || empty($drops)) {
            return $data;
        }

        $dragmap = [];
        foreach ($drags as $drag) {
            $dragmap[(int) $drag->no] = trim((string) $drag->label);
        }

        foreach ($drops as $drop) {
            $choice = (int) $drop->choice;

            $data['ddmarkeroverlay']['drops'][] = [
                'no' => (int) $drop->no,
                'shape' => (string) $drop->shape,
                'coords' => (string) $drop->coords,
                'label' => $dragmap[$choice] ?? '',
            ];
        }

        $data['ddmarkeroverlay']['backgroundimage'] = $this->resolve_ddmarker_background_image(
            $questionid,
            (int) $data['questioncontextid']
        );

        return $data;
    }

    /**
     * Resolve ddimageortext background image URL.
     *
     * @param int $questionid
     * @param int $contextid
     * @return string|null
     */
    private function resolve_ddimageortext_background_image(int $questionid, int $contextid): ?string {
        $fs = get_file_storage();

        $files = $fs->get_area_files(
            $contextid,
            'qtype_ddimageortext',
            'bgimage',
            $questionid,
            'itemid, filepath, filename',
            false
        );

        if (empty($files)) {
            return null;
        }

        $file = reset($files);

        return \moodle_url::make_pluginfile_url(
            $file->get_contextid(),
            $file->get_component(),
            $file->get_filearea(),
            $file->get_itemid(),
            $file->get_filepath(),
            $file->get_filename()
        )->out(false);
    }

    /**
     * Resolve ddimageortext drag image URL.
     *
     * @param int $dragid
     * @param int $contextid
     * @return string|null
     */
    private function resolve_ddimageortext_drag_image(int $dragid, int $contextid): ?string {
        $fs = get_file_storage();

        $files = $fs->get_area_files(
            $contextid,
            'qtype_ddimageortext',
            'dragimage',
            $dragid,
            'itemid, filepath, filename',
            false
        );

        if (empty($files)) {
            return null;
        }

        $file = reset($files);

        return \moodle_url::make_pluginfile_url(
            $file->get_contextid(),
            $file->get_component(),
            $file->get_filearea(),
            $file->get_itemid(),
            $file->get_filepath(),
            $file->get_filename()
        )->out(false);
    }

    /**
     * Extract drag-and-drop onto image as overlay-ready data.
     *
     * @param array $data
     * @param int $questionid
     * @return array
     */
    private function extract_ddimageortext(array $data, int $questionid): array {
        $data['answers'] = [];
        $data['answerlabel'] = null;
        $data['ddimageortextoverlay'] = [
            'backgroundimage' => null,
            'drops' => [],
        ];

        $drags = $this->db->get_records(
            'qtype_ddimageortext_drags',
            ['questionid' => $questionid],
            'no ASC'
        );

        $drops = $this->db->get_records(
            'qtype_ddimageortext_drops',
            ['questionid' => $questionid],
            'no ASC'
        );

        if (empty($drags) || empty($drops)) {
            return $data;
        }

        $dragmap = [];
        foreach ($drags as $drag) {
            $dragmap[(int) $drag->no] = [
                'label' => trim((string) $drag->label),
                'imageurl' => $this->resolve_ddimageortext_drag_image(
                    (int) $drag->id,
                    (int) $data['questioncontextid']
                ),
            ];
        }

        foreach ($drops as $drop) {
            $choice = (int) $drop->choice;

            if (empty($dragmap[$choice])) {
                continue;
            }

            $dragitem = $dragmap[$choice];

            $data['ddimageortextoverlay']['drops'][] = [
                'no' => (int) $drop->no,
                'xleft' => (float) $drop->xleft,
                'ytop' => (float) $drop->ytop,
                'droplabel' => trim((string) $drop->label),
                'choice' => $choice,
                'draglabel' => $dragitem['label'],
                'dragimageurl' => $dragitem['imageurl'],
            ];
        }

        $data['ddimageortextoverlay']['backgroundimage'] = $this->resolve_ddimageortext_background_image(
            $questionid,
            (int) $data['questioncontextid']
        );

        return $data;
    }

    /**
     * Extract ordering question answers.
     *
     * @param array $data
     * @param int $questionid
     * @return array
     */
    private function extract_ordering(array $data, int $questionid): array {
        $data['answers'] = [];
        $data['answerlabel'] = get_string('correctanswers', 'quiz_downloadquiz');

        $sql = "SELECT qa.id, qa.answer, qa.answerformat, qa.fraction
                  FROM {question_answers} qa
                 WHERE qa.question = :questionid
              ORDER BY qa.fraction ASC, qa.id ASC";

        $rows = $this->db->get_records_sql($sql, ['questionid' => $questionid]);
        if (empty($rows)) {
            $data['notes'][] = get_string('noansweravailable', 'quiz_downloadquiz');
            return $data;
        }

        $position = 1;
        foreach ($rows as $row) {
            $label = $this->format_question_html(
                (string) $row->answer,
                (int) $row->answerformat,
                (int) $data['questioncontextid'],
                'answer',
                (int) $row->id
            );

            if ($label === '') {
                continue;
            }

            $data['answers'][] = [
                'label' => $position . '. ' . $label,
                'iscorrect' => true,
            ];

            $position++;
        }

        if (empty($data['answers'])) {
            $data['notes'][] = get_string('noansweravailable', 'quiz_downloadquiz');
        }

        return $data;
    }

    /**
     * Extract Gapselect answers.
     *
     * @param array $data
     * @param int $questionid
     * @return array
     */
    private function extract_gapselect(array $data, int $questionid): array {
        $data['answers'] = [];
        $data['answerlabel'] = get_string('correctanswers', 'quiz_downloadquiz');

        $sql = "SELECT qa.id, qa.answer, qa.answerformat, qa.feedback
                  FROM {question_answers} qa
                 WHERE qa.question = :questionid
              ORDER BY qa.id ASC";

        $rows = $this->db->get_records_sql($sql, ['questionid' => $questionid]);
        if (empty($rows)) {
            $data['notes'][] = get_string('noansweravailable', 'quiz_downloadquiz');
            return $data;
        }

        $choicesbynumber = [];
        $optionsbygroup = [];
        $position = 1;

        foreach ($rows as $row) {
            $label = $this->format_question_html(
                (string) $row->answer,
                (int) $row->answerformat,
                (int) $data['questioncontextid'],
                'answer',
                (int) $row->id
            );

            $group = trim((string) $row->feedback);
            if ($group === '') {
                $group = '1';
            }

            $choicesbynumber[$position] = [
                'label' => $label,
                'group' => $group,
            ];

            if (!isset($optionsbygroup[$group])) {
                $optionsbygroup[$group] = [];
            }

            $optionsbygroup[$group][] = $label;
            $position++;
        }

        if (!empty($optionsbygroup)) {
            ksort($optionsbygroup, SORT_NATURAL);
            $data['notes'][] = get_string('gapselectoptionsheading', 'quiz_downloadquiz');

            foreach ($optionsbygroup as $group => $options) {
                $cleanoptions = array_values(array_filter(array_map('trim', $options)));
                if (empty($cleanoptions)) {
                    continue;
                }

                $data['notes'][] = get_string(
                    'gapselectgroupoptions',
                    'quiz_downloadquiz',
                    [
                        'group' => $group,
                        'options' => implode(' | ', $cleanoptions),
                    ]
                );
            }
        }

        $questiontext = html_entity_decode((string) ($data['rawquestiontext'] ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        if (!preg_match_all('/\[\[(\d+)\]\]/', $questiontext, $matches)) {
            $data['notes'][] = get_string('noansweravailable', 'quiz_downloadquiz');
            return $data;
        }

        $gapnumber = 1;
        foreach ($matches[1] as $rawchoiceindex) {
            $choiceindex = (int) $rawchoiceindex;

            if (empty($choicesbynumber[$choiceindex])) {
                $data['notes'][] = '[[' . $gapnumber . ']]: ' . get_string('noansweravailable', 'quiz_downloadquiz');
                $gapnumber++;
                continue;
            }

            $choice = $choicesbynumber[$choiceindex];

            $data['answers'][] = [
                'label' => '[[' . $gapnumber . ']] → ' . $choice['label'] .
                    ' (' . get_string('group', 'quiz_downloadquiz') . ': ' . s($choice['group']) . ')',
                'iscorrect' => true,
            ];

            $gapnumber++;
        }

        if (empty($data['answers'])) {
            $data['notes'][] = get_string('noansweravailable', 'quiz_downloadquiz');
        }

        return $data;
    }

    /**
     * Extract image sources from rendered HTML.
     *
     * @param string $html
     * @return array
     */
    private function extract_image_sources(string $html): array {
        $sources = [];

        if ($html === '') {
            return $sources;
        }

        if (preg_match_all('/<img[^>]+src="([^"]+)"/i', $html, $matches)) {
            foreach ($matches[1] as $src) {
                $src = trim((string) $src);

                if ($src !== '') {
                    $sources[] = $src;
                }
            }
        }

        return array_values(array_unique($sources));
    }
}
