<?php

if (!defined('QA_VERSION')) {
	require_once dirname(empty($_SERVER['SCRIPT_FILENAME']) ? __FILE__ : $_SERVER['SCRIPT_FILENAME']).'/../../qa-include/qa-base.php';
}

function ys_get_badge_list()
{
	$badges = array();

	$badegs['regular'] = array('var' => 10, 'level' => 1);
	$badegs['answerer'] = array('level' => '1');
	$badegs['savior'] = array('var' => 3, 'level' => 2);

	return $badges;
}

/*
	Omit PHP closing tag to help avoid accidental output
*/
