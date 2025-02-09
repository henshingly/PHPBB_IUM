<?php

namespace andreask\ium\classes;

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

class ignore_user
{
	protected $user;			/** User for the language */
	protected $db; 				/** DBAL driver for database use */
	protected $config_text;		/** Db config text	*/
	protected $log;				/** Log class for logging informatin */
	protected $auth;			/** Auth class to get admins and mods */

	public function __construct(\phpbb\user $user, \phpbb\db\driver\driver_interface $db, \phpbb\config\db_text $config_text, \phpbb\auth\auth $auth, \phpbb\log\log $log)
	{
		$this->user				=	$user;
		$this->db				=	$db;
		$this->log				=	$log;
		$this->auth				=	$auth;
		$this->config_text		=	$config_text;
		$this->u_action			=	append_sid(generate_board_url() . '/' . $this->user->page['page']);
	}

	/**
	 *	Check if user exist in phpbb users table
	 *	@return mixed true if all users found or array of users that was not found.
	 */

	public function exist($users)
	{

		$sql = 'SELECT user_id, username FROM '. USERS_TABLE .' WHERE '. $this->db->sql_in_set('username', $users);
		$result = $this->db->sql_query($sql);
		$users_found = $this->db->sql_fetchrowset($result);

		foreach ($users_found as $key => $user)
		{
			if (($key = array_search($user['username'], $users )) !== null)
			{
				unset($users[$key]);
			}
		}
		if (!empty($users))
		{
			return $users;
		}
		return true;
	}

	/**
	 *  function ignore_user updates trigers an update to users table.
	 *	with the dont_send flag so they will be ignored by the reminder.
	 *	@param	$username, array of username(s)
	 *	@param	$mode, 1 (default) auto, 2 admin
	 *	@return	null
	 */

	public function ignore_user($username, $mode = 1)
	{
		/**
		*	We have to check if the given users exist or not
		*	This is done by looking USERS_TABLE. And selecting users
		*/

		$sql_query = 'SELECT user_id, username
						FROM ' . USERS_TABLE . ' WHERE ' .
						$this->db->sql_in_set('username', $username ) . $this->ignore_groups();

		$result = $this->db->sql_query($sql_query);

		$user = $this->db->sql_fetchrowset($result);
		// Always free the results
		$this->db->sql_freeresult($result);

		$so_user 		= sizeof($user);
		$so_username 	= sizeof($username);
		if (!empty($user) && $so_user == $so_username)
		{
			$this->update_user($user, $mode);
		}
		else
		{
			trigger_error($this->user->lang('USER_EXIST_IN_IGNORED_GROUP') . adm_back_link( $this->u_action ), E_USER_WARNING);
		}
	}

	 /**
	  * Function Updates dont_sent field on users table
	  * @param  array  		$user	Usernames
	  * @param  boolean		$action  true for set user to ignore false for unset ignore
	  * @param  boolean 	$user_id use user_id instead of username
	  * @return void
	  */
	public function update_user($user, $action, $user_id = false)
	{
		if ($user_id)
		{
			$username = $this->get_user_username($user);
		}
		else
		{
			$username = array_column($user, 'username');
		}

		$data = array ('ium_dont_send' => $action);
		$sql = 'UPDATE ' . USERS_TABLE . '
				SET ' . $this->db->sql_build_array('UPDATE', $data) . '
				WHERE '. $this->db->sql_in_set('username', $username);

		$this->db->sql_query($sql);
	}

	/**
	 * Getter for username
	 * @param int user_id
	 * @return string username
	 */
	private function get_user_username($id)
	{

		$sql = 'SELECT username
							FROM ' . USERS_TABLE . '
							WHERE ' . $this->db->sql_in_set('user_id', $id);
		$result = $this->db->sql_query($sql);

		$usernames = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$usernames[] = $row['username'];
		}
		$this->db->sql_freeresult($result);

		return $usernames;
	}

	/**
	 * Returns a complete string of user_type and user_id that should be ignored by the queries.
	 * @param bool $acp_req if request came from acp or not
	 * @return string Complete ignore statement for sql
	 */
	public function ignore_groups(bool $acp_req = false)
	{
		$admin_mod_array = [];

		if (!$acp_req)
		{
			// Get administrator user_ids
			$administrators = $this->auth->acl_get_list(false, 'a_', false);
			$admin_ary = (!empty($administrators[0]['a_'])) ? $administrators[0]['a_'] : array();

			// Get moderator user_ids
			$moderators = $this->auth->acl_get_list(false, 'm_', false);
			$mod_ary = (!empty($moderators[0]['m_'])) ? $moderators[0]['m_'] : array();

			// Merge them together
			$admin_mod_array = array_unique(array_merge($admin_ary, $mod_ary));
		}

		// Ignored group_ids
		$ignore = $this->config_text->get('andreask_ium_ignored_groups');
		$ignore = json_decode($ignore, true);
		if (!empty($ignore))
		{
			$sql_ary = array(
				'SELECT'	=>	'user_id',
				'FROM'		=> array(USER_GROUP_TABLE => 'gr'),
				'WHERE'		=> $this->db->sql_in_set('group_id', $ignore)
			);
			$sql = $this->db->sql_build_query('SELECT', $sql_ary);
			$result = $this->db->sql_query($sql);
			$users = [];
			while ( $user = $this->db->sql_fetchrow($result))
			{
				$users[]= (int) $user['user_id'];
			}
			$this->db->sql_freeresult($result);
			$ignore = ' AND ' . $this->db->sql_in_set('user_id', $users, true );
		}
		else
		{
			$ignore = '';
		}

		// Make an array of user_types to ignore
		$ignore_users_extra = array(USER_FOUNDER, USER_IGNORE);

		// Make an array of banned users to ignore
		$banned_users = '';
		if (!$acp_req)
		{
			if ($banned = $this->get_banned())
			{
				$banned_users  = ' AND ' . $this->db->sql_in_set('user_id', $banned, true);
			}
			else
			{
				$banned_users = '';
			}
		}

		$text = ' AND '	. $this->db->sql_in_set('user_type', $ignore_users_extra, true) .'
			AND user_inactive_reason <> '. INACTIVE_MANUAL .' AND user_id <> ' . ANONYMOUS . $ignore . $banned_users;
		$text .= ($admin_mod_array) ? ' AND '	. $this->db->sql_in_set('user_id', $admin_mod_array, true) : '';

		return $text;
	}

	public function get_groups($user_id)
	{
		$sql = 'SELECT group_id FROM ' . USER_GROUP_TABLE . '
				WHERE user_id = ' . (int) $user_id;

		$result = $this->db->sql_query($sql);

		$group_ids = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$group_ids[] = $row['group_id'];
		}

		$this->db->sql_freeresult($result);

		return $group_ids;
	}

	/**
	 * Getter for banned users
	 *
	 * @return array of user ids
	 */
	private function get_banned(): array
	{
		$sql = 'SELECT ban_userid from '. BANLIST_TABLE;
		$result = $this->db->sql_query($sql);
		while($banned_user = $this->db->sql_fetchrow($result))
		{
			$banned[] = (int) $banned_user['ban_userid'];
		}
		$this->db->sql_freeresult($result);
		return $banned;
	}
}
