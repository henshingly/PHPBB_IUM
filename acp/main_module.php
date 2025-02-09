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

namespace andreask\ium\acp;

use DateTime;
use DateInterval;

class main_module
{

	var $tpl_name;
	var $page_title;
	public function main($id, $mode)
	{
		global $user, $template, $request, $config, $phpbb_container, $phpbb_root_path, $phpEx;
		$config_text = $phpbb_container->get('config_text');

		if ($mode == 'ium_settings')
		{
			$this->tpl_name 	= 'acp_ium_body';
			$this->page_title 	= $user->lang('ACP_IUM_TITLE');
			$user->add_lang_ext('andreask/ium', 'acp_ium_config');

			$form_key = 'andreask_ium';

			add_form_key($form_key);

			// Send sleeper template to admin
			if ($request->is_set_post('send_sleeper'))
			{
				$mail_to_sleeper = $phpbb_container->get('andreask.ium.classes.reminder');
				$mail_to_sleeper->send_to_admin($user->data['user_id'], 'send_sleeper');
				trigger_error($user->lang('SLEEPER_MAIL_SENT_TO', $user->data['user_email']) . adm_back_link( $this->u_action ), E_USER_NOTICE);
			}
			// Send inactive template to admin
			else if ($request->is_set_post('send_inactive'))
			{
				$mail_to_inactive = $phpbb_container->get('andreask.ium.classes.reminder');
				$mail_to_inactive->send_to_admin($user->data['user_id'], 'send_inactive');
				trigger_error($user->lang('INACTIVE_MAIL_SENT_TO', $user->data['user_email']) . adm_back_link( $this->u_action ), E_USER_NOTICE);
			}

			// Save settings
			if ( $request->is_set_post('submit_settings') )
			{
				// Check form key
				if ( !check_form_key($form_key) )
				{
					trigger_error($user->lang('FORM_INVALID'). adm_back_link( $this->u_action ), E_USER_WARNING);
				}

				// If everything is OK store the setting
				$this->update_config();
				trigger_error($user->lang('CONFIG_UPDATED') . adm_back_link($this->u_action));
			}

			// Exclude forum(s)
			if ( $request->is_set_post('exclude_forum') )
			{
				// Check form key
				if ( !check_form_key($form_key) )
				{
					trigger_error($user->lang('FORM_INVALID'). adm_back_link( $this->u_action ), E_USER_WARNING);
				}

				if ( $request->variable('subforum_id', 0 ) == 0 )
				{
					trigger_error($user->lang('SELECT_A_FORUM'). adm_back_link( $this->u_action ), E_USER_WARNING);
				}

				$already_excluded_forums_array = json_decode($config_text->get('andreask_ium_ignore_forum'), true);
				$new_forum_array = $this->sweap_sforums($request->variable('subforum_id', 0));
				if ($already_excluded_forums_array != null)
				{
					$merge_forums = array_merge($already_excluded_forums_array, $new_forum_array);
					$config_text->set('andreask_ium_ignore_forum', json_encode($merge_forums));
				}
				else
				{
					$config_text->set('andreask_ium_ignore_forum', json_encode($new_forum_array));
				}
			}

			// Include forum(s)
			if ( $request->is_set_post('include_forum'))
			{
				if ( !check_form_key($form_key) )
				{
					trigger_error($user->lang('FORM_INVALID'). adm_back_link( $this->u_action ), E_USER_WARNING);
				}

				if ( $request->variable('excluded_forum', 0) == 0 )
				{
					trigger_error($user->lang('SELECT_A_FORUM'). adm_back_link( $this->u_action ), E_USER_WARNING);
				}
				$remove_array 		= $this->sweap_sforums($request->variable('excluded_forum', 0));
				$conf_text_array 	= json_decode($config_text->get('andreask_ium_ignore_forum'));
				$new_conf_array 	= array_values(array_diff( $conf_text_array, $remove_array));
				$new_conf_text 		= json_encode($new_conf_array);
				$config_text->set('andreask_ium_ignore_forum', $new_conf_text);
			}

			// To get the forum list we have to include functions_admin
			// First check if it's not exist already...
			if (!function_exists('make_forum_select'))
			{
				include $phpbb_root_path . "includes/functions_admin." . $phpEx;
			}

			$ignored_forum_ids_text = $config_text->get('andreask_ium_ignore_forum');
			$ignored_forum_ids = json_decode($ignored_forum_ids_text);
			if (!empty($ignored_forum_ids))
			{
				$excluded_list = $this->make_excluded_forums_list($ignored_forum_ids);
			}
			else
			{
				// Get the excluded list, if not exist show somethin else instead.
				$excluded_list = '<option disabled>' . $user->lang('EXCLUDED_EMPTY') . '</option>';
			}

			// TODO Check dont reply just in case!
			// if(filter_var("some@address.com", FILTER_VALIDATE_EMAIL)) {
			// 	// valid address
			// }
			// else {
			//     // invalid address
			// }
			// php > 5.2

			// Get the forum list
			$forum_list = make_forum_select(false, $ignored_forum_ids, true, false, false, false, true);

			// Build option list from forums list
			$included_forum_list = $this->build_subforum_options($forum_list);

			$template->assign_vars(array(
				'ANDREASK_IUM_ENABLE'					=>	$config['andreask_ium_enable'],
				'ANDREASK_IUM_INTERVAL'					=>	$config['andreask_ium_interval'],
				'ANDREASK_IUM_INTERVAL2'				=>	$config['andreask_ium_interval2'],
				'ANDREASK_IUM_INTERVAL3'				=>	$config['andreask_ium_interval3'],
				'ANDREASK_IUM_USR_RSPCT'				=>	$config['andreask_ium_respect_user_choice'],
				'ANDREASK_IUM_IGNORE_LIMIT'				=>	$config['andreask_ium_ignore_limit'],
				'ANDREASK_IUM_TOP_USER_THREADS'			=>	$config['andreask_ium_top_user_threads'],
				'ANDREASK_IUM_TOP_USER_THREADS_COUNT'	=>	$config['andreask_ium_top_user_threads_count'],
				'ANDREASK_IUM_TOP_FORUM_THREADS'		=>	$config['andreask_ium_top_forum_threads'],
				'ANDREASK_IUM_TOP_FORUM_THREADS_COUNT'	=>	$config['andreask_ium_top_forum_threads_count'],
				'ANDREASK_IUM_EMAIL_LIMIT'				=>	$config['andreask_ium_email_limit'],
				'ANDREASK_IUM_NO_REPLY'					=>	$config['andreask_ium_no_reply'],
				'ANDREASK_IUM_SELF_DELETE'				=>	$config['andreask_ium_self_delete'],
				'ANDREASK_IUM_APPROVE_DEL'				=>	$config['andreask_ium_approve_del'],
				'ANDREASK_IUM_KEEP_POSTS'				=>	$config['andreask_ium_keep_posts'],
				'ANDREASK_IUM_AUTO_DEL'					=>	$config['andreask_ium_auto_del'],
				'ANDREASK_IUM_AUTO_DEL_DAYS'			=>	$config['andreask_ium_auto_del_days'],
				'ANDREASK_IUM_TEST_EXPLAIN'				=>	$user->lang('ANDREASK_IUM_TEST_EMAIL_EXPLAIN', $user->data['user_email']),
				'ANDREASK_IUM_EXCLUDE_FORUMS'			=>	$included_forum_list,
				'ANDREASK_IUM_UNEXCLUDE_LIST'			=>	$excluded_list,
			));
		}

		if ($mode == 'ium_list')
		{
			$this->tpl_name = 'acp_ium_inactive_users';
			$this->page_title = $user->lang('ACP_IUM_TITLE2');
			$user->add_lang_ext('andreask/ium', 'inactive_user_list');
			$user->add_lang('memberlist');

			$start 		= $request->variable('start', 0);
			$limit 		= $request->variable('users_per_page', 20);
			$with_posts = $request->variable('with_posts', 0);
			$actions 	= $request->variable('count_back', '30d');
			if ($actions == 'select')
			{
				$actions = '30d';
			}
			$sort_by = $request->variable('sort_by', 'reg_date');
			if ($sort_by == 'select')
			{
				$sort_by = 'reg_date';
			}
			$sort_order = $request->variable('sort_order', 0);

			// Keep the limit between 20 and 200
			if ($limit > 200)
			{
				$limit = 200;
			}
			else if ($limit < 20)
			{
				$limit = 20;
			}

			// get the options to an array so that we pass them to the sql query
			$options = array(
				'with_posts'	=>	$with_posts,
				'count_back'	=>	$actions,
				'sort_by'		=>	$sort_by,
				'sort_order'	=>	$sort_order,
				'approval'		=>	null,
				'ignore'		=>	false
			);

			//base url for pagination, filtering and sorting
			$base_url = $this->u_action
				. "&amp;users_per_page="	. $limit
				. "&amp;with_posts=" 		. $with_posts
				. "&amp;count_back=" 		. $actions
				. "&amp;sort_by=" 			. $sort_by
				. "&amp;sort_order=" 		. $sort_order;

			// Long list probably should make shorter.
			// IDEA perhaps just set number of days insted of this?
			$option_ary = array(
				'select'	=> 'SELECT',
				'30d' 		=> 'THIRTY_DAYS',
				'60d' 		=> 'SIXTY_DAYS',
				'90d' 		=> 'NINETY_DAYS',
				'4m' 		=> 'FOUR_MONTHS',
				'6m' 		=> 'SIX_MONTHS',
				'9m' 		=> 'NINE_MONTHS',
				'1Y' 		=> 'ONE_YEAR',
				'2Y' 		=> 'TWO_YEARS',
				'3Y' 		=> 'THREE_YEARS',
				'5Y' 		=> 'FIVE_YEARS',
				'7Y' 		=> 'SEVEN_YEARS',
				'10Y' 		=> 'DECADE',
			);

			// Sort by element
			$sort_by_ary = array(
				'select' 				=> 'SORT_BY_SELECT',
				'username' 				=> 'USERNAME',
				'posts' 				=> 'POSTS',
				'reg_date' 				=> 'JOINED',
				'last_visit' 			=> 'LAST_VISIT',
				'last_sent_reminder'	=> 'LAST_SENT_REMINDER',
				'count' 				=> 'COUNT',
				'reminder_date' 		=> 'REMINDER_DATE',
			);

			// Get the users list using get_inactive_users required parameters $limit $start for the full list of users
			$rows 			= $this->get_inactive_users(true, $limit, $start, false, $options);
			$inactive_count = $rows['count'];
			$rows 			= $rows['results'];

			// Load pagination
			$pagination = $phpbb_container->get('pagination');
			$start 		= $pagination->validate_start($start, $limit, $inactive_count);
			$pagination->generate_template_pagination($base_url, 'pagination', 'start', $inactive_count, $limit, $start);

			// Assign template vars (including pagination)
			$template->assign_vars(array(
				'S_INACTIVE_USERS' 		=> true,
				'S_INACTIVE_OPTIONS' 	=> build_select($option_ary, $actions),
				'S_IUM_SORT_BY' 		=> build_select($sort_by_ary, $sort_by),
				'COUNT_BACK' 			=> $options['count_back'],
				'PER_PAGE' 				=> $limit,
				'TOTAL_USERS' 			=> $inactive_count,
				'WITH_POSTS' 			=> (bool) $with_posts,
				'SORT_ORDER' 			=> (bool) $sort_order,
				'USERS_PER_PAGE' 		=> $limit,
				'TOTAL_USERS_WITH_DAY'	=> $user->lang('TOTAL_USERS_WITH_DAY_AMOUNT', $inactive_count, $user->lang($option_ary[$actions]))
			));
			$user->add_lang('common');
			// Assign row results to template var inactive
			foreach ($rows as $row)
			{
				switch ($row['user_inactive_reason'])
				{
					case 0:
						$row['user_inactive_reason'] = $user->lang('ACP_IUM_INACTIVE', 0);
					break;

					case INACTIVE_REGISTER:
						$row['user_inactive_reason'] = $user->lang('INACTIVE_REASON_REGISTER');
					break;

					case INACTIVE_PROFILE:
						$row['user_inactive_reason'] = $user->lang('INACTIVE_REASON_PROFILE');
					break;

					case INACTIVE_MANUAL:
						$row['user_inactive_reason'] = $user->lang('INACTIVE_REASON_MANUAL');
					break;

					case INACTIVE_REMIND:
						$row['user_inactive_reason'] = $user->lang('INACTIVE_REASON_REMIND');
					break;
				}

				$link = generate_board_url() . "/adm/index.$phpEx?i=users&amp;mode=overview&amp;redirect=ium_approval_list&amp;sid=$user->session_id&amp;u=".$row['user_id'];
				$template->assign_block_vars('inactive', array(
					'USERNAME' 				=> $row['username'],
					'JOINED' 				=> $user->format_date($row['user_regdate']),
					'POSTS' 				=> ($row['user_posts']) ?: 0,
					'LAST_VISIT' 			=> ($row['user_lastvisit']) ? $user->format_date($row['user_lastvisit']) : $user->lang('NEVER_CONNECTED'),
					'INACTIVE_DATE' 		=> ($row['user_inactive_time']) ? $user->format_date($row['user_inactive_time']) : $user->lang('ACP_IUM_NODATE'),
					'REASON' 				=> $row['user_inactive_reason'],
					'COUNT' 				=> ($row['ium_remind_counter']) ?: $user->lang('NO_REMINDER_COUNT'),
					'LAST_SENT_REMINDER' 	=> ($row['ium_previous_sent_date']) ? $user->format_date($row['ium_previous_sent_date']) : $user->lang('NO_PREVIOUS_SENT_DATE'),
					'REMINDER_DATE' 		=> ($row['ium_reminder_sent_date']) ? $user->format_date($row['ium_reminder_sent_date']) : $user->lang('NO_REMINDER_SENT_YET'),
					'IGNORE_USER' 			=> $row['ium_dont_send'],
					'LINK_TO_USER'			=> $link,
				));
			}
		}

		if ($mode == 'ium_approval_list')
		{
			$form_key = 'andreask_ium';
			add_form_key($form_key);

			$this->tpl_name		= 'acp_ium_approval_list';
			$this->page_title	= $user->lang('ACP_IUM_APPROVAL_LIST_TITLE');
			$user->add_lang('memberlist');
			$user->add_lang_ext('andreask/ium', 'ignore_delete_list');

			if ( $request->is_set_post('approve') )
			{
				// Check form key
				if ( !check_form_key($form_key) )
				{
					trigger_error($user->lang('FORM_INVALID') . adm_back_link( $this->u_action ), E_USER_WARNING);
				}

				// Check if any user is selected from the list
				if ($request->variable('mark', array(0)) == 0)
				{
					trigger_error($user->lang('NO_USER_SELECTED') . adm_back_link( $this->u_action ), E_USER_WARNING);
				}

				// Else do your magic...
				$delete = $phpbb_container->get('andreask.ium.classes.delete_user');
				$mark = $request->variable('mark', array(0));
				$delete->delete($mark, 'admin');

				trigger_error($user->lang('DELETED_SUCCESSFULLY') . adm_back_link($this->u_action), E_USER_NOTICE);
			}

			if ( $request->is_set_post('do') )
			{
				// Check form key
				if ( !check_form_key($form_key) )
				{
					trigger_error($user->lang('FORM_INVALID') . adm_back_link( $this->u_action ), E_USER_WARNING);
				}

				// Check if any user is selected from the list
				if (empty($request->variable('mark', array(0))))
				{
					trigger_error($user->lang('NO_USER_SELECTED') . adm_back_link( $this->u_action ), E_USER_WARNING);
				}

				// Else do your magic...
				$action = $request->variable('action', 'none');
				$mark = $request->variable('mark', array(0));

				switch ($action)
				{
					case 'approve':
						$delete = $phpbb_container->get('andreask.ium.classes.delete_user');
						$delete->delete($mark, 'admin');
						trigger_error($user->lang('DELETED_SUCCESSFULLY') . adm_back_link($this->u_action), E_USER_NOTICE);
						break;
					case 'reset':
						$update = $phpbb_container->get('andreask.ium.classes.reminder');
						$update->reset_counter($mark);
						trigger_error($user->lang('RESET_REMINDERS') . adm_back_link($this->u_action), E_USER_NOTICE);
						break;
					case 'dont_ignore':
						$unignore = $phpbb_container->get('andreask.ium.classes.ignore_user');
						$unignore->update_user($mark, 0, true);
						trigger_error($user->lang('NOT_IGNORED') . adm_back_link($this->u_action), E_USER_NOTICE);
						break;
					case 'none':
						trigger_error($user->lang('SELECT_ACTION') . adm_back_link($this->u_action), E_USER_WARNING);
						break;
				}
			}

			if ( $request->is_set_post('add_users_options'))
			{
				// Check form key
				if ( !check_form_key($form_key) )
				{
					trigger_error($user->lang('FORM_INVALID') . adm_back_link( $this->u_action ), E_USER_WARNING);
				}
				if ( !$request->variable('usernames', ''))
				{
					trigger_error($user->lang('NO_USER_TYPED') . adm_back_link( $this->u_action ), E_USER_WARNING);
				}

				// Users ignore list
				$users 	= explode("\n", $request->variable('usernames', '', true));
				// clean empty lines if exist...
				$users 	= array_filter($users);
				// trim white spaces
				$users 	= array_map('trim', $users);
				// remove doubles if exist...
				$users 	= array_unique($users);

				$ignore = $phpbb_container->get('andreask.ium.classes.ignore_user');

				$result = $ignore->exist($users);
				if ($result === true)
				{
					$ignore->ignore_user($users, 2);
				}
				else
				{
					$not_found = implode(', ', $result);
					trigger_error($user->lang('USER_NOT_FOUND', $not_found) . adm_back_link( $this->u_action ), E_USER_WARNING);
				}
			}

			if ( $request->is_set_post('ignore'))
			{
				$user_ids 	= $request->variable('user_id', array(0));
				$remove 	= $phpbb_container->get('andreask.ium.classes.ignore_user');
				foreach ($user_ids as $usr_id)
				{
						$remove->update_user($usr_id, false, true);
				}
			}

			// Group ignore list
			$groups 		= $this->get_groups();
			$ignored_groups = $config_text->get('andreask_ium_ignored_groups');
			$s_defined_group_options = '';

			foreach ($groups as $group)
			{
				$group_name = $user->lang($group['group_name']);
				$group_name = isset($group_name) ? $user->lang($group['group_name']) : $group['group_name'];
				$selected = '';
				if ($ignored_groups != null)
				{
					$selected = (in_array($group['group_id'], json_decode($ignored_groups))) ? ' selected="selected" ' : '';
				}

				$s_defined_group_options .= '<option value="' . $group['group_id'] . '"'. $selected .'>' . $group_name . '</option>';
			}

			if ( $request->is_set_post('ignore_group'))
			{
				$group_ids 		= $request->variable('group_id', array(0));

				if ($group_ids != 0)
				{
					$ignore_groups 	= json_encode($group_ids);
					$config_text->set('andreask_ium_ignored_groups', $ignore_groups);
					trigger_error($user->lang('CONFIG_UPDATED') . adm_back_link($this->u_action));
				}
			}

			$start = $request->variable('start', 0);
			$limit = $request->variable('users_per_page', 20);

			// get the options to an array so that we pass them to the sql query
			$options = array(
				'with_posts'	=>	false,
				'count_back'	=>	false,
				'sort_by'		=>	'request_date',
				'sort_order'	=>	false,
				'approval'		=>	true,
				'ignore'		=>	false,
			);

			//base url for pagination, filtering and sorting
			$base_url = $this->u_action . "&amp;users_per_page=" . $limit;

			// Get the users list for approval deletion/reset using get_inactive_users
			$rows = $this->get_inactive_users(true, $limit, $start, true, $options);
			$approval_count = $rows['count'];
			$rows = $rows['results'];

			$opt_out = array(
				'with_posts'	=> false,
				'count_back'	=> false,
				'sort_by'		=> 'username',
				'sort_order'	=> true,
				'approval'		=> false,
				'ignore'		=> 2,
			);

			// Get the users list of ignored by the admin
			$ignored 				= $this->get_inactive_users(false, $limit, $start, true, $opt_out);
			$ignored 				= $ignored['results'];
			$s_defined_user_options = '';

			foreach ($ignored as $ignored_user)
			{
				$s_defined_user_options .= '<option value="' . $ignored_user['user_id'] . '">' . $ignored_user['username'] . '</option>';
			}

			// Load pagination
			$pagination = $phpbb_container->get('pagination');
			$start 		= $pagination->validate_start($start, $limit, $approval_count);
			$pagination->generate_template_pagination($base_url, 'pagination', 'start', $approval_count, $limit, $start);

			// Assign template vars (including pagination)
			$template->assign_vars(array(
				'S_SELF_DELETE'		=>	$config['andreask_ium_approve_del'],
				'USERS_PER_PAGE'	=>	$limit,
				'TOTAL_USERS'		=>	$approval_count,
				'U_ACTION'			=>	$this->u_action,
				'IGNORED_USER'		=>	$s_defined_user_options,
				'IGNORED_GROUP'		=>	$s_defined_group_options,
				'U_FIND_USERNAME'	=>	append_sid("{$phpbb_root_path}memberlist.$phpEx", 'mode=searchuser&amp;form=add_user&amp;field=usernames')
			));

			foreach ($rows as $row)
			{
				$link = generate_board_url() . "/adm/index.$phpEx?i=users&amp;mode=overview&amp;redirect=ium_approval_list&amp;sid=$user->session_id&amp;u=".$row['user_id'];
				$template->assign_block_vars(
					'approval_list', array(
					'USER_ID'			=>	$row['user_id'],
					'USERNAME'			=>	$row['username'],
					'POSTS'				=>	($row['user_posts']) ?: 0,
					'REQUEST_DATE' 		=>	$user->format_date($row['ium_request_date']),
					'TYPE'				=>	$row['ium_type'],
					'LINK_TO_USER'		=>	$link,
					'IGNORE_METHODE'	=>	$user->lang('IGNORE_METHODE', (int) $row['ium_dont_send']),
				));
			}
		}
	}

