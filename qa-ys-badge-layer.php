<?php

class qa_html_theme_layer extends qa_html_theme_base {

	function doctype()
	{
		qa_html_theme_base::doctype();
		if(!qa_opt('ys_badge_active')) {
			return;
		}

		$userid = qa_get_logged_in_userid();

		if (!$userid) {	// not logged in?
			return;
		}

		if (ys_badge_db::is_first_visit($userid)) {
			ys_badge_db::insert_first_visit($userid);
			return;
		}

		$ua = ys_badge_db::get_user_achievements($userid);
		if (!isset($ua)) {
			return;
		}

		$last_diff = ys_check_days_diff($ua['lv']);
		$oldest_consec_diff = ys_check_days_diff($ua['ocv']) + 1;	// include the first day
		$first_visit_diff = ys_check_days_diff($ua['fv']);

		if ($last_diff < 0) {
			return;
		}

		if ($last_diff < 2) {
			if($oldest_consec_diff > $ua['lcv']) {
				$ua['lcv'] = $oldest_consec_diff;
				ys_badge_db::update_longest_consec_visit($oldest_consec_diff,
													$last_diff,
													$userid);
			} else {
				ys_badge_db::update_total_days_visited($last_diff, $userid);
			}
		} else {
			// 2+ days, reset consecutive days due to lapse
			ys_badge_db::update_oldest_consec_visit($userid);
		}
		// ys_badge_award_check();
	}

	function head_custom()
	{
		qa_html_theme_base::head_custom();
		if(!qa_opt('ys_badge_active')) {
			return;
		}
		if (qa_opt('ys_badge_active') && $this->template !== 'admin') {
			$this->badge_notify();
		}

		$this->output('<style>', qa_opt('ys_badges_css'), '</style>');

	}

	function badge_notify() {
		$userid = qa_get_logged_in_userid();

		if (empty($userid)) {
			return;
		}
		ys_badge_db::create_userbadges_table();

		$result = ys_badge_db::get_badge_to_notify($userid);

		if(count($result) > 0) {
			$notice = '<div class="notify-container">';

			if(count($result) == 1) {
				$slug = $result[0];
				$badge_name=qa_lang('ys_badges/'.$slug);
				if(!qa_opt('ys_badge_'.$slug.'_name')) qa_opt('ys_badge_'.$slug.'_name',$badge_name);
				$name = qa_opt('ys_badge_'.$slug.'_name');

				$notice .= '<div class="badge-notify notify">'.qa_lang('ys_badges/badge_notify')."'".$name.'\'&nbsp;&nbsp;'.qa_lang('ys_badges/badge_notify_profile_pre').'<a href="'.qa_path_html((QA_FINAL_EXTERNAL_USERS?qa_path_to_root():'').'user/'.qa_get_logged_in_handle(),array('tab'=>'badges'),qa_opt('site_url')).'">'.qa_lang('ys_badges/badge_notify_profile').'</a><div class="notify-close" onclick="jQuery(this).parent().slideUp(\'slow\')">x</div></div>';
			} else {
				$number_text = count($result)>2?str_replace('#', count($result)-1, qa_lang('ys_badges/badge_notify_multi_plural')):qa_lang('ys_badges/badge_notify_multi_singular');
				$slug = $result[0];
				$badge_name=qa_lang('ys_badges/'.$slug);
				if(!qa_opt('badge_'.$slug.'_name')) qa_opt('ys_badge_'.$slug.'_name',$badge_name);
				$name = qa_opt('ys_badge_'.$slug.'_name');
				$notice .= '<div class="badge-notify notify">'.qa_lang('ys_badges/badge_notify')."'".$name.'\'&nbsp;'.$number_text.'&nbsp;&nbsp;'.qa_lang('ys_badges/badge_notify_profile_pre').'<a href="'.qa_path_html('user/'.qa_get_logged_in_handle(),array('tab'=>'badges'),qa_opt('site_url')).'">'.qa_lang('ys_badges/badge_notify_profile').'</a><div class="notify-close" onclick="jQuery(this).parent().slideUp(\'slow\')">x</div></div>';
			}

			$notice .= '</div>';

			// remove notification flag
			// ya_badge_db::remove_notification_flag($userid);
			$this->badge_notice = $notice;
		}
	}
}

/*
	Omit PHP closing tag to help avoid accidental output
*/
