<?php


class Bookeasy_ShortCodes extends Bookeasy {

    public $scriptIncluded = false;

    /**
     * Start up
     */
    public function __construct(){
        add_shortcode('bookeasy_horizontal_search', array($this, 'horizontal_search'));
        add_shortcode('bookeasy_single', array($this, 'single'));
        add_shortcode('bookeasy_results', array($this, 'results'));
        add_shortcode('bookeasy_tour_results', array($this, 'tour_results'));
        add_shortcode('bookeasy_cart', array($this, 'cart'));
        add_shortcode('bookeasy_book', array($this, 'book'));
        add_shortcode('bookeasy_rooms', array($this, 'rooms'));
        add_shortcode('bookeasy_confirm', array($this, 'confirm'));
    }


    public function script(){

        if(!$this->scriptIncluded){
            $this->scriptIncluded = true;
            $return = '';
            $return .= '<script type="text/javascript" src="//gadgets.impartmedia.com/gadgets.jsz?key='.$this->api_key().'"></script>';
            return $return;
        } 
        
        return '';
    }

    /** 
     * shortcodes 
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
        $return .= BookEasy_Template::get('templates/horizontal-search', $data);

        return $return;
    }    

    public function rooms($atts = array()){
        $this->load();
        
        $defaults = array(
            'type' => 'accom',
            'operatorID' => 0,
        );

        $data = array_merge($defaults, $this->options, $atts);


        $return = '';
        $return .= $this->script();
        $return .= BookEasy_Template::get('templates/rooms', $data);

        return $return;
    }

    public function single($atts = array()){
        $this->load();
        
        $defaults = array(
            'type' => 'accom',
            'operatorID' => 0,
        );

        $data = array_merge($defaults, $this->options, $atts);


        $return = '';
        $return .= $this->script();
        $return .= BookEasy_Template::get('templates/single', $data);

        return $return;
    }


    public function confirm($atts){
        $this->load();

        $return = '';
        $return .= $this->script();
        $return .= BookEasy_Template::get('templates/confirm', $this->options);

        return $return;
    } 


    public function results($atts){
        $this->load();

        $return = '';
        $return .= $this->script();
        $return .= BookEasy_Template::get('templates/results', $this->options);

        return $return;
    }   


    public function tour_results($atts){
        $this->load();

        $return = '';
        $return .= $this->script();
        $return .= BookEasy_Template::get('templates/tour-results', $this->options);

        return $return;
       
    }    

    public function book($atts){
        $this->load();

        $return = '';
        $return .= $this->script();
        $return .= BookEasy_Template::get('templates/book', $this->options);

        return $return;
    }

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
        $return .= BookEasy_Template::get('templates/cart', $data);

        return $return;
    }



}

new Bookeasy_ShortCodes();

