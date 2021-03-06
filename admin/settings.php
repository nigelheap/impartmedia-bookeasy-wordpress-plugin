<?php
namespace Bookeasy\admin;

use Bookeasy\Base;
use Bookeasy\library\Request;

class Settings extends Base {

    private $tabs = [
        'sync' => 'Sync',
        'config' => 'Config',
        'categories' => 'Categories',
        'info' => 'Info',
    ];


    private $fields = [
        'environment' => [
            'type' => 'radios',
            'title' => 'Environment',
            'options' => [
                'production' => 'Production',
                'pvt' => 'Pre-Version Testing (PVT)'
            ]
        ],
        'vc_id' => [
            'type' => 'text',
            'title' => 'VC ID',
            'desc' => '',
        ],
        'posttype' => [
            'type' => 'posttype',
            'title' => 'Post Type',
            'desc' => 'The post type to store the bookeasy data',
        ],
        'taxonomy' => [
            'type' => 'taxonomy',
            'title' => 'Taxonomy',
            'desc' => 'The taxonomy to store the bookeasy posts in',
        ],
        /*
        'run_folder' => [
            'type' => 'text',
            'title' => 'Run Folder',
            'desc' => 'Path to folder to dump bookeasy.sync file in. eg /var/www/html/wp-content/uploads',
        ],
        */
        'accom_search_path' => [
            'type' => 'text',
            'title' => 'Accommodation Search Path',
            'desc' => 'Relative to home page',
        ],
        'accom_tabname' => [
            'type' => 'text',
            'title' => 'Accommodation Tab Name',
            'desc' => '',
        ],
        'tours_search_path' => [
            'type' => 'text',
            'title' => 'Tour Search Path',
            'desc' => 'Relative to home page',
        ],
        'tours_tabname' => [
            'type' => 'text',
            'title' => 'Tour Tab Name',
            'desc' => '',
        ],
        'bookingurl' => [
            'type' => 'text',
            'title' => 'Booking URL',
            'desc' => 'Full url including protocol',
        ],
        'confirmationurl' => [
            'type' => 'text',
            'title' => 'confirmation URL',
            'desc' => 'Full url including protocol',
        ],
        'itinerarycss' => [
            'type' => 'text',
            'title' => 'itinerary css',
            'desc' => 'Full url including protocol',
        ],
        'location_ids' => [
           'type' => 'text',
           'title' => "Location IDs",
           'desc' => 'Comma seperated list of location IDs to sync. If this is empty it will import all the operators. Eg 12,34,78 ',
        ],
        'notificaton_email' => [
            'type' => 'text',
            'title' => "Notification Email",
            'desc' => 'Email addresses to send the sync notifications. comma separated',
        ],
        'apikeys' => [
            'type' => 'textarea',
            'title' => 'Gadget Api Keys',
            'desc' => 'one per line eg:<br> domain.com|AS3245AsdNQwjhwekrh',
        ],
        'api_key' => [
            'type' => 'text',
            'title' => "Web Api Key",
            'desc' => 'Key for connecting to domain webapi.bookeasy.com.au',
        ],

        'maps_api_key' => [
            'type' => 'text',
            'title' => "Google Maps Api Key",
            'desc' => 'Google maps api, leave blank if you do not want bookeasy to include its version of the maps api',
        ]

    ];