	/**
	 * Updates configuration of the ext
	 * @return void
	 */
	protected function update_config() :void
	{
		global $config, $request;

		$config->set('andreask_ium_enable', 					$request->variable('andreask_ium_enable', false));
		$config->set('andreask_ium_interval', 					$request->variable('andreask_ium_interval', 30));
		$config->set('andreask_ium_interval2', 					$request->variable('andreask_ium_interval2', 0));
		$config->set('andreask_ium_interval3', 					$request->variable('andreask_ium_interval3', 0));
		$config->set('andreask_ium_respect_user_choice',		$request->variable('andreask_ium_respect_user_choice', true));
		$config->set('andreask_ium_ignore_limit',				$request->variable('andreask_ium_ignore_limit', true));
		$config->set('andreask_ium_self_delete', 				$request->variable('andreask_ium_self_delete', false));
		$config->set('andreask_ium_email_limit', 				$request->variable('andreask_ium_email_limit', 250));
		$config->set('andreask_ium_no_reply',					$request->variable('andreask_ium_no_reply', ''));
		$config->set('andreask_ium_approve_del', 				$request->variable('andreask_ium_delete_approve', true));
		$config->set('andreask_ium_keep_posts', 				$request->variable('andreask_ium_keep_posts', true));
		$config->set('andreask_ium_auto_del', 					$request->variable('andreask_ium_auto_del', false));
		$config->set('andreask_ium_auto_del_days', 				$request->variable('andreask_ium_auto_del_days', 7));
		$config->set('andreask_ium_top_user_threads', 			$request->variable('andreask_ium_top_user_threads', false));
		$config->set('andreask_ium_top_user_threads_count', 	$request->variable('andreask_ium_top_user_threads_count', 5));
		$config->set('andreask_ium_top_forum_threads', 			$request->variable('andreask_ium_top_forum_threads', false));
		$config->set('andreask_ium_top_forum_threads_count', 	$request->variable('andreask_ium_top_forum_threads_count', 5));

	}

