<?php

/*Infusion API calls
This file contains all the API calls from WordPress to InfusionSoft
*/

/**
 * Adds tag to user within InfusionSoft
 * @param  int $infusionId InfusionSoft contact ID
 * @return bool            true on success, false on error
 */
function infusion_add_nologin_tag( $infusionId ) {

    // Phone home to IS and add the tag for not having
    
    $options = get_option('infusion_api');

    if( !$options['app_name'] || !$options['infusion_key'] || !$options['never_tag_id'] ) {
        return false;
    }
    
    // Set our Infusionsoft application as the client

    $app_name = $options['app_name'];
    $client_name = "https://{$app_name}.infusionsoft.com/api/xmlrpc";
    $client = new xmlrpc_client( $client_name );

    $client->return_type = "phpvals";
    $client->setSSLVerifyPeer( FALSE );

    $key = $options['infusion_key'];

    $groupId = $options['never_tag_id'];
    
    $call = new xmlrpcmsg("ContactService.addToGroup", array(
        php_xmlrpc_encode( $key ),
        php_xmlrpc_encode( $infusionId ),
        php_xmlrpc_encode( $groupId ) 
    ));

    $result = $client->send($call);

    // ADD RETURN RESULT
}

function infusion_setup_client() {
    $options = get_option('infusion_api');
    if( !$options['app_name'] ) {
        return false;
    }

    $app_name = $options['app_name'];
    $client_name = "https://{$app_name}.infusionsoft.com/api/xmlrpc";
    $client = new xmlrpc_client( $client_name );
    $client->return_type = "phpvals";
    $client->setSSLVerifyPeer( FALSE );

    return $client;
}

function infusion_get_key() {
    $options = get_option('infusion_api');
    if( !$options['infusion_key'] ) {
        return false;
    }
    
    return $options['infusion_key'];
}
/**
 * Finds an InfusionSoft contact by email address
 * @param  string $email Email Address to find
 * @return int           InfusionSoft contact ID or false
 */
function infusion_ContactService_findByEmail( $email ) {

    $client = infusion_setup_client();
    $key = infusion_get_key();

    $call = new xmlrpcmsg('ContactService.findByEmail', array(
        php_xmlrpc_encode( $key ),
        php_xmlrpc_encode( $email ),
        php_xmlrpc_encode( array('Id'))
    ));
    
    // Get and return the result

    $result = $client->send($call);
    if( $result->val ) {
        return $result->val[0]['Id'];
    } else {
        return false;
    }
}
/**
 * Creates a contact within InfusionSoft
 * @param  array $contact_info Key/value pairs of fields to add
 * @return int                 InfusionSoft contact ID number if successful
 */
function infusion_ContactService_add( $contact_info ) {

    if( !isset($contact_info['Email'])) {
        return false;
    }

    // Whitelist the fields
    
    $accepted_fields = array(
        'Address1Type',
        'AccountId',
        'Address2Street1',
        'Address2Street2',
        'Address2Type',
        'Address3Street1',
        'Address3Street2',
        'Address3Type',
        'Anniversary',
        'AssistantName',
        'AssistantPhone',
        'BillingInformation',
        'Birthday',
        'City',
        'City2',
        'City3',
        'Company',
        'CompanyID',
        'ContactNotes',
        'ContactType',
        'Country',
        'Country2',
        'Country3',
        'CreatedBy',
        'Email',
        'EmailAddress2',
        'EmailAddress3',
        'Fax1',
        'Fax1Type',
        'Fax2',
        'Fax2Type',
        'FirstName',
        'JobTitle',
        'LastName',
        'Leadsource',
        'LeadSourceId',
        'MiddleName',
        'Nickname',
        'OwnerID',
        'Password',
        'Phone1',
        'Phone1Ext',
        'Phone1Type',
        'Phone2',
        'Phone2Ext',
        'Phone2Type',
        'Phone3',
        'Phone3Ext',
        'Phone3Type',
        'Phone4',
        'Phone4Ext',
        'Phone4Type',
        'Phone5',
        'Phone5Ext',
        'Phone5Type',
        'PostalCode',
        'PostalCode2',
        'PostalCode3',
        'ReferralCode',
        'SpouseName',
        'State',
        'State2',
        'State3',
        'StreetAddress1',
        'StreetAddress2',
        'Suffix',
        'Title',
        'Username',
        'Website',
        'ZipFour1',
        'ZipFour2',
        'ZipFour3'
    );
    $contact_info = array_intersect_key( $contact_info, array_flip( $accepted_fields ));

    // Setup the call

    $client = infusion_setup_client();
    $key = infusion_get_key();
    $call = new xmlrpcmsg('ContactService.add', array(
        php_xmlrpc_encode( $key ),
        php_xmlrpc_encode( $contact_info )
    ));

    $result = $client->send($call);

    return $result->val;
}

/**
 * Adds tag to an InfusionSoft contact
 * @param  int $infusion_id InfusionSoft contact ID
 * @param  int $tag_id      InfusionSoft tag ID
 * @return xmlrpcresp       Result of XML-RPC call
 */
function infusion_ContactService_addToGroup( $infusion_id, $tag_id ) {

    $client = infusion_setup_client();
    $key = infusion_get_key();
    
    $call = new xmlrpcmsg("ContactService.addToGroup", array(
        php_xmlrpc_encode( $key ),
        php_xmlrpc_encode( $infusion_id ),
        php_xmlrpc_encode( $tag_id ) 
    ));

    $result = $client->send($call);

    return $result;
}