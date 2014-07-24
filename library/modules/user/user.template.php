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

function template_user_list()
{
	global $template;

	echo '
		<div class="page-header">
			<div class="pull-right">
				<a class="btn btn-info" href="', build_url(array('user', 'import')), '">Import Users</a>
				<a class="btn btn-warning" href="', build_url(array('user', 'edit')), '">Add User</a>
			</div>
			<h2>User List</h2>
		</div>
		<table class="table table-striped table-bordered">
			<thead>
				<tr>
					<th>ID</th>
					<th>SSID</th>
					<th>First Name</th>
					<th>Last Name</th>
					<th>Generation</th>
					<th>Section</th>
					<th>Registered</th>
					<th>Admin</th>
					<th>Actions</th>
				</tr>
			</thead>
			<tbody>';

	if (empty($template['users']))
	{
		echo '
				<tr>
					<td class="align_center" colspan="9">There are not any users added yet!</td>
				</tr>';
	}

	foreach ($template['users'] as $user)
	{
		echo '
				<tr>
					<td class="align_center">', $user['id'], '</td>
					<td>', $user['ssid'], '</td>
					<td>', $user['first_name'], '</td>
					<td>', $user['last_name'], '</td>
					<td class="align_center">', $user['generation'], '</td>
					<td class="align_center">', $user['section'], '</td>
					<td class="align_center">', $user['registered'], '</td>
					<td class="align_center">', $user['admin'], '</td>
					<td class="span3 align_center">
						<a class="btn btn-success" href="', build_url(array('user', 'survey', $user['id'])), '">Survey</a>
						<a class="btn btn-primary" href="', build_url(array('user', 'edit', $user['id'])), '">Edit</a>
						<a class="btn btn-danger" href="', build_url(array('user', 'delete', $user['id'])), '">Delete</a>
					</td>
				</tr>';
	}

	echo '
			</tbody>
		</table>';
}

