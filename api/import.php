<?php


class BookeasyOperators_Import extends Bookeasy{

    /**
     * Holds the values to be used in the fields callbacks
     */
    public $catOptions;
    public $catMapping;

    public $postmetaPrefix = 'bookeasy';

    private $postFields = array(
        'post_title' => 'TradingName',
        'post_content' => 'Description',
    );

    private $catTypes = array(
        'AccommodationType1', 
        'AccommodationType2', 
        'BusinessType1', 
        'BusinessType2',
        'BusinessType3',
        'BusinessType4',
        'SettingType1',
        'SettingType2',
        'Type1',
        'Type2',
        'Type3',
        'Type4',
    );

    /**
     * Start up
     */
    public function __construct(){
        //add_action( 'bookeasyoperators_daily_event_hook', array( $this, 'sync' ) );

        //returning for chaining
        return $this;
    }

    public function date(){

        global $wpdb;
        
        $this->options = get_option($this->optionGroup);
        $this->catMapping = get_option($this->optionGroupCategories);

        $id = $this->options['vc_id'];

        // Mod dates
        $url = BOOKEASY_ENDPOINT . BOOKEASY_MODDATES;
        $url = str_replace('[vc_id]', $id, $url);

        // create the url and fetch the stuff
        $json = file_get_contents($url);
        $arr = json_decode($json, true);

        $modDates = array();
        if(!isset($arr['Items']) || !is_array($arr['Items'])){
            return 'Url/Json Fail : Mod Dates';
        }

        foreach($arr['Items'] as $mod){
            $modDates[$mod['OperatorId']] = $mod;
        }


        if(!empty($modDates)){

            foreach($modDates as $op){
                foreach($op as $opKey => $opItem){
                    if(in_array($opKey, array('OperatorId'))){
                        continue;
                    }
                    $postDate = date('Y-m-d H:i:s', strtotime($modDates[$op['OperatorId']]['DetailsModDate']));
                    $post_id = $this->getPostId($op['OperatorId']);
                    if(!empty($post_id)){
                        $wpdb->query("UPDATE $wpdb->posts SET post_date='$postDate', post_date_gmt='$postDate' WHERE ID=".$post_id);
                    }
                }
            }

        }

        

    }

