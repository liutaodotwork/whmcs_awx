<?php
/**
 * WHMCS Remote Input Gateway Callback File
 *
 * The purpose of this file is to demonstrate how to handle the return post
 * from a Remote Input and Remote Update Gateway
 *
 * It demonstrates verifying that the payment gateway module is active,
 * validating an Invoice ID, checking for the existence of a Transaction ID,
 * Logging the Transaction for debugging, Adding Payment to an Invoice and
 * adding or updating a payment method.
 *
 * For more information, please refer to the online documentation.
 *
 * @see https://developers.whmcs.com/payment-gateways/
 *
 * @copyright Copyright (c) WHMCS Limited 2019
 * @license http://www.whmcs.com/license/ WHMCS Eula
 */

require_once __DIR__ . '/../../../init.php';

App::load_function('gateway');
App::load_function('invoice');

// Detect module name from filename.
$gatewayModuleName = basename(__FILE__, '.php');


// Fetch gateway configuration parameters.
$gatewayParams = getGatewayVariables($gatewayModuleName);
// Verify the module is active.
if (!$gatewayParams['type']) {
    die("Module Not Activated");
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
function awx_send_post2( $url = '', $param = '' )
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


$clientId    = $gatewayParams['accountID'];
$apiKey    = $gatewayParams['secretKey'];
$testMode       = $gatewayParams['testMode'];
$system_url       = $gatewayParams['systemurl'];


// Retrieve data returned in redirect
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
$invoiceId = isset($_REQUEST['invoice_id']) ? $_REQUEST['invoice_id'] : '';
$customerId = isset($_REQUEST['customer_id']) ? $_REQUEST['customer_id'] : '';
$amount = isset($_REQUEST['amount']) ? $_REQUEST['amount'] : '';
$fees = isset($_REQUEST['fees']) ? $_REQUEST['fees'] : '';
$currencyCode = isset($_REQUEST['currency']) ? $_REQUEST['currency'] : '';
$transactionId = isset($_REQUEST['transaction_id']) ? $_REQUEST['transaction_id'] : '';
$cardToken = isset($_REQUEST['card_token']) ? $_REQUEST['card_token'] : '';


// Get card info
$paymentMethodId = isset($_REQUEST['paymentmethod_id']) ? $_REQUEST['paymentmethod_id'] : '';

$res = awx_send_post2( $system_url . 'modules/gateways/airwallex/public/index.php?c=Awx_Embedded_Fields_Controller&m=get_paymentmethod', [ 
    'client_id'         => $clientId,
    'api_key'           => $apiKey,
    'test_mode'         => $testMode,
    'paymentmethod_id'  => $paymentMethodId,
] );

$response = json_decode( $res, TRUE );

$success = isset($response['payment_method']) ? $response['payment_method'] : '';

if ( ! empty( $success ) )
{
    $cardLastFour   = $response[ 'payment_method' ][ 'card' ][ 'last4' ];
    $cardType       = $response[ 'payment_method' ][ 'card' ][ 'brand' ];
    $cardExpiryDate = $response[ 'payment_method' ][ 'card' ][ 'expiry_month' ] . substr( $response[ 'payment_method' ][ 'card' ][ 'expiry_year' ], -2 );
}

$payMethodId = isset($_REQUEST['custom_reference']) ? (int) $_REQUEST['custom_reference'] : 0;


if ($action == 'payment') {
    if ($success) {
        // Validate invoice id received is valid.
        $invoiceId = checkCbInvoiceID($invoiceId, $gatewayParams['paymentmethod']);

        // Log to gateway log as successful.
        logTransaction($gatewayParams['paymentmethod'], $_REQUEST, "Success");

        // Create a pay method for the newly created remote token.
        invoiceSaveRemoteCard( $invoiceId, $cardLastFour, $cardType, $cardExpiryDate, $cardToken );

        // Apply payment to the invoice.
        addInvoicePayment($invoiceId, $transactionId, $amount, $fees, $gatewayModuleName);

        // Redirect to the invoice with payment successful notice.
        callback3DSecureRedirect($invoiceId, true);
    } else {
        // Log to gateway log as failed.
        logTransaction($gatewayParams['paymentmethod'], $_REQUEST, "Failed");

        sendMessage('Credit Card Payment Failed', $invoiceId);

        // Redirect to the invoice with payment failed notice.
        callback3DSecureRedirect($invoiceId, false);
    }
}

if ($action == 'create') {
    if ($success) {
        try {
            // Function available in WHMCS 7.9 and later
            createCardPayMethod(
                $customerId,
                $gatewayModuleName,
                $cardLastFour,
                $cardExpiryDate,
                $cardType,
                null, //start date
                null, //issue number
                $cardToken
            );

            // Log to gateway log as successful.
            logTransaction($gatewayParams['paymentmethod'], $_REQUEST, 'Create Success');

            // Show success message.
            echo 'Create successful.';
        } catch (Exception $e) {
            // Log to gateway log as unsuccessful.
            logTransaction($gatewayParams['paymentmethod'], $_REQUEST, $e->getMessage());

            // Show failure message.
            echo 'Create failed. Please try again.';
        }
    } else {
        // Log to gateway log as unsuccessful.
        logTransaction($gatewayParams['paymentmethod'], $_REQUEST, 'Create Failed');

        // Show failure message.
        echo 'Create failed. Please try again.';
    }
}

if ($action == 'update') {
    if ($success) {
        try {
            // Function available in WHMCS 7.9 and later
            updateCardPayMethod(
                $customerId,
                $payMethodId,
                $cardExpiryDate,
                null, // card start date
                null, // card issue number
                $cardToken
            );

            // Log to gateway log as successful.
            logTransaction($gatewayParams['paymentmethod'], $_REQUEST, 'Update Success');

            // Show success message.
            echo 'Update successful.';
        } catch (Exception $e) {
            // Log to gateway log as unsuccessful.
            logTransaction($gatewayParams['paymentmethod'], $_REQUEST, $e->getMessage());

            // Show failure message.
            echo 'Update failed. Please try again.';
        }
    } else {
        // Log to gateway log as unsuccessful.
        logTransaction($gatewayParams['paymentmethod'], $_REQUEST, 'Update Failed');

        // Show failure message.
        echo 'Update failed. Please try again.';
    }
}
