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

function template_group_list()
{
	global $template;

	echo '
		<div class="page-header">
			<div class="pull-right">
				<a class="btn btn-warning" href="', build_url(array('group', 'edit')), '">Add Group</a>
			</div>
			<h2>Group List</h2>
		</div>
		<table class="table table-striped table-bordered">
			<thead>
				<tr>
					<th>ID</th>
					<th>Generation</th>
					<th>Section</th>
					<th>Actions</th>
				</tr>
			</thead>
			<tbody>';

	if (empty($template['groups']))
	{
		echo '
				<tr>
					<td class="align_center" colspan="4">There are not any groups added yet!</td>
				</tr>';
	}

	foreach ($template['groups'] as $group)
	{
		echo '
				<tr>
					<td class="align_center">', $group['id'], '</td>
					<td>', $group['generation'], '</td>
					<td>', $group['section'], '</td>
					<td class="span3 align_center">
						<a class="btn btn-primary" href="', build_url(array('group', 'edit', $group['id'])), '">Edit</a>
						<a class="btn btn-danger" href="', build_url(array('group', 'delete', $group['id'])), '">Delete</a>
					</td>
				</tr>';
	}

	echo '
			</tbody>
		</table>';
}

function template_group_edit()
{
	global $user, $template;

	echo '
		<form class="form-horizontal" action="', build_url(array('group', 'edit')), '" method="post">
			<fieldset>
				<legend>', (!$template['group']['is_new'] ? 'Edit' : 'Add'), ' Group</legend>
				<div class="control-group">
					<label class="control-label" for="generation">Generation:</label>
					<div class="controls">
						<input type="text" class="input-xlarge" id="generation" name="generation" value="', $template['group']['generation'], '" />
					</div>
				</div>
				<div class="control-group">
					<label class="control-label" for="section">Section:</label>
					<div class="controls">
						<input type="text" class="input-xlarge" id="section" name="section" value="', $template['group']['section'], '" />
					</div>
				</div>
				<div class="form-actions">
					<input type="submit" class="btn btn-primary" name="save" value="Save changes" />
					<input type="submit" class="btn" name="cancel" value="Cancel" />
				</div>
			</fieldset>
			<input type="hidden" name="group" value="', $template['group']['id'], '" />
			<input type="hidden" name="session_id" value="', $user['session_id'], '" />
		</form>';
}

function template_group_delete()
{
	global $user, $template;

	echo '
		<form class="form-horizontal" action="', build_url(array('group', 'delete')), '" method="post">
			<fieldset>
				<legend>Delete Group</legend>
				Are you sure you want to delete the group &quot;', $template['group']['name'], '&quot;?
				<div class="form-actions">
					<input type="submit" class="btn btn-danger" name="delete" value="Delete" />
					<input type="submit" class="btn" name="cancel" value="Cancel" />
				</div>
			</fieldset>
			<input type="hidden" name="group" value="', $template['group']['id'], '" />
			<input type="hidden" name="session_id" value="', $user['session_id'], '" />
		</form>';
}