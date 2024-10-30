<?php

/**
 * Add rewrite rules for api-infusionsoft endpoint.
 */

function infusion_add_rewrite_rules() {
    infusion_rewrite_rules();

    infusion_flush_rewrite_rules();


}

function infusion_rewrite_rules() {
    add_rewrite_rule(
        'api-infusionsoft/?$',
        'index.php',
        'top'
   );
}

function infusion_template_route( $default_template ) {
    global $wp, $wp_rewrite;

    if ( $wp->matched_rule == 'api-infusionsoft/?$' ) {

        api_evaluation();
        exit();   
    }
}

function infusion_flush_rewrite_rules() {
    global $wp_rewrite;
    $wp_rewrite->flush_rules( false );

}