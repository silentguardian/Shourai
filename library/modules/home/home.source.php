<?php

/**
 * @package Shourai
 *
 * @author Selman Eser
 * @copyright 2014 Selman Eser
 * @license BSD 2-clause
 *
 * @version 1.0
 */

if (!defined('CORE'))
	exit();

function home_main()
{
	global $core, $template;

	$request = db_query("
		SELECT COUNT(id_user)
		FROM user
		LIMIT 1");
	list ($template['total_users']) = db_fetch_row($request);
	db_free_result($request);

	$request = db_query("
		SELECT COUNT(id_item)
		FROM item
		LIMIT 1");
	list ($template['total_items']) = db_fetch_row($request);
	db_free_result($request);

	$request = db_query("
		SELECT COUNT(id_response)
		FROM response
		LIMIT 1");
	list ($template['total_responses']) = db_fetch_row($request);
	db_free_result($request);

	$template['page_title'] = 'Home';
	$core['current_template'] = 'home_main';
}