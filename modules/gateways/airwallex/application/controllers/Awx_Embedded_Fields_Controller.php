<?php defined('BASEPATH') OR exit('No direct script access allowed');

if ( ! class_exists( 'Awx_Controller', FALSE ) )
{
    require_once( APPPATH . 'controllers/Awx_Controller.php' );
}

class Awx_Embedded_Fields_Controller extends Awx_Controller
{
    /**
     * Constructor
     *
     * @access public
     */
    public function __construct()
    {
        parent::__construct();
    }

    // --------------------------------------------------------------------

    /**
     * Get payment method.
     */
    public function get_paymentmethod()
    {
        $client_id          = $this->input->post( 'client_id', TRUE );
        $api_key            = $this->input->post( 'api_key', TRUE );
        $payment_method_id  = $this->input->post( 'paymentmethod_id', TRUE );
        $test_mode          = $this->input->post( 'test_mode', TRUE );

        $this->set_test_mode( $test_mode );

        // Token
        $token = $this->get_api_token( $client_id, $api_key );

        if ( FALSE === $token )
        {
            exit();
        }

        $this->json_response( [
            'payment_method' => $this->get_payment_method( $token, $payment_method_id )
        ] );

        return TRUE;
    }

    // --------------------------------------------------------------------

    /**
     * Get intent.
     */
    public function get_intent()
    {
        $client_id      = $this->input->post( 'client_id', TRUE );
        $api_key        = $this->input->post( 'api_key', TRUE );
        $test_mode      = $this->input->post( 'test_mode', TRUE );

        $this->set_test_mode( $test_mode );

        // Token
        $token = $this->get_api_token( $client_id, $api_key );

        if ( FALSE === $token )
        {
            exit();
        }

        // Params
        $params[ 'action' ]         = $this->input->post( 'action', TRUE );
        $params[ 'product_id' ]     = $this->input->post( 'product_id', TRUE );
        $params[ 'product_name' ]   = $this->input->post( 'product_name', TRUE );
        $params[ 'amount' ]         = $this->input->post( 'amount', TRUE );
        $params[ 'currency' ]       = $this->input->post( 'currency', TRUE );
        $params[ 'customer_id' ]    = $this->input->post( 'customer_id', TRUE );
        $params[ 'test_mode' ]      = $this->input->post( 'test_mode', TRUE );

        $params[ 'invoice_id' ]         = $this->input->post( 'invoice_id', TRUE );
        $params[ 'card_type' ]          = '';
        $params[ 'card_last_four' ]     = '';
        $params[ 'card_token' ]         = '';
        $params[ 'card_expiry_date' ]   = '1223';

        $params[ 'fees' ]       = $this->input->post( 'fees', TRUE );
        $params[ 'transaction_id' ] = $this->input->post( 'transaction_id', TRUE );

        // Create intent
        $order = [
            'request_id'        => random_string(),
            'amount'            => $params[ 'amount' ],
            'currency'          => strtoupper( $params[ 'currency' ] ),
            'merchant_order_id' => $params[ 'invoice_id' ],
            'order'             => [
                'products' => [
                    [
                        'code' => $params[ 'product_id' ],
                        'sku'  => $params[ 'product_id' ],
                        'name' => $params[ 'product_name' ],
                        'desc' => '',
                        'quantity' => 1,
                        'unit_price' => $params[ 'amount' ],
                        'type' => 'subscription'
                    ]
                ],
                'type' => 'digital_goods'
            ],
            'referrer_data' => [
                'type'      => 'whmcs',
                'version'   => '1.0'
            ],
        ];

        $customer = $this->get_customer( $token, $params[ 'customer_id' ] );

        if ( ! isset( $customer[ 'items' ][ 0 ] )  )
        {
            $customer = $this->create_customer( $token, [
                'request_id'            => random_string(),
                'merchant_customer_id'  => $params[ 'customer_id' ],
            ] );
        }
        else
        {
            $customer = $customer[ 'items' ][ 0 ];
        }

        $order[ 'customer_id' ] = $customer[ 'id' ];
    
        $this->json_response( [
            'intent' => $this->get_secret( $token, $order ),
            'params' => $params,
            'customer_awx_id' => $customer[ 'id' ]
        ] );

        return TRUE;
    }

