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
        add_shortcode('bookeasy_horizontal_search', array($this, 'horizontal_search'));
        add_shortcode('bookeasy_single', array($this, 'single'));
        add_shortcode('bookeasy_results', array($this, 'results'));

        add_shortcode('bookeasy_packages', array($this, 'packages'));
        add_shortcode('bookeasy_package_details', array($this, 'package_details'));

        add_shortcode('bookeasy_tour_results', array($this, 'tour_results'));
        add_shortcode('bookeasy_cart', array($this, 'cart'));
        add_shortcode('bookeasy_book', array($this, 'book'));
        add_shortcode('bookeasy_rooms', array($this, 'rooms'));
        add_shortcode('bookeasy_confirm', array($this, 'confirm'));
        add_shortcode('bookeasy_script', array($this, 'script'));

        //#todo need to remove this one day when we make logical changes to tour plugin 
        add_shortcode('bookeasy_platinum_partners', array($this, 'platinum_partners'));
    }

    /**
     * Script
     * @return String 
     */
    public function script(){

        if(!$this->scriptIncluded){
            $this->scriptIncluded = true;
            return '<script type="text/javascript" src="//gadgets.impartmedia.com/gadgets.jsz?key='.$this->api_key().'"></script>';
        } 
        
        return '';
    }

    /** 
     * Shortcodes 
     *
     * @return String 
     */
    public function horizontal_search($atts){
        $this->load();

        if(empty($atts)){
            $atts = array();
        }
        
        $defaults = array(
            'tours' => true, 
            'accom' => true, 
        );

        $data = array_merge($defaults, $this->options, $atts);

        $return = '';
        $return .= $this->script();
        $return .= Template::get('templates/horizontal-search', $data);

        return $return;
    }    

    /**
     * Return rooms template
     * @param  array  $atts 
     * @return String       
     */
    public function rooms($atts = array()){
        $this->load();
        
        $defaults = array(
            'type' => 'accom',
            'operator_id' => 0,
        );

        $data = array_merge($defaults, $this->options, $atts);


        $return = '';
        $return .= $this->script();
        $return .= Template::get('templates/rooms', $data);

        return $return;
    }

    /**
     * Display the single member 
     * 
     * @param  array  $atts 
     * @return String       
     */
    public function single($atts = array()){
        $this->load();
        $atts = !empty($atts) ? $atts : array();
        
        $defaults = array(
            'type' => 'accom',
            'operator_id' => 0,
            'specific_tours' => false,
        );

        $data = array_merge($defaults, $this->options, $atts);


        $return = '';
        $return .= $this->script();
        $return .= Template::get('templates/single', $data);

        return $return;
    }

    /**
     * Confirmation page
     * @param  array $atts
     * @return String       
     */
    public function confirm($atts = array()){
        $this->load();
        $atts = !empty($atts) ? $atts : array();


        $defaults = array(
            'pdf_link_text' => 'Download your itinerary PDF now.',
            'thank_you_text' => '',
        );

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
    public function results($atts = array()){
        $this->load();
        $atts = !empty($atts) ? $atts : array();

        $defaults = array(
            'period' => '3',
            'adults' => '2',
            'force_accom_type' => false,
            'platinum_partners_limit' => false,
            'default_region_loc' => false,
            'google_maps_api' => get_option('maps_api_key', ''),
        );

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
    public function packages($atts = array()){

        $this->load();
        $atts = !empty($atts) ? $atts : array();

        $defaults = array(
            'package_url' => '/package',
        );


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
    public function package_details($atts = array()){
        $this->load();
        $atts = !empty($atts) ? $atts : array();

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
    public function tour_results($atts = array()){
        $this->load();
        $atts = !empty($atts) ? $atts : array();

        $defaults = array(
            'period' => '1',
            'adults' => '1',
            'force_tour_type' => false,
            'default_region_loc' => false,
            'google_maps_api' => get_field('maps_api_key', 'option'),
        );

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
    public function platinum_partners($atts = array()){
        $this->load();

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
    public function hide_operators($atts = array()){
        $this->load();

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
    public function book($atts = array()){
        $this->load();
        $atts = !empty($atts) ? $atts : array();

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
    public function cart($atts = array()){
        $this->load();

        if(empty($atts)){
            $atts = array();
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


