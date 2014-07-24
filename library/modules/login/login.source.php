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

function login_main()
{
	global $core, $template;

	if (!empty($_POST['submit']))
	{
		check_session('login');

		$ssid = !empty($_POST['ssid']) ? $_POST['ssid'] : '';
		$password = !empty($_POST['password']) ? $_POST['password'] : '';

		if ($ssid === '' || preg_match('~[^A-Za-z0-9\._]~', $ssid) || $password === '')
			fatal_error('Invalid username or password!');
		$ssid = htmlspecialchars($ssid, ENT_QUOTES);

		$request = db_query("
			SELECT id_user, first_name, last_name, password
			FROM user
			WHERE ssid = '$ssid'
			LIMIT 1");
		list ($id, $first_name, $last_name, $real_password) = db_fetch_row($request);
		db_free_result($request);

		$hash = sha1($password);
		if ($hash !== $real_password)
			fatal_error('Invalid username or password!');

		create_cookie(60 * 3153600, $id, $hash);

		db_query("
			UPDATE user
			SET last_login = " . time() . ",
				login_count = login_count + 1
			WHERE id_user = $id
			LIMIT 1");

		db_query("
			REPLACE INTO online
				(id_user, name, time)
			VALUES
				($id, '$first_name $last_name', " . time() . ")");

		redirect(build_url());
	}

	$template['page_title'] = 'User Login';
	$core['current_template'] = 'login_main';
}