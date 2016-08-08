<?php

class qa_html_theme_layer extends qa_html_theme_base {

	function head_custom()
	{
		qa_html_theme_base::head_custom();
		if(!qa_opt('ys_badge_active')) {
			return;
		}

		$this->output('<style>',qa_opt('ys_badges_css'),'</style>');

	}
}