    // --------------------------------------------------------------------

    /**
     * Checkout Page.
     */
    public function embedded_fields()
    {
        $action       =  $this->input->post( 'followup', TRUE );
        if ( $action != 'payment' )
        {
            exit( 'Add new card with this payment method is not supported currently.' );
        }

        $this->vars[ 'intent_id' ]      = $this->input->post( 'intent_id', TRUE );
        $this->vars[ 'client_secret' ]  = $this->input->post( 'client_secret', TRUE );
        $this->vars[ 'params' ]         = [
            'action'            =>  $this->input->post( 'followup', TRUE ),
            'product_id'        =>  $this->input->post( 'product_id', TRUE ),
            'description'       =>  $this->input->post( 'description', TRUE ),
            'product_name'      =>  $this->input->post( 'product_name', TRUE ),
            'amount'            =>  $this->input->post( 'amount', TRUE ),   
            'currency'          =>  $this->input->post( 'currency', TRUE ), 
            'customer_id'       =>  $this->input->post( 'customer_id', TRUE ),
            'customer_awx_id'   =>  $this->input->post( 'customer_awx_id', TRUE ),
            'test_mode'         =>  $this->input->post( 'test_mode', TRUE ), 
            'invoice_id'        =>  $this->input->post( 'invoice_id', TRUE ), 
            'card_token'        =>  '',
            'paymentmethod_id'  =>  '',
            'transaction_id'    =>  '',
        ];

        $this->vars[ 'customer_awx_id' ]    = $this->input->post( 'customer_awx_id', TRUE );
        $this->vars[ 'return_url' ]         = $this->input->post( 'return_url', TRUE );

        $this->load->view( 'embedded_fields_checkout', $this->vars );
    }

    // --------------------------------------------------------------------

    /**
     * Do checkout.
     */
    public function do_checkout_embedded_fields()
    {
        if ( ! $this->input->is_ajax_request() )
        {
            show_error(404);
        }

        $client_id = $this->input->post( 'client-id', TRUE );
        $api_key = $this->input->post( 'api-key', TRUE );

        $token = $this->get_api_token( $client_id, $api_key );

        if ( FALSE === $token )
        {
            $this->json_response( [ 'result' => 0, 'msg' => [
                'token' => 'Invalid Client ID or API Key'
            ] ] );
            return FALSE;
        }

        $order = [
            'request_id'        => random_string(),
            'amount'            => '80.05',
            'currency'          => 'USD',
            'merchant_order_id' => random_string( 'alnum', 32 ),
            'order' => [
                'products' => [
                    [
                    'code' => random_string(),
                    'sku'  => random_string(),
                    'name' => 'iPhone XR',
                    'desc' => '64 GB White',
                    'quantity' => 1,
                    'unit_price' => 850,
                    'type' => 'physical'
                    ],
                    [
                    'code' => random_string(),
                    'sku'  => random_string(),
                    'name' => 'Shipping',
                    'desc' => 'Ship to the US',
                    'quantity' => 1,
                    'unit_price' => 10,
                    'type' => 'shipping'
                    ],
                ],
                'shipping' => [
                    'first_name' => 'Steve',
                    'last_name'  => 'Gates',
                    'phone_number' => '+187631283',
                    'shipping_method' => 'DEFINED by YOUR WEBSITE',
                    'address' => [
                        'country_code' => "US",
                        'state' => "AK",
                        'city' => "Akhiok",
                        'street' => "Street No. 4",
                        'postcode' => "99654"
                    ]
                ]
            ]
        ];

        if ( TRUE )
        {
            $customer = $this->create_customer( $token, [
                'request_id' => random_string(),
                'merchant_customer_id' => random_string(),
                'first_name' => 'Steve',
                'last_name' => 'Gates',
            ] );

            $order[ 'customer_id' ] = $customer[ 'id' ];
        }

        $token = $this->get_api_token( $this->vars[ 'client_id' ], $this->vars[ 'api_key' ] );

        if ( ! empty( $token ) )
        {
            $this->vars[ 'customer' ] = $this->get_customer( $token, $this->vars[ 'customer_id' ] );
        }

        $intent = $this->get_secret( $token, $order );
    
        $this->json_response( [ 'result' => 1, 'intent' => $intent, 'customer' => $customer  ] );

        return TRUE;
    }

