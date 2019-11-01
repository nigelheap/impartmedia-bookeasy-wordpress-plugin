<?php
namespace Bookeasy\api;

use Bookeasy\Base;

class Import extends Base {

    /**
     * Holds the values to be used in the fields callbacks
     */
    public $catOptions;
    public $catMapping;
    public $visibleOperators;
    public $tz;
    public $tzUTC;
    public $email;
    public $upload_dir;
    public $modDates = [];

    public $postmetaPrefix = 'bookeasy';

    private $postFields = [
        'post_title' => 'TradingName',
        'post_content' => 'Description',
    ];

    private $catTypes = [
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
    ];


    private $defaultMeta = [
        'Directions',
        'Email',
        'Website',
        'ResidentialAddress',
        'Telephone1',
        'Cancellation',
        'OperatorLogo',
        'Facilities',
    ];

    /**
     * Start up
     */
    public function __construct(){

        $this->tz = new \DateTimeZone('AWST');
        $this->tzUTC = new \DateTimeZone('UTC');
        $this->upload_dir   = wp_upload_dir();

        //returning for chaining
        return $this;
    }

    /**
     * pull the visible operators
     */
    public function loadVisibleOperator(){

        if(!empty($this->visibleOperators)){
            return;
        }


        $arr = $this->request(
            BOOKEASY_VISIBLEOPERATORS,
            'OperatorIds',
            'visibleOperators'
        );

        $this->visibleOperators = $arr;


    }

