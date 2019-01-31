<?php
namespace Bookeasy\library;

class Template{
    

    /**
     * @see load() function
     * @param string $name Specifies the parameter name.
     * @param mixed $default Specifies a default value.
     * @return mixed Returns the POST parameter value, NULL or default value.
     */
    public static function load( $template_name, $template_data = array()){
        echo self::get($template_name, $template_data);
    }

    public static function get( $template_name, $template_data = array()){

        if(!empty($template_data)){
            extract($template_data);
        }


        $template_name = plugin_dir_path(dirname(__FILE__)) . $template_name.'.php';
        if(!file_exists($template_name)){
            return '';
        }

        unset($template_data);
        ob_start();
        include($template_name);
        return ob_get_clean();

    }

    public static function splitChapterTitle($title){

        if(strstr($title, ':')){

            $parts = explode(':', $title);
            return $parts[0];

        }

        return $title;
    }


    public static function selected($current = "", $loopValue = ""){

        if(empty($current) || empty($loopValue)){
            return "";
        }

        if($current == $loopValue)
            return 'selected="selected"';

        return "";

    }

    public static function checked($current = array(), $value = ""){

        if(empty($current) || empty($value)){
            return "";
        }

        if(in_array($value, $current))
            return 'checked="checked"';


        return "";

    }


    public static function userFieldValue($userMeta, $key){

        if(!empty($userMeta[$key]) && !empty($userMeta[$key][0])){
            return $userMeta[$key][0];
        }

        return "";

    }


}