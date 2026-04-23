<?php
// This file is part of Moodle - https://moodle.org/.
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

/**
 * Version information for quiz report downloadquiz.
 *
 * @package     quiz_downloadquiz
 * @copyright   2026 Center for Digital Innovation and Artificial Intelligence
 * @author      Center for Digital Innovation and Artificial Intelligence
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$plugin->component = 'quiz_downloadquiz';
$plugin->version = 2026041700;
$plugin->requires = 2025021000;
$plugin->maturity = MATURITY_STABLE;
$plugin->release = '1.0.0';


// Center for Digital Innovation and Artificial Intelligence (CINIA)
$plugin->copyright = '2026 Center for Digital Innovation and Artificial Intelligence (CINIA) | https://usj.edu.lb/cinia/';

// Governance and Development Leadership.
$plugin->collaborators = array(
    'Wadad Wazen',              // <wadad.wazen@usj.edu.lb>         Director, CINIA
    'Mario Gharib'              // <mario.gharib@usj.edu.lb>        Team Leader (Architecture and implementation)
);

// Functional Contributions.
$plugin->contributors = array(
    'Patrick Hajj',             // <patrick.hajj@usj.edu.lb>        Chief Information Security Officer (security review)    
    'Helena Saade',             // <helena.saade@usj.edu.lb>        Translation
    'Elie Bechara',             // <elie.bechara2@usj.edu.lb>       User testing
    'Elise Oueiss Melki (El)',  // <elise.oueissmelki@usj.edu.lb>   Visual assets
    'Anthony Bassil'            // <anthony.bassil5@usj.edu.lb>     User testing
);