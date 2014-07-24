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

function template_survey_response()
{
	global $template, $user;

	echo '
		<div class="page-header">
			<div class="pull-right">
				<a class="btn btn-success" href="', build_url(array('survey', 'graph')), '">Survey Graph</a>
			</div>
			<h2>Survey Response</h2>
		</div>
		<form class="form-horizontal" action="', build_url(array('survey', 'response', !empty($template['current_page']) ? $template['current_page'] : null)), '" method="post">
			<table class="table table-striped table-bordered">
				<thead>
					<tr>
						<th>#</th>
						<th>Question</th>
						<th>Never</th>
						<th>Sometimes</th>
						<th>Often</th>
						<th>Always</th>
					</tr>
				</thead>
				<tbody>';

	if (empty($template['items']))
	{
		echo '
					<tr>
						<td class="align_center" colspan="6">There are not any items available!</td>
					</tr>';
	}

	foreach ($template['items'] as $item)
	{
		echo '
					<tr>
						<td class="align_center span1">', $item['id'], '</td>
						<td>', $item['body'], '</td>';

		for ($i = 1; $i < 5; $i++)
		{
			echo '
						<td class="align_center span112">
							<input type="radio" name="response[', $item['id'], ']" value="', $i, '"', ($item['value'] == $i ? 'checked="checked"' : ''), '>
						</td>';
		}

		echo '
					</tr>';
	}

	echo '
				</tbody>
			</table>';

	if (!empty($template['items']))
	{
		if (!empty($template['use_pagination']))
		{
			echo '
			<div class="alert alert-info align_center">
				Please make sure you save your changes using the button right below before you move onto the next page! You must respond to all the items to be able to view the survey graph.
			</div>';
		}

		echo '
			<div class="overflow mini-padding">
				<input type="submit" class="btn btn-primary pull-right" name="save" value="Save changes" />';

		if (!empty($template['use_pagination']))
		{
			echo '
				<div class="pagination pagination-small">
					<ul>';

			for ($i = 1; $i <= $template['total_pages']; $i++)
			{
				echo '
						<li', $template['current_page'] == $i ? ' class="active"' : '', '><a href="', build_url(array('survey', 'response', $i)), '">', $i, '</a></li>';
			}

			echo '
					</ul>
				</div>';
		}

		echo '
			</div>';
	}

	echo '
			<input type="hidden" name="session_id" value="', $user['session_id'], '" />
		</form>';
}

function template_survey_graph()
{
	echo '
		<div class="page-header">
			<div class="pull-right">
				<a class="btn" href="', build_url('survey'), '">Back</a>
			</div>
			<h2>Survey Graph</h2>
		</div>
		<div id="fullgraph"></div>';
}