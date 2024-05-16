<?php

/**
* This file is part of the phpBB Forum extension package
* IUM (Inactive User Manager).
*
* @copyright (c) 2016 by Andreas Kourtidis
* @license   GNU General Public License, version 2 (GPL-2.0)
*
* For full copyright and license information, please see
* the CREDITS.txt file.
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty( $lang) || !is_array($lang) )
{
	$lang = array();
}

$lang = array_merge(
	$lang, array(
		'USER_SELF_DELETE_TITLE'	=>  'Seite zum Selbstlöschen.',
		'USER_SELF_DELETE_EXPLAIN'	=>  'Indem Du das Bestätigungskästchen markierst und auf die Schaltfläche "Bestätigen" klickst, akzeptierst Du die Löschung Deines Benutzerkontos in diesem Forum.<br/>Alle Deine Beiträge bleiben erhalten, aber Du kannst Dich nicht mehr mit Deinem Benutzernamen/Passwort verbinden.<br/>Wenn Du ein Konto mit demselben Benutzernamen erstellst, wird der vorherige Beitrag nicht mit dem neuen Konto verknüpft.',
		'USER_SELF_DELETE_VERIFY'	=>  'Ich verstehe die Konsequenzen und bestätige sie',
		'HAVE_TO_LOGIN'				=>  'Es tut uns leid, aber Du musst Dich anmelden, um diese Seite anzuzeigen.',
		'HAVE_TO_VERIFY'			=>  'Bitte überprüfe das Bestätigungsfeld.',
		'PAGE_NOT_EXIST'			=>  'Wir bedauern die Unannehmlichkeiten sehr.<br/><br/>Die Selbstlöschung ist jedoch deaktiviert.<br/>Wenn Du versehentlicht auf diese Seite gelangt bist, überprüfe bitte die URL, die Du in Deinem Browser eingegeben hast.<br/>Wenn Du einem Link aus einer E-Mail gefolgt bist, die Du von uns erhalten hast, wenden Dich bitte an den Administrator der Seite.',
		'NEEDS_APPROVAL'			=>	'Es tut uns sehr leid, dass Du dich entschieden hast, %s zu verlassen. Bitte beachte, dass Dein Konto noch nicht gelöscht ist, sondern zunächst genehmigt werden muss. Bitte gebe uns etwas Zeit für diese Aktion. In 3 Sekunden wirst Du auf unsere Homepage weitergeleitet.',
		'USER_SELF_DELETE_SUCCESS'	=>	'Es tut uns sehr leid, dass Du dich entschieden hast, %s zu verlassen. Dein Konto wurde gelöscht. In 3 Sekunden wirst Du auf unsere Homepage weitergeleitet.',
		'INVALID_LINK_OR_USER'		=>	'Ungültige Kombination.',
	)
);
