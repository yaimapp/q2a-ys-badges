<?php

if (!defined('QA_VERSION')) { // don't allow this page to be requested directly from browser
		header('Location: ../../');
		exit;
}

function ys_get_badge_level($level) {

	// badge categories, e.g. bronze, silver, gold

	$badge_levels = array();

	$badge_levels[1] = array('slug'=>'bronze','name'=>qa_lang('ys_badges/bronze'));
	$badge_levels[2] = array('slug'=>'silver','name'=>qa_lang('ys_badges/silver'));
	$badge_levels[3] = array('slug'=>'gold','name'=>qa_lang('ys_badges/gold'));

	$level = (int)$level;

	return $badge_levels[$level];

}

function ys_badge_name($slug, $reset=false) {
	if($reset)
		$name = qa_lang('ys_badges/'.$slug);
	else
		$name = qa_opt('ys_badge_'.$slug.'_name') ? qa_opt('ys_badge_'.$slug.'_name') : qa_lang('ys_badges/'.$slug);

	// plugins

	if($name == '[ys_badges/'.$slug.']') {
		global $qa_lang_file_pattern;
		foreach($qa_lang_file_pattern as $name => $files) {
			$lang = qa_lang($name.'/ys_badge_'.$slug);
			if($lang != '['.$name.'/ys_badge_'.$slug.']') {
				return $lang;
			}
		}
		return $slug;
	}
	return $name;
}

function ys_badge_desc_replace($slug, $var=null, $admin=false) {
	$desc = qa_opt('ys_badge_'.$slug.'_desc') ? qa_opt('ys_badge_'.$slug.'_desc') : qa_lang('ys_badges/'.$slug.'_desc');

	// plugins

	if($desc == '[ys_badges/'.$slug.'_desc]') {
		global $qa_lang_file_pattern;
		foreach($qa_lang_file_pattern as $name => $files) {
			$lang = qa_lang($name.'/ys_badge_'.$slug.'_desc');
			if($lang != '['.$name.'/ys_badge_'.$slug.'_desc]') {
				$desc = $lang;
				break;
			}
		}
	}

	// var replace

	if($var) {
		$desc = $admin ? str_replace('#','<input type="text" name="ys_badge_'.$slug.'_var" size="4" value="'.$var.'">',$desc) : str_replace('#',$var,$desc);
		$desc = preg_replace('/\^([^^]+)\^(\S+)/', ($var == 1 ? "$1" : "$2"), $desc);
	}

	// other badge reference replace

	preg_match_all('|\$(\S+)|',$desc,$others,PREG_SET_ORDER);

	if(!$others) return $desc;

	foreach($others as $other) {
		if(!qa_opt('ys_badge_'.$other[1].'_name')) qa_opt('ys_badge_'.$other[1].'_name',qa_lang('ys_badges/'.$other[1]));
		$name = qa_opt('ys_badge_'.$other[1].'_name');

		$desc = str_replace($other[0],$name,$desc);
	}
	return $desc;
}

function ys_check_days_diff($dest)
{
	$today = new DateTime();
	$today->setTime(0,0,0);
	$dest_date = new DateTime($dest);
	$dest_date->setTime(0,0,0);
	$interval = $today->diff($dest_date);
	return $interval->days;
}

function ys_badge_award_check($badges, $var, $uid, $oid = NULL, $notify = 1)
{
	if(!$uid) return;
	$awarded = array();
	foreach($badges as $badge_slug) {

		if(($var === false || (int)$var >= (int)qa_opt('ys_badge_'.$badge_slug.'_var')) && qa_opt('ys_badge_'.$badge_slug.'_enabled') !== '0') {
			if($oid) {
				$result = ys_badge_db::check_awarded_badges($badge_slug, $uid, $oid);
			} else {
				$result = ys_badge_db::check_awarded_badges($badge_slug, $uid);
			}

			if ($result === null) { // not already awarded this badge
				ys_badge_db::insert_badge_award($badge_slug, $notify, $uid, $oid);

				if($notify > 0) {
					//qa_db_usernotice_create($uid, $content, 'html');

					// if(qa_opt('badge_email_notify') && $notify == 1) qa_badge_notification($uid, $oid, $badge_slug);

					if(qa_opt('event_logger_to_database')) { // add event
						ys_badge_db::insert_eventlog($badge_slug, $uid, $oid);
					}
				}

				array_push($awarded, $badge_slug);
			}
		}
	}
	return $awarded;
}

function ys_check_answer_parent_post($parentid, $postid, $uid, $notify = 1)
{
	if (empty($parentid)) {
		return;
	}
	$parent_post = ys_badge_db::get_single_post($parentid);

	if (ys_check_badge_award_savior($parent_post)) {
		ys_badge_db::insert_badge_award('savior', $notify, $uid, $postid);
	}
}

function ys_check_badge_award_savior($post)
{
	if (empty($post['created'])) {
		return false;
	}
	$days = (int)qa_opt('ys_badge_savior_var');
	$comp_date = new DateTime();
	$comp_date->modify('-'.$days.' days');
	$comp_date->setTime(0,0,0);

	$created = new DateTime();
	$created->setTimestamp($post['created']);
	$created->setTime(0,0,0);

	if ($created < $comp_date && $post['acount'] <= 1) {
		return true;
	} else {
		return false;
	}

}

/*
	Omit PHP closing tag to help avoid accidental output
*/