	/**
	 * Getter for inactive users
	 * @param bool       $paginate
	 * @param int|null   $limit
	 * @param int|null   $start
	 * @param bool		 $acp_req
	 * @param array|null $filters
	 * @return array
	 */
	public function get_inactive_users(bool $paginate = true, int $limit = null, int $start = null, bool $acp_req = false, array $filters = null): array
	{
		return $this->inactive_users($paginate, $limit, $start, $acp_req, $filters);
	}

	/**
	 * @param            $paginate bool define if pagination is used or not.
	 * @param int|null   $limit    int Used for pagination in sql query to limit the numbers of rows.
	 * @param int|null   $start    int Used for pagination in sql query to say where to start from.
	 * @param bool		 $acp_req  bool this will enable the query to load faster since we don't need the ignored by default members
	 * @param array|null $filters  array Used for query to supply extra filters.
	 * @return array result of query and total amount of the result.
	 * @throws \Exception
	 */

	private function inactive_users(bool $paginate = true, int $limit = null, int $start = null, bool $acp_req = false, array $filters = null)
	{
		global $db, $phpbb_container;

		if ($filters)
		{
			$ignore 	= 'select';
			$options 	= '';

			if ( $filters['with_posts'] )
			{
				$options .= ' AND user_posts <> 0';
			}
			if ( $filters['approval'])
			{
				$options .= ' AND (ium_request_date <> 0 OR '. $db->sql_in_set('ium_type', array('user', 'auto')).')';
			}
			if ( $filters['ignore'] == 1)
			{
				$options .= ' AND ium_dont_send = 1 AND ium_request_date = 0 ';
			}
			if ( $filters['ignore'] == 2)
			{
				$options .= ' AND ium_dont_send = 2 ';
			}
			if ( $filters['count_back'] && $filters['count_back'] != $ignore )
			{
				/**
				* XXX
				* Big case with days back, probably will have to rethink it.
				*/

				switch ( $filters['count_back'] )
				{
					case "30d":
						$back = 'P30D';
						break;
					case "60d":
						$back = 'P60D';
						break;
					case '90d':
						$back = 'P90D';
						break;
					case '4m':
						$back = 'P4M';
						break;
					case '6m':
						$back = 'P6M';
						break;
					case '9m':
						$back = 'P9M';
						break;
					case '1Y':
						$back = 'P1Y';
						break;
					case '2Y':
						$back = 'P2Y';
						break;
					case '3Y':
						$back = 'P3Y';
						break;
					case '5Y':
						$back = 'P5Y';
						break;
					case '7Y':
						$back = 'P7Y';
						break;
					case '10Y':
						$back = 'P10Y';
						break;
					case 'select':
						$back = 'P30D';
						break;
				}
				// Current date
				$present = new DateTime();

				// Set interval
				$interval = new DateInterval($back);

				// Substract the interval of Days/Months/Years from present
				$present->sub($interval);

				// Convert past to timestamp
				$past = strtotime($present->format("y-m-d h:i:s"));

				$options .= ' AND user_regdate < ' . $past . ' AND user_lastvisit < ' . $past;
			}

			/*
			* XXX
			* Big case with sort by, probably will have to rethink it.
			*/
			if ($filters['sort_by'] && $filters['sort_by'] != $ignore)
			{
				$sort = ' ORDER BY ';
				switch ($filters['sort_by'])
				{
					case 'username':
						$sort .= 'username';
						break;
					case 'reg_date':
						$sort .= 'user_regdate';
						break;
					case 'last_visit':
						$sort .= 'user_lastvisit';
						break;
					case 'posts':
						$sort .= 'user_posts';
						break;
					case 'last_sent_reminder':
						$sort .= 'ium_previous_sent_date';
						break;
					case 'count':
						$sort .= 'ium_remind_counter';
						break;
					case 'reminder_date':
						$sort .= 'ium_reminder_sent_date';
						break;
					case 'request_date':
						$sort .= 'ium_request_date';
						break;
					case 'select':
						$sort .= 'user_regdate';
						break;
				}
				if ($filters['sort_order'] === 1)
				{
					$sort .= ' DESC';
				}
			}
		}

		// Create the SQL statement
		// $table_name = $table_prefix . 'ium_reminder';

		$must_ignore = '';
			$ignore_groups = $phpbb_container->get('andreask.ium.classes.ignore_user');
			$must_ignore = $ignore_groups->ignore_groups($acp_req);

		$sql = 'SELECT user_id, username, user_regdate, user_posts, user_lastvisit, user_inactive_time, user_inactive_reason, ium_remind_counter, ium_previous_sent_date, ium_reminder_sent_date, ium_dont_send, ium_request_date, ium_random, ium_type
			FROM ' . USERS_TABLE . '
			WHERE user_id not in (SELECT ban_userid FROM ' . BANLIST_TABLE . ')'
			. $must_ignore
			. $options
			. $sort;

		if ($paginate)
		{
			$result = $db->sql_query_limit( $sql, $limit, $start );
		}
		// w/o pagination
		else
		{
			$result = $db->sql_query($sql);
		}

		$inactive_users = array();

		// Store results
		$inactive_users = $db->sql_fetchrowset($result);

		// Be sure to free the result after a SELECT query
		$db->sql_freeresult($result);

		// Run the same query but this time unlimited to count the result, to get the total amount.
		$result = $db->sql_query($sql);

		// $row should hold the data you selected
		$count_inactive_users = array();

		// Store results
		$count_inactive_users = $db->sql_fetchrowset($result);

		$count = sizeof($count_inactive_users);

		// Be sure to free the result after a SELECT query
		$db->sql_freeresult($result);

		return array('results'	=>	$inactive_users,	'count'	=>	$count);
	}

