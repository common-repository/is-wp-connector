<?php

function api_evaluation() {

	/*
	First, get and sanitize API key and action tag
	*/

	ob_start();

	global $wp_query;
	$apiKey = sanitize_text_field( $_REQUEST['apikey'] );
	$action = sanitize_text_field( $_REQUEST['action'] );
	
	/*
	Next, verify the API key
	*/
	
	$options = get_option('infusion_api');
	$verifyApiKey = $options['wordpress_key'];

	if( !$verifyApiKey || ( $apiKey != $verifyApiKey )) {
		header("HTTP/1.0 403 Forbidden");
		echo 'On API page';
		die();
	}

	do_action('infusion_api', $action );
	
	ob_end_clean();

	echo 'On API page';
}