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
     * Checkout Page.
     */
    public function index()
    {
    }

    // --------------------------------------------------------------------

    /**
     * Checkout Page.
     */
    public function embedded_fields()
    {
        $client_id      = $this->input->post( 'client_id', TRUE );
        $api_key        = $this->input->post( 'api_key', TRUE );
        $customer_id    = $this->input->post( 'customer_id', TRUE );


        $token = $this->get_api_token( $client_id, $api_key );

        if ( FALSE === $token )
        {
            exit();
        }

        // Params
        $params[ 'action' ] = 'payment';//$this->input->post( 'action', TRUE );
        $params[ 'amount' ] = '12.00';//$this->input->post( 'amount', TRUE );
        $params[ 'currency' ] = 'USD';//$this->input->post( 'currency', TRUE );

        $params[ 'invoice_id' ] = $this->input->post( 'invoice_id', TRUE );
        $params[ 'card_type' ] = 'Visa';
        $params[ 'card_last_four' ] = '';
        $params[ 'card_token' ] = '';
        $params[ 'card_expiry_date' ] = '1223';

        $params[ 'fees' ] = $this->input->post( 'fees', TRUE );
        $params[ 'success' ] = TRUE;//$this->input->post( 'fees', TRUE );
        $params[ 'transaction_id' ] = $this->input->post( 'transaction_id', TRUE );

        $params[ 'customer_id' ] = $this->input->post( 'customer_id', TRUE );

        $this->vars[ 'params' ] = $params;


        // Create intent
        $order = [
            'request_id'        => random_string(),
            'amount'            => '12.00',
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

        // TODO
        $awx_customer_id = 'cus_hkdmr5b88g4rwhlqbwj';
        $order[ 'customer_id' ] = $awx_customer_id;
        // if ( $customer = $this->get_customer( $token, $customer_id ) )
        // {
        //     $customer = $this->create_customer( $token, [
        //         'request_id' => random_string(),
        //         'merchant_customer_id' => $customer_id,
        //         'first_name' => 'Steve',
        //         'last_name' => 'Gates',
        //     ] );


        //     $order[ 'customer_id' ] = $customer[ 'id' ];
        // }

    
        $this->vars[ 'intent' ]     = $this->get_secret( $token, $order );
        $this->vars[ 'customer_id' ]   = $awx_customer_id;


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

    // --------------------------------------------------------------------

    /**
     * Checkout Page.
     */
    public function direct_api()
    {
        $this->vars[ 'client_id' ]  = $this->input->get( 'c', TRUE );
        $this->vars[ 'api_key' ]    = $this->input->get( 'k', TRUE );

        $this->load->view( 'direct_api_checkout', $this->vars );
    }

    // --------------------------------------------------------------------

    /**
     * Success Result.
     */
    public function success()
    {
        $client_id = $this->input->get( 'c', TRUE );
        $api_key = $this->input->get( 'k', TRUE );
        $intent_id = $this->input->get( 'id', TRUE );
        if ( empty( $intent_id ) OR  empty( $client_id ) OR empty( $api_key )  )
        {
            show_404();
        }

        $token = $this->get_api_token( $client_id, $api_key );

        if ( FALSE === $token )
        {
            show_404();
        }

        $intent = $this->get_payment_intent( $token, $intent_id );

        if ( FALSE === $intent )
        {
            show_404();
        }

        $this->vars[ 'intent' ] = $intent;
        $this->vars[ 'back_url' ] = '/embedded-fields-for-card-payments?c=' . $client_id . '&k=' . $api_key;

        $this->load->view( 'success', $this->vars );
    }

    // --------------------------------------------------------------------

    /**
     * Failure Result.
     */
    public function failure()
    {
        $this->load->view( 'failure', $this->vars );
    }

    // --------------------------------------------------------------------

    /**
     * 3DS Result.
     */
    public function three_ds_result( $res = 1 )
    {
        $res =  ! in_array( $res, [ 1, 0 ] ) ? 1 : $res;

        $result_uri = ( $res == 1 ) ? 'success' : 'failure';

        $cko_session_id = $this->input->get( 'cko-session-id', TRUE );
        if ( ! empty( $cko_session_id ) )
        {
            $result_uri .= '?cko-session-id=' . $cko_session_id;
        }

        $this->vars[ 'result_page' ] = site_url( $result_uri );

        $this->load->view( 'three_ds_result', $this->vars );
    }
}
