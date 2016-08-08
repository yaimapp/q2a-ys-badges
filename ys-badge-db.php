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
}

/*
	Omit PHP closing tag to help avoid accidental output
*/
