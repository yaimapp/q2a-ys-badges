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

function ys_badge_plugin_user_form($userid)
{

	$handle = qa_userid_to_handle($userid);

	// displays badge list in user profile

	$result = ys_badge_db::get_badge_slug_and_oid($userid);

	$fields = array();

	if(count($result) > 0) {

		// count badges
		$bin = ys_get_badge_list();

		$badges = array();

		foreach($result as $info) {
			$slug = $info['slug'];
			$type = $bin[$slug]['level'];
			if(isset($badges[$type][$slug])) {
				$badges[$type][$slug]['count']++;
			} else {
				$badges[$type][$slug]['count'] = 1;
			}
			if ($info['oid']) {
				$badges[$type][$slug]['oid'][] = $info['oid'];
			}
		}

		foreach($badges as $type => $badge) {

			$typea = ys_get_badge_level($type);
			$types = $typea['slug'];
			$typed = $typea['name'];

			$output = '
					<table>
						<tr>
							<td class="qa-form-wide-label">
								<h3 class="badge-title" title="'.qa_lang('ys_badges/'.$types.'_desc').'">'.$typed.'</h3>
							</td>
						</tr>';
			foreach($badge as $slug => $info) {

				$badge_name = ys_badge_name($slug);
				if (!qa_opt('ys_badge_'.$slug.'_name')) {
					qa_opt('ys_badge_'.$slug.'_name',$badge_name);
				}
				$name = qa_opt('ys_badge_'.$slug.'_name');

				$count = $info['count'];

				if(qa_opt('ys_badge_show_source_posts')) {
					$oids = @$info['oid'];
				}
				else $oids = null;

				$var = qa_opt('ys_badge_'.$slug.'_var');
				$desc = ys_badge_desc_replace($slug,$var,false);

				// badge row

				$output .= '
						<tr>
							<td class="ys-badge-container">
								<div class="ys-badge-container-badge">
									<span class="ys-badge-'.$types.'" title="'.$desc.' ('.$typed.')">'.qa_html($name).'</span>&nbsp;<span onclick="jQuery(\'.ys-badge-container-sources-'.$slug.'\').slideToggle()" class="badge-count'.(is_array($oids)?' badge-count-link" title="'.qa_lang('ys_badges/badge_count_click'):'').'">x&nbsp;'.$count.'</span>
								</div>';

				// source row(s) if any
				if(is_array($oids)) {
					$output .= '
								<div class="ys-badge-container-sources-'.$slug.'" style="display:none">';
					foreach($oids as $oid) {
						$post = ys_badge_db::get_single_post($oid);
						$title = $post['title'];

						$anchor = '';

						if ($post['parentid']) {
							$anchor =  urlencode(qa_anchor($post['type'],$oid));
							$oid = $post['parentid'];
							$title = ys_badge_db::get_binary_title($oid);
						}

						$length = 30;

						$text = (qa_strlen($title) > $length ? qa_substr($title,0,$length).'...' : $title);

						$output .= '
									<div class="ys-badge-source"><a href="'.qa_path_html(qa_q_request($oid,$title),NULL,qa_opt('site_url')).($anchor?'#'.$anchor:'').'">'.qa_html($text).'</a></div>';
					}
					$output .= '</div>';
				}
				$output .= '
							</td>
						</tr>';
			}
			$output .= '
					</table>';

			$outa[] = $output;
		}

		$fields[] = array(
				'value' => '<table class="ys-badge-user-tables"><tr><td class="ys-badge-user-table">'.implode('</td><td class="ys-badge-user-table">',$outa).'</td></tr></table>',
				'type' => 'static',
		);
	}

	$ok = null;
	$tags = null;
	$buttons = array();

	if((bool)qa_opt('badge_email_notify') && qa_get_logged_in_handle() == $handle) {
	// add badge notify checkbox


		if(qa_clicked('badge_email_notify_save')) {
			qa_opt('badge_email_notify_id_'.$userid, (bool)qa_post_text('badge_notify_email_me'));
			$ok = qa_lang('badges/badge_notified_email_me');
		}

		$select = (bool)qa_opt('badge_email_notify_id_'.$userid);

		$tags = 'id="badge-form" action="'.qa_self_html().'#signature_text" method="POST"';

		$fields[] = array(
			'type' => 'blank',
		);

		$fields[] = array(
			'label' => qa_lang('badges/badge_notify_email_me'),
			'type' => 'checkbox',
			'tags' => 'NAME="badge_notify_email_me"',
			'value' => $select,
		);

		$buttons[] = array(
			'label' => qa_lang_html('main/save_button'),
			'tags' => 'NAME="badge_email_notify_save"',
		);
	}



	return array(
		'ok' => ($ok && !isset($error)) ? $ok : null,
		'style' => 'tall',
		'tags' => $tags,
		'title' => qa_lang('ys_badges/badges'),
		'fields'=>$fields,
		'buttons'=>$buttons,
	);

}

/*
	Omit PHP closing tag to help avoid accidental output
*/
