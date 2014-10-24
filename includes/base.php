<?php

class Bookeasy {

    /**
     * Holds the values to be used in the fields callbacks
     */
    public $options;
    public $categories;
    public $categoriesSync;

    public $nameSpace = 'bookeasy';

    public $optionGroup = 'bookeasy_options';
    public $optionGroupCategories = 'bookeasy_categories';
    public $optionGroupCategoriesSync = 'bookeasy_categoriessync';

    public $settingsName = 'bookeasy_options';
    public $settingsNameCategories = 'bookeasy_categories';

    public $sectionName = 'settings_section';
    public $sectionNameCategories = 'categories_section';


    public function load_vars(){
        $this->options = get_option($this->optionGroup); 
        $this->categories = get_option($this->optionGroupCategories);
        $this->categoriesSync = get_option($this->optionGroupCategoriesSync);
    }


    public function load(){
        if(empty($this->options) || empty($this->categories) || empty($this->categoriesSync)){
            $this->load_vars();
        }
    }


    public function api_key(){
        if(empty($this->options)){
            $this->loat_vars();
        }

        if(empty($this->options['apikeys'])){
            return;
        }

        $keys = $this->options['apikeys'];
        $keys = explode("\n", $keys);

        foreach ($keys as $key) {
            list($domain, $apikey) = explode('|', $key);
            if (isset($_SERVER['HTTP_HOST']) && $domain == $_SERVER['HTTP_HOST']) {
                return $apikey;
            }
        }

    }

    public function storeRooms($operatorId, $postId){

        $id = $this->options['vc_id'];
        $url = BOOKEASY_ENDPOINT . BOOKEASY_OPERATORDETAILSSHORT;
        $url = str_replace('[vc_id]', $id, $url);
        $url = str_replace('[operators_id]', $operatorId, $url);

        // create the url and fetch the stuff
        $json = file_get_contents($url);
        $arr = json_decode($json, true);


    }


}


