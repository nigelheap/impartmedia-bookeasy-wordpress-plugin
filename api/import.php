<?php


class BookeasyOperators_Import extends Bookeasy{

    /**
     * Holds the values to be used in the fields callbacks
     */
    public $catOptions;
    public $catMapping;
    public $visibleOperators;
    public $tz;
    public $tzUTC;

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
        $this->tz = new DateTimeZone(get_option('timezone_string'));
        $this->tzUTC = new DateTimeZone('UTC');

        //returning for chaining
        return $this;
    }

    public function loadVisibleOperator(){

        if(!empty($this->visibleOperators)){
            return;
        }

        $this->load();

        $id = $this->options['vc_id'];

        // Visible operators
        $url = BOOKEASY_ENDPOINT . BOOKEASY_VISIBLEOPERATORS;
        $url = str_replace('[vc_id]', $id, $url);

        // create the url and fetch the stuff
        $json = file_get_contents($url);
        $arr = json_decode($json, true);
        $this->visibleOperators = $arr['OperatorIds'];

    }

    public function date($onlySync = null){

        global $wpdb;

        $this->load();
        $this->loadVisibleOperator();

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

       

        $updated = 0;

        if(!empty($modDates)){

            foreach($modDates as $opId => $op){

                if(!is_null($onlySync) && !in_array($op['OperatorId'], $onlySync)){
                    continue;
                }

                $post_id = $this->getPostId($op['OperatorId']);

                if(!empty($post_id)){

                    list($post_date, $post_date_gmt) = $this->getDates($modDates[$op['OperatorId']]['DetailsModDate']);
                    
                    $wpdb->query("UPDATE $wpdb->posts 
                                  SET post_date='$post_date', 
                                  post_date_gmt='$post_date_gmt' 
                                  WHERE ID=".$post_id);

                    $updated++;
                    
                }
            }

        }


        return $updated;
        
    }

    /**
     * Get the 2 dates and if they are in the future, fix it
     * @param  String $date 
     * @return Array       
     */
    public function getDates($date){

        $compare = new DateTime('now', $this->tzUTC);
        $date = new DateTime($date);        

        if($date > $compare){
            $date = $compare->modify('-3 Hours');
        }

        $post_date_gmt = $date->format('Y-m-d H:i:s');
        $date->setTimezone($this->tz);
        $post_date = $date->format('Y-m-d H:i:s');


        return array($post_date, $post_date_gmt);
    }





    /**
     * Syncing the operators with post type and category
     * @return [type] [description]
     */
    public function sync($onlySync = null){

        $this->load();
        
        set_time_limit(1800);
        global $wpdb;
        
        //date_default_timezone_set(get_option('timezone_string'));

        $startedTime =  date('d/m/Y h:i:s a', time());
        
        $message = '';


        $this->options = get_option($this->optionGroup);
        $this->catMapping = get_option($this->optionGroupCategories);

        $id = $this->options['vc_id'];

        $this->loadVisibleOperator();

        $toLoad = null;

        if(!$onlySync){

            // Mod dates
            $url = BOOKEASY_ENDPOINT . BOOKEASY_MODDATES;
            $url = str_replace('[vc_id]', $id, $url);

            // create the url and fetch the stuff
            $json = file_get_contents($url);
            $arr = json_decode($json, true);

            $modDates = array();
            if(!isset($arr['Items']) || !is_array($arr['Items'])){
                return $this->sendFailed('Url/Json Fail : Mod Dates', $onlySync);
            }

            $toLoad = array();
            foreach($arr['Items'] as $mod){
                $post_id = $this->getPostId($mod['OperatorId']);

                if(!empty($post_id)){

                    $currentModDates = array(
                        'ImagesModDate' => get_post_meta($post_id, $this->postmetaPrefix . '_' . 'ImagesModDate', true),
                        'DetailsModDate' => get_post_meta($post_id, $this->postmetaPrefix . '_' . 'DetailsModDate', true),
                    );

                    $currentImages = $this->rawOptionValue($post_id, $this->postmetaPrefix . '_Pictures');

                    if(empty($currentModDates['DetailsModDate']) || $mod['DetailsModDate'] != $currentModDates['DetailsModDate']){
                        $toLoad[] = $mod['OperatorId'];
                    }

                    if(!empty($mod['ImagesModDate']) || empty($currentImages)){
                        if(empty($currentModDates['ImagesModDate']) || $mod['ImagesModDate'] != $currentModDates['ImagesModDate']){
                            $toLoad[] = $mod['OperatorId'];
                        }
                    }

                } else {
                    $toLoad[] = $mod['OperatorId'];
                }

                $modDates[$mod['OperatorId']] = $mod;
            }
        }

        //Operators info
        $url = BOOKEASY_ENDPOINT . BOOKEASY_OPERATORINFO;
        
        $postType = $this->options['posttype'];
        $category = $this->options['taxonomy'];

        if(empty($url) || empty($postType) || empty($id)){
            return $this->sendFailed('Please set the vc_id, post type and taxonomy', $onlySync);
        }

        $url = str_replace('[vc_id]', $id, $url);

        if($onlySync && is_array($onlySync)){
            $url .= '&operators='.implode(',', $onlySync);
        }


        if(!empty($toLoad)){
            $base = $url;
            $toLoad = array_unique($toLoad);
            $chunks = array_chunk($toLoad, 20);

            $arr = array();
            $arr['Operators'] = array();

            foreach($chunks as $chunk){
                $load = $base . '&operators=' . implode(',', $chunk);


                // create the url and fetch the stuff
                $json = file_get_contents($load);
                $result = json_decode($json, true);

                if(!isset($result['Operators']) || !is_array($result['Operators'])){
                    return $this->sendFailed('Url/Json Fail : Operators Chunk');
                } else {
                    $arr['Operators'] = array_merge($arr['Operators'], $result['Operators']);
                }
            }
            
        } else {
            // create the url and fetch the stuff
            $json = file_get_contents($url);
            $arr = json_decode($json, true);
        
            if(!isset($arr['Operators']) || !is_array($arr['Operators'])){
                return $this->sendFailed('Url/Json Fail : Operators', $onlySync);
            }
        }

        // Get the path to the upload directory.
        $wp_upload_dir = wp_upload_dir();

        $include_locations = array();
        $location_ids = trim($this->options['location_ids']);
        if(!empty($location_ids)){
            $include_locations = explode(',', $location_ids);
        }

        $create_count = 0;
        $update_count = 0;
        $image_update_count = 0;
        $operatorsUpdated = array();
        $operatorsCreated = array();


        foreach($arr['Operators'] as $op){

            $operatorId = $op['OperatorID'];

            $forced = (!is_null($onlySync) && in_array($operatorId, $onlySync));

            $post_id = $this->getPostId($operatorId);

            if(!empty($include_locations) && is_array($op['Locations']) && !empty($op['Locations'])){
               foreach($op['Locations'] as $location){
                   if(!in_array($location['LocationId'], $include_locations)){
                       if(!empty($post_id)){
                           $this->markDraft($post_id);
                       }
                       continue 2;
                   }
               }
            }

            if(empty($op[$this->postFields['post_title']])){
                continue;
            }

            list($post_date, $post_date_gmt) = $this->getDates($modDates[$operatorId]['DetailsModDate']);
            
            // Create the post array
            $post = array(
              'post_content'   => $op[$this->postFields['post_content']],
              'post_title'     => $op[$this->postFields['post_title']], 
              'post_type'      => $postType,
              'post_date'      => $post_date,
              'post_date_gmt'  => $post_date_gmt,
            );  

            // Does this operator id exist already?
            if(!empty($post_id)){

                $post = array_merge($post, array('ID' => $post_id));

                $currentModDates = array(
                    'ImagesModDate' => get_post_meta($post_id, $this->postmetaPrefix . '_' . 'ImagesModDate', true),
                    'DetailsModDate' => get_post_meta($post_id, $this->postmetaPrefix . '_' . 'DetailsModDate', true),
                    //'CLinkModDate' => get_post_meta($post_id, $this->postmetaPrefix . '_' . 'CLinkModDate', true),
                );

                $post['post_status'] = $this->getPostStatus($post_id);
                $pt = strtotime($post_date);
                if($post['post_status'] == 'future' && $pt <= time()){
                    $post['post_status'] = 'publish';
                }

            } else {
                $post['post_status'] = in_array(intval($operatorId), $this->visibleOperators) ? 'publish' : 'draft';
            }
            
            if(empty($currentModDates['DetailsModDate']) || 
                $modDates[$operatorId]['DetailsModDate'] != $currentModDates['DetailsModDate']){

                $inserted_id = wp_insert_post( $post );
                update_post_meta($inserted_id, $this->postmetaPrefix . '_OperatorID', $operatorId);
    
                // something happed??
                if( is_wp_error( $inserted_id ) ) {
                    return $return->get_error_message();
                }

            } elseif(!empty($post_id)) {
                $inserted_id = $post_id;
            }
            

            $currentImages = $this->rawOptionValue($inserted_id, $this->postmetaPrefix . '_Pictures');

            /**
             * Process images
             */
            
            if(!empty($modDates[$operatorId]['ImagesModDate']) || 
                (empty($currentImages) && !empty($op['Pictures'])) || 
                $forced
                ){

                if(empty($currentModDates['ImagesModDate']) || 
                    $modDates[$operatorId]['ImagesModDate'] != $currentModDates['ImagesModDate'] || 
                    $forced){
                    
                    if(!empty($op['Pictures']) && 
                        is_array($op['Pictures']) || $forced){

                            $currentItems = get_attached_media('image', $inserted_id);
                            
                            foreach ($currentItems as $currentItem) {
                                wp_delete_attachment($currentItem->ID);
                            }
                            
                            $imageCount = 1;
                            foreach($op['Pictures'] as $path){
                                //$imageCount++;
                                //continue;
                                
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
                                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                                curl_exec($ch);
                                curl_close($ch);
                                fclose($fp);
                                
                                
                                /*
                                $ch = curl_init();
                                $source = 'http:'.$dir . '/' . $dlname;
                                $destination = $wp_upload_dir['path'] .'/'.$name;
      
                                curl_setopt($ch, CURLOPT_URL, $source);
                                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                                $data = curl_exec ($ch);
                                curl_close ($ch);

                                $file = fopen($destination, "w+");
                                fputs($file, $data);
                                fclose($file);
                                */
                                

                                // $filename should be the path to a file in the upload directory.
                                $filename = $wp_upload_dir['path'] .'/'.$name;

                                // Check the type of file. We'll use this as the 'post_mime_type'.
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
                                $attach_id = wp_insert_attachment( 
                                    $attachment, 
                                    $filename, 
                                    $inserted_id
                                );
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
            }

            if(empty($currentModDates['DetailsModDate']) || 
                $modDates[$operatorId]['DetailsModDate'] != $currentModDates['DetailsModDate'] 
                || $forced){

                if(!empty($post_id)){
                    $update_count++;
                    $operatorsUpdated[] = $operatorId;
                } else {
                    $create_count++;
                    $operatorsCreated[] = $operatorId;
                }
                
                // Save the updated dates to the database
                $cats = array();
                foreach($op as $opKey => $opItem){
                    if(in_array($opKey, $this->postFields)){
                        continue;
                    }
                    
                    // $message .= print_r($opItem, true);
                    $key = $opKey . '|' . $opItem;
                     if(isset($this->catMapping[$key]) && !empty($this->catMapping[$key])){
                    //     $message .= print_r($key, true) . "\n\n";
                    //     $message .= $this->catMapping[$key] . "\n\n";
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

            // Cache refresh for operator
            if (function_exists('w3tc_pgcache_flush_post')){
                if (!empty($post_id) ) {
                    w3tc_pgcache_flush_post($post_id);
                }
            }
        }

        $this->modDates($modDates);

        $this->roomDetail($onlySync, $toLoad);
        $this->extraAccom($onlySync, $toLoad);
        $this->date($onlySync);
        
        $endTime =  date('d/m/Y h:i:s a', time());

        $message = array();
        $message[] = "The bookeasy sync for " . get_bloginfo('name' ) . " has finished".PHP_EOL;
        $message[] = "Created: " . $create_count;
        $message[] = "Updated: " . $update_count;
        $message[] = "Operator Images Updates: " . $image_update_count;
        $message[] = "Start Time: " . $startedTime;
        $message[] = "Operators Created: " . implode(',', $operatorsCreated); 
        $message[] = "Operators Updated: " . implode(',', $operatorsUpdated);   

        if(is_null($onlySync) && !empty($this->options['notificaton_email'])){
            $emails = $this->options['notificaton_email'];
            if(strstr($this->options['notificaton_email'], ',')){
                $emails = explode(',', $this->options['notificaton_email']);
            }

            wp_mail(
                $emails, 
                get_bloginfo('name' ) . ' Bookeasy sync finished', 
                implode(PHP_EOL, $message)
            );

        }

        $json = array();
        $json['created'] = $create_count;
        $json['updated'] = $update_count;
        $json['updated_images'] = $image_update_count;
        $json['start_time'] = $startedTime;
        $json['end_time'] = $endTime;

        if(PHP_SAPI == 'cli'){
            return implode(PHP_EOL, $json);
        } 

        return json_encode($json);

    }

    public function sendFailed($reason, $onlySync = null){
        $this->load();

        $message = array();
        $message[] = "The bookeasy sync for " . get_bloginfo('name' ) . " has failed".PHP_EOL;
        $message[] = "Reason: " . $reason;

        if(is_null($onlySync) && !empty($this->options['notificaton_email'])){
            $emails = $this->options['notificaton_email'];
            if(strstr($this->options['notificaton_email'], ',')){
                $emails = explode(',', $this->options['notificaton_email']);
            }

            wp_mail(
                $emails, 
                get_bloginfo('name' ) . ' Bookeasy sync finished', 
                implode(PHP_EOL, $message)
            );

        }

        return $reason;

    }

    private function modDates($modDates){

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

    }

    /**
     * Extra accom details
     */
    public function extraAccom($onlySync = null, $toLoad = null){

        $this->load();
        $id = $this->options['vc_id'];

        $url = BOOKEASY_ENDPOINT . BOOKEASY_OPERATORDETAILSSHORT_ALL;
        $url = str_replace('[vc_id]', $id, $url);

        if(!empty($toLoad)){
            $base = $url;
            $chunks = array_chunk($toLoad, 30);

            $arr = array();
            foreach($chunks as $chunk){
                $load = $base . '&operators=' . implode(',', $chunk);

                // create the url and fetch the stuff
                $json = file_get_contents($load);
                $result = json_decode($json, true);

                if(!isset($result) || !is_array($result)){
                    return $this->sendFailed('Url/Json Fail : Extra Accom Chunk');
                } else {
                    $arr = array_merge($arr, $result);
                }
            }
            
        } elseif($onlySync && is_array($onlySync)){
            $url .= '&operators='.implode(',', $onlySync);
            // create the url and fetch the stuff
            $json = file_get_contents($url);
            $arr = json_decode($json, true);
        }


        if(!empty($arr)){
            foreach($arr as $op){
                $post_id = $this->getPostId($op['OperatorID']);
                if(empty($post_id)){
                    update_post_meta($post_id, $this->postmetaPrefix . '_ShortDetails', $op);
                }
            }
        }
        
    }

    /**
     * Sync room details
     * @return [type] [description]
     */
    public function roomDetail($onlySync = null, $toLoad = null){

        $this->load();
        $id = $this->options['vc_id'];

        /**
         * Room details
         */
        $url = BOOKEASY_ENDPOINT . BOOKEASY_ACCOMROOMSDETAILS_ALL;
        $url = str_replace('[vc_id]', $id, $url);

        if(!empty($toLoad)){

            $base = $url;
            $chunks = array_chunk($toLoad, 30);

            $arr = array();

            foreach($chunks as $chunk){

                $load = $base . '&operators=' . implode(',', $chunk);

                // create the url and fetch the stuff
                $json = file_get_contents($load);
                $result = json_decode($json, true);

                if(!isset($result) || !is_array($result)){
                    return $this->sendFailed('Url/Json Fail : Rooms Chunk');
                } else {
                    $arr = array_merge($arr, $result);
                }
            }
            
        } elseif($onlySync && is_array($onlySync)){
            $url .= '&operators='.implode(',', $onlySync);
            // create the url and fetch the stuff
            $json = file_get_contents($url);
            $arr = json_decode($json, true);
        }



        if(!empty($arr)){
            foreach($arr as $op){
                $post_id = $this->getPostId($op['OperatorId']);
                if(!empty($post_id)){
                    update_post_meta($post_id, $this->postmetaPrefix . '_RoomDetails', $op);
                }
            }
        }
    } 


    /**
     * Sync the categories from the json data.
     * @return String result for iframe
     */
    public function cats(){
        
        set_time_limit(1800);
        global $wpdb;

        $this->load();

        $id = $this->options['vc_id'];

        $toLoad = null;

        // Mod dates
        $url = BOOKEASY_ENDPOINT . BOOKEASY_MODDATES;
        $url = str_replace('[vc_id]', $id, $url);

        // create the url and fetch the stuff
        $json = file_get_contents($url);
        $arr = json_decode($json, true);

        $modDates = array();
        if(!isset($arr['Items']) || !is_array($arr['Items'])){
            return $this->sendFailed('Url/Json Fail : Mod Dates', $onlySync);
        }

        $toLoad = array();
        foreach($arr['Items'] as $mod){
                $toLoad[] = $mod['OperatorId'];
        }
        
        //Operators info
        $url = BOOKEASY_ENDPOINT . BOOKEASY_OPERATORINFO;
        
        $postType = $this->options['posttype'];
        $category = $this->options['taxonomy'];

        if(empty($url) || empty($postType) || empty($id)){
            return $this->sendFailed('Please set the vc_id, post type and taxonomy', $onlySync);
        }

        $url = str_replace('[vc_id]', $id, $url);

        if(!empty($toLoad)){
            $base = $url;
            $toLoad = array_unique($toLoad);
            $chunks = array_chunk($toLoad, 20);

            $arr = array();
            $arr['Operators'] = array();

            foreach($chunks as $chunk){
                $load = $base . '&operators=' . implode(',', $chunk);


                // create the url and fetch the stuff
                $json = file_get_contents($load);
                $result = json_decode($json, true);

                if(!isset($result['Operators']) || !is_array($result['Operators'])){
                    return $this->sendFailed('Url/Json Fail : Operators Chunk');
                } else {
                    $arr['Operators'] = array_merge($arr['Operators'], $result['Operators']);
                }
            }
            
        } else {
            // create the url and fetch the stuff
            $json = file_get_contents($url);
            $arr = json_decode($json, true);
        
            if(!isset($arr['Operators']) || !is_array($arr['Operators'])){
                return $this->sendFailed('Url/Json Fail : Operators', $onlySync);
            }
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
    
    public function markDraft($post_id){
        $post = array(
            'ID' => $post_id, 
            'post_status' => 'draft'
        );
        wp_update_post($post);
    }
    
    public function getPostId($operatorId){

        global $wpdb;

        $postMeta_query ="SELECT ID 
        FROM wp_postmeta pm 
        JOIN wp_posts p ON pm.post_id = p.ID 
        WHERE meta_key = %s 
        AND meta_value = %d 
        ORDER BY post_date DESC";

        return $wpdb->get_var($wpdb->prepare($postMeta_query, $this->postmetaPrefix . '_OperatorID', $operatorId));

        //return $wpdb->get_var($wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE ID = %d", $postMeta_postId));

    }

    public function getPostStatus($post_id){

        global $wpdb;

        // check if it exists based on the operator id
        $post_query = "SELECT post_status FROM $wpdb->posts WHERE ID = %d";
        return $wpdb->get_var($wpdb->prepare($post_query, $post_id));


    }

    public function rawOptionValue($post_id, $key){

        global $wpdb;

        // check if it exists based on the operator id
        $post_query = "SELECT meta_value FROM $wpdb->postmeta WHERE post_id = %d AND meta_key = %s";
        return $wpdb->get_var($wpdb->prepare($post_query, $post_id, $key));


    }

}

new BookeasyOperators_Import();
