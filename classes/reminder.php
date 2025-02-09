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

namespace andreask\ium\classes;

use DateInterval;
use DateTimeImmutable;

class reminder
{
	protected $inactive_users = [];
	protected $config;
	protected $db;
	protected $user;
	protected $user_loader;
	protected $log;
	protected $top_topics;
	protected $ignore_user;
	protected $request;
	protected $table_prefix;
	protected $phpbb_root_path;
	protected $php_ext;
	protected $table_name;
	protected $routing_helper;
	protected $intervals;

	/**
	 * @param \phpbb\config\config              $config				phpBB Config
	 * @param \phpbb\db\driver\driver_interface $db					phpBB Database
	 * @param \phpbb\user                       $user				phpBB User
	 * @param \phpbb\user_loader                $user_loader		phpBB User Loader
	 * @param \phpbb\log\log                    $log				phpBB Log
	 * @param \andreask\ium\classes\top_topics  $top_topics			@andreask_ium.classes.top_topics
	 * @param \andreask\ium\classes\ignore_user $ignore_user		@andreask_ium.classes.ignore_user
	 * @param \phpbb\request\request            $request			phpBB Request
	 * @param \phpbb\routing\helper             $routing_helper		phpBB Routing Helper
	 * @param                                   $table_prefix		phpBB Table Prefix
	 * @param                                   $phpbb_root_path	phpBB Root path
	 * @param                                   $php_ext			php file ext
	 */
	public function __construct(\phpbb\config\config $config, \phpbb\db\driver\driver_interface $db, \phpbb\user $user, \phpbb\user_loader $user_loader, \phpbb\log\log $log, \andreask\ium\classes\top_topics $top_topics, \andreask\ium\classes\ignore_user $ignore_user, \phpbb\request\request $request, \phpbb\routing\helper $routing_helper, $table_prefix, $phpbb_root_path, $php_ext)
	{
		$this->config           =	$config;
		$this->db				=	$db;
		$this->user				=	$user;
		$this->user_loader		=	$user_loader;
		$this->log              =	$log;
		$this->top_topics		=	$top_topics;
		$this->ignore_user		=	$ignore_user;
		$this->request			=	$request;
		$this->table_prefix		=	$table_prefix;
		$this->routing_helper	=	$routing_helper;
		$this->php_ext          =	$php_ext;
		$this->phpbb_root_path	=	$phpbb_root_path;
		$this->intervals		=	$this->get_intervals();
	}

	/**
	 * @param $single bool is set to true set_user needs to be evoced prior to set user_id
	 * @return void
	 */

