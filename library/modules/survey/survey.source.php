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

function survey_main()
{
	global $core, $template;

	$actions = array('response', 'graph');

	$core['current_action'] = 'response';
	if (!empty($_REQUEST['action']) && in_array($_REQUEST['action'], $actions))
		$core['current_action'] = $_REQUEST['action'];

	call_user_func($core['current_module'] . '_' . $core['current_action']);
}

function survey_response()
{
	global $core, $template, $user;

	if (!empty($_POST['save']))
	{
		if (!empty($_POST['response']) && is_array($_POST['response']))
		{
			$inserts = array();

			foreach ($_POST['response'] as $id => $value)
			{
				$id = (int) $id;
				$value = $value < 1 || $value > 4 ? 0 : (int) $value;
				$inserts[] = "($user[id], $id, $value)";
			}

			if (!empty($inserts))
			{
				db_query("
					REPLACE INTO response
						(id_user, id_item, value)
					VALUES
						" . implode(', ', $inserts));
			}
		}
	}

	$items_per_page = 25;

	$request = db_query("
		SELECT COUNT(id_item)
		FROM item
		LIMIT 1");
	list ($total_items) = db_fetch_row($request);
	db_free_result($request);

	if ($total_items > $items_per_page)
	{
		$template['use_pagination'] = true;
		$template['total_pages'] = floor($total_items / $items_per_page) + ($total_items % $items_per_page == 0 ? 0 : 1);
		$template['current_page'] = !empty($_REQUEST['survey']) && $_REQUEST['survey'] > 0 && $_REQUEST['survey'] <= $template['total_pages'] ? (int) $_REQUEST['survey'] : 1;

		$start = ($template['current_page'] - 1) * $items_per_page;
	}
	else
		$start = 0;

	$request = db_query("
		SELECT i.id_item, i.body, r.value
		FROM item AS i
			LEFT JOIN response as r ON (r.id_item = i.id_item AND r.id_user = $user[id])
		ORDER BY i.id_item
		LIMIT $start, $items_per_page");
	$template['items'] = array();
	while ($row = db_fetch_assoc($request))
	{
		$template['items'][] = array(
			'id' => $row['id_item'],
			'body' => $row['body'],
			'value' => $row['value'],
		);
	}
	db_free_result($request);

	$template['page_title'] = 'Survey Response';
	$core['current_template'] = 'survey_response';
}

function survey_graph()
{
	global $core, $template, $user;

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
			LEFT JOIN response AS r ON (r.id_item = i.id_item AND r.id_user = $user[id])
		WHERE ISNULL(r.value)");
	list ($missing_responses) = db_fetch_row($request);
	db_free_result($request);

	if (!empty($missing_responses))
		fatal_error('You have not responded to ' . $missing_responses . ' item(s). You can only view your graph after you have completed the survey.');

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
			LEFT JOIN response as r ON (r.id_item = i.id_item AND r.id_user = $user[id])
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
			$("#fullgraph").highcharts({
				chart: {
					type: "column"
				},
				title: {
					text: "Survey Graph for ' . $user['name'] . '"
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

	$template['page_title'] = 'Survey Graph';
	$core['current_template'] = 'survey_graph';
}