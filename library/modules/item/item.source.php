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

function item_main()
{
	global $core, $template;

	$actions = array('list', 'edit', 'delete', 'import');

	$core['current_action'] = 'list';
	if (!empty($_REQUEST['action']) && in_array($_REQUEST['action'], $actions))
		$core['current_action'] = $_REQUEST['action'];

	call_user_func($core['current_module'] . '_' . $core['current_action']);
}

function item_list()
{
	global $core, $template;

	$request = db_query("
		SELECT i.id_item, i.id_category, c.name, i.body
		FROM item AS i
			LEFT JOIN category as c ON (c.id_category = i.id_category)
		ORDER BY i.id_item");
	$template['items'] = array();
	while ($row = db_fetch_assoc($request))
	{
		$template['items'][] = array(
			'id' => $row['id_item'],
			'category' => empty($row['name']) ? 'None' : $row['name'],
			'body' => $row['body'],
		);
	}
	db_free_result($request);

	$template['page_title'] = 'Item List';
	$core['current_template'] = 'item_list';
}

function item_edit()
{
	global $core, $template;

	$id_item = !empty($_REQUEST['item']) ? (int) $_REQUEST['item'] : 0;
	$is_new = empty($id_item);

	if ($is_new)
	{
		$template['item'] = array(
			'is_new' => true,
			'id' => 0,
			'body' => '',
			'id_category' => 0,
		);
	}
	else
	{
		$request = db_query("
			SELECT id_item, body, id_category
			FROM item
			WHERE id_item = $id_item
			LIMIT 1");
		while ($row = db_fetch_assoc($request))
		{
			$template['item'] = array(
				'is_new' => false,
				'id' => $row['id_item'],
				'body' => $row['body'],
				'id_category' => $row['id_category'],
			);
		}
		db_free_result($request);

		if (!isset($template['item']))
			fatal_error('The item requested does not exist!');
	}

	if (!empty($_POST['save']))
	{
		check_session('item');

		$values = array();
		$fields = array(
			'body' => 'string',
			'id_category' => 'int',
		);

		foreach ($fields as $field => $type)
		{
			if ($type === 'string')
				$values[$field] = !empty($_POST[$field]) ? htmlspecialchars($_POST[$field], ENT_QUOTES) : '';
			elseif ($type === 'int')
				$values[$field] = !empty($_POST[$field]) ? (int) $_POST[$field] : 0;
		}

		if ($values['body'] === '')
			fatal_error('Item body field cannot be empty!');

		if ($is_new)
		{
			$insert = array();
			foreach ($values as $field => $value)
				$insert[$field] = "'" . $value . "'";

			db_query("
				INSERT INTO item
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
				UPDATE item
				SET " . implode(', ', $update) . "
				WHERE id_item = $id_item
				LIMIT 1");
		}
	}

	if (!empty($_POST['save']) || !empty($_POST['cancel']))
		redirect(build_url('item'));

	$request = db_query("
		SELECT id_category, name
		FROM category
		ORDER BY name");
	$template['categories'] = array();
	while ($row = db_fetch_assoc($request))
	{
		$template['categories'][] = array(
			'id' => $row['id_category'],
			'name' => $row['name'],
		);
	}
	db_free_result($request);

	$template['page_title'] = (!$is_new ? 'Edit' : 'Add') . ' Item';
	$core['current_template'] = 'item_edit';
}

function item_delete()
{
	global $core, $template;

	$id_item = !empty($_REQUEST['item']) ? (int) $_REQUEST['item'] : 0;

	$request = db_query("
		SELECT id_item
		FROM item
		WHERE id_item = $id_item
		LIMIT 1");
	while ($row = db_fetch_assoc($request))
	{
		$template['item'] = array(
			'id' => $row['id_item'],
		);
	}
	db_free_result($request);

	if (!isset($template['item']))
		fatal_error('The item requested does not exist!');

	if (!empty($_POST['delete']))
	{
		check_session('item');

		db_query("
			DELETE FROM item
			WHERE id_item = $id_item
			LIMIT 1");

		db_query("
			DELETE FROM response
			WHERE id_item = $id_item");

		redirect(build_url('item'));
	}

	if (!empty($_POST['delete']) || !empty($_POST['cancel']))
		redirect(build_url('item'));

	$template['page_title'] = 'Delete Item';
	$core['current_template'] = 'item_delete';
}

function item_import()
{
	global $core, $template;

	if (!empty($_POST['save']))
	{
		check_session('item');

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
				$inserts[] = "($parts[0], '$parts[1]')";
			}

			if (!empty($inserts))
			{
				db_query("
					INSERT INTO item
						(id_category, body)
					VALUES
						" . implode(', ', $inserts));
			}
		}
		else
			fatal_error('You have not uploaded a file!');

		redirect(build_url('item'));
	}

	if (!empty($_POST['save']) || !empty($_POST['cancel']))
		redirect(build_url('item'));

	$template['page_title'] = 'Import Items';
	$core['current_template'] = 'item_import';
}