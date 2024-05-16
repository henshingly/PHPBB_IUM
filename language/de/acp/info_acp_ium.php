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
		//
		'ACP_IUM'				=>	'IUM Einstellungen',
		'ACP_IUM_LIST'			=>	'Liste inaktiver Benutzer',
		'ACP_IUM_TITLE'			=>	'IUM Extension',
		'ACP_IUM_TITLE2'		=>	'Liste inaktiver Benutzer',
		'ACP_IUM_APPROVAL_LIST'	=>	'Genehmigungsliste ignorieren/löschen',

		// acp user overview add option
		'USER_ADMIN_ANDREASK_IUM_USERS_OVERVIEW_OPTION'	=>	'Eine Erinnerung senden',

		// Log
		'LOG_SENT_REMINDER_TO_ADMIN'	=>  'Vorlage "%1$s" wurde an "%2$s" gesendet',
		'LOG_SENT_REMINDERS'			=>  array(
			0	=>	'Es wurden keine Erinnerungen gesendet',
			1	=>	'%s Erinnerung wurde verschickt.',
			2	=>	'%s Erinnerungen wurden verschickt.'
		),
		'LOG_USERS_DELETED'				=>	'"%1$s" Benutzer wurden gelöscht "<b>%2$s"</b>, Anfragetyp: "<b>%3$s</b>"',
		'LOG_FAILED_EMAILS'				=>	'Es konnten keine E-Mails an die folgenden Benutzer gesendet werden: "<strong>%1$s</strong>". Weitere Details findest Du in den Fehlerprotokollen.<br/>Werde es morgen noch einmal versuchen',
		'LOG_USER_DELETED'				=>	'Benutzer "<b>%1$s</b>" wurde gelöscht, Anfragetyp: "<b>%2$s</b>"',
		'LOG_DELETE_REQUEST_DONT_MATCH'	=>	'Mit Deiner Anfrage stimmte etwas nicht. Die zum löschen angeforderten Benutzer stimmten nicht mit den tatsächlichen Benutzern in der Datenbank überein',
		'LOG_USER_SELF_DELETED'			=>	'Ein Benutzer wurde selbst gelöscht. Die Konfiguration für Beiträge wurde auf "%s" festgelegt.',
		'LOG_SENT_REMINDER_TO'			=>	'Eine Erinnerung wurde an den Benutzer "%s" gesendet.',
)
);