    // --------------------------------------------------------------------

    /**
     * Save cards Page.
     */
    public function embedded_fields_save_cards()
    {
        $this->vars[ 'client_id' ]      = $this->input->get( 'c', TRUE );
        $this->vars[ 'api_key' ]        = $this->input->get( 'k', TRUE );
        $this->vars[ 'customer_id' ]    = $this->input->get( 'cu', TRUE );


        $this->load->view( 'embedded_fields_save_cards', $this->vars );
    }

    // --------------------------------------------------------------------

    /**
     * Do checkout.
     */
    public function do_save_cards_embedded_fields()
    {
        // 1. Back-end validation
        if ( ! $this->input->is_ajax_request() )
        {
            show_error(404);
        }


        // 2. Get an access token
        $client_id  = $this->input->post( 'client_id', TRUE );
        $api_key    = $this->input->post( 'api_key', TRUE );

        $token      = $this->get_api_token( $client_id, $api_key );

        if ( FALSE === $token )
        {
            $this->json_response( [ 'result' => 0, 'msg' => [
                'token' => 'Invalid Client ID or API Key'
            ] ] );
            return FALSE;
        }


        // 3. Fetch current Customer
        $customer_id = $this->input->post( 'customer-id', TRUE );

        if ( ! empty( $customer_id ) )
        {
            $customer = $this->get_customer( $token, $customer_id );

            if ( empty( $customer ) )
            {
                $error_msg = [
                    'client_id'   => 'Invalid client Id',
                ];
                $this->json_response( [ 'result' => 0, 'msg' => $error_msg ] );
                return FALSE;
            }

            $client_secret = $this->generate_customer_client_secret( $token, $customer_id );

            if ( empty( $client_secret ) )
            {
                $error_msg = [
                    'client_id'   => 'Invalid client Id',
                ];
                $this->json_response( [ 'result' => 0, 'msg' => $error_msg ] );
                return FALSE;
            }

            $customer[ 'client_secret' ] = $client_secret[ 'client_secret' ]; 

            $this->json_response( [ 'result' => 1, 'customer' => $customer  ] );
        }

        return TRUE;
    }

    // --------------------------------------------------------------------

    /**
     * Do charge fees.
     */
    public function do_charge_fees()
    {
        // 1. Get an access token
        $client_id  = $this->input->post( 'client_id', TRUE );
        $api_key    = $this->input->post( 'api_key', TRUE );
        $test_mode  = $this->input->post( 'test_mode', TRUE );

        $this->set_test_mode( $test_mode );

        $token      = $this->get_api_token( $client_id, $api_key );

        $consent_id     = $this->input->post( 'consent_id', TRUE );
        $customer_id    = $this->input->post( 'customer_id', TRUE );

        $invoice_id     = $this->input->post( 'invoice_id', TRUE );
        $amount     = $this->input->post( 'amount', TRUE );
        $currency     = $this->input->post( 'currency', TRUE );

        $intent = [
            'request_id'        => random_string( 'alnum', 32 ),
            'amount'            => $amount,
            'currency'          => $currency,
            'merchant_order_id' => $invoice_id,
            'customer_id'       => $customer_id
        ];

        $consent = [
            'request_id'        => random_string( 'alnum', 32 ),
            'customer_id'       => $customer_id,
            'payment_consent_reference' => [
                'id' => $consent_id
            ],
        ];


        $this->json_response( $this->charge_fees( $token, $intent, $consent ));
    }

}