	public function send(bool $single = false) :void
	{
		if ($single)
		{
			$user = array_shift($this->inactive_users);
			$this->get_users($user['user_id']);
		}

		if (!$this->has_users())
		{
			$this->get_users();
		}

		if ( $this->has_users() )
		{
			if ( !class_exists('messenger') )
			{
				include( $this->phpbb_root_path . 'includes/functions_messenger.' . $this->php_ext );
			}

			$i = 0;
			$failed = [];
			foreach ($this->inactive_users as $sleeper)
			{
				// If it's not for a single user...
				if (!$single)
				{
					// Skip the users that have 3 reminders or more if the ext is set to send only 3 reminders.
					if ($this->config['andreask_ium_ignore_limit'] != 1 && $sleeper['ium_remind_counter'] >= 3)
					{
						// skip user
						continue;
					}
					// Skip the users that should not receive the reminder acording to their reminder counter and the set interval for it.
					if ($sleeper['ium_remind_counter'] == 0 && ($sleeper['user_lastvisit'] > $this->intervals[1]))
					{
						// skip user
						continue;
					}
					else if ($sleeper['ium_remind_counter'] == 1 && ($sleeper['ium_reminder_sent_date'] > $this->intervals[2]))
					{
						// skip user
						continue;
					}
					else if ($sleeper['ium_remind_counter'] == 2 && ($sleeper['ium_reminder_sent_date'] > $this->intervals[3]))
					{
						// skip again
						continue;
					}
					// If the user has 3 reminders or more and he was not skiped already because there is no limit to the # of the reminders. he must be skiped if it's still not the time to send a reminder.
					else if ($sleeper['ium_remind_counter'] >= 3 && ($sleeper['ium_reminder_sent_date'] > $this->intervals[3]))
					{
						continue;
					}
				}
				$i++;

				if (phpbb_version_compare($this->config['version'], '3.2', '>='))
				{
					$lang_file_loader = new \phpbb\language\language_file_loader($this->phpbb_root_path, $this->php_ext);
					$user_instance = new \phpbb\language\language($lang_file_loader);
					$user_instance->set_user_language($sleeper['user_lang']);
				}
				else
				{
					$user_row = $this->user_loader->get_user($sleeper['user_id']);
					$user_instance = new \phpbb\user('\phpbb\datetime');
					$user_instance->lang_name = $user_instance->data['user_lang'] = $sleeper['user_lang'];
					$user_instance->timezone = $user_instance->data['user_timezone'] = $sleeper['user_timezone'];
				}
				
				// Set the user topic links first.
				$topic_links = null;
				
				// If there are topics then prepare them for the e-mail.
				if ($top_user_topics = $this->top_topics->get_user_top_topics($sleeper['user_id'], $sleeper['user_lastvisit']))
				{
					$topic_links = $this->make_topics($top_user_topics);
				}
				
				// Set the forum topic links first.
				$forum_links = null;
				
				// If there are topics then prepare them for the e-mail.
				if ($top_forum_topics = $this->top_topics->get_forum_top_topics($sleeper['user_id'], $sleeper['user_lastvisit']))
				{
					$forum_links = $this->make_topics($top_forum_topics);
				}
				
				// dirty fix for now, need to find a way for the templates.
				if (phpbb_version_compare($this->config['version'], '3.2', '>='))
				{
					$lang = ( $this->lang_exists($sleeper['user_lang']) ) ? $sleeper['user_lang'] : $this->config['default_lang'];
				}
				else
				{
					$lang = ( $this->lang_exists( $user_instance->lang_name ) ) ? $user_instance->lang_name : $this->config['default_lang'];
				}

				// add template variables
				$template_ary	=	array(
					'FORGOT_PASS'	=>	generate_board_url() . "/ucp." . $this->php_ext . "?mode=sendpassword",
					'SEND_ACT_AG'	=>	generate_board_url() . "/ucp." . $this->php_ext . "?mode=resend_act",
					'USERNAME'		=>	htmlspecialchars_decode($sleeper['username']),
					'LAST_VISIT'	=>	date('d-m-Y', $sleeper['user_lastvisit']),
					'REG_DATE'		=>	date('d-m-Y', $sleeper['user_regdate']),
					'ADMIN_MAIL'	=>	$this->config['board_contact'],
				);

				$messenger = new \messenger(false);

				if (!is_null($topic_links))
				{
					$messenger->assign_vars(['USR_TPC_LIST' => $topic_links,]);
				}
				if (!is_null($forum_links))
				{
					$messenger->assign_vars(['USR_FRM_LIST' => $forum_links,]);
				}
				if ( $this->config['andreask_ium_self_delete'] == 1 && $sleeper['ium_random'] )
				{
					$link = $this->routing_helper->route('andreask_ium_controller', array('random' => $sleeper['ium_random']), true, null, \Symfony\Component\Routing\Generator\UrlGeneratorInterface::ABSOLUTE_URL);
					$template_ary = array_merge($template_ary, array('SELF_DELETE_LINK' => $link));
				}
				
				$messenger->anti_abuse_headers($this->config, $this->user);
				
				if ($this->config['andreask_ium_no_reply'])
				{
					$no_reply = htmlspecialchars_decode('No-reply');
					$no_reply_mail = $this->config['andreask_ium_no_reply'];
					
					$board_contact = '"' . mail_encode($no_reply) .'" '. '<' . $no_reply_mail . '>';
					$messenger->from($board_contact);
					$messenger->replyto($board_contact);
				}

				// mail content...
				$messenger->to($sleeper['user_email'], htmlspecialchars_decode($sleeper['username']));
				
				// Load email template depending on the user
				if ($sleeper['user_lastvisit'] != 0)
				{
					// User never came back after registration...
					$messenger->template('@andreask_ium/sleeper', $lang);
				}
				else
				{
					// User has forsaken us! :(
					$messenger->template('@andreask_ium/inactive', $lang);
				}
				$messenger->assign_vars($template_ary);
				
				// Send mail...
				if ($messenger->send())
				{
					// Update users...
					$this->update_user($sleeper);
				}
				else
				{
					// if failed save user for loging
					$failed[] = $sleeper['username'];
					$i--;
				}
				unset($topics);
				if ($i == $this->config['andreask_ium_email_limit'])
				{
					break;
				}
			}
		}

		$reminders_sent[] = (isset($i)) ? $i : 0;

		// Log it and release the user list.
		if (!empty($failed))
		{
			$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_FAILED_EMAILS', time(), [implode(', ', $failed)]);
		}
		$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_SENT_REMINDERS', time(), $reminders_sent);
		unset( $this->inactive_users );
	}

