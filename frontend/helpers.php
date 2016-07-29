<?php

class Bookeasy_Helpers extends Bookeasy {

    public static $_instance;

    public function __construct(){
        add_action("wp_ajax_refresh_operator", array($this, 'refresh_operator'));
        add_action("wp_ajax_nopriv_refresh_operator", array($this, 'refresh_operator'));
    }

    public function refresh_operator(){
        $operators = isset($_REQUEST['operators']) ? explode(',', $_REQUEST['operators']) : false;

        if(!empty($operators) && is_array($operators)){
            $sync = new BookeasyOperators_Import();
            echo $sync->sync($operators);  
            die();
        }

        echo json_encode(array(
            'updated' => 0,
            'created' => 0,
        ));
        die();
    }

    public function loadRooms($operatorId, $postId){

        if(empty($operatorId)){
            return false;
        }

        $url = BOOKEASY_ENDPOINT . BOOKEASY_ACCOMROOMSDETAILS;
        $url = str_replace('[vc_id]', $id, $url);
        $url = str_replace('[operators_id]', $operatorId, $url);


    }

    public function loadShortDetails($operatorId, $postId){

        if(empty($operatorId)){
            return false;
        }

        $shortDetails = get_post_meta($postId, 'bookeasy_shortDetails');

        if(!empty($shortDetails)){
            return $shortDetails;
        }
        
        var_dump($shortDetails);


    }
    

    public static function rooms($operatorId, $postId){
        self::loadInstance();
        return self::$_instance->loadRooms($operatorId, $postId);
    }

    public static function shortDetails($operatorId, $postId){
        self::loadInstance();
        return self::$_instance->loadShortDetails($operatorId, $postId);
    }



    public static function loadInstance(){
        if(!self::$_instance){
            self::$_instance = new self();
        }
        return self::$_instance;
    }


}

new Bookeasy_Helpers();

