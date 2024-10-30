<?php

add_action( 'infusion_api', 'infusion_add_tag', 10, 1 );
add_action( 'infusion_api', 'infusion_last_login', 10, 1 );
add_action( 'infusion_api', 'infusion_remove_tag', 10, 1 );
add_action( 'infusion_api', 'infusion_addif_tag', 10, 1 );

/**
 * Action to add user with a specific custom capability tag
 * All paramaters for this function are passed from the $_REQUEST global
 * @param  infusionid: user's InfusionSoft id
 * @param  infusiontag: comma-delimited list of custom capability tags to add
 * @param  firstname: user's first name (only required if user is not already in WP database)
 * @param  lastname: user's last name (only required if user is not already in WP database)
 * @param  email: user's email address (only required if user is not already in WP database)
 */
function infusion_add_tag( $action ) {

	if( $action == 'add' ) {

		global $wp_query;
		$infusionId = sanitize_text_field( $_REQUEST['infusionid'] );
		$infusionTag = sanitize_text_field( $_REQUEST['infusiontag'] );
		$firstName = sanitize_text_field( $_REQUEST['firstname'] );
		$lastName = sanitize_text_field( $_REQUEST['lastname'] );
		$email = sanitize_text_field( $_REQUEST['email'] );

		if( !$infusionId || !$infusionTag ) {
			header("HTTP/1.0 400 Bad Request");
			die();
		}

		$create_user = FALSE;

		$user = wp_find_infusion_user( $infusionId );
		
		if( !$user ) {

			if( !$email || !$firstName || !$lastName ) {
				header("HTTP/1.0 400 Bad Request");
				die();
			}

			$user = wp_create_infusion_user( $infusionId, $email, $firstName, $lastName );
			
			$create_user = TRUE;

			if( !$user ){
				wp_send_infusion_error_email( $infusionId, $email, $action, $infusionTag );
				die();
			}

		}
		
		$ccaps = explode( ',', $infusionTag );
		foreach ( $ccaps as $ccap ) {
			$capability = build_permission_tag( $ccap );
			$user->add_cap( $capability );
		}

		if( !$create_user ) { // If we didn't have to add a user, we should tell them that they have been added

			if( is_multisite() ) { // If we're on a multisite, make sure that the user is added to this blog

				$blog_id = get_current_blog_id();
				add_user_to_blog( $blog_id, $user->ID, 'subscriber' );

			}

			$options = get_option('infusion_api');
			$rawMessage = stripslashes($options['existing_email']);
			if( !$rawMessage ) {
				// Do nothing
			} else {
				$filteredMessage = token_replace( $rawMessage, $user );
				wp_mail( $email, 'Course Registration Completed', $filteredMessage );
			}

		}
	}
}

/**
 * Action to add a specific custom capability tag only to an already existing user
 * All paramaters for this function are passed from the $_REQUEST global
 * @param  infusionid: user's InfusionSoft id
 * @param  infusiontag: comma deliminted list of custom capability tags to add
 */
function infusion_addif_tag( $action ) {

	if( $action == 'addif' ) {
		global $wp_query;
		$infusionId = sanitize_text_field( $_REQUEST['infusionid'] );
		$infusionTag = sanitize_text_field( $_REQUEST['infusiontag'] );

		if( !$infusionId || !$infusionTag ) {
			header("HTTP/1.0 400 Bad Request");
			die();
		}

		$user = wp_find_infusion_user( $infusionId );
		
		if( !$user ) {
			header("HTTP/1.0 202 Accepted");
			die();
		}
		
		$ccaps = explode( ',', $infusionTag );
		foreach ( $ccaps as $ccap ) {
			$capability = build_permission_tag( $ccap );
			$user->add_cap( $capability );
		}
	}
}

/**
 * Action to test if the user has logged in within a certain time span
 * All parameters for this function are passed from the $_REQUEST global
 * @param infusionid: user's InfusionSoft id
 * @param logininterval: interval (in seconds) to test whether the user logged in
 */
function infusion_last_login( $action ) {

	if( $action == 'last-login' ) {
		// Get request variables
		// If no login time specified, default to 0

		global $wp_query;
		$infusionId = sanitize_text_field( $_REQUEST['infusionid'] );
		$loginInterval = ( $_REQUEST['logininterval'] ? absint( sanitize_text_field( $_REQUEST['logininterval'] ) ) : 0 );

		if( !$infusionId ) {
			header("HTTP/1.0 400 Bad Request");
			die();
		}

		// Get the user based on IS id number

		$user = wp_find_infusion_user( $infusionId );

		if( !$user ) {
			header("HTTP/1.0 400 Bad Request");
			die();
		}

		// Get the last login date

		global $wpdb;
		$lastLoginKey = $wpdb->prefix . 's2member_last_login_time';
		$lastLoginTime = get_user_meta( $user->ID, $lastLoginKey, true );

		// Compare the last login time with the range given

		if( !$lastLoginTime ) {
			
			infusion_add_nologin_tag( $infusionId );

		} else {
			// They have logged in, but was it recently enough?
			if ( $loginInterval ) { // We only need to check if there's a specific time range, because they definitely have logged in at some point
				
				$now = new DateTime();
				$withinInterval = ( $now->getTimestamp() - $loginInterval );
				if( $lastLoginTime < $withinInterval ) {
					// Not recent enough!
					infusion_add_nologin_tag( $infusionId );
				}
			}
		}
	}
}

/**
 * Action to remove a custom capability tag from an existing user
 * All parameters for this function are passed from the $_REQUEST global
 * @param infusionid: user's InfusionSoft id
 * @param infusiontag: comma-delimited list of custom capability tags to remove
 */
function infusion_remove_tag( $action ) {

	if( $action == 'remove' ) {
		global $wp_query;
		$infusionId = sanitize_text_field( $_REQUEST['infusionid'] );
		$infusionTag = sanitize_text_field( $_REQUEST['infusiontag'] );

		if( !$infusionId || !$infusionTag ) {
			header("HTTP/1.0 400 Bad Request");
			die();
		}

		$user = wp_find_infusion_user( $infusionId );
		
		if ( $user ) { // We only need to remove a tag if the user exists
		
			$ccaps = explode( ',', $infusionTag );
			foreach( $ccaps as $ccap ) {
				$capability = build_permission_tag( $ccap );
				$user->remove_cap( $capability );
			}
		}
	}
}