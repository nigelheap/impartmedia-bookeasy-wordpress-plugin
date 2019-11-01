<?php
namespace Bookeasy\frontend;

use Bookeasy\Base;
use Bookeasy\library\Template;

class ShortCodes extends Base {

    public $scriptIncluded = false;

    /**
     * Start up
     */
    public function __construct(){
        add_shortcode('bookeasy_horizontal_search', [$this, 'horizontal_search']);
        add_shortcode('bookeasy_single', [$this, 'single']);
        add_shortcode('bookeasy_results', [$this, 'results']);

        add_shortcode('bookeasy_packages', [$this, 'packages']);
        add_shortcode('bookeasy_package_details', [$this, 'package_details']);

        add_shortcode('bookeasy_tour_results', [$this, 'tour_results']);
        add_shortcode('bookeasy_cart', [$this, 'cart']);
        add_shortcode('bookeasy_book', [$this, 'book']);
        add_shortcode('bookeasy_confirm', [$this, 'confirm']);
        add_shortcode('bookeasy_script', [$this, 'script']);


        add_shortcode('bookeasy_field', [$this, 'field']);
        add_shortcode('bookeasy_content', [$this, 'content']);

        //#todo need to remove this one day when we make logical changes to tour plugin 
        add_shortcode('bookeasy_platinum_partners', [$this, 'platinum_partners']);
    }


    /**
     * @return string
     */
    public function content()
    {
        return get_post_field('post_content');
    }

    /**
     * @return string
     */
    public function field($atts)
    {
        if(empty($atts)){
            $atts = [];
        }

        $data = array_merge([
            'key' => false,
        ], $atts);

        if(empty($data['key'])){
            return '';
        }

        $key = $data['key'];
        $content = new Content();

        if($first = strpos($key, '.')){
            $drill = substr($key, $first);
            $key = substr($key, 0, $first);

        }

        $field = $content->get_field($key);

        if(isset($drill)){
            $field = $content->pull($field, $drill);
        }

        return $field;


    }

    /**
     * Script
     * @return String 
     */
    public function script(){


        if(!$this->scriptIncluded){
            
            $this->scriptIncluded = true;
            
            return Template::get('templates/_script', [
                'api_key' => $this->api_key(),
                'environment' => isset($this->options['environment']) ? $this->options['environment'] : 'live',
            ]);
        } 

        
        return '';
    }

    /** 
     * Shortcodes 
     *
     * @return String 
     */
    public function horizontal_search($atts)
    {
        $this->load();

        if(empty($atts)){
            $atts = [];
        }

        $defaults = [
            'tours' => true,
            'accom' => true,
            'accom_tabname' => 'Accommodation',
            'tours_tabname' => 'Tours',
            'tours_search_path' => '/tours/',
            'accom_search_path' => '/accommodation/',
        ];

        $data = array_merge($defaults, $this->options, $atts);

        $return = '';
        $return .= $this->script();
        $return .= Template::get('templates/horizontal-search', $data);

        return $return;
    }


    /**
     * Display the single member
     *
     * @param array $atts
     * @return String
     */
    public function single($atts = [])
    {

        $this->load();
        $atts = !empty($atts) ? $atts : [];

        $content = new Content();

        $defaults = [
            'type' => $content->operator_type(),
            'operator_id' => $content->operator_id(),
            'specific_tours' => false,
        ];

        $data = array_merge($defaults, $this->options, $atts);

        $return = '';
        $return .= $this->script();
        $return .= Template::get('templates/single', $data);

        return $return;
    }

    /**
     * Confirmation page
     * @param Array $atts
     * @return String
     */
    public function confirm($atts = [])
    {
        $this->load();

        $atts = !empty($atts) ? $atts : [];

        $defaults = [
            'pdf_link_text' => 'Download your itinerary PDF now.',
            'thank_you_text' => '',
        ];

        $data = array_merge($defaults, $this->options, $atts);

        $return = '';
        $return .= $this->script();
        $return .= Template::get('templates/confirm', $data);

        return $return;
    } 

