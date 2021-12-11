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
        'DisplayName' => 'Airwallex',
        'APIVersion' => '1.1', // Use API Version 1.1
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
        ),
        // a password field type allows for masked text input
        // 'webhookSecret' => array(
        //     'FriendlyName' => 'Airwallex Webhook Secret',
        //     'Type' => 'password',
        //     'Size' => '100',
        //     'Description' => 'Enter your Webhook Secret',
        // ),
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
    $clientId   = $params['accountID'];
    $apiKey     = $params['secretKey'];
    $testMode   = $params['testMode'];

    // Capture Parameters
    $remoteGatewayToken = $params['gatewayid'];
    $cardCvv            = $params['cccvv']; // Card Verification Value

    // Invoice Parameters
    $invoiceId      = $params['invoiceid'];
    $description    = $params['description'];
    $amount         = $params['amount'];
    $currencyCode   = $params['currency'];

    // Client Parameters
    $firstname  = $params['clientdetails']['firstname'];
    $lastname   = $params['clientdetails']['lastname'];
    $email      = $params['clientdetails']['email'];
    $address1   = $params['clientdetails']['address1'];
    $address2   = $params['clientdetails']['address2'];
    $city       = $params['clientdetails']['city'];
    $state      = $params['clientdetails']['state'];
    $postcode   = $params['clientdetails']['postcode'];
    $country    = $params['clientdetails']['country'];
    $phone      = $params['clientdetails']['phonenumber'];

    $systemUrl   = 'http://dev.whmcs/';//$params['systemurl'];

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
        'invoice_id'        => $invoiceId,
        'amount'            => $amount,
        'currency'          => $currencyCode,
        'client_id'         => $clientId,
        'api_key'           => $apiKey,
    ];

    // Perform API call to initiate capture
    $res = awx_send_post( $systemUrl . 'modules/gateways/airwallex/public/index.php?c=Awx_Embedded_Fields_Controller&m=do_charge_fees', $postFields );
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
function airwallex_remoteinput($params)
{
    // Gateway Configuration Parameters
    $clientId = $params['accountID'];
    $apiKey = $params['secretKey'];
    $testMode = $params['testMode'];

    // Invoice Parameters
    $invoiceId = $params['invoiceid'];
    $description = $params['description'];
    $amount = $params['amount'];
    $currencyCode = $params['currency'];

    // Client Parameters
    $customerId = $params['clientdetails']['id'];
    $firstname = $params['clientdetails']['firstname'];
    $lastname = $params['clientdetails']['lastname'];
    $email = $params['clientdetails']['email'];
    $address1 = $params['clientdetails']['address1'];
    $address2 = $params['clientdetails']['address2'];
    $city = $params['clientdetails']['city'];
    $state = $params['clientdetails']['state'];
    $postcode = $params['clientdetails']['postcode'];
    $country = $params['clientdetails']['country'];
    $phone = $params['clientdetails']['phonenumber'];

    // System Parameters
    $companyName = $params['companyname'];
    $systemUrl = 'http://dev.whmcs/';//$params['systemurl'];
    $returnUrl = $params['returnurl'];
    $langPayNow = $params['langpaynow'];
    $moduleDisplayName = $params['name'];
    $moduleName = $params['paymentmethod'];
    $whmcsVersion = $params['whmcsVersion'];

    // Build a form which can be submitted to an iframe target to render
    // the payment form.

    $action = '';
    if ($amount > 0) {
        $action = 'payment';
    } else {
        $action = 'create';
    }

    $formAction = $systemUrl . 'modules/gateways/airwallex/public/index.php?c=Awx_Embedded_Fields_Controller&m=embedded_fields';
    $formFields = [
        'test_mode' => $testMode,
        'client_id' => $clientId,
        'api_key' => $apiKey,
        'systemUrl' => $systemUrl,
        'action' => $action,
        'invoice_id' => $invoiceId,
        'amount' => $amount,
        'currency' => $currencyCode,
        'customer_id' => $customerId,
        'first_name' => $firstname,
        'last_name' => $lastname,
        'email' => $email,
        'address1' => $address1,
        'address2' => $address2,
        'city' => $city,
        'state' => $state,
        'postcode' => $postcode,
        'country' => $country,
        'phonenumber' => $phone,
        'return_url' => $systemUrl . 'modules/gateways/callback/airwallex.php',
        // Sample verification hash to protect against form tampering
        'verification_hash' => sha1(
            implode('|', [
                $apiUsername,
                $clientId,
                $invoiceId,
                $amount,
                $currencyCode,
                $apiPassword,
                '', // This will be the remoteStorageToken in an update
            ])
        ),
    ];

    $formOutput = '';
    foreach ($formFields as $key => $value) {
        $formOutput .= '<input type="hidden" name="' . $key . '" value="' . $value . '">' . PHP_EOL;
    }

    return '<form method="post" action="' . $formAction . '">
    ' . $formOutput . '
    <noscript>
        <input type="submit" value="Click here to continue &raquo;">
    </noscript>
</form>';
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
    // Gateway Configuration Parameters
    $apiUsername = $params['apiUsername'];
    $apiPassword = $params['apiPassword'];
    $remoteStorageToken = $params['gatewayid'];
    $testMode = $params['testMode'];

    // Client Parameters
    $clientId = $params['clientdetails']['id'];
    $firstname = $params['clientdetails']['firstname'];
    $lastname = $params['clientdetails']['lastname'];
    $email = $params['clientdetails']['email'];
    $address1 = $params['clientdetails']['address1'];
    $address2 = $params['clientdetails']['address2'];
    $city = $params['clientdetails']['city'];
    $state = $params['clientdetails']['state'];
    $postcode = $params['clientdetails']['postcode'];
    $country = $params['clientdetails']['country'];
    $phone = $params['clientdetails']['phonenumber'];
    $payMethodId = $params['paymethodid'];

    // System Parameters
    $companyName = $params['companyname'];
    $systemUrl = 'http://dev.whmcs/';//$params['systemurl'];
    $returnUrl = $params['returnurl'];
    $langPayNow = $params['langpaynow'];
    $moduleDisplayName = $params['name'];
    $moduleName = $params['paymentmethod'];
    $whmcsVersion = $params['whmcsVersion'];

    // Build a form which can be submitted to an iframe target to render
    // the payment form.

    $formAction = $systemUrl . 'demo/remote-iframe-demo.php';
    $formFields = [
        'client_id' => $client_id,
        'card_token' => $remoteStorageToken,
        'action' => 'update',
        'invoice_id' => 0,
        'amount' => 0,
        'currency' => '',
        'customer_id' => $clientId,
        'first_name' => $firstname,
        'last_name' => $lastname,
        'email' => $email,
        'address1' => $address1,
        'address2' => $address2,
        'city' => $city,
        'state' => $state,
        'postcode' => $postcode,
        'country' => $country,
        'phonenumber' => $phone,
        'return_url' => $systemUrl . 'modules/gateways/callback/remoteinputgateway.php',
        // Sample verification hash to protect against form tampering
        'verification_hash' => sha1(
            implode('|', [
                $apiUsername,
                $clientId,
                0, // Invoice ID - there is no invoice for an update
                0, // Amount - there is no amount when updating
                '', // Currency Code - there is no currency when updating
                $apiPassword,
                $remoteStorageToken,
            ])
        ),
        // The PayMethod ID will need to be available in the callback file after
        // update. We will pass a custom variable here to enable that.
        'custom_reference' => $payMethodId,
    ];

    $formOutput = '';
    foreach ($formFields as $key => $value) {
        $formOutput .= '<input type="hidden" name="' . $key . '" value="' . $value . '">' . PHP_EOL;
    }

    // This is a working example which posts to the file: demo/remote-iframe-demo.php
    return '<div id="frmRemoteCardProcess" class="text-center">
    <form method="post" action="' . $formAction . '" target="remoteUpdateIFrame">
        ' . $formOutput . '
        <noscript>
            <input type="submit" value="Click here to continue &raquo;">
        </noscript>
    </form>
    <iframe name="remoteUpdateIFrame" class="auth3d-area" width="90%" height="600" scrolling="auto" src="about:blank"></iframe>
</div>
<script>
    setTimeout("autoSubmitFormByContainer(\'frmRemoteCardProcess\')", 1000);
</script>';
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
    $testMode = $params['testMode'];

    // Invoice Parameters
    $remoteGatewayToken = $params['gatewayid'];
    $invoiceId = $params['id']; // The Invoice ID
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

