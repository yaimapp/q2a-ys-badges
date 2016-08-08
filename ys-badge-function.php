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

/*
	Omit PHP closing tag to help avoid accidental output
*/
