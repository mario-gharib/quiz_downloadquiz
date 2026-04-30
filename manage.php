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
 * Admin management page for timed user grants.
 *
 * @package   quiz_downloadquiz
 * @copyright 2026 Center for Digital Innovation and Artificial Intelligence <moodle.cinia@usj.edu.lb>
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../../config.php');
require_once($CFG->libdir . '/adminlib.php');

require_login();

$systemcontext = context_system::instance();
require_capability('moodle/site:config', $systemcontext);

$baseurl = new moodle_url('/mod/quiz/report/downloadquiz/grants.php');

$PAGE->set_url($baseurl);
$PAGE->set_context($systemcontext);
$PAGE->set_pagelayout('admin');
$PAGE->set_title(get_string('managegrants', 'quiz_downloadquiz'));
$PAGE->set_heading(get_string('managegrants', 'quiz_downloadquiz'));

$grantmanager = new \quiz_downloadquiz\local\grant_manager();
$grantmanager->disable_expired_grants();

$page = max(0, optional_param('page', 0, PARAM_INT));
$download = optional_param('download', 0, PARAM_BOOL);
$revokeuserid = optional_param('revoke', 0, PARAM_INT);

$requiredrole = $DB->get_record('role', ['shortname' => 'downloadquizaccess'], '*', IGNORE_MISSING);
quiz_downloadquiz_redirect_if_required_role_missing($requiredrole);

quiz_downloadquiz_sync_grant_role_configuration($requiredrole, $grantmanager);

if ($download) {
    require_sesskey();
    quiz_downloadquiz_export_active_grants_csv($grantmanager);
}

if ($revokeuserid > 0) {
    require_sesskey();
    quiz_downloadquiz_revoke_user_grant($grantmanager, $revokeuserid, $page);
}

$form = new \quiz_downloadquiz\form\grant_form();

if ($form->is_cancelled()) {
    redirect(new moodle_url('/admin/settings.php', ['section' => 'modsettingsquizcatdownloadquiz']));
}

if ($data = $form->get_data()) {
    quiz_downloadquiz_process_grant_form_submission($grantmanager, $data, $page);
}

quiz_downloadquiz_render_manage_page($form, $grantmanager, $page);

/**
 * Redirect to plugin settings if the required timed-access role is missing.
 *
 * @param stdClass|false $requiredrole
 * @return void
 */
function quiz_downloadquiz_redirect_if_required_role_missing($requiredrole): void {
    if ($requiredrole) {
        return;
    }

    redirect(
        new moodle_url('/admin/settings.php', ['section' => 'modsettingsquizcatdownloadquiz']),
        get_string('requiredrolemissing', 'quiz_downloadquiz'),
        null,
        \core\output\notification::NOTIFY_WARNING
    );
}

/**
 * Keep plugin configuration and role assignments aligned with the required role.
 *
 * @param stdClass $requiredrole
 * @param \quiz_downloadquiz\local\grant_manager $grantmanager
 * @return void
 */
function quiz_downloadquiz_sync_grant_role_configuration(
    stdClass $requiredrole,
    \quiz_downloadquiz\local\grant_manager $grantmanager
): void {
    set_config('grantroleid', (int)$requiredrole->id, 'quiz_downloadquiz');

    $grantmanager->resync_active_grants_to_role((int)$requiredrole->id);
    accesslib_clear_all_caches(true);
}

/**
 * Export active grants as a CSV download.
 *
 * @param \quiz_downloadquiz\local\grant_manager $grantmanager
 * @return void
 */
function quiz_downloadquiz_export_active_grants_csv(\quiz_downloadquiz\local\grant_manager $grantmanager): void {
    $grants = $grantmanager->get_current_grants();
    $filename = userdate(time(), '%Y%m%d%H%M') . '-DownloadQuizActiveGrantsUsers-Confidential.csv';

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');

    $output = fopen('php://output', 'w');

    fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

    fputcsv($output, [
        get_string('userfullname', 'quiz_downloadquiz'),
        get_string('useremail', 'quiz_downloadquiz'),
        get_string('grantedby', 'quiz_downloadquiz'),
        get_string('timegranted', 'quiz_downloadquiz'),
        get_string('expirestime', 'quiz_downloadquiz'),
        get_string('timeleft', 'quiz_downloadquiz'),
    ]);

    foreach ($grants as $grant) {
        fputcsv($output, [
            $grant->userfullname,
            $grant->useremail,
            $grant->grantedbyname,
            userdate((int)$grant->timegranted),
            userdate((int)$grant->timeexpires),
            \quiz_downloadquiz\local\grant_manager::format_remaining_time((int)$grant->timeexpires),
        ]);
    }

    fclose($output);
    exit;
}

/**
 * Revoke a user's active grant and redirect back to the management page.
 *
 * @param \quiz_downloadquiz\local\grant_manager $grantmanager
 * @param int $userid
 * @param int $page
 * @return void
 */
function quiz_downloadquiz_revoke_user_grant(
    \quiz_downloadquiz\local\grant_manager $grantmanager,
    int $userid,
    int $page
): void {
    $grantmanager->revoke_grant($userid);

    redirect(
        new moodle_url('/mod/quiz/report/downloadquiz/manage.php', ['page' => $page]),
        get_string('grantrevoked', 'quiz_downloadquiz'),
        null,
        \core\output\notification::NOTIFY_SUCCESS
    );
}

/**
 * Process a submitted timed grant form.
 *
 * @param \quiz_downloadquiz\local\grant_manager $grantmanager
 * @param stdClass $data
 * @param int $page
 * @return void
 */
