<?php

/*
	Plugin Name: YS Badges
	Plugin URI:
	Plugin Description: Provides simple badges.
	Plugin Version: 1.0
	Plugin Date: 2016-08-05
	Plugin Author: 38qa.net
	Plugin Author URI: http://38qa.net/
	Plugin License: GPLv2
	Plugin Minimum Question2Answer Version: 1.7
	Plugin Update Check URI:
*/

if (!defined('QA_VERSION')) { // don't allow this page to be requested directly from browser
	header('Location: ../../');
	exit;
}

	//Define global constants
	@define( 'YS_BADGES_DIR', dirname( __FILE__ ) );
	@define( 'YS_BADGES_FOLDER', basename( dirname( __FILE__ ) ) );

	require_once YS_BADGES_DIR . '/ys-badges.php';
	require_once YS_BADGES_DIR . '/ys-badge-function.php';
	require_once YS_BADGES_DIR . '/ys-badge-db.php';

	// admin
	qa_register_plugin_module('module', 'qa-ys-badge-admin.php', 'qa_ys_badge_admin', 'Ys Badge Admin');
	// languages
	qa_register_plugin_phrases('qa-ys-badge-lang-*.php', 'ys_badges');
	// layer
	qa_register_plugin_layer('qa-ys-badge-layer.php', 'Ys Badge Layer');

/*
	Omit PHP closing tag to help avoid accidental output
*/
