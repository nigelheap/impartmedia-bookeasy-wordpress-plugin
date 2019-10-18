<?php
namespace Bookeasy;

class Base {

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

    /**
     * Load all the vars
     */
    public function load_vars()
    {
        $this->options = get_option($this->optionGroup); 
        $this->categories = get_option($this->optionGroupCategories);
        $this->categoriesSync = get_option($this->optionGroupCategoriesSync);
    }

    /**
     * Loading items
     */
    public function load()
    {
        if(empty($this->options) || empty($this->categories) || empty($this->categoriesSync)){
            $this->load_vars();
        }
    }


    /**
     * Work out the api key for the frontend
     */
    public function api_key(){
        if(empty($this->options)){
            $this->load_vars();
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


}


