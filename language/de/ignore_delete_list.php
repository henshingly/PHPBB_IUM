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
		// Genehmigungsseite
		'IGNORE_METHODE'	=> array(
			'0'	=>	'Nicht ignoriert',
			'1'	=>	'Automatisch',
			'2'	=>	'Vom Admin ignoriert'
		),
		'IGNORE_METHODES'				=>	'Art ignorieren',
		'ACP_IUM_APPROVAL_LIST_TITLE'	=>  'Genehmigungsliste für die Löschung',
		'APPROVAL_LIST_PAGE_TITLE'		=>  'Genehmigungsliste für die Löschung',
		'IUM_APPROVAL_LIST_EXPLAIN'		=>  'Liste der Benutzer, die einen Antrag auf Löschung ihres Kontos gestellt haben',
		'NO_REQUESTS'					=>  'Noch keine Anträge',
		'NO_USER_SELECTED'				=>	'Kein Benutzer gewählt.',
		'SELECT_ACTION'					=>	'Eine Aktion auswählen',
		'IUM_MANAGMENT'					=>	'Verwaltung inaktiver Nutzer',
		'IGNORE_USER_LIST'				=>	'Benutzer zur Ignorieren-Liste hinzufügen',
		'IGNORED_USERS_LIST'			=>	'Liste von Benutzern, die ignoriert werden',
		'ADD_IGNORE_USER'				=>	'Zur Liste hinzufügen',
		'REMOVE_IGNORE_USER'			=>	'Von der Liste entfernen',
		'DELETED_SUCCESSFULLY'			=>	'Erfolgreich entfernt.',
		'REQUEST_TYPE'					=>	'Anfrage Art',
		'APPROVE'						=>	'Genehmigen',
		'NO_USER_TYPED'					=>	'Es wurde kein Benutzer angegeben',
		'USER_NOT_FOUND'				=>	'Der/die Benutzer %s wurde/n nicht gefunden.',
		'REGISTERED'					=>	'Registrierte Benutzer',
		'GUESTS'						=>	'Gäste',
		'REGISTERED_COPPA'				=>	'Registrierte COPPA-Benutzer',
		'GLOBAL_MODERATORS'				=>	'Globale Moderatoren',
		'BOTS'							=>	'Bots',
		'NEWLY_REGISTERED'				=>	'Kürzlich registrierte Benutzer',
		'USER_SELECT'					=>	'Makieren',
		'SELECT_AN_ACTION'				=>	'Eine Aktion auswählen',
		'DONT_IGNORE'					=>	'Nicht ignorieren',
		'NOT_IGNORED'					=>	'Der/die Benutzer wird/werden nicht mehr ignoriert.',
		'RESET_REMINDERS'				=>	'Zurücksetzen war erfolgreich.',
		'USER_EXIST_IN_IGNORED_GROUP'	=>	'Der/die Benutzer existiert/en in einer bereits ignorierten Gruppe.',
		'REQUEST_DATE'					=>	'Datum des Löschantrags',
		'ANDREASK_IUM_MARK_NOPOST'		=>	'Benutzer ohne Beiträge auswählen',

		'IUM_IGNORE_GROUP_MANAGMENT'		=>	'Verwaltung der Gruppen',
		'ANDREASK_IUM_UPDATE_IGNORE_LIST'	=>	'Ignorieren',
		'ANDREASK_IUM_GROUP_IGNORE'			=>	'Gruppen ignorieren',

		'ANDREASK_IUM_IGNORE_GROUP_LIST_EXPLAIN'	=>	'Hier kannst Du auswählen, welche Gruppe(n) von der Erweiterung ignoriert werden sollen. Bitte beachte, dass, auch wenn sie hier nicht <u>ausgewählt</u> sind,</br>BOTS, ADMINISTRATOREN, MODERATOREN und GÄSTE werden <b>ignoriert</b>.',
		'ANDREASK_IUM_GROUP_IGNORE_EXPLAIN'			=>	'Halte die Steuerung (STRG) (oder &#8984; für Mac) auf der Tastatur gedrückt, um mehrere Gruppen auszuwählen.',
		'ANDREASK_IUM_IGNORE_LIST_EXPLAIN'			=>	'Hier kannst Du die Benutzer verwalten, die Du ignorieren (keine Erinnerung senden) oder aus der Ignorierliste entfernen möchtest.<br/><strong>Jeder Benutzer in einer neuen Zeile.</strong><br/>Hinweis: Die folgenden Gruppen werden <strong>standardmäßig ignoriert</strong>: 1. GÄSTE, 4. GLOBALE MODERATOREN, 5. ADMINISTRATOREN und 6. BOTS',
	)
);