    /**
     * @param null $onlySync
     * @return int
     * @throws \Exception
     */
    public function date($onlySync = null){

        global $wpdb;

        $this->load();
        $this->loadVisibleOperator();

        $arr = $this->request(
            BOOKEASY_MODDATES,
            'Items',
            'Mod Dates',
            $onlySync
        );

        foreach($arr as $mod){
            $this->modDates[$mod['OperatorId']] = $mod;
        }

        $updated = 0;

        if(!empty($this->modDates)){

            foreach($this->modDates as $opId => $op){

                if(!is_null($onlySync) && !in_array($op['OperatorId'], $onlySync)){
                    continue;
                }

                $post_id = $this->getPostId($op['OperatorId']);


                if(!empty($post_id)){

                    list($post_date, $post_date_gmt) = $this->getDates($this->modDates[$op['OperatorId']]['DetailsModDate']);

                    
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
     * @return array
     * @throws \Exception
     */
    public function getDates($date){

        $compare = new \DateTime('now', $this->tz);
        $date = new \DateTime($date, $this->tz);
        //$date->setTimezone($this->tz);

        if($date > $compare){
            $date = $compare;
        }

        $post_date = $date->modify('-10 Minutes')->format('Y-m-d H:i:s');
        $date->setTimezone($this->tzUTC);
        $post_date_gmt = $date->modify('-10 Minutes')->format('Y-m-d H:i:s');

        return [$post_date, $post_date_gmt];
    }


    /**
     * Syncing the operators with post type and category
     * @param null $onlySync
     * @param string $email
     * @return mixed|string|void [type] [description]
     * @throws \Exception
     */
    public function sync($onlySync = null, $email = ''){

        $this->load();

        $startedTime =  date('d/m/Y h:i:s a', time());
        $this->email = $email;

        $this->log(print_r([
            'email' => $this->email,
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'max_input_time' => ini_get('max_input_time'),
        ], true));

        $this->options = get_option($this->optionGroup);
        $this->catMapping = get_option($this->optionGroupCategories);

        $this->loadVisibleOperator();

        $arr = $this->request(
            BOOKEASY_MODDATES,
            'Items',
            'Mod Dates',
            $onlySync
        );

        $toLoad = [];
        $imageUpdate = [];
        $dataUpdate = [];

        $operatorsWithNoImages = 0;
        $operatorsWithNoImageDate = 0;
        $operatorsWithNewImages = 0;

        $operatorsWithNoDetailsDate = 0;
        $operatorsWithNewDetails = 0;
        $newOperators = 0;


        foreach($arr as $mod){

            if(!empty($onlySync) && !in_array($mod['OperatorId'], $onlySync)){
                continue;
            }

            $post_id = $this->getPostId($mod['OperatorId']);

            if(!empty($post_id)){

                $currentModDates = [
                    'ImagesModDate' => get_post_meta($post_id, $this->postmetaPrefix . '_' . 'ImagesModDate', true),
                    'DetailsModDate' => get_post_meta($post_id, $this->postmetaPrefix . '_' . 'DetailsModDate', true),
                ];

                $currentImages = $this->rawOptionValue($post_id, $this->postmetaPrefix . '_Pictures');

                $detailsMatch = ($mod['DetailsModDate'] == $currentModDates['DetailsModDate']);
                $imagesMatch = ($mod['ImagesModDate'] == $currentModDates['ImagesModDate']);

                if(!$detailsMatch){

                    if(empty($currentModDates['DetailsModDate'])){
                        $toLoad[] = $mod['OperatorId'];
                        $dataUpdate[] = $mod['OperatorId'];
                        $operatorsWithNoDetailsDate++;
                    } 

                    if($mod['DetailsModDate'] != $currentModDates['DetailsModDate']){
                        $toLoad[] = $mod['OperatorId'];
                        $dataUpdate[] = $mod['OperatorId'];
                        $operatorsWithNewDetails++;
                    }

                }

                if(!$imagesMatch){

                    if(empty($currentModDates['ImagesModDate'])){
                        $toLoad[] = $mod['OperatorId'];
                        $imageUpdate[] = $mod['OperatorId'];
                        $operatorsWithNoImageDate++;
                    }

                    if(empty($currentImages)){
                        $toLoad[] = $mod['OperatorId'];
                        $imageUpdate[] = $mod['OperatorId'];
                        $operatorsWithNoImages++;
                    }

                    if($mod['ImagesModDate'] != $currentModDates['ImagesModDate']){
                        $toLoad[] = $mod['OperatorId'];
                        $imageUpdate[] = $mod['OperatorId'];
                        $operatorsWithNewImages++;
                    }
                }


            } else {
                $toLoad[] = $mod['OperatorId'];
                $imageUpdate[] = $mod['OperatorId'];
                $dataUpdate[] = $mod['OperatorId'];
                $newOperators++;
            }

            //force all
            //$imageUpdate[] = $mod['OperatorId'];
            //$dataUpdate[] = $mod['OperatorId'];
            //$toLoad[] = $mod['OperatorId'];

            $this->modDates[$mod['OperatorId']] = $mod;
        }

        if($onlySync && is_array($onlySync)){
            $toLoad = $onlySync;
        }

        $toLoad = array_unique($toLoad);
        $dataUpdate = array_unique($dataUpdate);
        $imageUpdate = array_unique($imageUpdate);

        

        $this->log(print_r([
            'operators' => $toLoad,
            'total' => count($toLoad),
            'total for data' => count($dataUpdate),
            'total for images' => count($imageUpdate),
            'operators with no image date' => $operatorsWithNoImageDate,
            'operators with no images' => $operatorsWithNoImages,
            'operators with new images' => $operatorsWithNewImages,
            'operators with no details date' => $operatorsWithNoDetailsDate,
            'operators with new details' => $operatorsWithNewDetails,
            'new operators' => $newOperators,
        ], true));

        


        //Operators info
        $url = BOOKEASY_OPERATORINFO;
        
        $postType = $this->options['posttype'];
        $category = $this->options['taxonomy'];


        if(empty($toLoad)){
            $this->log('Nothing to sync');
        }

        $base = $url;
        $chunks = array_chunk($toLoad, 20);

        $arr = [];
        $arr['Operators'] = [];

        foreach($chunks as $chunk){
            $load = $base . '&operators=' . implode(',', $chunk);

            $result = $this->request(
                $load,
                false,
                ' Operators Chunk',
                $onlySync
            );

            $arr['Operators'] = array_merge($arr['Operators'], $result['Operators']);
        }


        $include_locations = [];
        $location_ids = trim($this->options['location_ids']);
        
        if(!empty($location_ids)){
            $include_locations = explode(',', $location_ids);
        }

        $create_count = 0;
        $update_count = 0;
        $skipped_count = 0;
        $image_update_count = 0;
        $operatorsUpdated = [];
        $operatorsCreated = [];

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
                       $skipped_count++;
                       continue 2;
                   }
               }
            }

            if(empty($op[$this->postFields['post_title']])){
                $skipped_count++;
                continue;
            }

            list($post_date, $post_date_gmt) = $this->getDates($this->modDates[$operatorId]['DetailsModDate']);
            
            $post_title = $op[$this->postFields['post_title']];
            $post_name = sanitize_title($post_title);

            $post_content = $op[$this->postFields['post_content']];
            if(empty($post_content)){
                $post_content = $post_title;
            }
            
            // Create the post array
            $post = [
              'post_content'   => $post_content,
              'post_title'     => $post_title,
              'post_name'      => $post_name, 
              'post_type'      => $postType,
              'post_date'      => $post_date,
              'post_date_gmt'  => $post_date_gmt,
            ];

            if(empty($post_id)){
                $post_id = $this->getPostIdFromName($post_name);
            }

            if(!empty($this->visibleOperators)){
                $status = in_array(intval($operatorId), $this->visibleOperators) ? 'publish' : 'draft';
            } else {
                $status = 'publish';
            }


            // Does this operator id exist already?
            if(!empty($post_id)){

                $post['post_author'] = get_post_field( 'post_author', $post_id );
                $post['ID'] =  $post_id;

                $currentModDates = [
                    'ImagesModDate' => get_post_meta($post_id, $this->postmetaPrefix . '_' . 'ImagesModDate', true),
                    'DetailsModDate' => get_post_meta($post_id, $this->postmetaPrefix . '_' . 'DetailsModDate', true),
                    //'CLinkModDate' => get_post_meta($post_id, $this->postmetaPrefix . '_' . 'CLinkModDate', true),
                ];

                //$post['post_status'] = $this->getPostStatus($post_id);
                //make sure you always default to bookeasy status, not the wordpress status
                $post['post_status'] = $status;
            } else {
                $post['post_status'] = $status;
            }
            
            if(empty($currentModDates['DetailsModDate']) ||
                $this->modDates[$operatorId]['DetailsModDate'] != $currentModDates['DetailsModDate']){

                $inserted_id = wp_insert_post( $post );
                update_post_meta($inserted_id, $this->postmetaPrefix . '_OperatorID', $operatorId);
    
                // something happed??
                if( is_wp_error( $inserted_id ) ) {

                    $this->log(print_r([
                        'title' => $post_title,
                        'error' => $inserted_id->get_error_message(),
                    ], true));
                    
                    //continue;
                }

            } elseif(!empty($post_id)) {
                $inserted_id = $post_id;
            }
            

            /**
             * Process images
             */

            if(in_array($operatorId, $imageUpdate) || $forced){
                $this->images($op, $operatorId, $inserted_id, $image_update_count, $post_title, $forced);
            }


            if(in_array($operatorId, $dataUpdate) || $forced){


                if(!empty($post_id)){
                    $update_count++;
                    $operatorsUpdated[] = $operatorId;
                } else {
                    $create_count++;
                    $operatorsCreated[] = $operatorId;
                }
                
                // Save the updated dates to the database
                $cats = [];

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

                update_post_meta($inserted_id, $this->postmetaPrefix . '_DetailsModDate', $this->modDates[$operatorId]['DetailsModDate']);

                //set the cats if we need to
                if(!empty($cats)){
                     // post id, cats, ammend to current cats
                     wp_set_object_terms($inserted_id, $cats, $category, true);
                }
            }
        }

        $this->modDates($this->modDates);

        $this->roomDetail($onlySync, $toLoad);
        $this->extraAccom($onlySync, $toLoad);

        $this->date($onlySync);

        $this->accomPrices($onlySync);
        $this->tourPrices($onlySync);

        list($unpublished_count, $operatorsUnpublished) = $this->unpublish($onlySync);
        
        $endTime =  date('d/m/Y h:i:s a', time());

        $message = [];
        $message[] = "The bookeasy sync for " . get_bloginfo('name' ) . " has finished".PHP_EOL;

        $message[] = "Total Operators with Added/Updates: " . count($toLoad);
        $message[] = "Total Operators with data Added/Updates: " . count($dataUpdate);
        $message[] = "Total Operators with images Added/Updates: " . count($imageUpdate);
        $message[] = PHP_EOL;
        $message[] = "Detailed data update reasons: ";
        $message[] = "- operators with no details date count: " . $operatorsWithNoDetailsDate;
        $message[] = "- operators with new details count: " . $operatorsWithNewDetails;
        $message[] = "- new operators count: " . $newOperators;
        $message[] = PHP_EOL;
        $message[] = "Detailed image Update reasons: ";
        $message[] = "- operators with no image date count: " . $operatorsWithNoImageDate;
        $message[] = "- operators with no images count: " . $operatorsWithNoImages;
        $message[] = PHP_EOL;

        $message[] = "Results: ";
        $message[] = "- Skipped (location not valid or no title): " . $skipped_count;
        $message[] = "- Created: " . $create_count;
        $message[] = "- Updated: " . $update_count;
        $message[] = "- Unpublished: " . $unpublished_count;
        $message[] = "- Operator Images Updates: " . $image_update_count;
        $message[] = "- Start Time: " . $startedTime;
        $message[] = "- Operators Created: " . implode(',', $operatorsCreated);
        $message[] = "- Operators Updated: " . implode(',', $operatorsUpdated);
        $message[] = "- Operators Unpublished: " . implode(',', $operatorsUnpublished);


        $this->sendEmail(
            implode(PHP_EOL, $message),
            $onlySync,
            'Bookeasy sync finished'
        );

        $json = [];
        $json['created'] = $create_count;
        $json['updated'] = $update_count;
        $json['unpublished'] = $unpublished_count;
        $json['updated_images'] = $image_update_count;
        $json['start_time'] = $startedTime;
        $json['end_time'] = $endTime;

        if(isset($forced) && isset($op) && $forced){
            $json['single'] = $op;
        }


        if(PHP_SAPI == 'cli'){
            return implode(PHP_EOL, $message);
        } 

        return json_encode($json);

    }

