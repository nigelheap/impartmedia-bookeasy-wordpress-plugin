<?php


class Bookeasy_Helpers extends Bookeasy {

    public static $_instance;

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

//new Bookeasy_Helpers();

