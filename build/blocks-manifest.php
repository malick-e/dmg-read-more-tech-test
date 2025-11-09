<?php
// This file is generated. Do not modify it manually.
return array(
	'dmg' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'dmg/read-more',
		'version' => '0.1.0',
		'title' => 'DMG Read More',
		'category' => 'text',
		'icon' => 'admin-links',
		'description' => 'Read More block for DMG tech test',
		'example' => array(
			
		),
		'supports' => array(
			'html' => false
		),
		'textdomain' => 'dmg',
		'attributes' => array(
			'postId' => array(
				'type' => 'number',
				'default' => null
			)
		),
		'editorScript' => 'file:./index.js',
		'editorStyle' => 'file:./index.css',
		'style' => 'file:./style-index.css'
	)
);