    /**
     * Syncing the operators with post type and category
     * @return [type] [description]
     */
    public function sync(){

        global $wpdb;
        
        $message = '';

        $this->options = get_option($this->optionGroup);
        $this->catMapping = get_option($this->optionGroupCategories);

        $id = $this->options['vc_id'];

        // Mod dates
        $url = BOOKEASY_ENDPOINT . BOOKEASY_MODDATES;
        $url = str_replace('[vc_id]', $id, $url);

        // create the url and fetch the stuff
        $json = file_get_contents($url);
        $arr = json_decode($json, true);

        $modDates = array();
        if(!isset($arr['Items']) || !is_array($arr['Items'])){
            return 'Url/Json Fail : Mod Dates';
        }

        foreach($arr['Items'] as $mod){
            $modDates[$mod['OperatorId']] = $mod;
        }
        
        //Operators info
        $url = BOOKEASY_ENDPOINT . BOOKEASY_OPERATORINFO;
        
        $postType = $this->options['posttype'];
        $category = $this->options['taxonomy'];

        if(empty($url) || empty($postType) || empty($id)){
            return 'Please set the url, vc_id, post type and taxonomy';
        }

        $url = str_replace('[vc_id]', $id, $url);

        // create the url and fetch the stuff
        $json = file_get_contents($url);
        $arr = json_decode($json, true);
    
        if(!isset($arr['Operators']) || !is_array($arr['Operators'])){
            return 'Url/Json Fail';
        }

        // Get the path to the upload directory.
        $wp_upload_dir = wp_upload_dir();

        $create_count = 0;
        $update_count = 0;
        $image_update_count = 0;
        foreach($arr['Operators'] as $op){

            $operatorId = $op['OperatorID'];

            $post_id = $this->getPostId($operatorId);

            if(empty($op[$this->postFields['post_title']])){
                continue;
            }

            $postTime = strtotime($modDates[$operatorId]['DetailsModDate']);

            if($postTime > time()){
                $postDate = date('Y-m-d H:i:s', strtotime('-1 Hour'));
            } else {
                $postDate = date('Y-m-d H:i:s', $postTime);
            }
            
            
            // Create the post array
            $post = array(
              'post_content'   => $op[$this->postFields['post_content']],
              'post_title'     => $op[$this->postFields['post_title']], 
              'post_type'      => $postType,
              'post_date'      => $postDate,
              'post_date_gmt'  => $postDate,
            );  

            // Does this operator id exist already?
            if(!empty($post_id)){
                $post = array_merge($post, array('ID' => $post_id));
                $currentModDates = array(
                    'ImagesModDate' => get_post_meta($post_id, $this->postmetaPrefix . '_' . 'ImagesModDate', true),
                    'DetailsModDate' => get_post_meta($post_id, $this->postmetaPrefix . '_' . 'DetailsModDate', true),
                    'CLinkModDate' => get_post_meta($post_id, $this->postmetaPrefix . '_' . 'CLinkModDate', true),
                );
                $post['post_status'] = $this->getPostStatus($post_id);
                
            } else {
                $post['post_status'] = 'publish';
                

            }
            
            if(empty($currentModDates['DetailsModDate']) || $modDates[$operatorId]['DetailsModDate'] != $currentModDates['DetailsModDate']){

                //ram this thing in the database.
                $inserted_id = wp_insert_post( $post );
            

                // something happed??
                if( is_wp_error( $inserted_id ) ) {
                    return $return->get_error_message();
                }

            } elseif(!empty($post_id)) {
                $inserted_id = $post_id;
            }
            

            /**
             * Process images
             */
            if(empty($currentModDates['ImagesModDate']) || $modDates[$operatorId]['ImagesModDate'] != $currentModDates['ImagesModDate']){
                
                if(PHP_SAPI == 'cli'){
                    echo 'Image Update PID ' . $inserted_id . " OID " . $operatorId .  "\n";
                }
                $message .= 'Image Update PID ' . $inserted_id . " OID " . $operatorId .  "\n";
                
                if(!empty($op['Pictures']) && is_array($op['Pictures']) && !empty($op['Pictures'])){

                    $currentItems = get_attached_media('image', $inserted_id);
                    
                    foreach ($currentItems as $currentItem) {
                        wp_delete_attachment($currentItem->ID);
                    }
                    
                    if(PHP_SAPI == 'cli'){
                        print_r($op['Pictures']);
                    }
                    
                    $message .= print_r($op['Pictures'], true);

                    $imageCount = 1;
                    foreach($op['Pictures'] as $path){
                        
                        $image_update_count++;

                        $dir = dirname($path);                        

                        $name = basename($path);
                        $dlname = str_replace(' ', '%20', $name);

                        $name = str_replace(' ', '', $name);

                        if(file_exists($wp_upload_dir['path'] .'/'.$name)){
                            unlink($wp_upload_dir['path'] .'/'.$name);
                        }
                        
                        $ch = curl_init('http:'.$dir . '/' . $dlname);
                        $fp = fopen($wp_upload_dir['path'] .'/'.$name, 'wb');
                        curl_setopt($ch, CURLOPT_FILE, $fp);
                        curl_setopt($ch, CURLOPT_HEADER, 0);
                        curl_exec($ch);
                        curl_close($ch);
                        fclose($fp);

                        // $filename should be the path to a file in the upload directory.
                        $filename = $wp_upload_dir['path'] .'/'.$name;

                        // Check the type of tile. We'll use this as the 'post_mime_type'.
                        $filetype = wp_check_filetype( basename( $filename ), null );

                        
                        // Prepare an array of post data for the attachment.
                        $attachment = array(
                            'guid'           => $wp_upload_dir['url'] . '/' . basename( $filename ), 
                            'post_mime_type' => $filetype['type'],
                            'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $filename ) ),
                            'post_content'   => '',
                            'post_status'    => 'inherit'
                        );

                        // Insert the attachment.
                        $attach_id = wp_insert_attachment( $attachment, $filename, $inserted_id );
                        //error_log('Created Attachement:'. $attach_id);
                        // Make sure that this file is included, as wp_generate_attachment_metadata() depends on it.
                        require_once( ABSPATH . 'wp-admin/includes/image.php' );

                        // Generate the metadata for the attachment, and update the database record.
                        $attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
                        wp_update_attachment_metadata( $attach_id, $attach_data );

                        if($imageCount == 1){
                            add_post_meta($inserted_id, '_thumbnail_id', $attach_id, true);
                        }
                        

                        $imageCount++;

                    }

                }

            }

            if(empty($currentModDates['DetailsModDate']) || $modDates[$operatorId]['DetailsModDate'] != $currentModDates['DetailsModDate']){

                if(!empty($post_id)){
                    $update_count++;
                    $message .= 'Content Update PID ' . $inserted_id . " OID " . $operatorId .  "\n";
                } else {
                    $create_count++;
                    $message .= 'Content Create PID ' . $inserted_id . " OID " . $operatorId .  "\n";
                }
                
                // Save the updated dates to the database
                $cats = array();
                foreach($op as $opKey => $opItem){
                    if(in_array($opKey, $this->postFields)){
                        continue;
                    }
    
                    $key = $opKey . '|' . $opItem;
                    if(isset($this->catMapping[$key]) && !empty($this->catMapping[$key])){
                        $cats[] = intval($this->catMapping[$key]);
                    }
    
                    update_post_meta($inserted_id, $this->postmetaPrefix . '_' . $opKey, $opItem);
                    
                }
    
                //set the cats if we need to
                if(!empty($cats)){
                    // post id, cats, ammend to current cats
                    wp_set_object_terms($inserted_id, $cats, $category, true);
                }

            }

        }


        if(!empty($modDates)){

            foreach($modDates as $op){
                foreach($op as $opKey => $opItem){
                    if(in_array($opKey, array('OperatorId'))){
                        continue;
                    }

                    $post_id = $this->getPostId($op['OperatorId']);
                    if(!empty($post_id)){
                        update_post_meta($post_id, $this->postmetaPrefix . '_' . $opKey, $opItem);
                    }
                }
            }

        }
        
        
        $message .= 'Created:' . $create_count . ' Updated:'.$update_count. ' Operator Images Updates:'.$image_update_count;
        
        //mail('', '', $message);
        
        //die();
        
        /**
         * Extra accom details
         */
        
        $url = BOOKEASY_ENDPOINT . BOOKEASY_OPERATORDETAILSSHORT_ALL;
        $url = str_replace('[vc_id]', $id, $url);

        // create the url and fetch the stuff
        $json = file_get_contents($url);
        $arr = json_decode($json, true);

        if(!empty($arr)){
            foreach($arr as $op){
                $post_id = $this->getPostId($op['OperatorId']);
                update_post_meta($post_id, $this->postmetaPrefix . '_ShortDetails', $op);
            }
        }

        /**
         * Room details
         */
        $url = BOOKEASY_ENDPOINT . BOOKEASY_ACCOMROOMSDETAILS_ALL;
        $url = str_replace('[vc_id]', $id, $url);

        // create the url and fetch the stuff
        $json = file_get_contents($url);
        $arr = json_decode($json, true);

        if(!empty($arr)){
            foreach($arr as $op){
                $post_id = $this->getPostId($op['OperatorId']);
                update_post_meta($post_id, $this->postmetaPrefix . '_RoomDetails', $op);
            }
        }

        $this->date();
        
        return 'Created:' . $create_count . ' Updated:'.$update_count. ' Operator Images Updates:'.$image_update_count; 

    }


