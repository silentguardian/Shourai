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

function group_main()
{
	global $core, $template;

	$actions = array('list', 'edit', 'delete');

	$core['current_action'] = 'list';
	if (!empty($_REQUEST['action']) && in_array($_REQUEST['action'], $actions))
		$core['current_action'] = $_REQUEST['action'];

	call_user_func($core['current_module'] . '_' . $core['current_action']);
}

function group_list()
{
	global $core, $template;

	$request = db_query("
		SELECT id_group, generation, section
		FROM group
		ORDER BY generation, section");
	$template['groups'] = array();
	while ($row = db_fetch_assoc($request))
	{
		$template['groups'][] = array(
			'id' => $row['id_group'],
			'generation' => $row['generation'],
			'section' => $row['section'],
		);
	}
	db_free_result($request);

	$template['page_title'] = 'Group List';
	$core['current_template'] = 'group_list';
}

function group_edit()
{
	global $core, $template;

	$id_group = !empty($_REQUEST['group']) ? (int) $_REQUEST['group'] : 0;
	$is_new = empty($id_group);

	if ($is_new)
	{
		$template['group'] = array(
			'is_new' => true,
			'id' => 0,
			'generation' => '',
			'section' => '',
		);
	}
	else
	{
		$request = db_query("
			SELECT id_group, generation, section
			FROM group
			WHERE id_group = $id_group
			LIMIT 1");
		while ($row = db_fetch_assoc($request))
		{
			$template['group'] = array(
				'is_new' => false,
				'id' => $row['id_group'],
				'generation' => $row['generation'],
				'section' => $row['section'],
			);
		}
		db_free_result($request);

		if (!isset($template['group']))
			fatal_error('The group requested does not exist!');
	}

	if (!empty($_POST['save']))
	{
		check_session('group');

		$values = array();
		$fields = array(
			'generation' => 'int',
			'section' => 'string',
		);

		foreach ($fields as $field => $type)
		{
			if ($type === 'string')
				$values[$field] = !empty($_POST[$field]) ? htmlspecialchars($_POST[$field], ENT_QUOTES) : '';
			elseif ($type === 'int')
				$values[$field] = !empty($_POST[$field]) ? (int) $_POST[$field] : 0;
		}

		if ($values['generation'] < 1)
			fatal_error('Group generation field cannot be empty!');
		elseif ($values['section'] === '')
			fatal_error('Group section field cannot be empty!');

		if ($is_new)
		{
			$insert = array();
			foreach ($values as $field => $value)
				$insert[$field] = "'" . $value . "'";

			db_query("
				INSERT INTO group
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
				UPDATE group
				SET " . implode(', ', $update) . "
				WHERE id_group = $id_group
				LIMIT 1");
		}
	}

	if (!empty($_POST['save']) || !empty($_POST['cancel']))
		redirect(build_url('group'));

	$template['page_title'] = (!$is_new ? 'Edit' : 'Add') . ' Group';
	$core['current_template'] = 'group_edit';
}

function group_delete()
{
	global $core, $template;

	$id_group = !empty($_REQUEST['group']) ? (int) $_REQUEST['group'] : 0;

	$request = db_query("
		SELECT id_group, generation, section
		FROM group
		WHERE id_group = $id_group
		LIMIT 1");
	while ($row = db_fetch_assoc($request))
	{
		$template['group'] = array(
			'id' => $row['id_group'],
			'name' => $row['generation'] . ' ' . $row['section'],
		);
	}
	db_free_result($request);

	if (!isset($template['group']))
		fatal_error('The group requested does not exist!');

	if (!empty($_POST['delete']))
	{
		check_session('group');

		db_query("
			DELETE FROM group
			WHERE id_group = $id_group
			LIMIT 1");

		db_query("
			UPDATE user
			SET id_group = 0
			WHERE id_group = $id_group");

		redirect(build_url('group'));
	}

	if (!empty($_POST['delete']) || !empty($_POST['cancel']))
		redirect(build_url('group'));

	$template['page_title'] = 'Delete Group';
	$core['current_template'] = 'group_delete';
}