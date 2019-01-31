<?php
namespace Bookeasy\api;

use Bookeasy\Base;
use Bookeasy\library\Request;

/**
 * Nice endpoint for operator data
 *
 * /wp-json/bookeasy/operators
 * With no arguments you get all the active operators
 * 
 * ?s=Wines
 * Search string
 *
 * ?tag=bread+baking+recipe
 * Bread Baking and Recpie
 * 
 * ?tag=bread,baking 
 * Bread or Baking
 *
 * ?s=Wines&tag=merlot,cabsav
 * Search and tags
 *
 * ?orderby=title&order=ASC
 * Order by title in asending
 * Ordering defaults to post_date DESC
 *
 * 
 * 
 */
class Endpoint extends Base {


    public function __construct(){

        // rest api ... if it exists
        add_action('rest_api_init', function() {

            register_rest_route($this->nameSpace, 'operators', array(
                'methods' => \WP_REST_Server::READABLE,
                'callback' => array($this, 'operators'),
            ));
            
            // Custom metadata for API from Advanced custom fields
            register_rest_field('members',
              'custom_meta',
              array(
                'get_callback' => array($this, 'member'),
                'update_callback' => null,
                'scheme' => null
              )
            );
        });

    }

    /**
     * Return the members details
     * @param  Object|array $object
     * @param  String $field_name
     * @param  array $request [description]
     * @return array [type]             [description]
     */
    public function member($object, $field_name, $request) {

        $this->load();

        $category = $this->options['taxonomy'];

        $shortDetails = get_post_meta($object['id'], 'bookeasy_ShortDetails', true);

        $r = array();

        // Advanced custom fields
        $r['acf_directions'] = get_field('app_directions');
        $r['acf_shortDirections'] = get_field('app_short_directions');
        $r['acf_duration'] = get_field('app_duration');
        $r['app_directions_header'] = get_field('app_directions_header');
        $r['acf_mapIcon'] = get_field('app_map_icon');
        $r['acf_yelp_identifier'] = get_field('app_yelp_identifier');

        // Direction & location
        $r['bookeasy_Directions'] = get_field('bookeasy_Directions');
        $r['bookeasy_Residential'] = $shortDetails['ResidentialAddress'];

        // Price groups
        $r['prices'] = get_field('app_price_list');

        // Open hours
        $r['hours'] = strip_tags(trim(get_field('bookeasy_Hours')));

        $hours = array();
        $hours['day'] = get_field('app_open_days');
        $hours['openHour'] = get_field('app_opening_hour');
        $hours['openMin'] = get_field('app_opening_minute');
        $hours['closeHour'] = get_field('app_closing_hour');
        $hours['closeMin'] = get_field('app_closing_minute');
        $hours['lastEntryHour'] = get_field('app_last_entry_hour');
        $hours['lastEntryMin'] = get_field('app_last_entry_minute');
        $hours['moreInfo'] = get_field('app_open_hours_more_info');

        $r['openHours'] = $hours;

        // Summary
        $r['summary'] = nl2br(strip_tags(get_field('bookeasy_PointOfDifference'), '<em><strong><i></i>'));

        // Checkpoints
        $r['acf_checkpoints'] = get_field('app_checkpoints'); 

        // Latitude & Longitude
        $r['longitude'] = $shortDetails['Longitude'];
        $r['latitude'] = $shortDetails['Latitude'];

        // Bookeasy contact info
        $r['bookeasy_Phone'] = $shortDetails['PhoneNumber'];
        $r['bookeasy_Email'] = $shortDetails['Email'];

        // Background image
        $pictures = get_attached_media('image');
        $hero_image = array_slice($pictures, 0, 1)[0];
        $hero_url = wp_get_attachment_image_src($hero_image->ID, 'full')[0];
        $r['banner_image'] = $hero_url;
        $r['banner_lastmtime'] = $hero_image->post_modified;

        // grap primary cat
        $r['primary_member_category'] = $this->primary($object['id'], $category);

        return $r;

    }



    /**
     * Return operators in json api format
     * @return [type] [description]
     */
    public function operators(){

        $this->load();
        $result = array();

        $args = array(
            'post_type' => $this->options['posttype'],
            'posts_per_page' => '-1'
        );

        if(Request::get('s', false)){
            $args['s'] = Request::get('s', false);
        }

        if(Request::get('tag', false)){
            $args['tag'] = Request::get('tag', false);
        }

        if(Request::get('cat', false)){
            $args['cat'] = Request::get('cat', false);
        }

        if(Request::get('category_name', false)){
            $args['category_name'] = Request::get('category_name', false);
        }

        if(Request::get('orderby', false)){
            $args['orderby'] = Request::get('orderby', false);
        }

        if(Request::get('order', false)){
            $args['order'] = Request::get('order', false);
        }

        
        $query = new \WP_Query($args);

        if(!$query){
            return $result;
        }

        $items = $query->get_posts();

        if(!$items){
            return $result;
        }

        $category = $this->options['taxonomy'];

        foreach ($items as $item){ 
            $id = $item->ID;

            $shortDetails = $this->meta($id, 'ShortDetails');

            $imageUrl = array();
            $images = $this->field($shortDetails, 'ImageUrls');
            if(!empty($images)){
                foreach($images as $image){
                    $imageUrl[] = $this->field($image, 'FullSizeImage');
                }
            }
            

            $result[] = array(
                'post_id' => $id,
                'link' => get_permalink($id),
                'title' => $item->post_title,
                'operatorID' => $this->meta($id, 'OperatorID'),
                'latitude' => $this->field($shortDetails, 'Latitude'),
                'longitude' => $this->field($shortDetails, 'Longitude'),
                'images' => $imageUrl,
                'categories' => $this->terms($id, $category),
                'tags' => $this->terms($id, 'post_tag'),
            );
            
        }  

        return $result;

    }


    public function primary($postId, $taxonomy){
        
        if(class_exists('WPSEO_Primary_Term')){
            $primary = new \WPSEO_Primary_Term($taxonomy, $postId);
            return $primary->get_primary_term();
        }

        return 0;
    }

    /**
     * Returns terms for post
     * @param  [type] $postId   [description]
     * @param  [type] $taxonomy [description]
     * @return [type]           [description]
     */
    public function terms($postId, $taxonomy){

        
        $primary = $this->primary($postId, $taxonomy);

        $terms = wp_get_object_terms($postId, $taxonomy);
        
        $tags = array();

        if(!empty($terms)){
            foreach($terms as $term){
                $tags[] = array(
                    'id' => $term->term_id, 
                    'name' => $term->name,
                    'primary' => ($term->term_id == $primary)
                );
            }

        }

        return $tags;
    }

    /**
     * Get Meta
     * @param  Int $id  
     * @param  String $key 
     * @return array|string
     */
    public function meta($id, $key){
        return get_post_meta($id, $this->nameSpace . '_' . $key, true);
    }

    /**
     * Dot notation array search
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

