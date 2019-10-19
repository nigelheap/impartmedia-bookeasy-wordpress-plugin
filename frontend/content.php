<?php
namespace Bookeasy\frontend;

use Bookeasy\Base;

class Content extends Base {


    public $post_id;

    /**
     * PostType constructor.
     *
     * @param $post_id
     */
    public function __construct($post_id = null)
    {
        $this->post_id = !empty($post_id) ? $post_id : get_the_ID();
    }

    /**
     * @return false|string
     */
    public function operator_id()
    {
        return $this->get_field('OperatorID');
    }

    /**
     * @return false|string
     */
    public function link()
    {
        return get_the_permalink($this->post_id);
    }


    /**
     * operator types
     */
    public function operator_type()
    {
        $operator_types = $this->get_field('OperatorTypes');

        if(empty($operator_types)){
            return 'accom';
        }



        foreach($operator_types as $type)
        {
            if($type['TypeName'] == 'Accommodation'){
                return 'accom';
            }

            if($type['TypeName'] == 'Tours'){
                return 'tours';
            }

            if($type['TypeName'] == 'Services'){
                return 'tours';
            }
        }

        //'accom'
        //'tour'

        return 'accom';
    }

    /**
     * @param $name
     *
     * @return mixed|null
     */
    public function get_field($name)
    {
        return get_field('bookeasy_'.$name, $this->post_id);
    }

    /**
     * Returns terms for post
     * @return array
     */
    public function tags(){

        $terms = wp_get_object_terms($this->post_id, 'member-tags');

        $tags = [];

        if(!empty($terms)){
            foreach($terms as $term){

                //New ability to hide some tags
                if(substr($term->name, 0, 1) == '_'){
                    continue;
                }

                $tags[] = array(
                    'id' => $term->term_id,
                    'name' => $term->name
                );
            }
        }

        return $tags;
    }

    /**
     * @return string
     */
    public function hours()
    {
        $hours = trim($this->get_field('Hours'));
        return strip_tags($hours);
    }

    /**
     * @return mixed|null|string
     */
    public function web_link()
    {
        $web = $this->get_field('Website');
        return !strstr($web, 'http') ? 'http://'.$web : $web;
    }



    /**
     * Create the actual type
     */
    public function images()
    {

        // Get short details, lat and long for map
        $shortDetails = $this->get_field('ShortDetails');

        $pictures = get_attached_media('image', $this->post_id);

        if(!empty($shortDetails['ImageUrls'])){

            // find the wordpress image object
            $pictures = array_map(function($item) use ($pictures){
                return margaretriver_find_image_object(basename($item['FullSizeImage']), $pictures);
            }, $shortDetails['ImageUrls']);

            // remove empties
            $pictures = array_filter($pictures);

            $heros = array_filter($shortDetails['ImageUrls'], function($var){
                return (isset($var['Type']) && $var['Type'] == 'Primary');
            });

        }

        // Hero image name
        $heroName = '';
        if(!empty($heros)){
            $heroName = reset($heros);
            $heroName = basename($heroName['FullSizeImage']);
            // strip spaces out again
            $heroName = str_replace(' ', '', $heroName);
        }

        //hero pictures
        $hero_pictures = array();
        if(!empty($heroName)){
            $hero_pictures = array_filter($pictures, function($var) use ($heroName){
                return basename($var->guid) == $heroName;
            });
            $pictures = array_filter($pictures, function($var) use ($heroName){
                return basename($var->guid) != $heroName;
            });
        }

        //Find the first hero image
        if(!empty($hero_pictures)){
            $hero_image = reset($hero_pictures);
            $hero_url = wp_get_attachment_image_src($hero_image->ID, 'full')[0];
        } else {
            $hero_image = reset($pictures);
            $hero_url = wp_get_attachment_image_src($hero_image->ID, 'full')[0];
        }

        return [
            'hero_url' => $hero_url,
            'hero_image' => $hero_image,
            'pictures' => $pictures,
        ];

    }

    /**
     * @return array|bool
     */
    public function prices()
    {

        $prices = $this->price();

        $basePrice = false;
        if(!empty($prices)){
            if(!empty($prices['single'])){
                $basePrice = $prices['single'];
            } elseif(!empty($prices['min'])){
                $basePrice = $prices['min'];
            }
        }


        $prices['base_price'] = floatval($basePrice);

        return $prices;
    }


