<?php
/**
 * WHMCS Sample Merchant Gateway Module
 *
 * This sample file demonstrates how a merchant gateway module supporting
 * 3D Secure Authentication, Captures and Refunds can be structured.
 *
 * If your merchant gateway does not support 3D Secure Authentication, you can
 * simply omit that function and the callback file from your own module.
 *
 * Within the module itself, all functions must be prefixed with the module
 * filename, followed by an underscore, and then the function name. For this
 * example file, the filename is "airwallex" and therefore all functions
 * begin "airwallex_".
 *
 * For more information, please refer to the online documentation.
 *
 * @see https://developers.whmcs.com/payment-gateways/
 *
 * @copyright Copyright (c) WHMCS Limited 2019
 * @license http://www.whmcs.com/license/ WHMCS Eula
 */

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

/**
 * Define module related meta data.
 *
 * Values returned here are used to determine module related capabilities and
 * settings.
 *
 * @see https://developers.whmcs.com/payment-gateways/meta-data-params/
 *
 * @return array
 */
function airwallex_MetaData()
{
    return array(
        'DisplayName'   => 'Airwallex',
        'APIVersion'    => '1.1', // Use API Version 1.1
    );
}

/**
 * Define gateway configuration options.
 *
 * The fields you define here determine the configuration options that are
 * presented to administrator users when activating and configuring your
 * payment gateway module for use.
 *
 * Supported field types include:
 * * text
 * * password
 * * yesno
 * * dropdown
 * * radio
 * * textarea
 *
 * Examples of each field type and their possible configuration parameters are
 * provided in the sample function below.
 *
 * @see https://developers.whmcs.com/payment-gateways/configuration/
 *
 * @return array
 */
function airwallex_config()
{
    return array(
        // the friendly display name for a payment gateway should be
        // defined here for backwards compatibility
        'FriendlyName' => array(
            'Type' => 'System',
            'Value' => 'Airwallex',
        ),
        // a text field type allows for single line text input
        'accountID' => array(
            'FriendlyName' => 'Airwallex Client ID',
            'Type' => 'text',
            'Size' => '100',
            'Default' => '',
            'Description' => 'Enter your Client ID',
        ),
        // a password field type allows for masked text input
        'secretKey' => array(
            'FriendlyName' => 'Airwallex API Key',
            'Type' => 'password',
            'Size' => '100',
            'Default' => '',
            'Description' => 'Enter your API Key',
        ),
        // the yesno field type displays a single checkbox option
        'testMode' => array(
            'FriendlyName' => 'Test Mode',
            'Type' => 'yesno',
            'Default' => 'yes',
            'Description' => 'Tick to enable test mode with your demo account',
        )
    );
}

/**
 * No local credit card input.
 *
 * This is a required function declaration. Denotes that the module should
 * not allow local card data input.
 */
function airwallex_nolocalcc() {}

/**
 * Remote input.
 *
 * Called when a pay method is requested to be created or a payment is
 * being attempted.
 *
 * New pay methods can be created or added without a payment being due.
 * In these scenarios, the amount parameter will be empty and the workflow
 * should be to create a token without performing a charge.
 *
 * @param array $params Payment Gateway Module Parameters
 *
 * @see https://developers.whmcs.com/payment-gateways/remote-input-gateway/
 *
 * @return array
 */
