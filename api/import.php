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


    private $defaultMeta = array(
        'Directions',
        'Email',
        'Website',
        'ResidentialAddress',
        'Telephone1',
        'Cancellation',
        'OperatorLogo',
        'Facilities',
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

        $post_date_gmt = $date->modify('-1 Day')->format('Y-m-d H:i:s');
        $date->setTimezone($this->tz);
        $post_date = $date->modify('-1 Day')->format('Y-m-d H:i:s');


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

    
        $this->log(print_r(ini_get('memory_limit'), true));

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

                    //$this->log(print_r(array($currentModDates, $mod), true));

                    // update imaged based on DetailsModDate
                    if(empty($currentModDates['DetailsModDate']) || $mod['DetailsModDate'] != $currentModDates['DetailsModDate']){
                        $toLoad[] = $mod['OperatorId'];
                    }

                    // Include images to update ImagesModDate
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


        $this->log(print_r(array('toLoad' => count($toLoad)), true));

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
            
            $post_title = $op[$this->postFields['post_title']];
            $post_name = sanitize_title($post_title);
            
            // Create the post array
            $post = array(
              'post_content'   => $op[$this->postFields['post_content']],
              'post_title'     => $post_title,
              'post_name'      => $post_name, 
              'post_type'      => $postType,
              'post_date'      => $post_date,
              'post_date_gmt'  => $post_date_gmt,
            );  

            if(empty($post_id)){
                $post_id = $this->getPostIdFromName($post_name);
            }

            $bookeasyStatus = in_array(intval($operatorId), $this->visibleOperators) ? 'publish' : 'draft';

            // Does this operator id exist already?
            if(!empty($post_id)){

                $post['ID'] =  $post_id;

                $currentModDates = array(
                    'ImagesModDate' => get_post_meta($post_id, $this->postmetaPrefix . '_' . 'ImagesModDate', true),
                    'DetailsModDate' => get_post_meta($post_id, $this->postmetaPrefix . '_' . 'DetailsModDate', true),
                    //'CLinkModDate' => get_post_meta($post_id, $this->postmetaPrefix . '_' . 'CLinkModDate', true),
                );

                $post['post_status'] = $this->getPostStatus($post_id);
                $pt = strtotime($post_date);

                //make sure you alway default to bookeasy status, not the wordpress status
                if($post['post_status'] == 'publish'){
                    $post['post_status'] = $bookeasyStatus;
                } else if($post['post_status'] == 'future' && $pt <= time()){
                    $post['post_status'] = $bookeasyStatus;
                }

            } else {
                $post['post_status'] = $bookeasyStatus;
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
            
            if(!empty($modDates[$operatorId]['DetailsModDate']) || 
                !empty($modDates[$operatorId]['ImagesModDate']) || 
                (empty($currentImages) && !empty($op['Pictures'])) || 
                $forced
                ){

                if(empty($currentModDates['ImagesModDate']) || 
                    $modDates[$operatorId]['ImagesModDate'] != $currentModDates['ImagesModDate'] || 
                    $modDates[$operatorId]['DetailsModDate'] != $currentModDates['DetailsModDate'] || 
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
                                
                                
                                $ch = curl_init('https:'.$dir . '/' . $dlname);
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

                            update_post_meta($inserted_id, $this->postmetaPrefix . '_ImagesModDate', $modDates[$operatorId]['ImagesModDate']);
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

                foreach($this->defaultMeta as $key){
                    if(!isset($op[$key])){
                        $op[$key] = '';
                    }
                }

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

                update_post_meta($inserted_id, $this->postmetaPrefix . '_DetailsModDate', $modDates[$operatorId]['DetailsModDate']);
                
    
                //set the cats if we need to
                if(!empty($cats)){
                     // post id, cats, ammend to current cats
                     wp_set_object_terms($inserted_id, $cats, $category, true);
                }




            }

        }

        $this->modDates($modDates);

        $this->roomDetail($onlySync, $toLoad);
        $this->extraAccom($onlySync, $toLoad);

        $this->date($onlySync);

        list($unpublished_count, $operatorsUnpublished) = $this->unpublish($onlySync);
        
        $endTime =  date('d/m/Y h:i:s a', time());

        $message = array();
        $message[] = "The bookeasy sync for " . get_bloginfo('name' ) . " has finished".PHP_EOL;
        $message[] = "Created: " . $create_count;
        $message[] = "Updated: " . $update_count;
        $message[] = "Unpublished: " . $unpublished_count;
        $message[] = "Operator Images Updates: " . $image_update_count;
        $message[] = "Start Time: " . $startedTime;
        $message[] = "Operators Created: " . implode(',', $operatorsCreated); 
        $message[] = "Operators Updated: " . implode(',', $operatorsUpdated);   
        $message[] = "Operators Unpublished: " . implode(',', $operatorsUnpublished);   

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
        $json['unpublished'] = $unpublished_count;
        $json['updated_images'] = $image_update_count;
        $json['start_time'] = $startedTime;
        $json['end_time'] = $endTime;

        if(PHP_SAPI == 'cli'){
            return implode(PHP_EOL, $json);
        } 

        return json_encode($json);

    }

    public function unpublish($onlySync = null){

        $this->load();

        $unplublished = 0;
        $currentOperators = array();
        $activeOperators = array();

        if(is_null($onlySync)){

            $query = new WP_Query(array(
                'post_type' => $this->options['posttype'],
                'post_status' => 'publish',
                'posts_per_page' => '-1',
            ));
            $posts = $query->get_posts();

            foreach( $posts as $post ) { 
                $currentOperators[] = $this->rawOptionValue($post->ID, $this->postmetaPrefix . '_OperatorID');
            }

        } elseif(is_array($onlySync)) {
            $currentOperators = $onlySync;
        } else {
            return $unplublished;
        }

        $id = $this->options['vc_id'];

        // Mod dates
        $url = BOOKEASY_ENDPOINT . BOOKEASY_MODDATES;
        $url = str_replace('[vc_id]', $id, $url);

        // create the url and fetch the stuff
        $json = file_get_contents($url);
        $arr = json_decode($json, true);

        $modDates = array();
        if(!isset($arr['Items']) || !is_array($arr['Items'])){
            return $this->sendFailed('Url/Json Fail : Mod Dates unpublish', $onlySync);
        }

        foreach($arr['Items'] as $mod){
            $activeOperators[] = $mod['OperatorId'];
        }

        //print_r($currentOperators);
        //print_r($activeOperators);

        $toUnpublish = array_diff($currentOperators, $activeOperators);

        if(!empty($toUnpublish)){
            foreach ($toUnpublish as $operatorid) {
                $postId = $this->getPostId($operatorid);
                $this->markDraft($postId);

                $unplublished++;
            }
        }

        return array($unplublished, $toUnpublish);
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
                if(!empty($post_id)){
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

        $postMeta_query ="SELECT p.ID 
        FROM $wpdb->postmeta pm 
        JOIN $wpdb->posts p ON pm.post_id = p.ID 
        WHERE pm.meta_key = %s 
        AND pm.meta_value = %d 
        AND p.post_type = %s 
        ORDER BY p.ID ASC";

        return $wpdb->get_var(
            $wpdb->prepare(
                $postMeta_query, 
                $this->postmetaPrefix . '_OperatorID', 
                $operatorId,
                $this->options['posttype']
            )
        );
    }

    /**
     * [getPostId description]
     * @param  [type] $operatorId [description]
     * @return [type]             [description]
     */
    public function getPostIdFromName($name){

        global $wpdb;

        $postMeta_query ="SELECT ID 
        FROM $wpdb->posts 
        WHERE post_name = %s 
        AND post_type = %s 
        ORDER BY ID ASC";

        return $wpdb->get_var(
            $wpdb->prepare(
                $postMeta_query, 
                $name, 
                $this->options['posttype']
            )
        );

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


    public function log($txt = ''){
        //error_log($txt);
    }

}

new BookeasyOperators_Import();
