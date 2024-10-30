<?php

/*Utilities
This file contains all the functions that are called from actions.php except for InfusionSoft API calls
It also contains some helper functions
*/

/**
* Function build_permission_tag()
* @param $infusionTag: String custom capability tag that was passed via InfusionSoft
* 
* @return $capability: String custom capability for s2member API functions
*/
function build_permission_tag( $infusionTag ) {
    
    $capability = 'access_s2member_ccap_' . $infusionTag;

    return $capability;
}

/**
 * Function is_api_shortcode()
 * [is-api] embeds the API functionality into a given page
 * Useful for multisite when rewrite rules don't function as expected
 * but may produce side effects with multiple headers
 * @return string 'On API page'
 */
function is_api_shortcode() {

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
        die();
    }

    do_action('infusion_api', $action );

    ob_end_clean();
    
    // Test
    return 'On API page';
}

/**
* Function wp_create_infusion_user()
* @param $infusionId: InfusionSoft contact id of the user to create
* @param $email: Email address of the InfusionSoft contact
* @param $firstName: First name of the contact
* @param $lastName: Last name of the contact
* 
* @return $user: WP_User object or 0 if error in creation
*/
function wp_create_infusion_user( $infusionId, $email, $firstName, $lastName ) {

    $user = get_user_by( 'email', $email );
    if( !$user ) { // User email is not in use

        // Username can only contain letter and numbers for WP multisite
		$username = preg_replace( '/[^A-Za-z0-9]/', '', $email );

        // Ensure that the stripped username isn't already in use
        $username_exists = get_user_by( 'login', $username );
        
        if( $username_exists ) {
            
            $username_root = $username;
            $i = 0;
            
            while( $username_exists ) {
                $i++;
                $username = $username_root . $i;
                $username_exists = get_user_by( 'login', $username );
            }        
        }


		$random_password = wp_generate_password( $length=12, $include_standard_special_chars=false );

		$newUserId = wp_insert_user( array(
			'user_login' => $username,
			'user_email' => $email,
			'first_name' => $firstName,
			'last_name' => $lastName,
			'user_pass' => $random_password
			)
		);

		if( is_wp_error( $newUserId ) ) {
			return 0;
		}

        if( is_multisite() ) {

            $blog_id = get_current_blog_id();
            add_user_to_blog( $blog_id, $newUserId, 'subscriber' );
        }

		$user = get_userdata( $newUserId );

		// Now send the new user an email
		$options = get_option('infusion_api');
		$rawMessage = stripslashes($options['new_email']);
		if( !$rawMessage ) {
			wp_new_user_notification( $newUserId, $random_password ); // Send default email
		} else {
			$filteredMessage = token_replace( $rawMessage, $user );
			$filteredMessage = preg_replace( '/%password%/', $random_password, $filteredMessage ); // Need to add password here because it's not part of WP_User object
			wp_mail( $email, 'New User Account', $filteredMessage );
		}

		}

    // $user either contains the newly created WP_User object or the WP_User object that has the email specified
    // Link that user with the InfusionSoft account
    $userIdForInfusionMeta = $user->ID;
    add_user_meta( $userIdForInfusionMeta, 'is_contact_id', $infusionId );

    return $user;
}

/**
* Function wp_find_infusion_user()
* @param $infusionId: InfusionSoft contact id of the user to look up
* 
* @return $user: WP_User object or 0 if no user found
*/

function wp_find_infusion_user( $infusionId ) {
    $args = array (
        'meta_query'     => array(
            array(
                'key'       => 'is_contact_id',
                'value'     => $infusionId,
                'compare'   => '='
            ),
        ),
    );

    $user_query = new WP_User_Query( $args );

    $user_search_results = $user_query->results;

    if ( ! empty( $user_search_results ) ) {
        
        $user = $user_search_results[0];
    
    } else {

        $user = 0;
    }

    return $user;
}

/**
* Function wp_send_infusion_error_email
* @param $infusionId: InfusionSoft user id
* @param $email: User email address
* @param $action: Action attempting to do with the user
* @param $infusionTag: Course tag to change
* 
* @return true for successfully emailing, false if email failed to send
*
* As of 0.95-dev-review, only invoked if there is an error in user creation
*/
function wp_send_infusion_error_email( $infusionId, $email, $action, $infusionTag ) {
    
    $options = get_option('infusion_api');
    if( !$options['email_error'] ) {
        $email_recipient = get_option( 'admin_email' );
    } else {
        $email_recipient = $options['email_error'];
    }

    $blogname = get_option( 'blogname' );
    $message = "There was a problem with the InfusionSoft API on " . $blogname . ".\r\n";
    $message .= "\r\n";
    $message .= "InfusionSoft contact id: " . $infusionId . "\r\n";
    $message .= "Email address: " . $email . "\r\n";
    $message .= "Attempted action: " . $action . "\r\n";
    $message .= "Tag: " . $infusionTag . "\r\n";
    $message .= "\r\n";
    $message .= "Please check into this issue and resolve it manually.\r\n";

    $result = wp_mail( $email_recipient, 'Infusion API issue', $message );

    return $result;
}

function infusion_pretty_interval( $seconds ) {

    $w = $seconds / 86400 / 7;
    $d = $seconds / 86400 % 7;
    $h = $seconds / 3600 % 24;
    $m = $seconds / 60 % 60;
    $s = $seconds % 60;

    if ($w >= 1) {
        $output = "{$w} ".($w == 1 ? i18n::get('week') : i18n::get('weeks'));
        return $output;
    }
    
    if ($d >= 1) {
        $output = "{$d} ".($d == 1 ? i18n::get('day') : i18n::get('days'));
        return $output;
    }

    if ($h >= 1) {
        $output = "{$h} ".($h == 1 ? i18n::get('hour') : i18n::get('hours'));
        return $output;  
    }

    if ($m >= 1) {
        $output = "{$m} ".($m == 1 ? i18n::get('minute') : i18n::get('minutes'));
        return $output;
    }

    if ($s >= 1) {
        $output = "{$s} ".($s == 1 ? i18n::get('second') : i18n::get('seconds'));
        return $output;
    }

    return 0;
}

/**
* Function token_replace
* @param $text_to_replace: string of text with tokens
* @param $user: WP_User object that has relevant token content
* 
* @return $text_to_replace: string with tokens replaced
*/
function token_replace( $text_to_replace, $user ) {

	if( is_a( $user, 'WP_User' ) ) { // We only have replacements to perform if there is a WP_User object
		
        $displayName = $user->display_name;
		$accountName = $user->user_login;
		$email = $user->user_email;

        $text_to_replace = preg_replace('/%username%/', $displayName, $text_to_replace );
        $text_to_replace = preg_replace('/%accountname%/', $accountName, $text_to_replace );
        $text_to_replace = preg_replace('/%email%/', $email, $text_to_replace );

	} 
	
    return $text_to_replace;
}