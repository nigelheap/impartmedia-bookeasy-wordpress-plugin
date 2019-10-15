<?php
namespace Bookeasy\frontend;

use Bookeasy\api\Import as BookeasyImport;
use Bookeasy\Base;
use Bookeasy\library\Request;

class Helpers extends Base {

    public static $_instance;

    public function __construct()
    {
        add_action("wp_ajax_refresh_operator", array($this, 'refresh_operator'));
        add_action("wp_ajax_nopriv_refresh_operator", array($this, 'refresh_operator'));
    }

    /**
     *
     */
    public function refresh_operator()
    {
        $operators = isset($_REQUEST['operators']) ? explode(',', $_REQUEST['operators']) : false;
        $post_id = Request::req('post_id', false);
        $result = [];

        if(!empty($operators) && is_array($operators)){
            $sync = new BookeasyImport();
            $result = $sync->sync($operators);
            $result = json_decode($result, true);
        }


        if(!empty($result)){
            echo json_encode($result);
            die();
        }

        echo json_encode(array(
            'updated' => 0,
            'created' => 0,
        ));
        die();
    }

    /**
     * @param $operatorId
     * @param $postId
     * @return void|boolean
     */
    public function loadRooms($operatorId, $postId)
    {

        if(empty($operatorId)){
            return false;
        }

        $url = BOOKEASY_ENDPOINT . BOOKEASY_ACCOMROOMSDETAILS;
        $url = str_replace('[vc_id]', $id, $url);
        $url = str_replace('[operators_id]', $operatorId, $url);


    }

    /**
     * @param $operatorId
     * @param $postId
     * @return bool|mixed
     */
    public function loadShortDetails($operatorId, $postId)
    {

        if(empty($operatorId)){
            return false;
        }

        $shortDetails = get_post_meta($postId, 'bookeasy_shortDetails');

        if(!empty($shortDetails)){
            return $shortDetails;
        }
        
        var_dump($shortDetails);


    }

    /**
     * @param $operatorId
     * @param $postId
     * @return mixed
     */
    public static function rooms($operatorId, $postId){
        self::loadInstance();
        return self::$_instance->loadRooms($operatorId, $postId);
    }

    /**
     * @param $operatorId
     * @param $postId
     * @return mixed
     */
    public static function shortDetails($operatorId, $postId){
        self::loadInstance();
        return self::$_instance->loadShortDetails($operatorId, $postId);
    }


    /**
     * @return Helpers
     */
    public static function loadInstance(){
        if(!self::$_instance){
            self::$_instance = new self();
        }
        return self::$_instance;
    }


}


