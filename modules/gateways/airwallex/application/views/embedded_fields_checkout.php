<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>Airwallex Checkout - Card Payment Acceptance by embedded fields</title>
        <!-- SEO Meta Tags-->
        <meta name="description" content="Checkout - Card Payment Acceptance">
        <!-- Mobile Specific Meta Tag-->
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
        <!-- Vendor Styles including: Bootstrap, Font Icons, Plugins, etc.-->
        <link rel="stylesheet" media="screen" href="<?= $asset_path ?>/css/vendor.min.css">
        <!-- Main Template Styles-->
        <link id="mainStyles" rel="stylesheet" media="screen" href="<?= $asset_path ?>/css/styles.min.css">
        <style type="text/css">
            #payment-form iframe {
                height: 46px !important
            }

            #payment-form #cardNumber iframe,#payment-form #expiry iframe,#payment-form #cvc iframe {
                -webkit-box-flex: 1;
                -ms-flex: 1 1 auto;
                flex: 1 1 auto;
                padding-left: 37px;
                padding-top: 8px;
                padding-right: 13px;

            }

            #payment-form .icon-container {
                transition: color .3s;
                background-color: transparent !important;
                color: #999;
                display: inline-block;
                position: absolute;
                top: 48%;
                margin-top: 2px;
                -webkit-transform: translateY(-50%);
                -ms-transform: translateY(-50%);
                transform: translateY(-50%);
                font-size: 1.1em;
                left: 30px
            }

            #payment-form .icon-container.awx-focus {
                color: #05f;
            }

            #payment-form [id$="-error"] {
                display: none
            }

            #payment-form iframe {
                border: 1px solid #e0e0e0 !important;
                border-radius: 5px !important;
                background-color: #fff !important;
                color: #505050 !important;
                font-family: "Rubik",Helvetica,Arial,sans-serif !important;
                font-size: 14px !important;
                height: 46px !important
            }

            #payment-form iframe.awx-focus {
                border-color: #05f !important;
                outline: none !important;
                background-color: rgba(0,85,255,0.02) !important;
                color: #505050 !important;
                box-shadow: none !important
            }


        </style>
        <!-- Modernizr-->
        <script src="<?= $asset_path ?>/js/modernizr.min.js"></script>
    </head>
    <!-- Body-->
    
    <body>
        <!-- Page Title-->
        <!-- Page Content-->
        <div class="container padding-bottom-3x mb-2 padding-top-1x">
            <form id="payment-form">
                <input type="hidden" name="amount" value="860">
                <div class="row">
                    <!-- Checkout Adress-->
                    <div class="col-xl-8 col-lg-7">

        <div class="accordion" id="accordion" role="tablist">

            <div class="card">
              <div class="card-header" role="tab">
                <h6><a class="" href="#newcard" data-toggle="collapse" aria-expanded="true">Make Payment</a></h6>
              </div>
              <div class="collapse show" id="newcard" data-parent="#accordion" role="tabpanel" style="">
                <div class="card-body">
                    <div class="text-center modal-spinner"><div class="spinner-border text-primary m-2" role="status"></div></div>
                    <div class="awx-fields" style="display:none;">
                        <p>We accept following cards:&nbsp;&nbsp;
                            <img class="d-inline-block align-middle" src="https://checkout.airwallex.com/static/media/visa.745a6485.svg" height="24" alt="Cerdit Cards">
                            <img class="d-inline-block align-middle" src="https://checkout.airwallex.com/static/media/mastercard.262f85fc.svg" height="24" alt="Cerdit Cards">
                        </p>
                        <p id="error-payment" class="text-primary mb-3"></p>
                        <div class="row">
                            <div class="form-group col-12">
                                <div class="icon-container">
                                    <i class="icon-credit-card"></i>
                                </div>
                                <div id="cardNumber"></div>
                            </div>
                            <div class="form-group col-6">
                                <div class="icon-container">
                                    <i class="icon-calendar"></i>
                                </div>
                                <div id="expiry"></div>
                            </div>
                            <div class="form-group col-6">
                                <div class="icon-container">
                                    <i class="icon-lock"></i>
                                </div>
                                <div id="cvc"></div>
                            </div>
                            
                            <div class="form-group col-12 text-center paddin-top-1x">
                                <div class="row">
                                    <div class="col-sm-3"></div>
                                    <div class="col-sm-6">
                                        <button id="pay-button" class="btn btn-primary btn-block" disabled type="button" data-action="modules/gateways/airwallex/public/index.php?c=Awx_Embedded_Fields_Controller&m=do_checkout_embedded_fields"><i class="icon-credit-card"></i> Submit Payment</button>
                                    </div>
                                    <div class="col-sm-3"></div>
                                  </div>
                            </div>

                        </div>
                    </div>
                </div>
              </div>
            </div>
          </div>

                    </div>
                    <!-- Sidebar -->
                    <div class="col-xl-4 col-lg-5 order-first order-md-last">
                        <aside class="sidebar">
                            <!-- Order Summary Widget-->
                            <section class="widget widget-order-summary widget-featured-products">
                                <h3 class="widget-title"><?= $params[ 'description' ] ?></h3>
                                <div class="entry">
                                    <div class="entry-content">
                                        <h4 class="entry-title"><?= $params[ 'product_name' ] ?></h4>
                                        <span class="entry-meta"></span>
                                        <span class="entry-meta text-gray-dark text-right"><?= $params[ 'currency' ] . ' ' . $params[ 'amount' ] ?> x 1</span>
                                    </div>
                                </div>
                                <table class="table">
                                    <tr>
                                        <td class="text-lg">Total</td>
                                        <td class="text-lg text-gray-dark"><?= $params[ 'currency' ] . ' ' . $params[ 'amount' ] ?></td>
                                    </tr>
                                </table>
                            </section>
                        </aside>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal fade" id="modal-failure" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog">
        <div class="modal-dialog<?= ! $is_mobile ? ' modal-dialog-centered' : '' ?>" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Payment Failed</h4>
                        <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                    </div>
                    <div class="modal-body">
                        <p class="mt-3">Your payment failed, but you can <b> try again with another card</b>.</p>
                        <div class="padding-top-1x text-center">
                        <button class="btn btn-primary" type="button" data-dismiss="modal"><i class="icon-credit-card"></i> Try Again</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div id="callback" style="display:none;">
            <form id="callbackform" action="<?= $return_url ?>" method="post">
