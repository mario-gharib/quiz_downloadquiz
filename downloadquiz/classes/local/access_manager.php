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
 * Access manager for quiz_downloadquiz.
 *
 * @package   quiz_downloadquiz
 * @copyright 2026 Center for Digital Innovation and Artificial Intelligence <moodle.cinia@usj.edu.lb>
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace quiz_downloadquiz\local;

/**
 * Centralised access validation for the downloadquiz report.
 */
final class access_manager {
    /**
     * Plugin capability required to access the report.
     */
    private const VIEW_CAPABILITY = 'quiz/downloadquiz:view';

    /**
     * Teaching/reporting capability required in the current quiz context.
     *
     * This ensures that a timed plugin grant does not allow access in courses
     * where the user is only enrolled as a student or another non-teaching role.
     */
    private const TEACHING_CAPABILITY = 'mod/quiz:viewreports';

    /**
     * Validate report access.
     *
     * This method performs the baseline Moodle checks required before the
     * report page is displayed or a PDF is generated.
     *
     * @param \stdClass $course The course record.
     * @param \cm_info $cm The course module.
     * @param \stdClass $quiz The quiz record.
     * @param \context_module $context The quiz module context.
     * @return void
     */
    public static function validate_base(
        \stdClass $course,
        \cm_info $cm,
        \stdClass $quiz,
        \context_module $context
    ): void {
        require_login($course, false, $cm);

        self::validate_quiz_cm_consistency($cm, $quiz);
        self::validate_context_instance($cm, $context);
        self::require_report_access($context);
    }

    /**
     * Check whether the current user can access the report.
     *
     * This is intended for non-throwing checks such as conditional UI display.
     *
     * @param \stdClass $course The course record.
     * @param \cm_info $cm The course module.
     * @param \stdClass $quiz The quiz record.
     * @param \context_module $context The quiz module context.
     * @return bool
     */
    public static function can_access_report(
        \stdClass $course,
        \cm_info $cm,
        \stdClass $quiz,
        \context_module $context
    ): bool {
        if (!isloggedin() || isguestuser()) {
            return false;
        }

        if ((int)$cm->instance !== (int)$quiz->id) {
            return false;
        }

        if ((int)$context->instanceid !== (int)$cm->id) {
            return false;
        }

        return has_capability(self::VIEW_CAPABILITY, $context)
            && has_capability(self::TEACHING_CAPABILITY, $context);
    }

    /**
     * Require all capabilities needed to access the report.
     *
     * @param \context_module $context The quiz module context.
     * @return void
     */
    private static function require_report_access(\context_module $context): void {
        require_capability(self::VIEW_CAPABILITY, $context);
        require_capability(self::TEACHING_CAPABILITY, $context);
    }

    /**
     * Validate that the course module belongs to the supplied quiz.
     *
     * @param \cm_info $cm The course module.
     * @param \stdClass $quiz The quiz record.
     * @return void
     */
    private static function validate_quiz_cm_consistency(\cm_info $cm, \stdClass $quiz): void {
        if ((int)$cm->instance !== (int)$quiz->id) {
            throw new \moodle_exception('invalidcoursemodule');
        }
    }

    /**
     * Validate that the supplied context matches the supplied course module.
     *
     * @param \cm_info $cm The course module.
     * @param \context_module $context The quiz module context.
     * @return void
     */
    private static function validate_context_instance(\cm_info $cm, \context_module $context): void {
        if ((int)$context->instanceid !== (int)$cm->id) {
            throw new \moodle_exception('invalidcontext');
        }
    }
}
