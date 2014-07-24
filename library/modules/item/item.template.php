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

function template_item_list()
{
	global $template;

	echo '
		<div class="page-header">
			<div class="pull-right">
				<a class="btn btn-info" href="', build_url(array('item', 'import')), '">Import Items</a>
				<a class="btn btn-warning" href="', build_url(array('item', 'edit')), '">Add Item</a>
			</div>
			<h2>Item List</h2>
		</div>
		<table class="table table-striped table-bordered">
			<thead>
				<tr>
					<th>ID</th>
					<th>Body</th>
					<th>Category</th>
					<th>Actions</th>
				</tr>
			</thead>
			<tbody>';

	if (empty($template['items']))
	{
		echo '
				<tr>
					<td class="align_center" colspan="4">There are not any items added yet!</td>
				</tr>';
	}

	foreach ($template['items'] as $item)
	{
		echo '
				<tr>
					<td class="align_center">', $item['id'], '</td>
					<td>', $item['body'], '</td>
					<td class="align_center">', $item['category'], '</td>
					<td class="span3 align_center">
						<a class="btn btn-primary" href="', build_url(array('item', 'edit', $item['id'])), '">Edit</a>
						<a class="btn btn-danger" href="', build_url(array('item', 'delete', $item['id'])), '">Delete</a>
					</td>
				</tr>';
	}

	echo '
			</tbody>
		</table>';
}

function template_item_edit()
{
	global $user, $template;

	echo '
		<form class="form-horizontal" action="', build_url(array('item', 'edit')), '" method="post">
			<fieldset>
				<legend>', (!$template['item']['is_new'] ? 'Edit' : 'Add'), ' Item</legend>
				<div class="control-group">
					<label class="control-label" for="body">Body:</label>
					<div class="controls">
						<textarea class="input-xlarge span5" id="body" name="body" rows="3">', $template['item']['body'], '</textarea>
					</div>
				</div>
				<div class="control-group">
					<label class="control-label" for="id_category">Category:</label>
					<div class="controls">
						<select id="id_category" name="id_category">
							<option value="0"', ($template['item']['id_category'] == 0 ? ' selected="selected"' : ''), '>Select category</option>';

	foreach ($template['categories'] as $category)
	{
		echo '
							<option value="', $category['id'], '"', ($template['item']['id_category'] == $category['id'] ? ' selected="selected"' : ''), '>', $category['name'], '</option>';
	}

	echo '
						</select>
					</div>
				</div>
				<div class="form-actions">
					<input type="submit" class="btn btn-primary" name="save" value="Save changes" />
					<input type="submit" class="btn" name="cancel" value="Cancel" />
				</div>
			</fieldset>
			<input type="hidden" name="item" value="', $template['item']['id'], '" />
			<input type="hidden" name="session_id" value="', $user['session_id'], '" />
		</form>';
}

function template_item_delete()
{
	global $user, $template;

	echo '
		<form class="form-horizontal" action="', build_url(array('item', 'delete')), '" method="post">
			<fieldset>
				<legend>Delete Item</legend>
				Are you sure you want to delete the item selected?
				<div class="form-actions">
					<input type="submit" class="btn btn-danger" name="delete" value="Delete" />
					<input type="submit" class="btn" name="cancel" value="Cancel" />
				</div>
			</fieldset>
			<input type="hidden" name="item" value="', $template['item']['id'], '" />
			<input type="hidden" name="session_id" value="', $user['session_id'], '" />
		</form>';
}

function template_item_import()
{
	global $user;

	echo '
		<form class="form-horizontal" action="', build_url(array('item', 'import')), '" method="post" enctype="multipart/form-data">
			<fieldset>
				<legend>Import Items</legend>
				<div class="control-group">
					<label class="control-label" for="import">Select file:</label>
					<div class="controls">
						<input type="file" class="input-xlarge" id="import" name="import" />
					</div>
				</div>
				<div class="form-actions">
					<input type="submit" class="btn btn-primary" name="save" value="Save changes" />
					<input type="submit" class="btn" name="cancel" value="Cancel" />
				</div>
			</fieldset>
			<input type="hidden" name="session_id" value="', $user['session_id'], '" />
		</form>';
}