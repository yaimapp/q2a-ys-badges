<?php


class qa_ys_badge_admin
{
	function allow_template($tamplate)
	{
		return ($template !== 'admin');
	}

	function option_default($option)
	{
		$badges = qa_get_badge_list();
		
		$slug = preg_replace('/ys_badge_(.+)_[^_]+/',"$1",$option);
		switch($opstion) {
			case 'ys_badge_'.$slug.'_name'：
				return qa_lang('badges/'.$slug);
			case 'ys_badge_'.$slug.'_var':
				return @$badges[$slug]['var'];
			case 'ys_badge_'.$slug.'_enabled':
				return '0';
			default:
				return null;
		}
	}
}

/*
	Omit PHP closing tag to help avoid accidental output
*/
