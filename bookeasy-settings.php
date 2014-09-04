<?php


class BookeasyOperators_Settings
{
    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;

    public $optionGroup = 'BookeasyOperators_options';

    /**
     * Start up
     */
    public function __construct()
    {
        add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
        add_action( 'admin_init', array( $this, 'page_init' ) );
    }

    /**
     * Add options page
     */
    public function add_plugin_page()
    {
        // This page will be under "Settings"
        add_options_page(
            'Settings Admin', 
            'Bookeasy Operators', 
            'manage_options', 
            'bookeasy-operators', 
            array( $this, 'create_admin_page' )
        );
    }

    /**
     * Options page callback
     */
    public function create_admin_page()
    {
        // Set class property
        $this->options = get_option($this->optionGroup);

        // Add our CSS Styling
        wp_enqueue_style( 'bookeasy-options', plugins_url('css/bookeasy-options.css', __FILE__) );
        wp_enqueue_script( 'bookeasy-options', plugins_url('js/bookeasy-options.js', __FILE__) );
        ?>
        <div class="wrap">
            <?php screen_icon(); ?>
            <h2>Bookeasy</h2>  
            <ul class="subsubsub">
                <li class="settings"><a href="options-general.php?page=bookeasy-operators" class="current">Settings</a> |</li>
                <li class="cats"><a href="options-general.php?page=bookeasy-categories">Categories</a></li>
            </ul>

            <form method="post" action="options.php" class="postbox custom-form">
                <div class="inside custom-form-settings">
                <?php
                    // This prints out all hidden setting fields
                    settings_fields( 'BookeasyOperators_options' );   
                    do_settings_sections( 'bookeasy-operators' );
                    submit_button(); 
                    
                ?>
                </div>
            </form>


            <form method="post" target="bookeasy-results" id="sync-form" action="<?php echo plugins_url('bookeasy-sync.php', __FILE__); ?>" class="postbox custom-form">
                <div class="inside">
                <h3>Sync Bookeasy Operators</h3>
                <?php
                    submit_button('Sync Now'); 
                ?>
                </div>
            </form>



            <div class="postbox custom-form custom-results" id="sync-results">
                <div class="inner">
                    <h3>Sync Results</h3>
                    <div class="inner-inner">
                        <div id="sync-message"></div>
                        <iframe height="50" name="bookeasy-results" width="100%" frameborder="0"></iframe>
                    </div>
                </div>
            </div>
            
        </div>
        <?php
    }

    /**
     * Register and add settings
     */
    public function page_init()
    {        
        register_setting(
            $this->optionGroup, // Option group
            $this->optionGroup, // Option name
            array( $this, 'sanitize' ) // Sanitize
        );

        add_settings_section(
            'BookeasyOperators_options_settings', // ID
            'End Point Settings', // Title
            array( $this, 'print_section_info' ), // Callback
            'bookeasy-operators' // Page
        );  

        add_settings_field(
            'url', // ID
            'Url', // Title 
            array( $this, 'url_callback' ), // Callback
            'bookeasy-operators', // Page
            'BookeasyOperators_options_settings' // Section           
        );      

        add_settings_section(
            'BookeasyOperators_options_posttypesettings', // ID
            'Post Type Settings', // Title
            array( $this, 'print_posttype_section_info' ), // Callback
            'bookeasy-operators' // Page
        );  

        add_settings_field(
            'posttype', // ID
            'Post Type', // Title 
            array( $this, 'posttype_callback' ), // Callback
            'bookeasy-operators', // Page
            'BookeasyOperators_options_posttypesettings' // Section           
        );    


        add_settings_field(
            'taxonomy', // ID
            'Taxonomy', // Title 
            array( $this, 'taxonomy_callback' ), // Callback
            'bookeasy-operators', // Page
            'BookeasyOperators_options_posttypesettings' // Section           
        );   
        

    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize( $input )
    {
        $new_input = array();


        if( isset( $input['url'] ) )
            $new_input['url'] = sanitize_text_field( $input['url'] );

        if( isset( $input['vc_id'] ) )
            $new_input['vc_id'] = absint( $input['vc_id'] );

        if( isset( $input['posttype'] ) )
            $new_input['posttype'] = sanitize_text_field( $input['posttype'] );

        if( isset( $input['taxonomy'] ) )
            $new_input['taxonomy'] = sanitize_text_field( $input['taxonomy'] );

        return $new_input;
    }

    /** 
     * Print the Section text
     */
    public function print_section_info(){
        print 'Enter your settings below:';
    }


    /** 
     * Print the Section text
     */
    public function print_posttype_section_info(){
        print 'Which post type and category do you want to use:';
    }


    /** 
     * Get the settings option array and print one of its values
     */
    public function url_callback(){
        printf(
            '<input type="text" id="url" name="'.$this->optionGroup.'[url]" value="%s" />',
            isset( $this->options['url'] ) ? esc_attr( $this->options['url']) : ''
        );
        echo '<p class="description">Example : http://sjp.impartmedia.com/be/getOperatorsInformation?q=10 10 being the vc_id</p>';
    }


    public function posttype_callback(){
        $post_types = get_post_types( '', 'names' ); 
        echo '<select id="posttype" name="'.$this->optionGroup.'[posttype]">';
        foreach ( $post_types as $post_type ) {
            $selected = ($post_type == $this->options['posttype'] ? ' selected="selected"' : '');
            echo '<option'.$selected.' value="'.$post_type.'">' . $post_type . '</option>';
        }
        echo '</select>';
    }


    public function taxonomy_callback(){
        $taxonomies = get_taxonomies(array('public' => true), 'objects');
        echo '<select id="taxonomy" name="'.$this->optionGroup.'[taxonomy]">';
        foreach ( $taxonomies as $taxonomy ) {
            $selected = ($taxonomy->name == $this->options['taxonomy'] ? ' selected="selected"' : '');
            echo '<option'.$selected.' value="'.$taxonomy->name.'">' . $taxonomy->label . '</option>';
        }
        echo '</select>';
    }

}

if( is_admin()){
    new BookeasyOperators_Settings();
}
