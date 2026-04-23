<?php
// This file is part of Moodle - https://moodle.org/.
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

/**
 * French language strings for quiz report downloadquiz.
 *
 * @package    quiz_downloadquiz
 * @author     Mario Gharib <mario.gharib@usj.edu.lb | mario.gharib@hotmail.com>
 * @copyright  Mario Gharib 2026
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();


// Plugin identity and privacy.
$string['pluginname'] = 'Télécharger le test avec les réponses';
$string['downloadquiz'] = 'Télécharger le test avec les réponses';
$string['downloadquizreport'] = 'Télécharger le test avec les réponses';
$string['privacy:metadata'] = 'Le rapport “Télécharger le test avec les réponses” ne stocke aucune donnée personnelle.';
$string['downloadquiz:view'] = 'Consulter et télécharger le PDF du corrigé du test';


// Report UI.
$string['downloadpdf'] = 'Envoyer le test en PDF par courriel';
$string['reportintro'] = 'Générez un fichier PDF chiffré et protégé de la structure du test ainsi que des réponses correctes enregistrées, à l’aide d’une clé d’accès à 6 caractères. Cette exportation :
<ul>
<li>inclut un filigrane à des fins de traçabilité.</li>
<li>restreint les fonctions de copie, de collage et d’impression pour des raisons de sécurité.</li>
<li>exclut toutes les tentatives des étudiants, les réponses soumises, les notes et les analyses.</li>
</ul>';
$string['disclaimer'] = '<b>Clause de non-responsabilité</b> : Vous êtes sur le point de générer un rapport PDF à caractère confidentiel. Toute utilisation abusive, reproduction, diffusion ou divulgation non autorisée de son contenu est strictement interdite. L’utilisateur assume l’entière responsabilité de l’usage qu’il fait de ce document.';
$string['pdfkey'] = 'Prière de saisir une clé d’accès d’au moins 6 caractères, dont 1 chiffre et 1 lettre majuscule.';
$string['pdfkeywarning'] = 'Veuillez conserver cette clé d’accès en lieu sûr. Elle ne sera pas enregistrée et ne pourra pas être récupérée après l’envoi du PDF ou en cas d’actualisation de la page.';
$string['pdfkeyplaceholder'] = 'Clé d’accès à 6 caractères';
$string['generatepdfkey'] = 'Générer une clé d’accès';
$string['showpdfkey'] = 'Afficher la clé d’accès';
$string['hidepdfkey'] = 'Masquer la clé d’accès';
$string['pdfemailsent'] = 'Le PDF du test a été envoyé à {$a}.';
$string['usergranttimeleft'] = 'Votre accès pour télécharger les tests reste actif pendant {$a->remaining}. Il expirera le {$a->expireson}.';


// PDF document labels.
$string['revisioncopy'] = 'Copie de révision';
$string['generatedon'] = 'généré le';
$string['course'] = 'Nom complet du cours';
$string['question'] = 'Question';
$string['correctanswer'] = 'Bonne réponse';
$string['correctanswers'] = 'Bonnes réponses';
$string['acceptedanswers'] = 'Réponses acceptées';
$string['matchingpairs'] = 'Paires correctes';
$string['page'] = 'Page';
$string['maxmark'] = 'Score maximum : ';
$string['section'] = 'Section';
$string['sectionshuffle'] = 'Mélanger les questions';
$string['untitledsection'] = 'Section non définie';
$string['ofthequiz'] = 'du test';
$string['intentionallyblanknotice'] = 'Cette zone a été laissée vide intentionnellement.';


// PDF metadata and headings.
$string['quiztitle'] = 'Titre du test : ';
$string['quizgeneralinformation'] = 'Informations générales du test : ';
$string['quizsecurityinformation'] = 'Sécurité du test : ';
$string['assessmentcomposition'] = 'Composition de l’évaluation';
$string['totalquestions'] = 'Nombre total de questions';
$string['quizstartdate'] = 'Date de début du test';
$string['quizenddate'] = 'Date de fin du test';
$string['timelimit'] = 'Limite de temps';
$string['attemptsallowed'] = 'Nombre de tentatives autorisées';
$string['quizmaximumgrade'] = 'Note maximale du test';
$string['gradetopass'] = 'Note pour réussir';
$string['navigationmethod'] = 'Mode de navigation';
$string['shufflewithinquestions'] = 'Mélanger les éléments des questions';
$string['quizpassword'] = 'Clé du test insérée ?';
$string['blockconcurrentconnections'] = 'Connexions simultanées bloquées ?';
$string['networkrestriction'] = 'Restriction par adresse IP ou réseau activée ?';
$string['safeexambrowser'] = 'Le Safe Exam Browser est-il activé ?';


// Assessment composition labels.
$string['qtypeessay'] = 'Questions de type dissertation';
$string['qtypemultichoice'] = 'Questions à choix multiple';
$string['qtypetruefalse'] = 'Questions vrai/faux';
$string['qtypeshortanswer'] = 'Questions à réponse courte';
$string['qtypematch'] = 'Questions d’appariement';
$string['qtypenumerical'] = 'Questions numériques';


// Question extraction notes.
$string['unsupportedqtype'] = 'Ce type de question n’est pas pris en charge dans le cadre de l’exportation structurée du corrigé. Le texte de la question est affiché, sans génération de corrigé.';
$string['unsupportedrandom'] = 'Les emplacements aléatoires sont listés ; toutefois, la structure du test seule ne permet pas d’exporter une définition fixe des questions.';
$string['noansweravailable'] = 'Aucune bonne réponse définitive n’est enregistrée pour ce type de question. Le texte de la question est affiché, sans génération de réponse.';
$string['fallbackessay'] = 'Les questions de type dissertation ne disposent généralement pas de réponse correcte définitive auto-corrigée. Seul le texte de la question est exporté.';
$string['fallbackdescription'] = 'Les éléments de description sont affichés à titre informatif uniquement.';
$string['fallbackmultianswer'] = 'Plusieurs bonnes réponses.';
$string['clozefields'] = 'Champs intégrés : ';
$string['gapselectoptionsheading'] = 'Options disponibles par groupe';
$string['gapselectgroupoptions'] = 'Groupe {$a->group} : {$a->options}';
$string['group'] = 'Groupe';
$string['shufflechoicesstatus'] = 'Les choix sont-ils mélangés ? {$a}';
$string['mcqanswertype'] = 'Type de réponse : {$a}';
$string['mcqsingle'] = 'Une seule réponse';
$string['mcqmultiple'] = 'Réponses multiples autorisées';


// Admin settings and grant management.
$string['settingsintro'] = 'Utiliser la page d’administration ci-dessous pour accorder ou révoquer un accès temporaire à des utilisateurs à la fonctionnalité de téléchargement du test au format PDF. Ces autorisations s’appliquent globalement à tous les tests, mais uniquement aux utilisateurs disposant déjà d’un accès Moodle standard à ces tests';
$string['managegrants'] = 'Gérer les accès temporaires des utilisateurs.';
$string['managegrantslink'] = 'Ouvrir la gestion des accès temporaires';
$string['managegrantsdesc'] = 'Accorder l’accès en saisissant l’adresse électronique de l’utilisateur et en sélectionnant une date d’expiration. Si la même adresse est soumise à nouveau, l’autorisation existante est mise à jour et la date d’expiration est réinitialisée.';
$string['currentgrants'] = 'Autorisations actives en cours';
$string['nograntsconfigured'] = 'Aucune autorisation temporaire active n’est actuellement configurée.';
$string['downloadcsv'] = 'Télécharger le CSV (accès actifs)';
$string['grantroleid'] = 'Rôle d’accès temporaire';
$string['requiredrolemissing'] = 'Le rôle requis « Download Quiz PDF Access » avec le nom court « downloadquizaccess » n’existe pas encore.';
$string['createrequiredrole'] = 'Créer le rôle requis';
$string['requiredroleexists'] = 'Le rôle requis existe déjà. Le plugin y a été associé automatiquement.';
$string['requiredrolecreated'] = 'Le rôle requis a été créé avec succès et associé automatiquement au plugin.';


// Grant form and grant table.
$string['useremail'] = 'Adresse électronique de l’utilisateur';
$string['userfullname'] = 'Utilisateur';
$string['grantedby'] = 'Accordé par';
$string['timegranted'] = 'Accordé le';
$string['expirestime'] = 'Expire le';
$string['timeleft'] = 'Temps restant';
$string['savegrant'] = 'Enregistrer l’autorisation';
$string['grantsaved'] = 'Autorisation temporaire enregistrée pour {$a}.';
$string['grantrevoked'] = 'Autorisation temporaire révoquée.';
$string['revokegrant'] = 'Révoquer';


// Email delivery.
$string['emailsubject'] = 'CONFIDENTIAL PDF of the Quiz - {$a->filename}';
$string['emailbodytext'] = 'Bonjour {$a->fullname},

La version PDF du test « {$a->quizname} » du cours « {$a->coursename} » a été générée le « {$a->generateddate} » et jointe à ce courriel.

Ce document PDF est chiffré et protégé par une clé d’accès à 6 caractères générée au moment de la demande. Il inclut un filigrane à des fins de traçabilité, et les fonctions de copie, de collage et d’impression sont restreintes pour des raisons de sécurité.

<p style="color:#d32f2f; font-weight:bold;">Ce document est confidentiel. Ne le partagez pas sans autorisation. Toute utilisation abusive, diffusion ou divulgation non autorisée de son contenu relève exclusivement de la responsabilité de {$a->fullname}.</p>';
$string['errorpdfemailsend'] = 'Le PDF a été généré, mais l’envoi du courriel a échoué.';


// Events and confidentiality.
$string['eventquizdownloaded'] = 'Test téléchargé';
$string['confidentialnoticeprefix'] = 'Clause de non-responsabilité';
$string['confidentialnoticefull'] = 'Ce rapport PDF a été généré à la demande de l’utilisateur : {$a->user}. Toute utilisation abusive, reproduction, diffusion ou divulgation non autorisée de son contenu engage la seule responsabilité de l’utilisateur demandeur.';


// Status values.
$string['yes'] = 'Oui';
$string['no'] = 'Non';
$string['true'] = 'Vrai';
$string['false'] = 'Faux';
$string['notset'] = 'Non défini';
$string['notdefined'] = 'Non défini';
$string['unlimitedattempts'] = 'Illimité';
$string['expired'] = 'Expiré';


// Errors and validation.
$string['errorusernotfound'] = 'Aucun utilisateur Moodle actif correspondant à cette adresse électronique n’a été trouvé.';
$string['errorexpiryinvalid'] = 'La date d’expiration doit être dans le futur.';
$string['errorpdfkeyrequiredclient'] = 'Une clé d’accès doit être saisie.';
$string['errorpdfkeylength'] = 'La clé d’accès doit contenir au moins 6 caractères, 1 chiffre et 1 lettre majuscule.';
$string['errorpdfkeyrequired'] = 'Vous devez saisir une clé d’accès PDF avant de télécharger le PDF.';
$string['errorpdfgeneration'] = 'Le PDF n’a pas pu être généré.';
$string['errornoquestions'] = 'Aucune question exportable n’a été trouvée dans ce test.';
$string['errornoroleselected'] = 'Aucun rôle d’accès temporaire n’a été configuré dans les paramètres du plugin.';