	/**
	 * Gets the users from database and loades them to inactive_users and stores them to $this->inactive_users
	 *
	 * @param int $user user_id (if single user) of the requested user.
	 * @return void
	 */

	private function get_users(int $user = 0) :void
	{
		$sql_opt = '';

		if ($user != 0)
		{
			$sql_opt .= ' AND user_id = ' . $user;
		}
		else
		{
			$sql_opt .= ($this->config['andreask_ium_respect_user_choice']) ? ' AND user_allow_massemail <> 0 ' : '';
			$sql_opt .= ($this->config['andreask_ium_ignore_limit']) ? ' AND ium_dont_send < 1 ' : ' AND ium_dont_send < 2 ';
			$sql_opt .= ' AND user_regdate < ' . $this->intervals[1];
		}

		$ignore_groups = $this->ignore_user;
		$must_ignore = $ignore_groups->ignore_groups();

		$sql = 'SELECT user_id, username, user_email, user_lang, user_dateformat, user_regdate, user_timezone, user_posts, user_lastvisit, user_inactive_time, user_inactive_reason, ium_remind_counter, ium_previous_sent_date, ium_reminder_sent_date, ium_dont_send, ium_request_date, ium_random, ium_type
					FROM '. USERS_TABLE . '
					WHERE '. $this->db->sql_in_set('user_id', '(SELECT ban_userid FROM '. BANLIST_TABLE .')', true) . $sql_opt . ' ' . $must_ignore .'
					ORDER BY user_regdate ASC';
		$result = $this->db->sql_query($sql);
		$inactive_users = [];

		// Store results to rows
		while ($row = $this->db->sql_fetchrow($result))
		{
			$inactive_users[] = $row;
		}

		// Be sure to free the result after a SELECT query
		$this->db->sql_freeresult($result);

		// Store user so we can use them.
		$this->set_users($inactive_users);
	}

	/**
	 * Setter for $inactive_users
	 * @param array $users
	 * @return void
	 */
	private function set_users(array $users) :void
	{
		$this->inactive_users = $users;
	}

	/**
	 * Set user to send to a single user.
	 * @param array $user user_id
	 */
	public function set_single(array $user) :void
	{
		$this->inactive_users = $user;
	}

	/**
	 * Checks if inactive_users is populated
	 * @return bool returns false if empty.
	 */

	public function has_users() :bool
	{
		return (bool) sizeof($this->inactive_users);
	}

	/**
	 * Updates/inserts users to ium_reminder
	 * @param $user array single user
	 */

	private function update_user(array $user) :void
	{
		// Update user ium info for the reminder
		$update_arr = array('ium_reminder_sent_date' => time());
		$remind_counter = ($user['ium_remind_counter'] + 1);
		
		// No reminders
		if ( $user['ium_remind_counter'] == 0 )
		{
			$merge = array('ium_remind_counter' => $remind_counter);
			$update_arr = array_merge($update_arr, $merge);
		}
		// 1 reminder
		else if ( $user['ium_remind_counter'] == 1 )
		{
			$random_md5	= md5(uniqid($user['user_email'], true));
			$merge = array('ium_previous_sent_date'	=>	$user['ium_reminder_sent_date'],
				'ium_remind_counter'	=>	$remind_counter,
				'ium_random'			=>	$random_md5,
				);
			$update_arr = array_merge($update_arr, $merge);
		}
		// 2 or more reminders
		else if ($user['ium_remind_counter'] >= 2)
		{
			if ($user['ium_dont_send'] == 0)
			{
				$dont_send = ['ium_dont_send' => 1];
				$update_arr = array_merge($update_arr, $dont_send);
			}
			$merge = array('ium_previous_sent_date' =>	$user['ium_reminder_sent_date'],
				'ium_remind_counter'	=>	$remind_counter,
				'ium_request_date'		=>	time(),
				'ium_type'				=>	'auto',
				);
			$update_arr = array_merge($update_arr, $merge);
		}

		$sql = 'UPDATE ' . USERS_TABLE . ' SET ' . $this->db->sql_build_array('UPDATE', $update_arr) . '
						WHERE user_id = ' . $user['user_id'];

		$this->db->sql_query($sql);
	}