    /**
     * @return array|bool
     */
    private function price()
    {
        $from_price = $this->meta('from_price');

        if(!empty($from_price)){
            return ['single' => $from_price];
        }

        $min = $this->get_field('priceMin');
        $max = $this->get_field('priceMax');
        $time = $this->get_field('priceSynced');

        if(!empty($time) && $time < (time() - (60 * 60 * 24 * 7))){
            return false;
        }

        if(empty($min) && empty($max)){


            $prices = $this->get_field('Prices');

            if(!$prices){
                return false;
            }

            $items = !empty($prices['Rooms']) ? $prices['Rooms'] : false;
            $items = empty($items) && !empty($prices['Items']) ? $prices['Items'] : false;

            if(!$items){
                return false;
            }

            $singlePrices = [];

            foreach($items as $item){
                $cost = $this->pull($item, 'Availability.Cost', false);
                $days = $this->pull($item, 'Availability.Days', false);

                $singlePrices[] = intval($cost / count($days));
            }

            $singlePrices = array_filter($singlePrices);

            if(empty($singlePrices)){
                return false;
            }

            $min = min($singlePrices);
            $max = max($singlePrices);

        }

        if(empty($min) && empty($max)){
            return false;
        }

        if($min == $max){
            return ['single' => $min];
        }

        return [
            'min' => $min,
            'max' => $max,
        ];
    }

    /**
     * @param $item
     * @return array|String
     */
    public function googleMeta($item){

        return $this->meta('googleplaces_'.$item, []);
    }

    /**
     * @param $terms
     * @return bool
     */
    public function nonBookable($terms){

        // Loop through terms to get category
        $non_bookable = true;

        if(!empty($terms) && is_array($terms)){
            foreach($terms as $term) {
                if (in_array($term['name'], ['Tours', 'Accommodation'])) {
                    $non_bookable = false;
                    break;
                }
            }
        }

        // Loop through terms for non-bookable
        if(!empty($terms) && is_array($terms)){
            foreach($terms as $term) {
                if ($term['name'] == 'Non Bookable') {
                    $non_bookable = true;
                    break;
                }
            }
        }

        return $non_bookable;
    }

    /**
     * @param $name
     * @return string
     */
    public function postField($name)
    {
        return get_post_field($name, $this->post_id);
    }

    /**
     * Dot notation array search
     *
     * @param array $a
     * @param $path
     * @param null $default
     * @return array|mixed|null
     */
    public function pull($a, $path, $default = null)
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


    /**
     * Get Meta
     *
     * @param String $key
     * @param bool $default
     * @return String|array
     */
    public function meta($key, $default = false)
    {
        $meta = get_post_meta($this->post_id, $key, true);
        return !empty($meta) ? $meta : $default;
    }


    /**
     *
     */
    public function meta_boxes()
    {

        $options = get_nectar_theme_options();
        if(!empty($options['transparent-header']) && $options['transparent-header'] == '1') {
            $disable_transparent_header = array(
                'name' =>  __('Disable Transparency From Navigation', NECTAR_THEME_NAME),
                'desc' => __('You can use this option to force your navigation header to stay a solid color even if it qualifies to trigger the <a target="_blank" href="'. admin_url('?page=redux_options&tab=4#header-padding') .'"> transparent effect</a> you have activated in the Salient options panel.', NECTAR_THEME_NAME),
                'id' => '_disable_transparent_header',
                'type' => 'checkbox',
                'std' => ''
            );
            $force_transparent_header = array(
                'name' =>  __('Force Transparency On Navigation', NECTAR_THEME_NAME),
                'desc' => __('You can use this option to force your navigation header to start transparent even if it does not qualify to trigger the <a target="_blank" href="'. admin_url('?page=redux_options&tab=4#header-padding') .'"> transparent effect</a> you have activated in the Salient options panel.', NECTAR_THEME_NAME),
                'id' => '_force_transparent_header',
                'type' => 'checkbox',
                'std' => ''
            );
        } else {
            $disable_transparent_header = null;
            $force_transparent_header = null;
        }


        #-----------------------------------------------------------------#
        # Header Settings
        #-----------------------------------------------------------------#
        $meta_box = array(
            'id' => 'nectar-metabox-page-header',
            'title' => __('Page Header Settings', NECTAR_THEME_NAME),
            'description' => __('Here you can configure how your page header will appear. <br/> For a full width background image behind your header text, simply upload the image below. To have a standard header just fill out the fields below and don\'t upload an image.', NECTAR_THEME_NAME),
            'post_type' => $this->post_type,
            'context' => 'normal',
            'priority' => 'default',
            'fields' => array(
                $disable_transparent_header,
                $force_transparent_header
            )
        );

        $callback = create_function( '$post,$meta_box', 'nectar_create_meta_box( $post, $meta_box["args"] );' );
        add_meta_box( $meta_box['id'], $meta_box['title'], $callback, $meta_box['post_type'], $meta_box['context'], $meta_box['priority'], $meta_box );

    }
}