	/**
	 * Return groups of forum
	 * @return array groups
	 */
	private function get_groups(): array
	{
		global $db;

		$sql = 'SELECT group_id, group_name
				FROM ' . GROUPS_TABLE . '
				ORDER BY group_id';

		$result = $db->sql_query($sql);

		$groups = $db->sql_fetchrowset($result);
		$db->sql_freeresult($result);

		return $groups;
	}

	/**
	 * Build options from forums list, function is same as acp_permissions of phpbb.
	 * @param	array		$forum_list Need specific information from a function of phpbb
	 * @return string		formatted options list of forums
	 */
	function build_subforum_options(array $forum_list) :string
	{
		global $user;

		$s_options = '';

		$forum_list = array_merge($forum_list);

		foreach ($forum_list as $key => $row)
		{
			if ($row['disabled'])
			{
				continue;
			}

			$s_options .= '<option value="' . $row['forum_id'] . '"' . (($row['selected']) ? ' selected="selected"' : '') . '>' . $row['padding'] . $row['forum_name'];

			// We check if a branch is there...
			$branch_there = false;

			foreach (array_slice($forum_list, $key + 1) as $temp_row)
			{
				if ($temp_row['left_id'] > $row['left_id'] && $temp_row['left_id'] < $row['right_id'])
				{
					$branch_there = true;
					break;
				}
				continue;
			}

			if ($branch_there)
			{
				$s_options .= ' [' . $user->lang('PLUS_SUBFORUMS') . ']';
			}
			$s_options .= '</option>';
		}
		return $s_options;
	}