function airwallex_remoteinput( $params )
{
    // Gateway Configuration Parameters
    $client_id      = $params['accountID'];
    $api_key        = $params['secretKey'];
    $test_mode      = $params['testMode'];
    $api_username   = $params['apiUsername'];

    // Invoice Parameters
    $invoice_id     = $params['invoiceid'];
    $description    = $params['description'];
    $amount         = $params['amount'];
    $currency_code  = $params['currency'];

    $product_id     = $params[ 'cart' ]->items[ 0 ]->id;
    $product_name   = $params[ 'cart' ]->items[ 0 ]->name;

    // Client Parameters
    $customer_id    = $params['clientdetails']['id'];
    $first_name     = $params['clientdetails']['firstname'];
    $last_name      = $params['clientdetails']['lastname'];
    $email          = $params['clientdetails']['email'];
    $address1       = $params['clientdetails']['address1'];
    $address2       = $params['clientdetails']['address2'];
    $city           = $params['clientdetails']['city'];
    $state          = $params['clientdetails']['state'];
    $postcode       = $params['clientdetails']['postcode'];
    $country        = $params['clientdetails']['country'];
    $phone          = $params['clientdetails']['phonenumber'];

    // System Parameters
    $company_name           = $params['companyname'];
    $system_url             = $params['systemurl'];
    $return_url             = $params['returnurl'];
    $lang_paynow            = $params['langpaynow'];
    $module_displayname     = $params['name'];
    $module_name            = $params['paymentmethod'];
    $whmcs_version          = $params['whmcsVersion'];

    // 1. Get payment intent
    $form_fields = [
        'test_mode'             => $test_mode,
        'client_id'             => $client_id,
        'api_key'               => $api_key,
        'customer_id'           => $customer_id,
        'system_url'            => $system_url,
        'invoice_id'            => $invoice_id,
        'product_id'            => $product_id,
        'product_name'          => $product_name,
        'amount'                => $amount,
        'currency'              => $currency_code,
    ];

    $res = awx_send_post( $system_url . 'modules/gateways/airwallex/public/index.php?c=Awx_Embedded_Fields_Controller&m=get_intent', $form_fields );

    $response =  json_decode( $res, TRUE );

    // 2. Front-end values
    $form_action = $system_url . 'modules/gateways/airwallex/public/index.php?c=Awx_Embedded_Fields_Controller&m=embedded_fields';

    $form_fields = [
        'followup'               => ( $amount > 0 ) ? 'payment' : 'create',
        'intent_id'             => $response[ 'intent' ][ 'id' ],
        'client_secret'         => $response[ 'intent' ][ 'client_secret' ],
        'customer_awx_id'       => $response[ 'customer_awx_id' ],
        'description'           => $description,
        'first_name'            => $first_name,
        'last_name'             => $last_name,
        'email'                 => $email,
        'address1'              => $address1,
        'address2'              => $address2,
        'city'                  => $city,
        'state'                 => $state,
        'postcode'              => $postcode,
        'country'               => $country,
        'phonenumber'           => $phone,
        'return_url'            => $system_url . 'modules/gateways/callback/airwallex.php',
    ];

    foreach ( $response[ 'params' ] as $k => $p )
    {
        $form_fields[ $k ] = $p;
    }

    // 3. Post to front-end page
    $form_output = '';
    foreach ( $form_fields as $key => $value )
    {
        $form_output .= '<input type="hidden" name="' . $key . '" value="' . $value . '">' . PHP_EOL;
    }

    return '<form method="post" action="' . $form_action . '">
        ' . $form_output . '
        <noscript>
            <input type="submit" value="Click here to continue &raquo;">
        </noscript>
    </form>';
}

/**
 * Capture payment.
 *
 * Called when a payment is requested to be processed and captured.
 *
 * The CVV number parameter will only be present for card holder present
 * transactions and when made against an existing stored payment token
 * where new card data has not been entered.
 *
 * @param array $params Payment Gateway Module Parameters
 *
 * @see https://developers.whmcs.com/payment-gateways/remote-input-gateway/
 *
 * @return array
 */