function quiz_downloadquiz_process_grant_form_submission(
    \quiz_downloadquiz\local\grant_manager $grantmanager,
    stdClass $data,
    int $page
): void {
    $user = $grantmanager->get_user_by_email($data->useremail);

    if (!$user) {
        redirect(
            new moodle_url('/mod/quiz/report/downloadquiz/manage.php', ['page' => $page]),
            get_string('errorusernotfound', 'quiz_downloadquiz'),
            null,
            \core\output\notification::NOTIFY_ERROR
        );
    }

    $timeexpires = (int)$data->timeexpires;
    if ($timeexpires <= time()) {
        redirect(
            new moodle_url('/mod/quiz/report/downloadquiz/manage.php', ['page' => $page]),
            get_string('errorexpiryinvalid', 'quiz_downloadquiz'),
            null,
            \core\output\notification::NOTIFY_ERROR
        );
    }

    $grantmanager->create_grant((int)$user->id, $timeexpires, (int)$GLOBALS['USER']->id);

    redirect(
        new moodle_url('/mod/quiz/report/downloadquiz/manage.php', ['page' => $page]),
        get_string('grantsaved', 'quiz_downloadquiz', fullname($user)),
        null,
        \core\output\notification::NOTIFY_SUCCESS
    );
}

/**
 * Render the grant management page.
 *
 * @param \quiz_downloadquiz\form\grant_form $form
 * @param \quiz_downloadquiz\local\grant_manager $grantmanager
 * @param int $page
 * @return void
 */
function quiz_downloadquiz_render_manage_page(
    \quiz_downloadquiz\form\grant_form $form,
    \quiz_downloadquiz\local\grant_manager $grantmanager,
    int $page
): void {
    global $OUTPUT;

    $perpage = 10;
    $allgrants = $grantmanager->get_current_grants();
    $totalgrants = count($allgrants);

    if ($totalgrants > 0) {
        $maxpage = (int)floor(($totalgrants - 1) / $perpage);
        if ($page > $maxpage) {
            redirect(new moodle_url('/mod/quiz/report/downloadquiz/manage.php', ['page' => $maxpage]));
        }
    }

    $grants = array_slice($allgrants, $page * $perpage, $perpage);

    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('managegrants', 'quiz_downloadquiz'));
    echo $OUTPUT->notification(
        get_string('managegrantsdesc', 'quiz_downloadquiz'),
        \core\output\notification::NOTIFY_INFO
    );

    $form->display();

    echo quiz_downloadquiz_render_download_csv_button();

    echo html_writer::tag('h3', get_string('currentgrants', 'quiz_downloadquiz'));

    $baseurl = new moodle_url('/mod/quiz/report/downloadquiz/manage.php');

    if ($totalgrants > $perpage) {
        echo html_writer::div(
            $OUTPUT->paging_bar($totalgrants, $page, $perpage, $baseurl),
            'paging text-center mb-3'
        );
    }

    if (empty($grants)) {
        echo $OUTPUT->notification(
            get_string('nograntsconfigured', 'quiz_downloadquiz'),
            \core\output\notification::NOTIFY_INFO
        );
    } else {
        echo quiz_downloadquiz_render_grants_table($grants, $page);
    }

    if ($totalgrants > $perpage && !empty($grants)) {
        echo html_writer::div(
            $OUTPUT->paging_bar($totalgrants, $page, $perpage, $baseurl),
            'paging text-center mt-3'
        );
    }

    echo $OUTPUT->footer();
}

/**
 * Render the CSV export button.
 *
 * @return string
 */
function quiz_downloadquiz_render_download_csv_button(): string {
    $downloadurl = new moodle_url('/mod/quiz/report/downloadquiz/manage.php', [
        'download' => 1,
        'sesskey' => sesskey(),
    ]);

    return html_writer::div(
        html_writer::link(
            $downloadurl,
            get_string('downloadcsv', 'quiz_downloadquiz'),
            ['class' => 'btn btn-secondary']
        ),
        'mb-3',
        ['style' => 'text-align: right;']
    );
}

/**
 * Render the active grants table.
 *
 * @param array $grants
 * @param int $page
 * @return string
 */
function quiz_downloadquiz_render_grants_table(array $grants, int $page): string {
    $table = new html_table();

    $table->head = [
        get_string('userfullname', 'quiz_downloadquiz'),
        get_string('useremail', 'quiz_downloadquiz'),
        get_string('grantedby', 'quiz_downloadquiz'),
        get_string('timegranted', 'quiz_downloadquiz'),
        get_string('expirestime', 'quiz_downloadquiz'),
        get_string('timeleft', 'quiz_downloadquiz'),
        get_string('actions'),
    ];

    $table->data = [];

    foreach ($grants as $grant) {
        $revokeurl = new moodle_url('/mod/quiz/report/downloadquiz/manage.php', [
            'revoke' => $grant->userid,
            'sesskey' => sesskey(),
            'page' => $page,
        ]);

        $table->data[] = [
            s($grant->userfullname),
            s($grant->useremail),
            s($grant->grantedbyname),
            userdate((int)$grant->timegranted),
            userdate((int)$grant->timeexpires),
            \quiz_downloadquiz\local\grant_manager::format_remaining_time((int)$grant->timeexpires),
            html_writer::link($revokeurl, get_string('revokegrant', 'quiz_downloadquiz')),
        ];
    }

    return html_writer::table($table);
}
