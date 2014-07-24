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

function user_main()
{
	global $core;

	$actions = array('list', 'edit', 'delete', 'survey', 'import');

	$core['current_action'] = 'list';
	if (!empty($_REQUEST['action']) && in_array($_REQUEST['action'], $actions))
		$core['current_action'] = $_REQUEST['action'];

	call_user_func($core['current_module'] . '_' . $core['current_action']);
}

function user_list()
{
	global $core, $template;

	$request = db_query("
		SELECT
			u.id_user, u.ssid, u.first_name, u.last_name,
			u.registered, u.admin, g.generation, g.section
		FROM user AS u
			LEFT JOIN group AS g ON (g.id_group = u.id_group)
		ORDER BY u.id_user");
	$template['users'] = array();
	while ($row = db_fetch_assoc($request))
	{
		$template['users'][] = array(
			'id' => $row['id_user'],
			'ssid' => $row['ssid'],
			'first_name' => $row['first_name'],
			'last_name' => $row['last_name'],
			'generation' => empty($row['generation']) ? 'None' : $row['generation'],
			'section' => empty($row['section']) ? 'None' : $row['section'],
			'registered' => format_time($row['registered']),
			'admin' => $row['admin'] ? 'Yes' : 'No',
		);
	}
	db_free_result($request);

	$template['page_title'] = 'User List';
	$core['current_template'] = 'user_list';
}

function user_edit()
{
	global $core, $template;

	$id_user = !empty($_REQUEST['user']) ? (int) $_REQUEST['user'] : 0;
	$is_new = empty($id_user);

	if ($is_new)
	{
		$template['user'] = array(
			'is_new' => true,
			'id' => 0,
			'ssid' => '',
			'first_name' => '',
			'last_name' => '',
			'id_group' => 0,
			'admin' => 0,
		);
	}
	else
	{
		$request = db_query("
			SELECT
				id_user, uid, ssid, first_name, last_name, admin, id_group,
				login_count, last_login, last_password_change
			FROM user
			WHERE id_user = $id_user
			LIMIT 1");
		while ($row = db_fetch_assoc($request))
		{
			$template['user'] = array(
				'is_new' => false,
				'id' => $row['id_user'],
				'uid' => $row['uid'],
				'ssid' => $row['ssid'],
				'first_name' => $row['first_name'],
				'last_name' => $row['last_name'],
				'id_group' => $row['id_group'],
				'admin' => $row['admin'],
				'login_count' => $row['login_count'],
				'last_login' => empty($row['last_login']) ? 'Never' : format_time($row['last_login'], 'long'),
				'last_password_change' => empty($row['last_password_change']) ? 'Never' : format_time($row['last_password_change'], 'long'),
			);
		}
		db_free_result($request);

		if (!isset($template['user']))
			fatal_error('The user requested does not exist!');
	}

	if (!empty($_POST['save']))
	{
		check_session('user');

		$values = array();
		$fields = array(
			'ssid' => 'id',
			'first_name' => 'string',
			'last_name' => 'string',
			'password' => 'password',
			'verify_password' => 'password',
			'id_group' => 'int',
			'admin' => 'int',
		);

		foreach ($fields as $field => $type)
		{
			if ($type === 'password')
				$values[$field] = !empty($_POST[$field]) ? sha1($_POST[$field]) : '';
			elseif ($type === 'id')
				$values[$field] = !empty($_POST[$field]) && !preg_match('~[^A-Za-z0-9\._]~', $_POST[$field]) ? $_POST[$field] : '';
			elseif ($type === 'string')
				$values[$field] = !empty($_POST[$field]) ? htmlspecialchars($_POST[$field], ENT_QUOTES) : '';
			elseif ($type === 'int')
				$values[$field] = !empty($_POST[$field]) ? (int) $_POST[$field] : 0;
		}

		if ($values['first_name'] === '')
			fatal_error('You did not enter a valid first name!');
		elseif ($values['last_name'] === '')
			fatal_error('You did not enter a valid last name!');
		elseif ($values['ssid'] === '')
			fatal_error('You did not enter a valid ID!');

		$request = db_query("
			SELECT ssid
			FROM user
			WHERE ssid = '$values[ssid]'
				AND id_user != $id_user
			LIMIT 1");
		list ($duplicate_id) = db_fetch_row($request);
		db_free_result($request);

		if (!empty($duplicate_id))
			fatal_error('The ID entered is already in use!');

		if ($values['password'] === '' && $is_new)
			fatal_error('You did not enter a valid password!');
		elseif ($values['password'] === '')
			unset($values['password'], $values['verify_password']);
		elseif ($values['password'] !== $values['verify_password'])
			fatal_error('The passwords entered do not match!');
		else
			unset($values['verify_password']);

		if ($is_new)
		{
			$uid = substr(md5(session_id() . mt_rand() . (string) microtime()), 0, 10);

			$insert = array(
				'uid' => "'" . $uid . "'",
				'registered' => time(),
			);

			foreach ($values as $field => $value)
				$insert[$field] = "'" . $value . "'";

			db_query("
				INSERT INTO user
					(" . implode(', ', array_keys($insert)) . ")
				VALUES
					(" . implode(', ', $insert) . ")");
		}
		else
		{
			$update = array();
			foreach ($values as $field => $value)
				$update[] = $field . " = '" . $value . "'";

			db_query("
				UPDATE user
				SET " . implode(', ', $update) . "
				WHERE id_user = $id_user
				LIMIT 1");
		}

		if (!empty($_FILES['photo']) && !empty($_FILES['photo']['name']))
		{
			$photo_size = (int) $_FILES['photo']['size'];
			$photo_extension = htmlspecialchars(strtolower(substr(strrchr($_FILES['photo']['name'], '.'), 1)), ENT_QUOTES);
			$photo_dir = $core['site_dir'] . '/interface/img/photo_' . ($is_new ? $uid : $template['user']['uid']) . '.' . $photo_extension;

			if (!is_uploaded_file($_FILES['photo']['tmp_name']) || (@ini_get('open_basedir') == '' && !file_exists($_FILES['photo']['tmp_name'])))
				fatal_error('File could not be uploaded!');

			if ($photo_size > 1 * 1024 * 1024)
				fatal_error('Files cannot be larger than 1 MB!');

			if (!in_array($photo_extension, array('jpg')))
				fatal_error('Only files with the following extensions can be uploaded: jpg');

			@unlink($photo_dir);

			if (!move_uploaded_file($_FILES['photo']['tmp_name'], $photo_dir))
				fatal_error('File could not be uploaded!');
		}
	}

	if (!empty($_POST['save']) || !empty($_POST['cancel']))
		redirect(build_url('user'));

	$request = db_query("
		SELECT id_group, generation, section
		FROM group
		ORDER BY generation, section");
	$template['groups'] = array();
	while ($row = db_fetch_assoc($request))
	{
		$template['groups'][] = array(
			'id' => $row['id_group'],
			'name' => $row['generation'] . ' ' . $row['section'],
		);
	}
	db_free_result($request);

	$template['page_title'] = (!$is_new ? 'Edit' : 'Add') . ' User';
	$core['current_template'] = 'user_edit';
}

function user_delete()
{
	global $core, $template;

	$id_user = !empty($_REQUEST['user']) ? (int) $_REQUEST['user'] : 0;

	$request = db_query("
		SELECT id_user, uid, first_name, last_name
		FROM user
		WHERE id_user = $id_user
		LIMIT 1");
	while ($row = db_fetch_assoc($request))
	{
		$template['user'] = array(
			'id' => $row['id_user'],
			'uid' => $row['uid'],
			'name' => $row['first_name'] . ' ' . $row['last_name'],
		);
	}
	db_free_result($request);

	if (!isset($template['user']))
		fatal_error('The user requested does not exist!');

	if (!empty($_POST['delete']))
	{
		check_session('user');

		db_query("
			DELETE FROM user
			WHERE id_user = $id_user
			LIMIT 1");

		db_query("
			DELETE FROM response
			WHERE id_user = $id_user");

		if (file_exists($core['site_dir'] . '/interface/img/photo_' . $template['user']['uid'] . '.jpg'))
			@unlink($core['site_dir'] . '/interface/img/photo_' . $template['user']['uid'] . '.jpg');
	}

	if (!empty($_POST['delete']) || !empty($_POST['cancel']))
		redirect(build_url('user'));

	$template['page_title'] = 'Delete User';
	$core['current_template'] = 'user_delete';
}

function user_survey()
{
	global $core, $template;

	$id_user = !empty($_REQUEST['user']) ? (int) $_REQUEST['user'] : 0;

	$request = db_query("
		SELECT
			u.id_user, u.uid, u.ssid, u.first_name, u.last_name,
			u.registered, u.admin, g.generation, g.section
		FROM user AS u
			LEFT JOIN group AS g ON (g.id_group = u.id_group)
		WHERE id_user = $id_user
		LIMIT 1");
	while ($row = db_fetch_assoc($request))
	{
		$template['user'] = array(
			'id' => $row['id_user'],
			'uid' => $row['uid'],
			'ssid' => $row['ssid'],
			'name' => $row['first_name'] . ' ' . $row['last_name'],
			'first_name' => $row['first_name'],
			'last_name' => $row['last_name'],
			'generation' => empty($row['generation']) ? 'None' : $row['generation'],
			'section' => empty($row['section']) ? 'None' : $row['section'],
		);
	}
	db_free_result($request);

	if (!isset($template['user']))
		fatal_error('The user requested does not exist!');

	$request = db_query("
		SELECT COUNT(id_item)
		FROM item
		LIMIT 1");
	list ($total_items) = db_fetch_row($request);
	db_free_result($request);

	if (empty($total_items))
		fatal_error('There are not any items added yet!');

	$request = db_query("
		SELECT COUNT(i.id_item)
		FROM item AS i
			LEFT JOIN response AS r ON (r.id_item = i.id_item AND r.id_user = $id_user)
		WHERE ISNULL(r.value)");
	list ($missing_responses) = db_fetch_row($request);
	db_free_result($request);

	if (!empty($missing_responses))
		fatal_error('The user has not completed the survey yet!');

	$request = db_query("
		SELECT c.id_category, c.name, COUNT(i.id_item) AS items
		FROM category AS c
			LEFT JOIN item AS i ON (i.id_category = c.id_category)
		GROUP BY c.id_category");
	$template['categories'] = array();
	while ($row = db_fetch_assoc($request))
	{
		$template['categories'][$row['id_category']] = array(
			'id' => $row['id_category'],
			'name' => $row['name'],
			'items' => $row['items'],
			'sum' => 0,
			'percent' => 0,
		);
	}
	db_free_result($request);

	$request = db_query("
		SELECT i.id_item, i.id_category, r.value
		FROM item AS i
			LEFT JOIN response as r ON (r.id_item = i.id_item AND r.id_user = $id_user)
			INNER JOIN category AS c ON (c.id_category = i.id_category)");
	while ($row = db_fetch_assoc($request))
		$template['categories'][$row['id_category']]['sum'] += $row['value'];
	db_free_result($request);

	foreach ($template['categories'] as $id => $data)
	{
		if ($data['sum'] > 0)
			$template['categories'][$id]['percent'] = round(($data['sum'] * 25) / $data['items']);
	}

	$series = array();

	foreach ($template['categories'] as $data)
		$series[] = "[\"$data[name]\", $data[percent]]";

	$template['post_template'] = '
	<script src="' . $core['site_url'] . 'interface/js/highcharts-custom.js"></script>
	<script type="text/javascript">
		$(function () {
			$("#graph").highcharts({
				chart: {
					type: "column"
				},
				title: {
					text: "Survey Graph for ' . $template['user']['name'] . '"
				},
				xAxis: {
					type: "category",
					labels: {
						rotation: -45,
						align: "right",
						style: {
							fontSize: "13px",
							fontFamily: "Verdana, sans-serif"
						}
					}
				},
				yAxis: {
					min: 0,
					title: {
						text: "Percentage"
					}
				},
				legend: {
					enabled: false
				},
				credits: {
					enabled: false
				},
				series: [{
					name: "Percentage",
					data: [' . implode(', ', $series) . '],
					dataLabels: {
						enabled: true,
						rotation: -90,
						color: "#FFFFFF",
						align: "right",
						x: 4,
						y: 10,
						style: {
							fontSize: "13px",
							fontFamily: "Verdana, sans-serif",
							textShadow: "0 0 1px black"
						}
					}
				}]
			});
		});
	</script>';

	$template['page_title'] = 'User Survey Results';
	$core['current_template'] = 'user_survey';
}

function user_import()
{
	global $core, $template;

	if (!empty($_POST['save']))
	{
		check_session('user');

		if (!empty($_FILES['import']) && !empty($_FILES['import']['name']))
		{
			if (!is_uploaded_file($_FILES['import']['tmp_name']) || (@ini_get('open_basedir') == '' && !file_exists($_FILES['import']['tmp_name'])))
				fatal_error('File could not be uploaded!');

			$data = explode("\n", file_get_contents($_FILES['import']['tmp_name']));

			if (empty($data))
				fatal_error('The file uploaded is empty!');

			$inserts = array();

			foreach ($data as $row)
			{
				$parts = explode(';', htmlspecialchars($row, ENT_QUOTES));
				$uid = substr(md5(session_id() . mt_rand() . (string) microtime()), 0, 10);
				$inserts[] = "('$uid', '$parts[0]', '$parts[1]', '$parts[2]', '7110eda4d09e062aa5e4a390b0a572ac0d2c0220', '$parts[3]', " . time() . ")";
			}

			if (!empty($inserts))
			{
				db_query("
					INSERT INTO user
						(uid, ssid, last_name, first_name, password, id_group, registered)
					VALUES
						" . implode(', ', $inserts));
			}
		}
		else
			fatal_error('You have not uploaded a file!');

		redirect(build_url('user'));
	}

	if (!empty($_POST['save']) || !empty($_POST['cancel']))
		redirect(build_url('user'));

	$template['page_title'] = 'Import Users';
	$core['current_template'] = 'user_import';
}