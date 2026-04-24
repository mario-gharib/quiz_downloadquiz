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
 * Admin settings for quiz_downloadquiz.
 *
 * @package     quiz_downloadquiz
 * @copyright   2026 Center for Digital Innovation and Artificial Intelligence
 * @author      Center for Digital Innovation and Artificial Intelligence
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

if (!$hassiteconfig) {
    return;
}

global $DB;

$systemcontext = \context_system::instance();
$requiredshortname = 'downloadquizaccess';

$manageurl = new \moodle_url('/mod/quiz/report/downloadquiz/manage.php');
$createroleurl = new \moodle_url('/mod/quiz/report/downloadquiz/createrole.php');

$role = $DB->get_record('role', ['shortname' => $requiredshortname], '*', IGNORE_MISSING);

$descriptionparts = [];
$descriptionparts[] = \html_writer::tag(
    'p',
    get_string('settingsintro', 'quiz_downloadquiz')
);

if ($role) {
    set_config('grantroleid', (int)$role->id, 'quiz_downloadquiz');

    $rolename = role_get_name($role, $systemcontext);

    $descriptionparts[] = \html_writer::tag(
        'p',
        \html_writer::tag('strong', get_string('grantroleid', 'quiz_downloadquiz') . ': ') .
        s($rolename) . ' (' . s($requiredshortname) . ')'
    );

    $descriptionparts[] = \html_writer::tag(
        'p',
        \html_writer::link(
            $manageurl,
            get_string('managegrantslink', 'quiz_downloadquiz'),
            ['class' => 'btn btn-primary']
        )
    );
} else {
    $descriptionparts[] = \html_writer::tag(
        'div',
        get_string('requiredrolemissing', 'quiz_downloadquiz'),
        ['class' => 'alert alert-warning']
    );

    $descriptionparts[] = \html_writer::tag(
        'p',
        \html_writer::link(
            $createroleurl,
            get_string('createrequiredrole', 'quiz_downloadquiz'),
            ['class' => 'btn btn-primary']
        )
    );
}

$settings->add(new \admin_setting_heading(
    'quiz_downloadquiz/settingsheading',
    get_string('managegrants', 'quiz_downloadquiz'),
    implode('', $descriptionparts)
));