	/**
	 * Check if language file exist
	 * @param  string $user_lang user language preference
	 * @return bool
	 */

	public function lang_exists(string $user_lang) :bool
	{
		if (!$user_lang)
		{
			return false;
		}

		$ext_path = $this->phpbb_root_path . 'ext/andreask/ium';
		return file_exists($ext_path . '/language/' . $user_lang);
	}

	/**
	 * Sends selected reminder template to admin.
	 * @param  int $id       id of admin that requests the template
	 * @param  string $template requested template type
	 * @return void
	 */

	public function send_to_admin(int $id, string $template)
	{

		$sql = 'SELECT user_id, username, user_email, user_lang, user_dateformat, user_regdate, user_timezone, user_posts, user_lastvisit, user_inactive_time, user_inactive_reason
				FROM ' . USERS_TABLE . ' WHERE user_id = '. $id ;

		// Run the query
		$result = $this->db->sql_query($sql);

		// Store results to rows
		$sleeper = $this->db->sql_fetchrow($result);

		// Be sure to free the result after a SELECT query
		$this->db->sql_freeresult($result);

		if ($sleeper)
		{
			if ( !class_exists('messenger') )
			{
				include( $this->phpbb_root_path . 'includes/functions_messenger.' . $this->php_ext );
			}

			// Load top_topics class
			$topics = $this->top_topics;

			// Set the user topic links first.
			$topic_links = null;

			$admin_fake_last_visit = strtotime('-356 days');
			// If there are topics then prepare them for the e-mail.
			if ($top_user_topics = $topics->get_user_top_topics( $sleeper['user_id'], $admin_fake_last_visit ))
			{
				$topic_links = $this->make_topics($top_user_topics);
			}

			// Set the forum topic links first.
			$forum_links = null;

			// If there are topics then prepare the for the mail.
			if ( $top_forum_topics = $topics->get_forum_top_topics( $sleeper['user_id'], $admin_fake_last_visit ))
			{
				$forum_links = $this->make_topics($top_forum_topics);
			}

			// dirty fix for now, need to find a way for the templates.
			$lang = ( $this->lang_exists($this->user->data['user_lang']) ) ? $this->user->data['user_lang'] : $this->config['default_lang'];

			// add template variables
			$template_ary	=	array(
				'FORGOT_PASS'	=>	generate_board_url() . "/ucp." . $this->php_ext . "?mode=sendpassword",
				'SEND_ACT_AG'	=>	generate_board_url() . "/ucp." . $this->php_ext . "?mode=resend_act",
				'USERNAME'		=>	htmlspecialchars_decode($sleeper['username']),
				'LAST_VISIT'	=>	date('d-m-Y', $sleeper['user_lastvisit']),
				'REG_DATE'		=>	date('d-m-Y', $sleeper['user_regdate']),
				'ADMIN_MAIL'	=>	$this->config['board_contact'],
			);

			$messenger = new \messenger(false);

			// If there are topics for user merge them with the template_ary
			if (!is_null($topic_links))
			{
				$messenger->assign_vars(['USR_TPC_LIST' => $topic_links,]);
			}

			// If there are forum topics merge them with the template_ary
			if (!is_null($forum_links))
			{
				$messenger->assign_vars(['USR_FRM_LIST' => $forum_links,]);
			}

			// If self delete is set and 'random' has been generated for the user merge it with the template_ary
			if ( $this->config['andreask_ium_self_delete'] == 1 && isset($sleeper['ium_random']))
			{
				$link = $this->routing_helper->route('andreask_ium_controller', array('random' => $sleeper['ium_random']), true, null, \Symfony\Component\Routing\Generator\UrlGeneratorInterface::ABSOLUTE_URL);
				$template_ary = array_merge($template_ary, array('SELF_DELETE_LINK' => $link));
			}

			if ($this->config['andreask_ium_no_reply'])
			{
				$no_reply = htmlspecialchars_decode('No-reply');
				$no_reply_mail = $this->config['andreask_ium_no_reply'];

				$board_contact = '"' . $no_reply .'" '. '<' . $no_reply_mail . '>';
				$messenger->from(mail_encode($board_contact));
				$messenger->replyto(mail_encode($board_contact));
			}

			// mail headers
			$messenger->anti_abuse_headers($this->config, $this->user);

			// mail content...
			$messenger->to($sleeper['user_email'], $sleeper['username']);

			// Load template depending on the user
			switch ($template)
			{
				case 'send_sleeper':
					// Load sleeper template...
					$messenger->template('@andreask_ium/sleeper', $lang);
				break;
				case 'send_inactive':
					$messenger->template('@andreask_ium/inactive', $lang);
				break;
				default :
				break;
			}

			// Add template_ary to mail.
			$messenger->assign_vars($template_ary);

			// Send mail...
			$messenger->send();
			unset($topics);
		}

		// Log it and release the user list.
		$template = explode('_', $template);
		$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_SENT_REMINDER_TO_ADMIN', time(), array($template[1], $sleeper['user_email']));
		unset( $this->inactive_users );
	}

