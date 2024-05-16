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
		// ACP-Konfigurationsseite
		'ACP_IUM_SETTINGS'								=>	'Inactive User Reminder Einstellungen',
		'ACP_IUM_MAIL_SETTINGS'							=>	'Reminder Einstellungen',

		'ANDREASK_IUM_ENABLE'							=>	'Aktiviere die erweiterte Erinnerung für inaktive Benutzer',
		'ANDREASK_IUM_INTERVAL'							=>	'Interval',
		'ANDREASK_IUM_EMAIL_LIMIT'						=>	'E-Mails begrenzen',
		'ANDREASK_IUM_TOP_USER_THREADS'					=>	'Die Top-Themen des Benutzers einbeziehen',
		'ANDREASK_IUM_TOP_USER_THREADS_COUNT'			=>	'Wie viele Themen',
		'ANDREASK_IUM_TOP_FORUM_THREADS'				=>	'Die Top-Themen des Forums einbeziehen',
		'ANDREASK_IUM_TOP_FORUM_THREADS_COUNT'			=>	'Wie viele Themen',
		'ANDREASK_IUM_SELF_DELETE'						=>	'Der Benutzer kann sich selbst löschen',
		'ANDREASK_IUM_DELETE_APPROVE'					=>	'Für Selbstlöschungsanfragen ist eine Genehmigung erforderlich',
		'ANDREASK_IUM_KEEP_POSTS'						=>	'Die Beiträge gelöschter Benutzer behalten',
		'ANDREASK_IUM_AUTO_DEL'							=>	'Benutzer automatisch löschen',
		'ANDREASK_IUM_AUTO_DEL_DAYS'					=>	'Nach Tagen',
		'ANDREASK_IUM_TEST_EMAIL'						=>	'Test-E-Mail senden',
		'ANDREASK_IUM_INCLUDED_FORUMS'					=>	'Enthaltene Foren',
		'ANDREASK_IUM_EXCLUDE_FORUM'					=>	'Ausschließen',
		'EMAILS'										=>	'E-Mails',
		'ACP_IUM_MAIL_INCLUDE_SETTINGS'					=>	'Weitere Reminder Einstellungen',
		'ACP_IUM_DANGER'								=>	'Gefahrenbereich',

		'ANDREASK_IUM_EXCLUDED_FORUMS'					=>	'Ausgeschlossene Foren',
		'ANDREASK_IUM_INCLUDE_FORUM'					=>	'Einschließlich',
		'SELECT_A_FORUM'								=>	'Bitte ein Forum auswählen',
		'EXCLUDED_EMPTY'								=>	'Keine Foren ausgeschlossen...',
		'FIRST_REMINDER'								=>	'1.',
		'SECOND_REMINDER'								=>	'2.',
		'THIRD_REMINDER'								=>	'3.',
		'ANDREASK_IUM_IGNORE_LMT'						=>  'Kein Spam an den Benutzer',
		'ANDREASK_IUM_RESPECT_USR'						=>  'Wahl des Benutzers respektieren',
		'ANDREASK_IUM_NO_REPLY'							=>  'Keine Antwort',

		// Erläuterungen zur ACP-Konfigurationsseite
		'ANDREASK_IUM_ENABLE_EXPLAIN'					=>	'Wenn aktiviert, beginnt die Erweiterung damit, Erinnerungen an "Schläfer" zu senden',
		'ANDREASK_IUM_INTERVAL_EXPLAIN'					=>	'Dabei handelt es sich um die Anzahl der Tage, die zurückgezählt werden müssen, um einen Benutzer als "Schläfer" zu betrachten. Empfohlen sind 30 Tage. Der Mindestwert für das 1. Intervall beträgt 10, für das 2. und/oder 3. Intervall wird bei Einstellung 0 der Wert des vorherigen Intervalls angenommen. <br>d.h. wenn der 2. auf "0" gesetzt ist, beträgt das zweite Intervall die gleiche Länge wie im ersten eingestellt, wenn der 3. Intervall auf "0" gesetzt ist, beträgt die Länge des dritten Intervalls die Länge des zweiten.',
		'ANDREASK_IUM_EMAIL_LIMIT_EXPLAIN'				=>	'Anzahl der Erinnerungen, die <b>pro Tag</b> gesendet werden können. Empfohlen sind ~250. Erkundige Dich aber bei Deinem Provider nach einer eventuellen Begrenzung der E-Mails pro Tag.',
		'ANDREASK_IUM_TOP_USER_THREADS_EXPLAIN'			=>	'Wenn diese Option aktiviert ist, enthält die E-Mail die aktivsten Themen des Benutzers seit seinem letzten Besuch.',
		'ANDREASK_IUM_TOP_USER_THREADS_COUNT_EXPLAIN'	=>	'Anzahl der Top-Themen des Benutzers, die in die E-Mail aufgenommen werden sollen.',
		'ANDREASK_IUM_TOP_FORUM_THREADS_EXPLAIN'		=>	'Wenn diese Option aktiviert ist, enthält die E-Mail die Top-Themen des Forums seit dem letzten Besuch des Benutzers.',
		'ANDREASK_IUM_TOP_FORUM_THREADS_COUNT_EXPLAIN'	=>	'Anzahl der Forumsthemen, die in die E-Mail aufgenommen werden sollen',
		'ANDREASK_IUM_SELF_DELETE_EXPLAIN'				=>	'Wenn diese Option aktiviert ist, wird dem Benutzer ein Link zu einer Seite "<strong>board_url/ium/{random_key}</strong>" angezeigt, auf der er sein Konto selbst löschen kann.',
		'ANDREASK_IUM_DELETE_APPROVE_EXPLAIN'			=>	'Wenn diese Funktion aktiviert ist, müssen alle Anfragen zur Selbstlöschung vom Administrator genehmigt werden.',
		'ANDREASK_IUM_KEEP_POSTS_EXPLAIN'				=>	'"Ja" löscht den Benutzer, aber <strong>behält</strong> die Beiträge,<br>"Nein" löscht auch die Beiträge des Benutzers.',
		'ANDREASK_IUM_AUTO_DEL_EXPLAIN'					=>	'Benutzer werden nach einer bestimmten Anzahl von Tagen automatisch gelöscht, wenn sie nach den 3 Erinnerungen nicht zurückkehren.',
		'ANDREASK_IUM_AUTO_DEL_DAYS_EXPLAIN'			=>	'Anzahl der Tage, bevor der/die Benutzer ab dem gewünschten Tag automatisch gelöscht werden.',
		'ANDREASK_IUM_TEST_EMAIL_EXPLAIN'				=>	'Eine Test-E-Mail wird an "%s" gesendet',
		'ANDREASK_IUM_INCLUDED_FORUMS_EXPLAIN'			=>	'Wähle eine Kategorie oder Unterkategorie aus, um sie aus den Listen der Top-Themen, die an die Benutzer gesendet werden, <strong>auszuschließen</strong>.',
		'ANDREASK_IUM_EXCLUDED_FORUMS_EXPLAIN'			=>	'Wähle eine Kategorie oder Unterkategorie aus, um sie in die Liste der Top-Themen <strong>aufzunehmen</strong>, die an die Benutzer gesendet wird.',
		'ANDREASK_IUM_IGNORE_LMT_EXPLAIN'				=>	'Bei "Ja" sendet die Erweiterung nur 3 Erinnerungen, bei "Nein" sendet die Erweiterung weiterhin Erinnerungen mit Einstellung des dritten Intervalls',
		'ANDREASK_IUM_RESPECT_USR_EXPLAIN'				=>	'Einige Benutzer haben möglicherweise festgelegt, dass sie keine Massenmails von Administratoren erhalten möchten. Bei "Ja" sendet die Erweiterung die Erinnerung nicht an diese Benutzer.',
		'ANDREASK_IUM_NO_REPLY_EXPLAIN'					=>  'Wenn Du möchtest, kannst Du eine <strong>Antwort/keine Antwort</strong>-E-Mail angeben. Wenn Du nichts einträgst, wird die Erweiterung die Kontaktdaten des Forums verwenden.',


		// ACP-Konfigurationsseite
		'INACTIVE_MAIL_SENT_TO'							=>	'Eine Muster-E-Mail für inaktive Benutzer wurde an "%s" gesendet.',
		'SLEEPER_MAIL_SENT_TO'							=>	'Eine Muster-E-Mail für "Schläfer" wurde an "%s" gesendet.',
		'SEND_SLEEPER'									=>	'Vorlage Schläfer senden',
		'SEND_INACTIVE'									=>	'Vorlage Inaktiver Benutzer senden',
		'PLUS_SUBFORUMS'								=>	'+Unterforen',
	)
);
