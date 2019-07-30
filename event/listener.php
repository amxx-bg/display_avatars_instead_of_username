<?php

/**
*
* @package Online users avatars
* @copyright Evil 2019 (c)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace evilsystem\display_avatars_instead_of_username\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class listener implements EventSubscriberInterface {
	protected $user;

	public function __construct(\phpbb\user $user) {
		$this->user = $user;
	}

	static public function getSubscribedEvents() {
		return array(
			'core.obtain_users_online_string_sql'			=> 'obtain_users_online_string_sql',
			'core.obtain_users_online_string_modify'		=> 'obtain_users_online_string_modify',
		);
	}

	/** Add avatars in SQL */
	public function obtain_users_online_string_sql($event) {
		$sql_ary = $event['sql_ary'];
		$sql_ary['SELECT'] .= ', u.user_avatar, u.user_avatar_type, u.user_avatar_width, u.user_avatar_height';
		$event['sql_ary'] = $sql_ary;
	}

	/** Edit online users */
	public function obtain_users_online_string_modify($event) {
		$u_online = $event['user_online_link'];
		$online_users = $event['online_users'];

		foreach ($event['rowset'] as $row) {
			if (!isset($u_online[$row['user_id']]))
				continue;
			
			$u_online[$row['user_id']] =( $row['user_type'] <> USER_IGNORE || !isset($online_users['hidden_users'][$row['user_id']]) ?  '<a class="lastpostavatar" href="' . get_username_string('profile', $row['user_id'], $row['username'], $row['user_colour']) . '" title="' . $row['username'] . '">' . $this->a_img($row) . '</a>' : '<span class="lastpostavatar" title="' . $row['username'] . '">' . $this->a_img($row) . '</span>');
		}

		$online_userlist = implode(' ', $u_online);
		$event['online_userlist'] = $online_userlist;
	}

	private function a_img($avatar) {
		if (!empty($avatar['user_avatar'])) {
			$avatar['user_avatar_width'] = ($avatar['user_avatar_height'] && $avatar['user_avatar_width'] ? round(40/$avatar['user_avatar_height']*$avatar['user_avatar_width']) : 40);
			$avatar['user_avatar_height'] = 40;

			return phpbb_get_user_avatar($avatar);
		}

		$no_avatar = generate_board_url() . "/styles/" . rawurlencode($this->user->style['style_path']) . '/theme/images/no_avatar.gif';
		return '<img class="avatar" src="' . $no_avatar . '" width="40" height="40" alt="" />';
	}
}
