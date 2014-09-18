<?php


class BookEasy_Request{
    

    /**
     * Returns a named GET parameter value.
     * If a parameter with the specified name does not exist in GET, returns <em>NULL</em> or a value 
     * specified in the $default parameter.
     * @documentable
     * @see get() get() function
     * @param string $name Specifies the parameter name.
     * @param mixed $default Specifies a default value.
     * @return mixed Returns the POST parameter value, NULL or default value.
     */
    public static function get( $name, $default = null )
    {
        if (array_key_exists($name.'_x', $_GET) && array_key_exists($name.'_y', $_GET))
            return true;

        if ( !array_key_exists($name, $_GET) )
            return $default;

        return $_GET[$name];
    }

    /**
     * Returns a named POST parameter value.
     * If a parameter with the specified name does not exist in POST, returns <em>NULL</em> or a value 
     * specified in the $default parameter.
     * @documentable
     * @see post() post() function
     * @param string $name Specifies the parameter name.
     * @param mixed $default Specifies a default value.
     * @return mixed Returns the POST parameter value, NULL or default value.
     */
    public static function post( $name, $default = null )
    {
        if (array_key_exists($name.'_x', $_POST) && array_key_exists($name.'_y', $_POST))
            return true;

        if ( !array_key_exists($name, $_POST) )
            return $default;

        return $_POST[$name];
    }

    /**
     * Returns a named POST parameter value.
     * If a parameter with the specified name does not exist in POST, returns <em>NULL</em> or a value 
     * specified in the $default parameter.
     * @documentable
     * @see post() post() function
     * @param string $name Specifies the parameter name.
     * @param mixed $default Specifies a default value.
     * @return mixed Returns the POST parameter value, NULL or default value.
     */
    public static function server( $name, $default = null )
    {

        if ( !array_key_exists($name, $_SERVER) )
            return $default;

        return $_SERVER[$name];
    }


    public static function req( $name, $default = null )
    {

        if ( !array_key_exists($name, $_REQUEST) )
            return $default;

        return $_REQUEST[$name];
    }

    /**
     * Returns a name of the User Agent.
     * If user agent data is not available, returns NULL.
     * @documentable
     * @return mixed Returns the user agent name or NULL.
     */
    public static function getUserAgent()
    {
        return isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : null;
    }



    /**
     * Returns the URL of the current request
     */
    public static function getRequestUri()
    {
        $provider = 'REQUEST_URI';

        if ( $provider !== null )
            return getenv( $provider );
        else
        {
            // Pick the provider from the server variables
            //
            $providers = array( 'REQUEST_URI', 'PATH_INFO', 'ORIG_PATH_INFO' );
            foreach ( $providers as $provider )
            {
                $val = getenv( $provider );
                if ( $val != '' )
                    return $val;
            }
        }
        
        return null;
    }



    public static function get_relative($url){

        $url = parse_url($url);
        return $url['path'];
    }


}