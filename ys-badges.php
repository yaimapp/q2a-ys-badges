<?php

if (!defined('QA_VERSION')) { // don't allow this page to be requested directly from browser
		header('Location: ../../');
		exit;
}

function ys_get_badge_list()
{
	$badges = array();

	$badegs['regular'] = array('var' => 10, 'level' => 1);
	$badegs['answerer'] = array('level' => '1');
	$badegs['savior'] = array('var' => 3, 'level' => 2)
}