<?php foreach ( $params as $k => $v ) { ?>
                <input type="hidden" name="<?= $k ?>" value="<?= $v ?>">
<?php } ?>
                <p><input type="submit" value="Continue"></p>
            </form>
        </div>

        <script src="<?= $asset_path ?>/js/vendor.min.js"></script>
        <script src="https://checkout.airwallex.com/assets/elements.bundle.min.js"></script>

        <script>
            $( document ).ready( function()
            {
                var card_is_completed = false;
                var expiry_is_completed = false;
                var cvc_is_completed = false;

                var button_text = $('#pay-button').html();

                try {

                    Airwallex.init({
                        env: '<?= ( $params[ 'test_mode' ] === 'on' ) ? 'demo' : 'prod' ?>',
                        origin: window.location.origin
                    });

                    const cardNumber = Airwallex.createElement('cardNumber', {
                        'placeholder': 'Card Number',
                        'autoCapture': true
                    });
                    const expiry = Airwallex.createElement('expiry', {
                        'placeholder': 'MM/YY'
                    });
                    const cvc = Airwallex.createElement('cvc', {
                        'placeholder': 'CVV'
                    });

                    cardNumber.mount('cardNumber');
                    expiry.mount('expiry');
                    cvc.mount('cvc');

                } catch (error) {

                }

                window.addEventListener('onReady', (event) => {
                    if( 'cvc' == event.detail.type)
                    {
                        $( '.awx-fields' ).show();
                        $( '.modal-spinner' ).remove();
                    }
                });

                window.addEventListener('onFocus', (event) => {
                    $('#' + event.detail.type + ' iframe').addClass('awx-focus');
                    $('#' + event.detail.type ).siblings('.icon-container').addClass('awx-focus');
                });

                window.addEventListener('onBlur', (event) => {
                   $('#' + event.detail.type + ' iframe').removeClass('awx-focus');
                   $('#' + event.detail.type ).siblings('.icon-container').removeClass('awx-focus');
                });

                window.addEventListener('onChange', (event) => {
                    if( 'cardNumber' == event.detail.type  ) card_is_completed = event.detail.complete;
                    if( 'expiry' == event.detail.type ) expiry_is_completed = event.detail.complete;
                    if( 'cvc' == event.detail.type ) cvc_is_completed = event.detail.complete;

                    $( '#pay-button' ).prop('disabled', !(card_is_completed && expiry_is_completed && cvc_is_completed));
                });

                $('#pay-button').click(function(){
                    submitPaymentForm();
                });
            });

            function submitPaymentForm()
            {
                $( '#pay-button' ).html('<div class="spinner-border spinner-border-sm text-white mr-2" role="status"></div>Processing...').prop('disabled', true);
                // Pay and save the card
                Airwallex.createPaymentConsent({
                    "intent_id": "<?= $intent_id ?>",
                    "client_secret": "<?= $client_secret ?>",
                    "element": Airwallex.getElement( "cardNumber" ),
                    "customer_id": "<?= $customer_awx_id ?>",
                    "currency": "<?= $params[ 'currency' ] ?>",
                    "next_triggered_by": "merchant",
                    "merchant_trigger_reason": "scheduled",
                    "requires_cvc": false,
                }).then((response) => {

                    retrieveIntent( response );

                }).catch((response) => {

                    showFailure();

                });
            }

            // Retrieve payment intent
            function retrieveIntent( response )
            {
                console.log( response );
                // Retrive the payment intent details
                $('input[name="paymentmethod_id"]').val( response.payment_method.id );
                $('input[name="card_token"]').val( response.payment_consent_id + '-|-' + response.customer_id );
                $('input[name="transaction_id"]').val( response.intent_id );
                $( '#callbackform' ).submit();
            }

            // Show error msg when it fails
            function showFailure()
            {
                var modal = $('#modal-failure');

                $(modal).modal('show');

                $('#pay-button').html('<i class="icon-credit-card"></i> Submit Payment').prop('disabled', false);
            }
        </script>
    </body>
</html>