    /**
     * @param $op
     * @param $operatorId
     * @param $inserted_id
     * @param $image_update_count
     * @param $post_title
     * @param $forced
     * @return int
     */
    private function images($op, $operatorId, $inserted_id, &$image_update_count, $post_title, $forced)
    {

        $imageCount = 0;

        if(!empty($op['Pictures']) && is_array($op['Pictures'])){

            $currentItems = get_attached_media('image', $inserted_id);

            foreach ($currentItems as $currentItem) {
                wp_delete_attachment($currentItem->ID);
            }

            foreach($op['Pictures'] as $path){
                //$imageCount++;
                //continue;

                $image_update_count++;

                $dir = dirname($path);

                $name = basename($path);
                $dlname = str_replace(' ', '%20', $name);

                $name = str_replace(' ', '', $name);

                if(file_exists($this->upload_dir['path'] .'/'.$name)){
                    unlink($this->upload_dir['path'] .'/'.$name);
                }


                $ch = curl_init('https:'.$dir . '/' . $dlname);
                $fp = fopen($this->upload_dir['path'] .'/'.$name, 'wb');
                curl_setopt($ch, CURLOPT_FILE, $fp);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_setopt($ch, CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_exec($ch);
                curl_close($ch);
                fclose($fp);

                // $filename should be the path to a file in the upload directory.
                $filename = $this->upload_dir['path'] .'/'.$name;

                // Check the type of file. We'll use this as the 'post_mime_type'.
                $filetype = wp_check_filetype( basename( $filename ), null );

                // Prepare an array of post data for the attachment.
                $attachment = [
                    'guid'           => $this->upload_dir['url'] . '/' . basename( $filename ),
                    'post_mime_type' => $filetype['type'],
                    'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $filename ) ),
                    'post_content'   => $post_title,
                    'post_status'    => 'inherit'
                ];

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

                $imageCount++;

                if($imageCount == 1){
                    add_post_meta($inserted_id, '_thumbnail_id', $attach_id, true);
                }

                update_post_meta($inserted_id, $this->postmetaPrefix . '_ImagesModDate', $this->modDates[$operatorId]['ImagesModDate']);
            }

        }

        return $imageCount;
    }

    /**
     * @param string $message
     * @param null $onlySync
     * @param string $subject
     * @return array|string
     */
    public function sendEmail($message = '', $onlySync = null, $subject = 'Bookeasy notification'){
        $this->load();

        $message = [$message];
        $message[] = "Time: " . date('d/m/Y h:i:s a', time());

        if(is_null($onlySync) && !empty($this->options['notificaton_email'])){
            $emails = $this->options['notificaton_email'];

            if(!empty($this->email)){
                $emails .= ','.$this->email;
            }

            if(strstr($emails, ',')){
                $emails = explode(',', $emails);
            }

            wp_mail(
                $emails, 
                get_bloginfo('name' ) . ' ' . $subject,
                implode(PHP_EOL, $message)
            );

        }

        return $message;

    }


    /**
     * @param null $onlySync
     * @return array|mixed
     */
    public function unpublish($onlySync = null){

        $this->load();

        $unplublished = 0;
        $currentOperators = [];
        $activeOperators = [];

        if(is_null($onlySync)){

            $query = new \WP_Query([
                'post_type' => $this->options['posttype'],
                'post_status' => 'publish',
                'posts_per_page' => '-1',
            ]);
            $posts = $query->get_posts();

            foreach( $posts as $post ) { 
                $currentOperators[] = $this->rawOptionValue($post->ID, $this->postmetaPrefix . '_OperatorID');
            }

        } elseif(is_array($onlySync)) {
            $currentOperators = $onlySync;
        } else {
            return $unplublished;
        }

        $arr = $this->request(
            BOOKEASY_MODDATES,
            'Items',
            'Mod Dates unpublish',
            $onlySync
        );

        foreach($arr as $mod){
            $activeOperators[] = $mod['OperatorId'];
        }


        $toUnpublish = array_diff($currentOperators, $activeOperators);

        if(!empty($toUnpublish)){
            foreach ($toUnpublish as $operatorid) {
                $postId = $this->getPostId($operatorid);
                $this->markDraft($postId);

                $unplublished++;
            }
        }

        return [$unplublished, $toUnpublish];
    }

    /**
     * @param $reason
     * @param null $onlySync
     * @return mixed
     */
    public function sendFailed($reason, $onlySync = null){
        $this->load();

        $message = [];
        $message[] = "The bookeasy sync for " . get_bloginfo('name' ) . " has failed".PHP_EOL;
        $message[] = "Reason: " . $reason;

        if(is_null($onlySync) && !empty($this->options['notificaton_email'])){
            $emails = $this->options['notificaton_email'];

            if(!empty($this->email)){
                $emails .= ','.$this->email;
            }

            if(strstr($emails, ',')){
                $emails = explode(',', $emails);
            }

            wp_mail(
                $emails, 
                get_bloginfo('name' ) . ' Bookeasy sync failed', 
                implode(PHP_EOL, $message)
            );

        }

        return $reason;

    }

    /**
     * @param $modDates
     */
    private function modDates($modDates){

        if(!empty($modDates)){

            foreach($modDates as $op){
                foreach($op as $opKey => $opItem){

                    if(in_array($opKey, ['OperatorId'])){
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
     * @param null $onlySync
     * @param null $toLoad
     */
    public function extraAccom($onlySync = null, $toLoad = null){

        $url = BOOKEASY_OPERATORDETAILSSHORT_ALL;

        if(!empty($toLoad)){
            $base = $url;
            $chunks = array_chunk($toLoad, 30);

            $arr = [];
            foreach($chunks as $chunk){
                $load = $base . '&operators=' . implode(',', $chunk);

                $result = $this->request(
                    $url,
                    'Data',
                    'Extra Accom Chunk',
                    $onlySync
                );

                $arr = array_merge($arr, $result);
            }

        } elseif($onlySync && is_array($onlySync)){

            $url .= '&operators='.implode(',', $onlySync);

            $arr = $this->request(
                $url,
                'Data',
                'Extra Accom Chunk',
                $onlySync
            );
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
     * Accom Prices
     *
     * @param null $onlySync
     * @param null $toLoad
     */
    public function accomPrices($onlySync = null, $toLoad = null){

        $url = BOOKEASY_ACCOMRATES;
        $url .= '&date='. date('Y-m-d', strtotime('+1 Week'));
        $url .= '&period=2&adults=2';

        $this->prices($url, 'Rooms', 'OperatorID', $onlySync, $toLoad);
    }


    /**
     * Tour Prices
     *
     * @param null $onlySync
     * @param null $toLoad
     */
    public function tourPrices($onlySync = null, $toLoad = null){

        $url = BOOKEASY_TOURRATES;
        $url .= '&date='. date('Y-m-d', strtotime('+1 Week'));
        $url .= '&adults=1';

        $this->prices($url, 'Items', 'OperatorId', $onlySync, $toLoad);
    }


    /**
     * Prices
     *
     * @param $url
     * @param string $key
     * @param string $operatorIdKey
     * @param null $onlySync
     * @param null $toLoad
     * @return void
     */
    protected function prices($url, $key = 'Rooms', $operatorIdKey = 'OperatorID', $onlySync = null, $toLoad = null){

        if(!empty($toLoad)){
            $base = $url;
            $chunks = array_chunk($toLoad, 30);

            $arr = [];
            foreach($chunks as $chunk){
                $load = $base . '&operators=' . implode(',', $chunk);

                $result = $this->request(
                    $load,
                    'Data',
                    'Prices Chunk',
                    $onlySync
                );

                $arr = array_merge($arr, $result);
            }

        } elseif($onlySync && is_array($onlySync)){

            $url .= '&operators='.implode(',', $onlySync);
            $arr = $this->request(
                $url,
                'Data',
                'Prices Chunk',
                $onlySync
            );

        } else {

            $arr = $this->request(
                $url,
                'Data',
                'Prices Chunk',
                $onlySync
            );
        }


        if(!empty($arr)){
            foreach($arr as $op){
                $post_id = $this->getPostId($op[$operatorIdKey]);
                if(!empty($post_id)){
                    update_post_meta($post_id, $this->postmetaPrefix . '_Prices', $op);
                    $this->savePrices($post_id, $op, $key);
                }
            }
        }

    }


    /**
     * Sync room details
     * @param null $onlySync
     * @param null $toLoad
     * @return void [type] [description]
     */
    public function roomDetail($onlySync = null, $toLoad = null){

        $url = BOOKEASY_ACCOMROOMSDETAILS_ALL;

        if(!empty($toLoad)){

            $base = $url;
            $chunks = array_chunk($toLoad, 30);

            $arr = [];

            foreach($chunks as $chunk){

                $load = $base . '&operators=' . implode(',', $chunk);

                $result = $this->request(
                    $load,
                    'Data',
                    'Room Details.',
                    $onlySync
                );

                $arr = array_merge($arr, $result);

            }
            
        } elseif($onlySync && is_array($onlySync)){
            $url .= '&operators='.implode(',', $onlySync);

            $arr = $this->request(
                $url,
                'Data',
                'Room Details. ',
                $onlySync
            );

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
     * @param string $onlySync
     * @return String result for iframe
     */
    public function cats($onlySync = '')
    {

        $this->load();
        $toLoad = null;

        $arr = $this->request(
            BOOKEASY_MODDATES,
            'Items',
            'Mod Dates',
            $onlySync
        );

        $toLoad = [];
        foreach($arr as $mod){
                $toLoad[] = $mod['OperatorId'];
        }
        
        //Operators info
        $url = BOOKEASY_OPERATORINFO;

        if(!empty($toLoad)){
            $base = $url;
            $toLoad = array_unique($toLoad);
            $chunks = array_chunk($toLoad, 20);

            $arr = [];

            foreach($chunks as $chunk){
                $load = $base . '&operators=' . implode(',', $chunk);

                $result = $this->request(
                    $load,
                    'Operators',
                    'Operators',
                    $onlySync
                );

                $arr = array_merge($arr, $result);
            }
            
        } else {

            $arr = $this->request(
                $url,
                'Operators',
                'Operators',
                $onlySync
            );
        }

        $types = [];
        foreach($arr as $op){

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

    /**
     * @param $url
     * @param string $key
     * @param string $message
     * @param $onlySync
     * @return array|mixed|object
     */

    protected function request($url, $key = 'Items', $message = 'Undefined', $onlySync = null)
    {
        $this->load();

        $id = $this->options['vc_id'];
        $apiKey = $this->options['api_key'];

        $endpoint = $this->options['environment'] == 'pvt' ? BOOKEASY_ENDPOINT_PVT : BOOKEASY_ENDPOINT;

        $url = $endpoint . str_replace('[vc_id]', $id, $url);

        $headers = [];
        if(!empty($apiKey)){
            $headers['apiKey'] = $apiKey;
        }

        $this->log(print_r([
            'request' => $url,
            'header' => $headers,
        ], true));

        //  Initiate curl
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_URL, $url);

        if(!empty($headers)){
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        // Execute
        $json = curl_exec($ch);
        // Closing
        curl_close($ch);

        $arr = json_decode($json, true);

        if(!empty($key) && isset($arr[$key]) && is_array($arr[$key])){
            return $arr[$key];
        }

        if(empty($key) && is_array($arr)){
            return $arr;
        }

        $this->sendFailed(
            'Url/Json Fail : '. $message . ' ' . $url,
            $onlySync
        );

        return empty($key) ? [] : [$key => []];
    }

    /**
     * @param $post_id
     */
    public function markDraft($post_id){
        $post = [
            'ID' => $post_id, 
            'post_status' => 'draft'
        ];
        wp_update_post($post);
    }

    /**
     * @param $operatorId
     * @return null|string
     */
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
     * @return null|string [type]
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

    /**
     * @param $post_id
     * @return null|string
     */
    public function getPostStatus($post_id){

        global $wpdb;

        // check if it exists based on the operator id
        $post_query = "SELECT post_status FROM $wpdb->posts WHERE ID = %d";
        return $wpdb->get_var($wpdb->prepare($post_query, $post_id));


    }

    /**
     * @param $post_id
     * @param $key
     * @return null|string
     */
    public function rawOptionValue($post_id, $key){

        global $wpdb;

        // check if it exists based on the operator id
        $post_query = "SELECT meta_value FROM $wpdb->postmeta WHERE post_id = %d AND meta_key = %s";
        return $wpdb->get_var($wpdb->prepare($post_query, $post_id, $key));
    }


    /**
     * Helper that saves tour and accom prices into correct format for searching later
     * @param $post_id
     * @param $item
     * @param string $key
     * @return array|bool
     */
    private function savePrices($post_id, $item, $key = 'Rooms')
    {

        if(!$item){
            return false;
        }

        $items = !empty($item[$key]) ? $item[$key] : false;

        if(!$items){
            return false;
        }

        $singlePrices = [];

        foreach($items as $item){
            $cost = $this->field($item, 'Availability.Cost', false);
            $days = $this->field($item, 'Availability.Days', false);
            $singlePrices[] = intval($cost / count($days));
        }

        $singlePrices = array_filter($singlePrices);

        if(empty($singlePrices)){
            return false;
        }

        $meta = [
            'priceMin' => min($singlePrices),
            'priceMax' => max($singlePrices),
        ];

        foreach($meta as $meta_key => $meta_value){
            update_post_meta($post_id, $this->postmetaPrefix . '_' . $meta_key, $meta_value);
        }

        update_post_meta($post_id, $this->postmetaPrefix . '_priceSynced', time());

        return $meta;

    }

    /**
     * @param string $txt
     */
    public function log($txt = ''){
        error_log($txt);
        file_put_contents($this->upload_dir['basedir'] . '/bookeasy.log', $txt.PHP_EOL , FILE_APPEND);
    }


    /**
     * Dot notation array search
     *
     * @param array $a
     * @param $path
     * @param null $default
     * @return array|mixed|null
     */
    public function field($a, $path, $default = null)
    {
        if(!is_array($a)){
            return $default;
        }

        $current = $a;
        $p = strtok($path, '.');

        while ($p !== false) {
            if (!isset($current[$p])) {
                return $default;
            }
            $current = $current[$p];
            $p = strtok('.');
        }

        return $current;
    }



}

