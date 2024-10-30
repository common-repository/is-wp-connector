<?php
/*
Plugin Name: IS-WP Connector
Plugin URI: 
Description: Connects IS and WP through API calls
Version: 2.1.1
Author: Colin J. Hahn
Author URI:
*/

include( plugin_dir_path( __FILE__ ) . 'rewrite.php');
include( plugin_dir_path( __FILE__ ) . 'api-evaluation.php');
include( plugin_dir_path( __FILE__ ) . 'actions.php');
include( plugin_dir_path( __FILE__ ) . 'infusion.php');
include( plugin_dir_path( __FILE__ ) . 'utilities.php');
include( plugin_dir_path( __FILE__ ) . 'options.php');

if( !function_exists( 'xmlprc_encode_entities' ) ) {
	include( plugin_dir_path( __FILE__ ) . '/xmlrpc-3.0.0.beta/lib/xmlrpc.inc');
}

register_activation_hook( __FILE__, 'infusion_add_rewrite_rules' );
add_action( 'init', 'infusion_rewrite_rules' );
add_action( 'parse_request', 'infusion_template_route' );
register_deactivation_hook( __FILE__, 'infusion_flush_rewrite_rules' );

add_shortcode( 'is-api', 'is_api_shortcode' ); // In utilities.php
?>