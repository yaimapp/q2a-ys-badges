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

		$this->output('<style>', qa_opt('ys_badges_css'), '</style>');

	}
}

/*
	Omit PHP closing tag to help avoid accidental output
*/
