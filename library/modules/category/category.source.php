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

function category_main()
{
	global $core, $template;

	$actions = array('list', 'edit', 'delete');

	$core['current_action'] = 'list';
	if (!empty($_REQUEST['action']) && in_array($_REQUEST['action'], $actions))
		$core['current_action'] = $_REQUEST['action'];

	call_user_func($core['current_module'] . '_' . $core['current_action']);
}

function category_list()
{
	global $core, $template;

	$request = db_query("
		SELECT id_category, name
		FROM category
		ORDER BY id_category");
	$template['categories'] = array();
	while ($row = db_fetch_assoc($request))
	{
		$template['categories'][] = array(
			'id' => $row['id_category'],
			'name' => $row['name'],
		);
	}
	db_free_result($request);

	$template['page_title'] = 'Category List';
	$core['current_template'] = 'category_list';
}

function category_edit()
{
	global $core, $template;

	$id_category = !empty($_REQUEST['category']) ? (int) $_REQUEST['category'] : 0;
	$is_new = empty($id_category);

	if ($is_new)
	{
		$template['category'] = array(
			'is_new' => true,
			'id' => 0,
			'name' => '',
		);
	}
	else
	{
		$request = db_query("
			SELECT id_category, name
			FROM category
			WHERE id_category = $id_category
			LIMIT 1");
		while ($row = db_fetch_assoc($request))
		{
			$template['category'] = array(
				'is_new' => false,
				'id' => $row['id_category'],
				'name' => $row['name'],
			);
		}
		db_free_result($request);

		if (!isset($template['category']))
			fatal_error('The category requested does not exist!');
	}

	if (!empty($_POST['save']))
	{
		check_session('category');

		$values = array();
		$fields = array(
			'name' => 'string',
		);

		foreach ($fields as $field => $type)
		{
			if ($type === 'string')
				$values[$field] = !empty($_POST[$field]) ? htmlspecialchars($_POST[$field], ENT_QUOTES) : '';
		}

		if ($values['name'] === '')
			fatal_error('Category name field cannot be empty!');

		if ($is_new)
		{
			$insert = array();
			foreach ($values as $field => $value)
				$insert[$field] = "'" . $value . "'";

			db_query("
				INSERT INTO category
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
				UPDATE category
				SET " . implode(', ', $update) . "
				WHERE id_category = $id_category
				LIMIT 1");
		}
	}

	if (!empty($_POST['save']) || !empty($_POST['cancel']))
		redirect(build_url('category'));

	$template['page_title'] = (!$is_new ? 'Edit' : 'Add') . ' Category';
	$core['current_template'] = 'category_edit';
}

function category_delete()
{
	global $core, $template;

	$id_category = !empty($_REQUEST['category']) ? (int) $_REQUEST['category'] : 0;

	$request = db_query("
		SELECT id_category, name
		FROM category
		WHERE id_category = $id_category
		LIMIT 1");
	while ($row = db_fetch_assoc($request))
	{
		$template['category'] = array(
			'id' => $row['id_category'],
			'name' => $row['name'],
		);
	}
	db_free_result($request);

	if (!isset($template['category']))
		fatal_error('The category requested does not exist!');

	if (!empty($_POST['delete']))
	{
		check_session('category');

		db_query("
			DELETE FROM category
			WHERE id_category = $id_category
			LIMIT 1");

		db_query("
			UPDATE item
			SET id_category = 0
			WHERE id_category = $id_category");

		redirect(build_url('category'));
	}

	if (!empty($_POST['delete']) || !empty($_POST['cancel']))
		redirect(build_url('category'));

	$template['page_title'] = 'Delete Category';
	$core['current_template'] = 'category_delete';
}