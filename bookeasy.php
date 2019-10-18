<?php
namespace Bookeasy;

/*
Plugin Name: Bookeasy
Plugin URI: http://itomic.com.au/
Description: Bookeasy
Version: 1.0
Author: Nigel Heap
Author URI: http://www.nigelheap.com
*/


define('BOOKEASY_PLUGIN', 'bookeasy');
define('BOOKEASY_VERSION', '2.0.1');
define('BOOKEASY_ENDPOINT', 'https://webapi.bookeasy.com.au');
define('BOOKEASY_ENDPOINT_PVT', 'https://webapi-pvt.bookeasy.com.au');
define('BOOKEASY_VISIBLEOPERATORS', '/api/getVcOperatorIds?q=[vc_id]');
define('BOOKEASY_OPERATORINFO', '/api/getOperatorsInformation?q=[vc_id]');
define('BOOKEASY_OPERATORDETAILSSHORT', '/api/getOperatorsDetailsShort?q=[vc_id]&operators=[operators_id]');
define('BOOKEASY_ACCOMROOMSDETAILS', '/api/getAccomRoomsDetails?q=[vc_id]&operators=[operators_id]');
define('BOOKEASY_OPERATORDETAILSSHORT_ALL', '/api/getOperatorsDetailsShort?q=[vc_id]');
define('BOOKEASY_ACCOMROOMSDETAILS_ALL', '/api/getAccomRoomsDetails?q=[vc_id]');
define('BOOKEASY_MODDATES', '/api/getOperatorModDates?q=[vc_id]');
define('BOOKEASY_ACCOMRATES', '/api/getAccomRates?q=[vc_id]');
define('BOOKEASY_TOURRATES', '/api/getToursRates?q=[vc_id]');

define('BOOKEASY_URL', plugin_dir_url(__FILE__));

require_once dirname(__FILE__) .  '/includes/base.php';

// library pages
require_once dirname(__FILE__) .  '/library/request.php';
require_once dirname(__FILE__) .  '/library/template.php';

// options pages
require_once dirname(__FILE__) .  '/admin/settings.php';
//require_once dirname(__FILE__) .  '/admin/categories.php';

//api
require_once dirname(__FILE__) .  '/api/import.php';
require_once dirname(__FILE__) .  '/api/endpoint.php';

//front end stuff
require_once dirname(__FILE__) .  '/frontend/content.php';
require_once dirname(__FILE__) .  '/frontend/shortcodes.php';
require_once dirname(__FILE__) .  '/frontend/helpers.php';


new \Bookeasy\api\Import();
new \Bookeasy\api\Endpoint();

new \Bookeasy\frontend\ShortCodes();
new \Bookeasy\frontend\Helpers();

if( is_admin()){
    new \Bookeasy\admin\Settings();
    //new \Bookeasy\admin\Categories();
}


/******************
 * From here on it's only MR stuff
 ******************/
/*
if( function_exists('acf_add_options_page') ) {

    acf_add_options_sub_page(array(
        'page_title'    => 'Extra Options',
        'menu_title' 	=> 'Extra Options',
        'position'      => '31.5',
        'parent_slug'   => 'bookeasy'
    ));
}
*/

/**
 * Proper way to enqueue scripts and styles
 */


add_action( 'wp_enqueue_scripts', function() {

    wp_register_style(
        'bookeasy-app',
        plugins_url('css/app.css', __FILE__),
        array(),
        BOOKEASY_VERSION
    );

    wp_enqueue_style('bookeasy-app');


    wp_enqueue_script('bookeasy-frontend',
        plugins_url('js/frontend.js', __FILE__),
        array(),
        BOOKEASY_VERSION,
        true);

    wp_localize_script( 'bookeasy-frontend', 'bookeasyAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' )));



});

add_action( 'init', function() {

  
  register_taxonomy(
    'member-category',
    'members',
    array(
      'label' => __( 'Member Categories' ),
      'rewrite' => array( 'slug' => 'member-category' ),
      'hierarchical' => true,
      'show_in_rest' => true
    )
  );

  register_post_type( 'members',
    array(
      'labels' => array(
        'name' => __( 'Members' ),
        'singular_name' => __( 'Member' )
      ),
      'public' => true,
      'has_archive' => false,
      'taxonomies' => array('post_tag'),
      'show_in_rest' => true
    )
  );

});