    /**
     * Start up
     */
    public function __construct()
    {
        add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
        //add_action( 'admin_menu', array( $this, 'add_post_columns' ) );
        add_action( 'admin_init', array( $this, 'admin_init' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );

        $options = get_option( $this->optionGroup );
        if(!empty($options['posttype'])){

            $post_type = $options['posttype'];
            // ONLY  CUSTOM TYPE POSTS
            add_filter('manage_'.$post_type.'_posts_columns', array( 
                $this, 
                'post_column'
            ), 10);

            add_action('manage_'.$post_type.'_posts_custom_column', array(
                $this, 
                'posts_custom_column'
            ), 10, 2);

        }

    }


    /**
     * Add column header
     * @param  array $columns
     * @return  array
     */
    public function post_column($columns){
        $columns['bookeasy'] = __( 'Bookeasy', 'bookeasy' );
        $columns['bookeasy_cats'] = __( 'Categories', 'categories' );
        return $columns;
    }

    /**
     * Add call content
     * @param  array $column
     * @param  Int $post_id 
     * @return Null          
     */
    public function posts_custom_column($column, $post_id){
        if($column == 'bookeasy'){
            $operatorID = get_post_meta($post_id, 'bookeasy_OperatorID', true);
            echo '<a href="#update_operator" class="button-primary update_operator" data-operator-id="'.$operatorID.'">Update operator</a>';
        }

        if($column == 'bookeasy_cats'){
            $this->load_vars();

            $post_categories = get_the_terms( $post_id, $this->options['taxonomy']);
            $primary_cat = $this->primary_cat($post_id, $this->options['taxonomy']);
            $cats = array();

            foreach($post_categories as $c){
                $cat = get_category( $c );
                $cats[] = $primary_cat == $cat->term_id ? '<strong>'.$cat->name.'</strong>' : $cat->name;
            }

            echo implode(', ', $cats);
        }
    }



    public function primary_cat($postId, $taxonomy){
        
        if(class_exists('WPSEO_Primary_Term')){
            $primary = new \WPSEO_Primary_Term($taxonomy, $postId);
            return $primary->get_primary_term();
        }

        return 0;
    }


    /**
     * Add options page
     */
    public function add_plugin_page(){
        // This page will be under "Settings"
        add_menu_page(
            'Bookeasy Manager',
            'Bookeasy',
            'edit_posts',
            'bookeasy',
            array( $this, 'page' ),
            'dashicons-calendar-alt',
            30
        );
    }


    public function admin_enqueue_scripts(){
        // Add our CSS Styling
        wp_enqueue_style( 'bookeasy-options', plugins_url('../css/admin.css', __FILE__), array(), false, false);
        wp_enqueue_script( 'bookeasy-options', plugins_url('../js/admin.js', __FILE__), array(), false, false);

        wp_localize_script( 'bookeasy-options', 'bookeasyAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' )));   
    }


    public function page( $active_tab = '' ) {
        $this->load_vars();

    ?>
        <!-- Create a header in the default WordPress 'wrap' container -->
        <div class="wrap">
            <div id="icon-themes" class="icon32"></div>
            <h2>Bookeasy</h2>
            <?php settings_errors(); ?>
            <?php $active_tab = Request::get('tab', 'sync'); ?>
            <h2 class="nav-tab-wrapper">
                <?php foreach($this->tabs as $key => $tab): ?>
                <a href="?page=<?php echo $this->nameSpace; ?>&tab=<?php echo $key; ?>" class="nav-tab <?php echo $active_tab == $key ? 'nav-tab-active' : ''; ?>"><?php echo $tab; ?></a>
                <?php endforeach; ?>
            </h2>
            <div>&nbsp;</div>
            <?php
                switch ($active_tab):
                    case 'sync':
                        $this->sync_page();
                    break;
                    case 'config':
                        $this->settings_page();
                    break;
                    case 'categories':
                        $this->category_page();
                    break;
                    case 'info':
                        $this->info_page();
                    break;
                endswitch;
            ?>
        </div><!-- /.wrap -->
    <?php
    } // end

    /**
     * Pages
     */

    public function info_page()
    {  ?>
        <div class="wrap">

            <div class="postbox custom-form custom-form-bookeasy custom-results">
                <div class="inner">
                    <h3>Short Codes</h3>
                    <div class="inner-inner">
                        <h2>Horizonal Search</h2>
                        <p>
                            <pre><code>[bookeasy_horizontal_search]</code></pre>
                            <pre><code>[bookeasy_horizontal_search tours="true" accom="true" tours_tabname="Tours" accom_tabname="Accom" accom_search_path="/accommodation" ]</code></pre>
                            <pre><code>[bookeasy_horizontal_search tours_tabname="Tours" tours_search_path="/accommodation"]</code></pre>
                            <br>
                        </p>

                        <h2>Single item</h2>
                        <p>
                            This will pick up the tour/accom type based on meta data in the post
                            <pre><code>[bookeasy_single]</code></pre>
                            <br>
                        </p>

                        <h2>Accom Results</h2>
                        <p>
                            <pre><code>[bookeasy_results]</code></pre>
                            <div>note: defaults to period = 3 and adults = 2</div>
                            <pre><code>[bookeasy_results period="3" adults="2"]</code></pre>
                            <pre><code>[bookeasy_results force_accom_type="Chalets/Villas/Cottages"]</code></pre>
                            <pre><code style="word-break: break-all;">[bookeasy_results limit_locations="Augusta,Busselton,Carbunup River,Cowaramup,Dunsborough,Gnarabup,Gracetown,Hamelin Bay,Karridale,Margaret River,Metricup,Prevelly,Rosa Brook,Witchcliffe,Yallingup"]</code></pre>
                            <br>
                        </p>

                        <h2>Tour Results</h2>
                        <p>
                            <pre><code>[bookeasy_tour_results]</code></pre>
                            <div>note: defaults to period = 1 and adults = 1</div>
                            <pre><code>[bookeasy_tour_results period="1" adults="2"]</code></pre>
                            <pre><code>[bookeasy_tour_results force_tour_type="Whale Watching"]</code></pre>
                            <br>
                        </p>

                        <h2>Cart</h2>
                        <p>
                            <pre><code>[bookeasy_cart]</code></pre>
                            <br>
                        </p>

                        <h2>Booking page</h2>
                        <p>
                            <pre><code>[bookeasy_book]</code></pre>
                            <pre><code>[bookeasy_book booked_by="Online"]</code></pre>
                            <br>
                        </p>

                        <h2>Confirmation page</h2>
                        <p>
                            <pre><code>[bookeasy_confirm]</code></pre>
                            <pre><code>[bookeasy_confirm pdf_link_text="Download your itinerary PDF now."]</code></pre>
                            <pre><code>[bookeasy_confirm thank_you_text=""]</code></pre>
                            <br>
                        </p>

                        <h2>Bookeasy Field</h2>
                        <p>
                            <pre><code>[bookeasy_field key="ShortDetails.Description"]</code></pre>
                            <pre><code>[bookeasy_field key="Location"]</code></pre>
                            <pre><code>[bookeasy_field key="Directions"]</code></pre>
                            <pre><code>[bookeasy_field key="Cancellation"]</code></pre>
                            <pre><code>[bookeasy_field key="ShortDetails.CancellationPolicy"]</code></pre>
                            <br>
                        </p>

                        <h2>Bookeasy Content</h2>
                        <p>
                            <pre><code>[bookeasy_content]</code></pre>
                            <br>
                        </p>

                    </div>


                    <h3>Helpers</h3>
                    <div class="inner-inner">
                        <p>
                            ... Soon
                            <pre><code>$rooms = \Bookeasy\frontend\Helpers::rooms($operatorID, get_the_ID());</code></pre>
                            <br />
                            <br />
                        </p>
                    </div>
                </div>
            </div>

        </div>
        <?php
    }


    public function sync_page(){
        // Set class property
        $email = $this->current_user_email();

        $queue = Request::post('queue_' . $this->nameSpace, false);
        $run_folder = rtrim(get_home_path() . 'wp-content/uploads', DIRECTORY_SEPARATOR);
        $file = $run_folder . DIRECTORY_SEPARATOR . 'bookeasy.sync';
        $success = false;
        if(!empty($queue)){
            $success = file_put_contents($file, $email);
        }

        ?>
        <div class="wrap">
            <?php if(!empty($run_folder) && $success): ?>
                <div class="updated settings-error notice is-dismissible">
                    <p><strong>Sync bookeasy operators on next cron run (< 1 minute)</strong></p>
                    <button type="button" class="notice-dismiss">
                        <span class="screen-reader-text">Dismiss this notice.</span>
                    </button>
                </div>
            <?php endif; ?>
            <?php if(file_exists($file) && $content = file_get_contents($file)): ?>
                <div class="updated settings-error notice">
                    <p><strong>Sync queued for <?php echo $content; ?></strong></p>
                </div>
            <?php endif; ?>
            <form method="post" id="sync-form" action="" class="postbox custom-form">
                <div class="inside">
                    <h3>Queue sync of bookeasy operators</h3>
                    <p>Once complete an email will be sent to <?php echo $email; ?></p>

                    <input type="hidden" name="queue_<?php echo $this->nameSpace; ?>" value="1">
                    <?php
                    submit_button('Queue Sync');
                    ?>
                </div>
            </form>


        </div>
        <?php
    }

    public function settings_page(){ ?>
        <div class="wrap">
            <form method="post" action="options.php" class="postbox custom-form">
                <div class="inside custom-form-settings">
                <?php
                    settings_fields( $this->optionGroup );
                    do_settings_sections( $this->settingsName );
                    submit_button();
                ?>
                <div>
            </form>
        </div>

    <?php }


    public function category_page(){
        // Set class property

        $this->load_vars();

        ?>
        <div class="wrap">
            <?php if(!empty($this->categoriesSync['bookeasy_cats'])): ?>
            <form method="post" action="options.php" class="postbox custom-form">
                <div class="inside custom-form-settings custom-mapping">
                <?php
                    // This prints out all hidden setting fields
                    settings_fields( $this->optionGroupCategories );
                    do_settings_sections( $this->settingsNameCategories );
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
     * save the page
     */
    public function save()
    {

        if(empty($_POST)){
            return;
        }

        if(Request::get('page') != $this->nameSpace){
            return;
        }


        switch(Request::get('tab')){
            case 'settings':
                update_option($this->optionGroup, Request::post($this->optionGroup));
            break;
            case 'categories':
                update_option($this->optionGroupCategories, Request::post($this->optionGroupCategories));
            break;
        }

        $this->load_vars();
    }

    /**
     * Register and add settings
     */
    public function admin_init()
    {

        // If the theme options don't exist, create them.
        if( false == get_option( $this->optionGroup ) ) {
            add_option( $this->optionGroup);
        } // end if

        // If the theme options don't exist, create them.
        if( false == get_option( $this->optionGroupCategories ) ) {
            add_option( $this->optionGroupCategories);
        } // end if

        // If the theme options don't exist, create them.
        if( false == get_option( $this->optionGroupCategoriesSync ) ) {
            add_option( $this->optionGroupCategoriesSync);
        } // end if

        //$this->save();
        /**
         * Settings page
         */
        add_settings_section(
            'Bookeasy_options', // ID
            'Settings', // Title
            array( $this, 'print_section_info' ), // Callback
            $this->settingsName // Page
        );

        foreach($this->fields as $id => $field){

            add_settings_field(
                $id, // ID
                $field['title'], // Title
                array($this, 'field_callback'), // Callback
                $this->settingsName, // Page
                'Bookeasy_options', // Section
                array_merge(array('id' => $id), $field)
            );

        }

        register_setting(
            $this->optionGroup, // Option group
            $this->optionGroup // Option name
        );

        /**
         * Category page
         */
        add_settings_section(
            'Bookeasy_categories_settings', // ID
            'Linked Categories', // Title
            array( $this, 'print_cat_section_info' ), // Callback
            $this->settingsNameCategories // Page
        );

        add_settings_field(
            'mapping', // ID
            'Mapping', // Title
            array( $this, 'mapping_callback' ), // Callback
            $this->settingsNameCategories, // Page
            'Bookeasy_categories_settings' // Section
        );

        register_setting(
            $this->optionGroupCategories, // Option group
            $this->optionGroupCategories // Option name
        );


    }


    public function field_callback($args)
    {

        $id = $args['id'];
        $desc = $args['desc'];

        switch($args['type']){

            case 'text':
                printf(
                    '<input type="text" id="'.$id.'" size="60" name="'.$this->optionGroup.'['.$id.']" value="%s" />',
                    isset( $this->options[$id] ) ? esc_attr( $this->options[$id]) : ''
                );
                echo '<p class="description">'.$desc.'</p>';
            break;
            case 'textarea':
                echo '<textarea id="'.$id.'" name="'.$this->optionGroup.'['.$id.']" style="width:90%; height:100px;">'.
                (isset( $this->options[$id] ) ? esc_attr( $this->options[$id]) : '')
                .'</textarea>';
                echo '<p class="description">'.$desc.'</p>';
            break;
            case 'taxonomy':
                $taxonomies = get_taxonomies(array('public' => true), 'objects');
                echo '<select id="'.$id.'" name="'.$this->optionGroup.'['.$id.']">';
                foreach ( $taxonomies as $taxonomy ) {
                    $selected = ($taxonomy->name == $this->options[$id] ? ' selected="selected"' : '');
                    echo '<option'.$selected.' value="'.$taxonomy->name.'">' . $taxonomy->label . '</option>';
                }
                echo '</select>';

            break;
            case 'posttype':
                $post_types = get_post_types( '', 'names' );
                echo '<select id="'.$id.'" name="'.$this->optionGroup.'['.$id.']">';
                foreach ( $post_types as $post_type ) {
                    $selected = ($post_type == $this->options[$id] ? ' selected="selected"' : '');
                    echo '<option'.$selected.' value="'.$post_type.'">' . $post_type . '</option>';
                }
                echo '</select>';
            break;
            case 'radios':
                foreach ( $args['options'] as $value => $label) {
                    $checked = ($value == $this->options[$id] ? ' checked="checked"' : '');
                    echo '<label><input '.$checked.' name="'.$this->optionGroup.'['.$id.']" type="radio" value="'.$value.'">' . $label . '</label>&nbsp;&nbsp;';
                }
            break;

        }

    }

    /**
     * Get the settings option array and print one of its values
     */
    public function mapping_callback()
    {

        $bookeasyCats = $this->categoriesSync['bookeasy_cats'];
        if(empty($bookeasyCats)){
            echo 'Sync first';
            return;
        }
        sort($bookeasyCats);
        $terms = get_terms(array($this->options['taxonomy']), array('hide_empty'=>false));
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
                            $attrs = 'name="'.$this->optionGroupCategories.'['.$bookeasyCat.']"';
                            $selected = isset($this->categories[$bookeasyCat]) ? $this->categories[$bookeasyCat] : 0;
                            echo $this->buildSelect($terms, $attrs, $selected);
                        ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        <?php
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
    public function print_cat_section_info()
    {
        print 'Please choose where the bookeasy Categories will be linked.';
    }

    /**
     * @param $terms
     * @param $attrs
     * @param null $selected
     * @return string
     */
    public function buildSelect($terms, $attrs, $selected = null)
    {
        $output ="<select ".$attrs.">";
        $output .="<option value='0'>Please Select</option>";
        foreach($terms as $term){
            $sel = ($term->term_id == $selected ? 'selected="selected"' : '');
            $output .="<option value='".$term->term_id."' ".$sel.">".$term->name."</option>";
        }
        $output .="</select>";

        return $output;

    }

    /**
     * Get current user email
     * @return string
     *
     */
    function current_user_email()
    {
        $current_user =  wp_get_current_user();
        return $current_user->user_email;
    }


}

