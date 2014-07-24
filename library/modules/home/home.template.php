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

function template_home_main()
{
	global $core, $template, $user;

	echo '
		<div class="page-header">
			<div class="pull-right">
				', $template['total_items'], ' items &bull; ', $template['total_responses'], ' responses &bull; ', $template['total_users'], ' users
			</div>
			<h2>', $core['title_long'], '</h2>
		</div>';

	if ($user['logged'])
	{
		echo '
		<p class="content well">Welcome to the career guidance system! Use the button above to access and complete the survey if you have not yet.</p>';
	}
	else
	{
		echo '
		<p class="content well">Welcome to the career guidance system! Please log in for access to the tools.</p>';
	}
}