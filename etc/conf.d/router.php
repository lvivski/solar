<?php
return array(
	'router' => array(

		'/' => array(
			'mod' => 'index',
		),

		'~/^\/static\/([a-z]+)\.html/' => array(
			'mod' => 'static',
			'page' => 1,
		),

		'~/^\/([a-z]+)\.html/' => array(
			'mod' => 1,
		),
	),
);
