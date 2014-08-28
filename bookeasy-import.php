<?php


class BookeasyOperators_Import{

    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;

    public $optionGroup = 'BookeasyOperators_options';
    public $postmetaPrefix = 'bookeasy';

    private $postFields = array(
        'post_title' => 'TradingName',
        'post_content' => 'Description',
    );

    /**
     * Start up
     */
    public function __construct(){

        //add_action( 'bookeasyoperators_daily_event_hook', array( $this, 'sync' ) );

        //returning for chaining
        return $this;
    }


    public function sync(){

        global $wpdb;

        $this->options = get_option($this->optionGroup);

        $url = $this->options['url'];
        $vc_id = $this->options['vc_id'];
        $postType = $this->options['posttype'];

        if(empty($url) || empty($vc_id) || empty($postType)){
            return 'Please set the url, vc_id and post type';
        }

        // create the url and fetch the stuff
        $url = str_replace('[vc_id]', $vc_id, $url);
        $json = file_get_contents($url);
        $arr = json_decode($json, true);

        //var_dump($arr);
    
        if(!isset($arr['Operators']) || !is_array($arr['Operators'])){
            return 'Url/Json Fail';
        }

        $create_count = 0;
        $update_count = 0;
        foreach($arr['Operators'] as $op){

            // check if it exists based on the operator id
            $postMeta_query = "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '{$this->postmetaPrefix}_OperatorID' AND meta_value = %d";
            $postMeta_postId = $wpdb->get_var($wpdb->prepare($postMeta_query, $op['OperatorID']));

            $post_id = $wpdb->get_var($wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE ID = %d", $postMeta_postId));

            if(empty($op[$this->postFields['post_title']])){
                continue;
            }

            $post = array(
              'post_content'   => $op[$this->postFields['post_content']],
              'post_title'     => $op[$this->postFields['post_title']], 
              'post_status'    => 'publish',
              'post_type'      => $postType,
            );  


            if(!empty($post_id)){
                $post = array_merge($post, array('ID' => $post_id));
            } 

            $inserted_id = wp_insert_post( $post );

            if( is_wp_error( $inserted_id ) ) {
                return $return->get_error_message();
            }

            foreach($op as $opKey => $opItem){
                if(in_array($opKey, $this->postFields)){
                    continue;
                }
                update_post_meta($inserted_id, $this->postmetaPrefix . '_' . $opKey, $opItem);
            }

            if(!empty($post_id)){
                $update_count++;
            } else {
                $create_count++;
            }
        }

        return 'Created:' . $create_count . ' Updated:'.$update_count. ' '; 

    }

}

new BookeasyOperators_Import();