function template_user_edit()
{
	global $user, $template;

	echo '
		<form class="form-horizontal" action="', build_url(array('user', 'edit')), '" method="post" enctype="multipart/form-data">
			<fieldset>
				<legend>', (!$template['user']['is_new'] ? 'Edit' : 'Add'), ' User</legend>
				<div class="control-group">
					<label class="control-label" for="ssid">ID:</label>
					<div class="controls">
						<input type="text" class="input-xlarge" id="ssid" name="ssid" value="', $template['user']['ssid'], '" />
					</div>
				</div>
				<div class="control-group">
					<label class="control-label" for="first_name">First Name:</label>
					<div class="controls">
						<input type="text" class="input-xlarge" id="first_name" name="first_name" value="', $template['user']['first_name'], '" />
					</div>
				</div>
				<div class="control-group">
					<label class="control-label" for="last_name">Last Name:</label>
					<div class="controls">
						<input type="text" class="input-xlarge" id="last_name" name="last_name" value="', $template['user']['last_name'], '" />
					</div>
				</div>
				<div class="control-group">
					<label class="control-label" for="password">Password:</label>
					<div class="controls">
						<input type="password" class="input-xlarge" id="password" name="password" />
					</div>
				</div>
				<div class="control-group">
					<label class="control-label" for="verify_password">Verify password:</label>
					<div class="controls">
						<input type="password" class="input-xlarge" id="verify_password" name="verify_password" />
					</div>
				</div>
				<div class="control-group">
					<label class="control-label" for="id_group">Group:</label>
					<div class="controls">
						<select id="id_group" name="id_group">
							<option value="0"', ($template['user']['id_group'] == 0 ? ' selected="selected"' : ''), '>Select group</option>';

	foreach ($template['groups'] as $group)
	{
		echo '
							<option value="', $group['id'], '"', ($template['user']['id_group'] == $group['id'] ? ' selected="selected"' : ''), '>', $group['name'], '</option>';
	}

	echo '
						</select>
					</div>
				</div>
				<div class="control-group">
					<label class="control-label" for="admin">Admin:</label>
					<div class="controls">
						<select id="admin" name="admin">
							<option value="0"', ($template['user']['admin'] == 0 ? ' selected="selected"' : ''), '>No</option>
							<option value="1"', ($template['user']['admin'] == 1 ? ' selected="selected"' : ''), '>Yes</option>
						</select>
					</div>
				</div>
				<div class="control-group">
					<label class="control-label" for="photo">', (!$template['user']['is_new'] ? 'Replace' : 'Select'), ' photo:</label>
					<div class="controls">
						<input type="file" class="input-xlarge" id="photo" name="photo" />
					</div>
				</div>';

	if (!$template['user']['is_new'])
	{
		echo '
				<div class="control-group">
					<label class="control-label">Current photo:</label>
					<div class="controls">
						<img src="', get_photo_src($template['user']['uid']), '" alt="" class="img-polaroid web_photo" />
					</div>
				</div>
				<div class="control-group">
					<label class="control-label" for="login_count">Login count:</label>
					<div class="controls">
						<span class="input-xlarge uneditable-input" id="login_count">', $template['user']['login_count'], '</span>
					</div>
				</div>
				<div class="control-group">
					<label class="control-label" for="last_login">Last login:</label>
					<div class="controls">
						<span class="input-xlarge uneditable-input" id="last_login">', $template['user']['last_login'], '</span>
					</div>
				</div>
				<div class="control-group">
					<label class="control-label" for="last_password_change">Last password change:</label>
					<div class="controls">
						<span class="input-xlarge uneditable-input" id="last_password_change">', $template['user']['last_password_change'], '</span>
					</div>
				</div>
				<div class="control-group">
					<label class="control-label" for="uid">Unique ID:</label>
					<div class="controls">
						<span class="input-xlarge uneditable-input" id="uid">', $template['user']['uid'], '</span>
					</div>
				</div>';
	}

	echo '
				<div class="form-actions">
					<input type="submit" class="btn btn-primary" name="save" value="Save changes" />
					<input type="submit" class="btn" name="cancel" value="Cancel" />
				</div>
			</fieldset>
			<input type="hidden" name="user" value="', $template['user']['id'], '" />
			<input type="hidden" name="session_id" value="', $user['session_id'], '" />
		</form>';
}

function template_user_delete()
{
	global $user, $template;

	echo '
		<form class="form-horizontal" action="', build_url(array('user', 'delete')), '" method="post">
			<fieldset>
				<legend>Delete User</legend>
				Are you sure you want to delete the user &quot;', $template['user']['name'], '&quot;?
				<div class="form-actions">
					<input type="submit" class="btn btn-danger" name="delete" value="Delete" />
					<input type="submit" class="btn" name="cancel" value="Cancel" />
				</div>
			</fieldset>
			<input type="hidden" name="user" value="', $template['user']['id'], '" />
			<input type="hidden" name="session_id" value="', $user['session_id'], '" />
		</form>';
}

function template_user_survey()
{
	global $template;

	echo '
		<div class="page-header">
			<div class="pull-right">
				<a class="btn" href="', build_url('user'), '">Back</a>
			</div>
			<h2>User Survey Results</h2>
		</div>
		<div class="overflow">
			<dl class="dl-horizontal profile well">
				<dt>Photo:</dt>
				<dd><img src="', get_photo_src($template['user']['uid']), '" alt="" class="img-polaroid web_photo" /></dd>
				<dt>SSID:</dt>
				<dd>', $template['user']['ssid'], '</dd>
				<dt>First Name:</dt>
				<dd>', $template['user']['first_name'], '</dd>
				<dt>Last Name:</dt>
				<dd>', $template['user']['last_name'], '</dd>
				<dt>Generation:</dt>
				<dd>', $template['user']['generation'], '</dd>
				<dt>Section:</dt>
				<dd>', $template['user']['section'], '</dd>
			</dl>
			<div id="graph"></div>
		</div>';
}

function template_user_import()
{
	global $user;

	echo '
		<form class="form-horizontal" action="', build_url(array('user', 'import')), '" method="post" enctype="multipart/form-data">
			<fieldset>
				<legend>Import Users</legend>
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