    /**
     * List bookeasy results
     * @param  array $atts
     * @return String
     */
    public function results($atts = [])
    {
        $this->load();


        $atts = !empty($atts) ? $atts : [];

        $defaults = [
            'period' => '3',
            'adults' => '2',
            'force_accom_type' => false,
            'platinum_partners_limit' => false,
            'default_region_loc' => false,
            'google_maps_api' => get_option('maps_api_key', ''),
        ];

        if(!empty($atts['limit_locations'])){
            $atts['limit_locations'] = explode(',', $atts['limit_locations']);
        }

        $data = array_merge($defaults, $this->options, $atts);

        $return = '';
        $return .= $this->script();
        $return .= Template::get('templates/results', $data);

        return $return;
    }

    /**
     * List bookeasy package results
     * @param  array $atts
     * @return String
     */
    public function packages($atts = []){

        $this->load();
        $atts = !empty($atts) ? $atts : [];

        $defaults = [
            'package_url' => '/package',
        ];

        $data = array_merge($defaults, $this->options, $atts);

        $return = '';
        $return .= $this->script();
        $return .= Template::get('templates/packages', $data);

        return $return;
    }

    /**
     * Display the single package details
     *
     * @param  array  $atts
     * @return String
     */
    public function package_details($atts = []){
        $this->load();
        $atts = !empty($atts) ? $atts : [];

        $defaults = array(
            'product_id' => !empty($_GET['pid']) ?  $_GET['pid'] : 0,
        );

        $data = array_merge($defaults, $this->options, $atts);

        $return = '';
        $return .= $this->script();
        $return .= Template::get('templates/package-details', $data);

        return $return;
    }


    /**
     * Listing only the tour results
     * @param  array $atts
     * @return String
     */
    public function tour_results($atts = []){
        $this->load();
        $atts = !empty($atts) ? $atts : [];

        $defaults = [
            'period' => '1',
            'adults' => '1',
            'force_tour_type' => false,
            'default_region_loc' => false,
            'google_maps_api' => get_option('maps_api_key', ''),
        ];

        if(!empty($atts['limit_locations'])){
            $atts['limit_locations'] = explode(',', $atts['limit_locations']);
        }

        $data = array_merge($defaults, $this->options, $atts);

        $return = '';
        $return .= $this->script();
        $return .= Template::get('templates/tour-results', $data);

        return $return;
       
    }    

    /**
     * Platinum partners functionality
     * 
     * @param  array $atts
     * @return String       
     */
    public function platinum_partners($atts = [])
    {
        $this->load();

        $atts = !empty($atts) ? $atts : [];

        $data = array_merge($this->options, $atts);

        $return = '';
        $return .= $this->script();
        $return .= Template::get('templates/_platinum_partners', $data);

        return $return;
       
    }

    /**
     *  Hide Operators functionality
     *
     * @param  array $atts
     * @return String
     */
    public function hide_operators($atts = [])
    {
        $this->load();

        $atts = !empty($atts) ? $atts : [];

        $data = array_merge($this->options, $atts);

        $return = '';
        $return .= $this->script();
        $return .= Template::get('templates/_hide_operators', $data);

        return $return;

    }


    /**
     * Booking link
     * @param  array $atts
     * @return String
     */
    public function book($atts = [])
    {
        $this->load();
        $atts = !empty($atts) ? $atts : [];

        $defaults = array(
            'booked_by' => 'Online',
        );

        $data = array_merge($defaults, $this->options, $atts);

        $return = '';
        $return .= $this->script();
        $return .= Template::get('templates/book', $data);

        return $return;
    }


    /**
     * The cart page
     * @param  array  $atts
     * @return String
     */
    public function cart($atts = [])
    {
        $this->load();

        if(empty($atts)){
            $atts = [];
        }

        $defaults = array(
            'cart_id' => 'toolbar-cart',
        );

        $data = array_merge($defaults, $this->options, $atts);

        $return = '';
        $return .= $this->script();
        $return .= Template::get('templates/cart', $data);

        return $return;
    }
}