	/**
	 * Creates the options for the excluded forums list
	 * @param  array $forum_ids Forum id(s)
	 * @return string options for selection.
	 */
	public function make_excluded_forums_list(array $forum_ids): string
	{
		global $db, $user;

		$sql = 'SELECT forum_id, forum_name, left_id, right_id FROM ' . FORUMS_TABLE . '
				WHERE ' . $db->sql_in_set('forum_id', $forum_ids) . ' ORDER BY left_id';
		$result = $db->sql_query($sql);

		$forums = [];
		$forums = $db->sql_fetchrowset($result);
		$db->sql_freeresult($result);

		$option = '';

		foreach ($forums as $forum)
		{
			if ($forum['left_id'] < $forum['right_id'] - 1)
			{
				$subforum = true;
			}
			else
			{
				$subforum = false;
			}

			$sub = ($subforum) ? '[' . $user->lang('PLUS_SUBFORUMS') . ']' : '';
			$option .= "<option value='{$forum['forum_id']}' >{$forum['forum_name']} {$sub}</option>";
		}
		return $option;
	}

	/**
	 * Getter of left and right id's for forums
	 * @param  int $forum_id Forum id
	 * @return array Comma separated forum id's
	 */
	public function sweap_sforums(int $forum_id) : array
	{
		global $db;
		$sql_arry = array('forum_id' => $forum_id);
		$sql = 'SELECT left_id, right_id
						FROM ' . FORUMS_TABLE . '
						WHERE ' . $db->sql_build_array('SELECT', $sql_arry);

		$result = $db->sql_query($sql);

		$subforums = $db->sql_fetchrow($result);

		$db->sql_freeresult($result);

		if ($subforums['left_id'] != $subforums['right_id'] - 1 )
		{
			$sql = 'SELECT forum_id FROM ' .
					FORUMS_TABLE . '
					WHERE left_id >= ' .  $subforums['left_id'] . '
					AND right_id <= ' .  $subforums['right_id'] . '
					ORDER BY left_id';
			$result = $db->sql_query($sql);
			$puzzle = [];

			while ($row = $db->sql_fetchrow($result))
			{
				$puzzle[] = $row['forum_id'];
			}
			$db->sql_freeresult($result);

			return $puzzle;
		}
		return array($forum_id);
	}
}
