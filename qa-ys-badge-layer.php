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
