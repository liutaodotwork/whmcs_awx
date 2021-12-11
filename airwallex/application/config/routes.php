<?php defined('BASEPATH') OR exit('No direct script access allowed');

$route['embedded-fields'] = 'Awx_Embedded_Fields_Controller/embedded_fields';
$route['embedded-fields-checkout']['post'] = 'Awx_Embedded_Fields_Controller/do_checkout_embedded_fields';


$route['default_controller'] = 'Awx_Controller/index';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;
