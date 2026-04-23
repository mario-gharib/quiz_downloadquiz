<?php
// This file is part of Moodle - https://moodle.org/.
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

/**
 * English language strings for quiz report downloadquiz.
 *
 * @package    quiz_downloadquiz
 * @author     Mario Gharib <mario.gharib@usj.edu.lb | mario.gharib@hotmail.com>
 * @copyright  Mario Gharib 2026
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();


// Plugin identity and privacy.
$string['pluginname'] = 'Download quiz with answers';
$string['downloadquiz'] = 'Download quiz with answers';
$string['downloadquizreport'] = 'Download quiz with answers';
$string['privacy:metadata'] = 'The "Download quiz with answers" report does not store personal data.';
$string['downloadquiz:view'] = 'View and download the quiz answer-key PDF';


// Report UI.
$string['downloadpdf'] = 'Send quiz as PDF by email';
$string['reportintro'] = 'Generate an encrypted and protected PDF of the quiz structure and the stored correct answers by a 6-character access key. This export:
<ul>
<li>includes watermarking for traceability.</li>
<li>restricts copy, paste, and printing functions for security purposes.</li>
<li>excludes all student attempts, responses, grades, and analytics.</li>
</ul>';
$string['disclaimer'] = '<b>Disclaimer</b>: You are about to generate a confidential PDF report. Any misuse, distribution, or unauthorized disclosure of its contents is strictly prohibited. You are solely responsible for the appropriate use of this document.';
$string['pdfkey'] = 'Insert an access key of at least 6 characters, 1 number, and 1 capital letter.';
$string['pdfkeywarning'] = 'Please save the access key securely. It will not be stored and cannot be recovered after the PDF is sent or if the page is refreshed.';
$string['pdfkeyplaceholder'] = '6-character access key';
$string['generatepdfkey'] = 'Generate access key';
$string['showpdfkey'] = 'Show access key';
$string['hidepdfkey'] = 'Hide access key';
$string['pdfemailsent'] = 'The quiz PDF has been sent to {$a}.';
$string['usergranttimeleft'] = 'Your access to download this quiz remains active for {$a->remaining}. It expires on {$a->expireson}.';


// PDF document labels.
$string['revisioncopy'] = 'Revision copy';
$string['generatedon'] = 'generated on';
$string['course'] = 'Course full name';
$string['question'] = 'Question';
$string['correctanswer'] = 'Correct answer';
$string['correctanswers'] = 'Correct answers';
$string['acceptedanswers'] = 'Accepted answers';
$string['matchingpairs'] = 'Correct matching pairs';
$string['page'] = 'Page';
$string['maxmark'] = 'Maximum mark: ';
$string['section'] = 'Section';
$string['sectionshuffle'] = 'Shuffle questions';
$string['untitledsection'] = 'Untitled section';
$string['ofthequiz'] = 'of the quiz';
$string['intentionallyblanknotice'] = 'This area has been left empty on purpose.';


// PDF metadata and headings.
$string['quiztitle'] = 'Quiz Title: ';
$string['quizgeneralinformation'] = 'General Information of the Quiz: ';
$string['quizsecurityinformation'] = 'Security of the Quiz: ';
$string['assessmentcomposition'] = 'Assessment Composition';
$string['totalquestions'] = 'Total number of questions';
$string['quizstartdate'] = 'Quiz Start Date';
$string['quizenddate'] = 'Quiz End Date';
$string['timelimit'] = 'Time Limit';
$string['attemptsallowed'] = 'Attempts allowed';
$string['quizmaximumgrade'] = 'Quiz Maximum Grade';
$string['navigationmethod'] = 'Navigation method';
$string['gradetopass'] = 'Grade to pass';
$string['shufflewithinquestions'] = 'Shuffle within questions ';
$string['quizpassword'] = 'Quiz Password Inserted?';
$string['blockconcurrentconnections'] = 'Concurrent Connections Blocked?';
$string['networkrestriction'] = 'Restriction by IP/Network Address Enabled?';
$string['safeexambrowser'] = 'Safe Exam Browser Enabled?';


// Assessment composition labels.
$string['qtypeessay'] = 'Essay questions';
$string['qtypemultichoice'] = 'Multiple choice questions';
$string['qtypetruefalse'] = 'True/False questions';
$string['qtypeshortanswer'] = 'Short answer questions';
$string['qtypematch'] = 'Matching questions';
$string['qtypenumerical'] = 'Numerical questions';


// Question extraction notes.
$string['unsupportedqtype'] = 'This question type is not supported for structured answer-key export. The question text is shown, but no answer key is generated.';
$string['unsupportedrandom'] = 'Random question placeholders are listed, but a fixed question definition cannot be exported from the quiz structure alone.';
$string['noansweravailable'] = 'No definitive correct answer is stored for this question type. The question text is shown without an invented answer.';
$string['fallbackessay'] = 'Essay questions do not normally store a definitive auto-graded correct answer. Only the question text is exported.';
$string['fallbackdescription'] = 'Description items are rendered as informational text only.';
$string['fallbackmultianswer'] = 'Multiple correct responses.';
$string['clozefields'] = 'Embedded fields: ';
$string['gapselectoptionsheading'] = 'Available options by group';
$string['gapselectgroupoptions'] = 'Group {$a->group}: {$a->options}';
$string['group'] = 'Group';
$string['shufflechoicesstatus'] = 'Choices are shuffled? {$a}';
$string['mcqanswertype'] = 'Answer type: {$a}';
$string['mcqsingle'] = 'One answer only';
$string['mcqmultiple'] = 'Multiple answers allowed';


// Admin settings and grant management.
$string['settingsintro'] = 'Use the administration page below to grant or revoke time-limited user access to the quiz PDF download feature. These grants apply globally across quizzes, but only for users who already have normal Moodle access to those quizzes.';
$string['managegrants'] = 'Manage timed user access';
$string['managegrantslink'] = 'Open timed access management';
$string['managegrantsdesc'] = 'Grant access by entering a user email address and selecting an expiry date. Re-submitting the same email updates the existing grant and resets the expiry time.';
$string['currentgrants'] = 'Current active grants';
$string['nograntsconfigured'] = 'No active timed grants are currently configured.';
$string['downloadcsv'] = 'Download CSV (Active grants)';
$string['grantroleid'] = 'Timed-access role';
$string['requiredrolemissing'] = 'The required role "Download Quiz PDF Access" with shortname "downloadquizaccess" does not exist yet.';
$string['createrequiredrole'] = 'Create required role';
$string['requiredroleexists'] = 'The required role already exists. The plugin has been linked to it automatically.';
$string['requiredrolecreated'] = 'The required role was created successfully and linked to the plugin automatically.';


// Grant form and grant table.
$string['useremail'] = 'User email';
$string['userfullname'] = 'User';
$string['grantedby'] = 'Granted by';
$string['timegranted'] = 'Granted on';
$string['expirestime'] = 'Expires on';
$string['timeleft'] = 'Time remaining';
$string['savegrant'] = 'Save grant';
$string['grantsaved'] = 'Timed access grant saved for {$a}.';
$string['grantrevoked'] = 'Timed access grant revoked.';
$string['revokegrant'] = 'Revoke';


// Email delivery.
$string['emailsubject'] = 'CONFIDENTIAL PDF of the Quiz - {$a->filename}';
$string['emailbodytext'] = 'Hello {$a->fullname},

The PDF copy of the quiz "{$a->quizname}" from the course "{$a->coursename}" has been generated on "{$a->generateddate}" and attached to this email.

This PDF is encrypted and protected by the 6-character access key that was generated at the time of request. It includes watermarking for traceability, and copy, paste, and printing functions are restricted for security purposes.

<p style="color:#d32f2f; font-weight:bold;">This document is confidential. Do not share it unless authorised. Any misuse, distribution, or unauthorized disclosure of its contents is solely the responsibility of {$a->fullname}</p>';
$string['errorpdfemailsend'] = 'The PDF was generated, but the email could not be sent.';


// Events and confidentiality.
$string['eventquizdownloaded'] = 'Quiz downloaded';
$string['confidentialnoticeprefix'] = 'Disclaimer';
$string['confidentialnoticefull'] = 'This PDF report has been generated at the request of the user: {$a->user}. Any misuse, distribution, or unauthorized disclosure of its contents is solely the responsibility of the requesting user.';


// Status values.
$string['yes'] = 'Yes';
$string['no'] = 'No';
$string['true'] = 'True';
$string['false'] = 'False';
$string['notset'] = 'Not set';
$string['notdefined'] = 'Not defined';
$string['unlimitedattempts'] = 'Unlimited';
$string['expired'] = 'Expired';


// Errors and validation.
$string['errorusernotfound'] = 'No active Moodle user was found for that email address.';
$string['errorexpiryinvalid'] = 'The expiry date must be in the future.';
$string['errorpdfkeyrequiredclient'] = 'You must enter an access key here.';
$string['errorpdfkeylength'] = 'The access key must contain at least 6 characters, 1 number, and 1 capital letter.';
$string['errorpdfkeyrequired'] = 'You must enter a PDF access key before downloading the PDF.';
$string['errorpdfgeneration'] = 'The PDF could not be generated.';
$string['errornoquestions'] = 'No exportable questions were found in this quiz.';
$string['errornoroleselected'] = 'No timed-access role has been configured in the plugin settings.';