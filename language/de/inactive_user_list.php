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
		// Sortieren nach, Optionen für die Liste der inaktiven Benutzer
		'ACP_IUM_INACTIVE'	=>  array(
			0	=>	'Aktiv',
			// Die übrigen Gründe sind nicht aktiv, da sie über constants.php überprüft werden.
			1	=>  'Registrierung vor Aktivierung',
			2	=>	'Profile change',
			3	=>	'Vom Admin deaktiviert',
			4	=>	'Dauerhaft gesperrt',
			5	=>	'Vorübergehend gesperrt'
		),
		'NEVER_CONNECTED'				=>	'Benutzer hat sich nie angemeldet',

		// Seite mit der Liste der inaktiven Benutzer
		'ACP_IUM_NODATE'				=>	'Benutzer ist <strong>nicht</strong> deaktiviert',
		'ACP_USERS_WITH_POSTS'			=>	'Nur Nutzer mit Beiträgen anzeigen',
		'LAST_SENT_REMINDER'			=>	'Vorherige Erinnerung',
		'NO_REMINDER_COUNT'				=>	'Noch keine Erinnerung verschickt',
		'COUNT'							=>	'Anzahl Erinnerungen',
		'NO_PREVIOUS_SENT_DATE'			=>  '-',
		'REMINDER_DATE'					=>	'Letzte Erinnerung gesendet',
		'NO_REMINDER_SENT_YET'			=>	'Noch keine Erinnerung verschickt',
		'IUM_INACTIVE_REASON'			=>	'Status',
		'TOTAL_USERS_WITH_DAY_AMOUNT'	=>	'<strong>%1$s</strong> Benutzer insgesamt <i>für das festgelegte Intervall</i> von "<strong>%2$s</strong>".',

		// Legende der Konfigurationsseite
		'IUM_INACTIVE_USERS_EXPLAIN'	=>	'In dieser Liste kannst Du die Benutzer sehen, die sich registriert haben, deren Konten aber inaktiv sind, und diejenigen, die das Forum seit dem hier festgelegten Zeitraum nicht besucht haben.<br>Die Farben der Benutzernamen stellen den Ignorierstatus dar. <span style="color: #DC143C;"><strong>Rot</strong></span> -> Von einem Administrator ignoriert, <span style="color: #008000;"><strong>Grün</strong ></span> -> Automatisch ignoriert, <span style="color: #000000;"><strong>Schwarz</strong></span> -> Nicht ignoriert.',

		// Listen sortieren
		'COUNT_BACK'					=>	'<strong>AB</strong> dem Intervall Tage/Monate/Jahre und rückwärts',
		'ACP_DESCENDING'				=>	'Absteigende Reihenfolge',
		'SORT_BY_SELECT'				=>	'Sortiere nach',
		'SELECT'						=>	'Wähle T/M/J',
		'THIRTY_DAYS'					=>	'Dreißig Tage',
		'SIXTY_DAYS'					=>	'Sechszig Tage',
		'NINETY_DAYS'					=>	'Neunzig Tage',
		'FOUR_MONTHS'					=>	'Vier Monate',
		'SIX_MONTHS'					=>	'Sechs Monate',
		'NINE_MONTHS'					=>	'Neun Monate',
		'ONE_YEAR'						=>	'Ein Jahr',
		'TWO_YEARS'						=>	'Zwei Jahre',
		'THREE_YEARS'					=>	'Drei Jahre',
		'FIVE_YEARS'					=>	'Fünf Jahre',
		'SEVEN_YEARS'					=>	'Sieben Jahre',
		'DECADE'						=>	'Eine Dekade',
	)
);
