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
			'INSERT INTO ^ys_achievements (user_id, first_visit, oldest_consec_visit, longest_consec_visit, last_visit, total_days_visited, questions_read, posts_edited) VALUES (#, NOW(), NOW(), #, NOW(), #, #, #)',
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
	
}

/*
	Omit PHP closing tag to help avoid accidental output
*/
