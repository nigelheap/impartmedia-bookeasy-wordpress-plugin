<?php

/*
Plugin Name: Bookeasy
Plugin URI: http://itomic.com.au/
Description: Bookeasy
Version: 1.0
Author: Nigel Heap
Author URI: http://www.nigelheap.com
*/
define('BOOKEASY_PLUGIN', 'bookeasy');
define('BOOKEASY_ENDPOINT', 'https://sjp.impartmedia.com');
define('BOOKEASY_VISIBLEOPERATORS', '/V1.1/be/getVcOperatorIds?q=[vc_id]');
define('BOOKEASY_OPERATORINFO', '/V1.1/be/getOperatorsInformation?q=[vc_id]');
define('BOOKEASY_OPERATORDETAILSSHORT', '/V1.1/be/getOperatorsDetailsShort?q=[vc_id]&operators=[operators_id]');
define('BOOKEASY_ACCOMROOMSDETAILS', '/V1.1/be/getAccomRoomsDetails?q=[vc_id]&operators=[operators_id]');
define('BOOKEASY_OPERATORDETAILSSHORT_ALL', '/V1.1/be/getOperatorsDetailsShort?q=[vc_id]');
define('BOOKEASY_ACCOMROOMSDETAILS_ALL', '/V1.1/be/getAccomRoomsDetails?q=[vc_id]');
define('BOOKEASY_MODDATES', '/V1.1/be/getOperatorModDates?q=[vc_id]');
define('BOOKEASY_ACCOMRATES', '/V1.1/be/getAccomRates?q=[vc_id]');

require_once 'includes/base.php';

// library pages
require_once 'library/request.php';
require_once 'library/template.php';

// options pages
require_once 'admin/settings.php';

//api
require_once 'api/import.php';
require_once 'api/endpoint.php';

//front end stuff
require_once 'frontend/shortcodes.php';
require_once 'frontend/helpers.php';

//register_activation_hook( __FILE__, 'bookeasyoperators_activation');
/**
 * On activation, set a time, frequency and name of an action hook to be scheduled.
 */
function bookeasy_activation() {
    //wp_schedule_event( time(), 'daily', 'bookeasyoperators_daily_event_hook');
}


if( function_exists('acf_add_options_page') ) {

    acf_add_options_sub_page(array(
        'page_title'    => 'Extra Options',
        'menu_title' 	=> 'Extra Options',
        'position'      => '31.5',
        'parent_slug'   => 'bookeasy'
    ));
}


/**
 * Proper way to enqueue scripts and styles
 */
function bookeasy_scripts() {
    
    wp_register_style(
        'bookeasy-responsive-style', 
        plugins_url('css/bookeasy-responsive.css', __FILE__),
        array(),
        '1.0.1'
    );

    wp_enqueue_style('bookeasy-responsive-style');


    //wp_enqueue_style( 'bookeasy-frontend', get_stylesheet_uri() );
    wp_enqueue_script('bookeasy-frontend', 
        plugins_url('js/frontend.js', __FILE__), 
        array(), 
        '1.0.1', 
        true);

    wp_localize_script( 'bookeasy-frontend', 'bookeasyAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' )));   

    wp_register_script( 
        'bookeasy-flexslider', 
        plugins_url('js/jquery.flexslider-min.js', __FILE__), 
        array(), 
        '1.0.0', 
        true);

    wp_enqueue_script('bookeasy-flexslider');

    wp_register_style(
        'bookeasy-flexslider-style', 
        plugins_url('css/flexslider.css', __FILE__)
    );
    wp_enqueue_style('bookeasy-flexslider-style');



}

add_action( 'wp_enqueue_scripts', 'bookeasy_scripts' );
