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
define('BOOKEASY_ENDPOINT', 'http://sjp.impartmedia.com');
define('BOOKEASY_OPERATORINFO', '/be/getOperatorsInformation?q=[vc_id]');
define('BOOKEASY_OPERATORDETAILSSHORT', '/be/getOperatorsDetailsShort?q=[vc_id]&operators=[operators_id]');
define('BOOKEASY_ACCOMROOMSDETAILS', '/be/getAccomRoomsDetails?q=[vc_id]&operators=[operators_id]');
define('BOOKEASY_OPERATORDETAILSSHORT_ALL', '/be/getOperatorsDetailsShort?q=[vc_id]');
define('BOOKEASY_ACCOMROOMSDETAILS_ALL', '/be/getAccomRoomsDetails?q=[vc_id]');
define('BOOKEASY_MODDATES', '/be/getOperatorModDates?q=[vc_id]');

require_once 'includes/base.php';

// library pages
require_once 'library/request.php';
require_once 'library/template.php';

// options pages
require_once 'admin/settings.php';

//api
require_once 'api/import.php';

//front end stuff
require_once 'frontend/shortcodes.php';

//register_activation_hook( __FILE__, 'bookeasyoperators_activation');
/**
 * On activation, set a time, frequency and name of an action hook to be scheduled.
 */

function bookeasy_activation() {
    //wp_schedule_event( time(), 'daily', 'bookeasyoperators_daily_event_hook');
}


/**
 * Proper way to enqueue scripts and styles
 */
function bookeasy_scripts() {
    //wp_enqueue_style( 'bookeasy-frontend', get_stylesheet_uri() );
    wp_enqueue_script( 'bookeasy-frontend', plugins_url('js/frontend.js', __FILE__), array(), '1.0.0', true );
}

add_action( 'wp_enqueue_scripts', 'bookeasy_scripts' );