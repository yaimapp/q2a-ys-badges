<?php

if (!defined('QA_VERSION')) { // don't allow this page to be requested directly from browser
		header('Location: ../../');
		exit;
}

class ys_badge_db
{
	public static function create_userbadges_table()
	{
		qa_db_query_sub(
			'CREATE TABLE IF NOT EXISTS ^ys_userbadges ('.
				'id INT(11) NOT NULL AUTO_INCREMENT,'.
				'awarded_at DATETIME NOT NULL,'.
				'user_id INT(11) NOT NULL,'.
				'notify TINYINT DEFAULT 0 NOT NULL,'.
				'object_id INT(10),'.
				'badge_slug VARCHAR (64) CHARACTER SET ascii DEFAULT \'\','.
				'PRIMARY KEY (id)'.
			') ENGINE=InnoDB DEFAULT CHARSET=utf8'
		);
	}

	public static function create_achievements_table()
	{
		qa_db_query_sub(
			'CREATE TABLE IF NOT EXISTS ^ys_achievements ('.
				'user_id INT(11) UNIQUE NOT NULL,'.
				'first_visit DATETIME,'.
				'oldest_consec_visit DATETIME,'.
				'longest_consec_visit INT(10),'.
				'last_visit DATETIME,'.
				'total_days_visited INT(10),'.
				'questions_read INT(10),'.
				'posts_edited INT(10)'.
			') ENGINE=InnoDB DEFAULT CHARSET=utf8'
		);
	}

	public static function is_first_visit($userid = null)
	{
		$sql = 'SELECT user_id FROM ^ys_achievements
				WHERE user_id = #';
		$result = qa_db_read_one_value(qa_db_query_sub($sql, $userid),true);
		if(empty($result)) {
			return true;
		}
		return false;
	}

	public static function insert_first_visit($userid)
	{
		qa_db_query_sub(
			'INSERT INTO ^ys_achievements
			 (user_id, first_visit, oldest_consec_visit,
			 longest_consec_visit, last_visit, total_days_visited,
			 questions_read, posts_edited)
			 VALUES (#, NOW(), NOW(), #, NOW(), #, #, #)',
			$userid, 1, 1, 0, 0
		);
	}

	public static function get_user_achievements($userid)
	{
		$sql = 'SELECT user_id as uid,
				first_visit as fv,
				oldest_consec_visit as ocv,
				longest_consec_visit as lcv,
				total_days_visited as tdv,
				last_visit as lv
				FROM ^ys_achievements
				WHERE user_id = #';
		return qa_db_read_one_assoc(qa_db_query_sub($sql, $userid),true);
	}

	public static function update_longest_consec_visit($oldest_consec_diff, $last_diff, $userid)
	{
		qa_db_query_sub(
			'UPDATE ^ys_achievements
			 SET last_visit = NOW(),
				 longest_consec_visit = #,
				 total_days_visited = total_days_visited + #
			 WHERE user_id = #',
			$oldest_consec_diff, $last_diff, $userid
		);
	}

	public static function update_total_days_visited($last_diff, $userid)
	{
		qa_db_query_sub(
			'UPDATE ^ys_achievements
			 SET last_visit = NOW(),
				 total_days_visited = total_days_visited + #
			WHERE user_id = #',
			$last_diff, $userid
		);
	}

	public static function update_oldest_consec_visit($userid)
	{
		qa_db_query_sub(
			'UPDATE ^ys_achievements
			 SET last_visit = NOW(),
				 oldest_consec_visit = NOW(),
				 total_days_visited = total_days_visited + 1
				 WHERE user_id = #',
			$userid
		);
	}

	public static function check_posts_number($uid, $type)
	{
		$count = qa_db_read_one_value(
			qa_db_query_sub(
				'SELECT count(*) FROM ^posts
				 WHERE userid = #
				 AND type = $',
				$uid, $type
			)
		);
		return $count;
	}

	public static function check_awarded_badges($badge_slug, $uid, $oid = NULL)
	{
		$sql = 'SELECT badge_slug FROM ^ys_userbadges
				WHERE user_id = #
				AND badge_slug = $';

		if (!empty($oid)) {
			$sql .= qa_db_apply_sub(' AND object_id = #',
									array($oid));
		}
		return qa_db_read_one_value(
			qa_db_query_sub($sql, $uid, $badge_slug),
			true
		);
	}

	public static function insert_badge_award($badge_slug, $notify, $uid, $oid = NULL)
	{
		qa_db_query_sub(
			'INSERT INTO ^ys_userbadges (awarded_at, notify, object_id, user_id, badge_slug, id) '.
			'VALUES (NOW(), #, #, #, #, 0)',
			$notify, $oid, $uid, $badge_slug
		);
	}

	public static function insert_eventlog($badge_slug, $uid, $oid = NULL)
	{
		$handle = qa_userid_to_handle($uid);
		error_log($badge_slug.":".$handle);
		qa_db_query_sub(
			'INSERT INTO ^eventlog (datetime, ipaddress, userid, handle, cookieid, event, params) '.
			'VALUES (NOW(), $, $, $, #, $, $)',
			qa_remote_ip_address(), $uid, $handle, qa_cookie_get(), 'badge_awarded', 'badge_slug='.$badge_slug.($oid ? "\t".'postid='.$oid : '')
		);
	}

	public static function get_single_post($postid = null)
	{
		return  qa_db_single_select(qa_db_full_post_selectspec(null, $postid));
	}

	public static function get_badge_to_notify($userid)
	{
		return qa_db_read_all_values(
			qa_db_query_sub(
				'SELECT badge_slug FROM ^ys_userbadges
				 WHERE user_id = #
				 AND notify >= 1',
				$userid
			)
		);
	}

	public static function remove_notification_flag($userid)
	{
		qa_db_query_sub(
			'UPDATE ^ys_userbadges SET notify=0
			 WHERE user_id = #
			 AND notify >= 1',
			$userid
		);
	}

	public static function get_badge_slug_and_oid($userid)
	{
		return qa_db_read_all_assoc(
			qa_db_query_sub(
				'SELECT badge_slug as slug, object_id AS oid
				FROM ^ys_userbadges
				WHERE user_id = #',
				$userid
			)
		);
	}

	public static function get_binary_title($postid)
	{
		return $title = qa_db_read_one_value(
			qa_db_query_sub(
				'SELECT BINARY title as title
				FROM ^posts
				WHERE postid = #',
				$postid
			),
			true
		);
	}
}

/*
	Omit PHP closing tag to help avoid accidental output
*/