    /**
     * Sync the categories from the json data.
     * @return String result for iframe
     */
    public function cats(){

        global $wpdb;

        $this->options = get_option($this->optionGroup);
        $this->catOptions = get_option($this->optionGroupCategoriesSync);

        $id = $this->options['vc_id'];
        $url = BOOKEASY_ENDPOINT . BOOKEASY_OPERATORINFO;

        $category = $this->options['taxonomy'];

        if(empty($url) || empty($category) || empty($id)){
            return 'Please set the url, vc_id, post type and taxonomy';
        }


        $url = str_replace('[vc_id]', $id, $url);
        // create the url and fetch the stuff
        $json = file_get_contents($url);
        $arr = json_decode($json, true);

        if(!isset($arr['Operators']) || !is_array($arr['Operators'])){
            return 'Url/Json Fail';
        }

        $types = array();
        foreach($arr['Operators'] as $op){

            // add the rest of the field in to post data
            foreach($op as $opKey => $opItem){
                if(in_array($opKey, $this->catTypes)){
                    $types[] = $opKey . '|' .$opItem;
                }
            }

        }

        $this->catOptions['bookeasy_cats'] = array_unique($types);
        update_option($this->optionGroupCategoriesSync, $this->catOptions);
        return count($this->catOptions['bookeasy_cats']) . ' Unique Categories <a href="' .admin_url('options.php?page=bookeasy&tab=categories') .'" target="_parent">Reload Page</a>'; 

    }


    /**
     * Helpers 
     */
    
    public function getPostId($operatorId){

        global $wpdb;

        // check if it exists based on the operator id
        $postMeta_query = "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '{$this->postmetaPrefix}_OperatorID' AND meta_value = %d";
        $postMeta_postId = $wpdb->get_var($wpdb->prepare($postMeta_query, $operatorId));

        return $wpdb->get_var($wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE ID = %d", $postMeta_postId));

    }

    public function getPostStatus($post_id){

        global $wpdb;

        // check if it exists based on the operator id
        $post_query = "SELECT post_status FROM $wpdb->posts WHERE ID = %d";
        return $wpdb->get_var($wpdb->prepare($post_query, $post_id));


    }

}

new BookeasyOperators_Import();

