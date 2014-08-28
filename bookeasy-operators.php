<?php

/*
Plugin Name: Bookeasy Operators
Plugin URI: http://nigelheap.com/
Description: Bookeasy Operators
Version: 1.0
Author: Nigel Heap
Author URI: http://www.nigelheap.com
*/

require_once 'bookeasy-settings.php';
require_once 'bookeasy-import.php';

//register_activation_hook( __FILE__, 'bookeasyoperators_activation');
/**
 * On activation, set a time, frequency and name of an action hook to be scheduled.
 */

function bookeasyoperators_activation() {
    //wp_schedule_event( time(), 'daily', 'bookeasyoperators_daily_event_hook');
}