function airwallex_capture($params)
{
    // Gateway Configuration Parameters
    $client_id   = $params['accountID'];
    $api_key     = $params['secretKey'];
    $test_mode   = $params['testMode'];

    // Capture Parameters
    $remoteGatewayToken = $params['gatewayid'];
    $cardCvv            = $params['cccvv']; // Card Verification Value

    // Invoice Parameters
    $invoice_id      = $params['invoiceid'];
    $description    = $params['description'];
    $amount         = $params['amount'];
    $currency_code   = $params['currency'];

    // Client Parameters
    $first_name  = $params['clientdetails']['firstname'];
    $last_name   = $params['clientdetails']['lastname'];
    $email      = $params['clientdetails']['email'];
    $address1   = $params['clientdetails']['address1'];
    $address2   = $params['clientdetails']['address2'];
    $city       = $params['clientdetails']['city'];
    $state      = $params['clientdetails']['state'];
    $postcode   = $params['clientdetails']['postcode'];
    $country    = $params['clientdetails']['country'];
    $phone      = $params['clientdetails']['phonenumber'];

    $system_url   = $params['systemurl'];

    // A token is required for a remote input gateway capture attempt
    $tokens = explode( '-|-', $remoteGatewayToken );

    if ( count( $tokens ) != 2 )
    {
        return [
            'status'            => 'declined',
            'decline_message'   => 'No Remote Token',
        ];
    }

    // Set post data
    $postFields = [
        'consent_id'        => $tokens[ 0 ],
        'customer_id'       => $tokens[ 1 ],
        'invoice_id'        => $invoice_id,
        'amount'            => $amount,
        'currency'          => $currency_code,
        'client_id'         => $client_id,
        'api_key'           => $api_key,
        'test_mode'         => $test_mode,
    ];

    // Perform API call to initiate capture
    $res = awx_send_post( $system_url . 'modules/gateways/airwallex/public/index.php?c=Awx_Embedded_Fields_Controller&m=do_charge_fees', $postFields );
    $response =  json_decode( $res, TRUE );

    if ( isset( $response['id'] ) )
    {
        return [
            'status'    => 'success',
            'transid'   => $response['id'],
            'rawdata'   => $response,
        ];
    }

    return [
        'status'        => 'declined',
        'declinereason' => 'the card has to be updated',
        'rawdata'       => $response
    ];
}

/**
 * Remote update.
 *
 * Called when a pay method is requested to be updated.
 *
 * The expected return of this function is direct HTML output. It provides
 * more flexibility than the remote input function by not restricting the
 * return to a form that is posted into an iframe. We still recommend using
 * an iframe where possible and this sample demonstrates use of an iframe,
 * but the update can sometimes be handled by way of a modal, popup or
 * other such facility.
 *
 * @param array $params Payment Gateway Module Parameters
 *
 * @see https://developers.whmcs.com/payment-gateways/remote-input-gateway/
 *
 * @return array
 */
function airwallex_remoteupdate($params)
{
}

/**
 * Admin status message.
 *
 * Called when an invoice is viewed in the admin area.
 *
 * @param array $params Payment Gateway Module Parameters.
 *
 * @return array
 */
function airwallex_adminstatusmsg($params)
{
    // Gateway Configuration Parameters
    $apiUsername = $params['apiUsername'];
    $apiPassword = $params['apiPassword'];
    $test_mode = $params['testMode'];

    // Invoice Parameters
    $remoteGatewayToken = $params['gatewayid'];
    $invoice_id = $params['id']; // The Invoice ID
    $userId = $params['userid']; // The Owners User ID
    $date = $params['date']; // The Invoice Create Date
    $dueDate = $params['duedate']; // The Invoice Due Date
    $status = $params['status']; // The Invoice Status

    if ($remoteGatewayToken) {
        return [
            'type' => 'info',
            'title' => 'Token Gateway Profile',
            'msg' => 'This customer has a Remote Token storing their card'
                . ' details for automated recurring billing with ID ' . $remoteGatewayToken,
        ];
    }
}

/**
 * Send POST CURL request.
 *
 * @access  private
 *
 * @param   string $url
 * @param   string $param
 *
 * @return  void
 */
function awx_send_post( $url = '', $param = '' )
{
    if ( empty( $url ) OR empty( $param ) )
    {
        return FALSE;
    }

    $ch = curl_init();
    curl_setopt( $ch, CURLOPT_URL, $url );
    curl_setopt( $ch, CURLOPT_HEADER, 0 );
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
    curl_setopt( $ch, CURLOPT_POST, 1 );
    curl_setopt( $ch, CURLOPT_POSTFIELDS, $param );

    $data = curl_exec( $ch );
    curl_close( $ch );
    return $data;
}

