<?php
namespace Bookeasy\admin;

class Categories
{
    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;
    private $settingsOptions;
    private $settingsOptionsSync;

    public $optionGroup = 'BookeasyOperators_categories';
    public $optionGroupSync = 'BookeasyOperators_categoriessync';
    public $optionGroupSettings = 'BookeasyOperators_options';

    public $version = '1.0.3';

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
            'Bookeasy Categories', 
            'manage_options', 
            'bookeasy-categories', 
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
        $this->settingsOptions = get_option($this->optionGroupSettings);
        $this->settingsOptionsSync = get_option($this->optionGroupSync);

        // Add our CSS Styling
        wp_enqueue_style( 'bookeasy-options', plugins_url('../css/bookeasy-options.css', __FILE__) , array(), $this->version);
        wp_enqueue_script( 'bookeasy-options', plugins_url('../js/bookeasy-options.js', __FILE__) , array(), $this->version);
        ?>
        <div class="wrap">
            <?php screen_icon(); ?>
            <h2>Bookeasy</h2>           
            <ul class="subsubsub">
                <li class="settings"><a href="options-general.php?page=bookeasy-operators">Settings</a> |</li>
                <li class="cats"><a href="options-general.php?page=bookeasy-categories" class="current">Categories</a></li>
            </ul>


            <?php if(!empty($this->settingsOptionsSync['bookeasy_cats'])): ?>
            <form method="post" action="options.php" class="postbox custom-form">
                <div class="inside custom-form-settings custom-mapping">
                <?php
                    // This prints out all hidden setting fields
                    settings_fields( 'BookeasyOperators_categories' );   
                    do_settings_sections( 'bookeasy-categories' );
                    submit_button(); 
                    
                ?>
                </div>
            </form>
            <?php endif; ?>

            <form method="post" target="bookeasy-results" id="sync-form" action="<?php echo plugins_url('../api/sync.php', __FILE__); ?>?type=cats" class="postbox custom-form">
                <div class="inside">
                    <h3>Sync Bookeasy Categories</h3>
                    <?php
                        submit_button('Sync Categories'); 
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
            $this->optionGroup // Option name
        );

        add_settings_section(
            'BookeasyOperators_categories_settings', // ID
            'Linked Categories', // Title
            array( $this, 'print_section_info' ), // Callback
            'bookeasy-categories' // Page
        );  

        add_settings_field(
            'mapping', // ID
            'Mapping', // Title 
            array( $this, 'mapping_callback' ), // Callback
            'bookeasy-categories', // Page
            'BookeasyOperators_categories_settings' // Section           
        );      

    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     * @return array
     */
    public function sanitize( $input )
    {
        //var_dump($input);
        $new_input = array();

        if( isset( $input['mapping'] ) )
            $new_input['mapping'] = $input['mapping'];


        return $new_input;
    }

    /** 
     * Print the Section text
     */
    public function print_section_info()
    {
        print 'Please choose where the bookeasy Categories will be linked.';
    }


    /** 
     * Get the settings option array and print one of its values
     */
    public function mapping_callback(){

        $bookeasyCats = $this->settingsOptionsSync['bookeasy_cats'];
        if(empty($bookeasyCats)){
            echo 'Sync first';
            return;
        }
        sort($bookeasyCats);
        $terms = get_terms(array($this->settingsOptions['taxonomy']), array('hide_empty'=>false));
        ?> 
            <table>
                <?php
                    $title = '';
                    foreach($bookeasyCats as $bookeasyCat): 
                    $name = explode('|', $bookeasyCat);
                    if($title != $name[0]): 
                ?>
                <tr>
                    <td colspan="2">
                        <h4><?php echo (isset($name[0]) ? $name[0] : ''); ?></h4>
                    </td>
                </tr>
                <?php $title = $name[0]; endif; ?>
                <tr>
                    <td><?php echo (isset($name[1]) ? $name[1] : $bookeasyCat); ?></td>
                    <td>
                        <?php 
                            $attrs = 'name="'.$this->optionGroup.'['.$bookeasyCat.']"';
                            $selected = isset($this->options[$bookeasyCat]) ? $this->options[$bookeasyCat] : 0;
                            echo $this->buildSelect($terms, $attrs, $selected); 
                        ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        <?php
    }

    public function buildSelect($terms, $attrs, $selected = null){
        $output ="<select ".$attrs.">";
        $output .="<option value='0'>Please Select</option>";
        foreach($terms as $term){
            $sel = ($term->term_id == $selected ? 'selected="selected"' : '');
            $output .="<option value='".$term->term_id."' ".$sel.">".$term->name."</option>";
        }
        $output .="</select>";

        return $output;

    }


}


