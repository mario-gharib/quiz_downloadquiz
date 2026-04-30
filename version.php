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
 * Version information for quiz report downloadquiz.
 *
 * @package   quiz_downloadquiz
 * @copyright 2026 Center for Digital Innovation and Artificial Intelligence <moodle.cinia@usj.edu.lb>
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$plugin->component = 'quiz_downloadquiz';
$plugin->version = 2026050102;
$plugin->requires = 2025021000;
$plugin->maturity = MATURITY_STABLE;
$plugin->release = '1.0.0';


// Center for Digital Innovation and Artificial Intelligence (CINIA).
$plugin->copyright = '2026 Center for Digital Innovation and Artificial Intelligence (CINIA) | https://usj.edu.lb/cinia/';

// Governance and Development Leadership.
$plugin->collaborators = [
    'Wadad Wazen', // Email: wadad.wazen@usj.edu.lb. Position: Director, CINIA.
    'Mario Gharib', // Email: mario.gharib@usj.edu.lb. Position: Team leader (architecture and implementation).
];
// Functional Contributions.
$plugin->contributors = [
    'Patrick Hajj', // Email: patrick.hajj@usj.edu.lb. Position: Chief Information Security Officer (security review).
    'Helena Saade', // Email: helena.saade@usj.edu.lb. Position: Translation.
    'Elie Bechara', // Email: elie.bechara2@usj.edu.lb. Position: User testing.
    'Elise Oueiss Melki (El)', // Email: elise.oueissmelki@usj.edu.lb. Position: Visual assets.
    'Anthony Bassil', // Email: anthony.bassil5@usj.edu.lb. Position: User testing.
];
