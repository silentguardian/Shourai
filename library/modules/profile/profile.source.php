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

function profile_main()
{
	global $core, $template, $user;

	$request = db_query("
		SELECT
			u.login_count, u.last_login,
			u.last_password_change,
			g.generation, g.section
		FROM user AS u
			LEFT JOIN group AS g ON (g.id_group = u.id_group)
		WHERE u.id_user = $user[id]
		LIMIT 1");
	while ($row = db_fetch_assoc($request))
	{
		$template['profile'] = array(
			'login_count' => $row['login_count'],
			'last_login' => empty($row['last_login']) ? 'Never' : format_time($row['last_login'], 'long'),
			'last_password_change' => empty($row['last_password_change']) ? 'Never' : format_time($row['last_password_change'], 'long'),
			'generation' => empty($row['generation']) ? 'None' : $row['generation'],
			'section' => empty($row['section']) ? 'None' : $row['section'],
		);
	}
	db_free_result($request);

	if (!empty($_POST['save']))
	{
		check_session('profile');

		$values = array();
		$fields = array(
			'choose_password' => 'password',
			'verify_password' => 'password',
			'current_password' => 'password',
		);

		foreach ($fields as $field => $type)
		{
			if ($type === 'password')
				$values[$field] = !empty($_POST[$field]) ? sha1($_POST[$field]) : '';
		}

		$request = db_query("
			SELECT password
			FROM user
			WHERE id_user = $user[id]
			LIMIT 1");
		list ($current_password) = db_fetch_row($request);
		db_free_result($request);

		if ($current_password !== $values['current_password'])
			fatal_error('The password entered is not correct!');

		if ($values['choose_password'] !== $values['verify_password'])
			fatal_error('The new passwords entered do not match.');

		$changes = array();
		if ($values['choose_password'] !== '')
		{
			$changes[] = "password = '$values[verify_password]'";
			$changes[] = "last_password_change = " . time();
		}

		if (!empty($changes))
		{
			db_query("
				UPDATE user
				SET " . implode(', ', $changes) . "
				WHERE id_user = $user[id]
				LIMIT 1");
		}

		if ($values['choose_password'] !== '')
			redirect(build_url('login'));
		else
			redirect(build_url('profile'));
	}

	$template['page_title'] = 'Edit Profile';
	$core['current_template'] = 'profile_main';
}