	/**
	 * Resets the counter of reminders this function is called by the listener.
	 * @param array $id user_id of logged in user.
	 * @param bool	$login if true requests to reset counter comes from event listener
	 * @return void
	 */

	public function reset_counter(array $id, bool $login = false) :void
	{
		$dont_send = '';
		if ($login)
		{
			$sql = "SELECT ium_dont_send FROM " . USERS_TABLE . " WHERE " .  $this->db->sql_in_set('user_id', $id);
			$result = $this->db->sql_query($sql);
			$dont_send = $this->db->sql_fetchfield('ium_dont_send');
			$this->db->sql_freeresult($result);
		}
		if ($dont_send != 2)
		{
			$action = ', ium_dont_send = 0 ';
		}
		else
		{
			$action = '';
		}

		// reset counter(s)!
		$sql = "UPDATE " . USERS_TABLE . " SET ium_remind_counter = 0, ium_request_date = 0, ium_type ='' ". $action ." WHERE ". $this->db->sql_in_set('user_id', $id);
		$this->db->sql_query($sql);
	}

	/**
	 * Generates a formated string of topic title and link to topic.
	 * @param  array $topics Must contain forum_id, topic_id
	 * @return array
	 */

	public function make_topics(array $topics) :array
	{
		$url = generate_board_url();
		$topic_links = [];
		foreach ($topics as $key => $item)
		{
			$topic_links[$key]['title'] = $item['topic_title'];
			$topic_links[$key]['url'] = $url . "/viewtopic." . $this->php_ext . "?f=" . $item['forum_id'] . "?&t=" . $item['topic_id'];
		}
		return $topic_links;
	}

	/**
	 * Generates reminder intervals
	 * @return array Intervals [1] First, [2] Second, [3] Third
	 * @throws \Exception No exceptions
	 */
	public function get_intervals() :array
	{
		$first_interval		= (int) $this->config['andreask_ium_interval'];
		$second_interval	= (int) $this->config['andreask_ium_interval2'] ?: $first_interval;
		$third_interval		= (int) $this->config['andreask_ium_interval3'] ?: $second_interval;

		// Current date
		$current_date = new DateTimeImmutable();

		// Substract the interval of Days/Months/Years from present
		$int = $current_date->sub(new DateInterval('P' . $first_interval . 'D'));
		$int2 = $current_date->sub(new DateInterval('P' . $second_interval . 'D'));
		$int3 = $current_date->sub(new DateInterval('P' . $third_interval . 'D'));

		$intervals[1] = ($int->getTimestamp());
		$intervals[2] = ($int2->getTimestamp());
		$intervals[3] = ($int3->getTimestamp());

		return $intervals;
	}
}
