<?php
/**
 * NTG Module Loader
 *
 * This file registers all modules and loads the selected modules as needed
 * 
 *
 * @package      NTG_Module_Loader
 * @author       Nick the Geek <NicktheGeek@NickGeek.com>
 * @copyright    Copyright (c) 2012, Nick Croft
 * @license      <a href="http://opensource.org/licenses/gpl-2.0.php" rel="nofollow">http://opensource.org/licenses/gpl-2.0.php</a> GNU Public License
 * @since        1.0
 * @alter        1.11.2012
 *
 */

/**
 * Loads the module files
 * 
 * @param array $modules 
 */
function ntg_add_modules( $modules ){
    foreach( $modules as $module ){
        require_once GMP_PLUGIN_DIR .'/modules/'. $module .'/module.php';
    }
}

/**
 * Initialize module loader
 *
 * @since 1.0.0
 */
function ntg_module_loader() {
    
    $modules = array();
    $modules = apply_filters ( 'ntg_module_loader' , $modules );
    
    //print_r( $admin_pages );
    
    
                
    new NTG_Module_Loader( $modules );
        
    
}
add_action( 'plugins_loaded', 'ntg_module_loader', 5 );


/**
 * Builds module page and loads modules as needed.
 *
 *
 * @since 1.0.0
 */
class NTG_Module_Loader {

    private $_modules;
    private $_load;
    private $_genesis_files;
    private $_setting_filter;
    
    /**
     * Sets variables and initializes the appropriate methods to create and maybe load modules
     * 
     * @param array $modules 
     */
    function __construct( $modules ){
        
        $this->_modules       = $modules;
        $this->_load          = array();
        $this->_genesis_files = array();
        $this->_setting_filter = 'gmp_options';
                
        if( is_admin() ) {
            add_filter( 'ntg_settings_builder', array( $this, 'add_module' ) );
            add_action( 'genesis_init', array( $this, 'init' ), 15 );
        }
        
        foreach( $modules as $module )
            $this->maybe_load_module( $module['type'], $module['id'] );
        
        add_action( 'init', array( $this, 'gmp_init' ), 9998 );
        
    }
    
    /**
     * Loads the Admin Builder Class if it has not been loaded already
     */
    function init(){
        
        if( ! class_exists( 'NTG_Theme_Settings_Builder' ) )
            require_once(GMP_PLUGIN_DIR . '/classes/admin-builder/admin-builder.php');
        
    }
    
    /**
     * Creates gmp_init hook near end of WordPress init
     */
    function gmp_init() {
        do_action( 'gmp_init' );
    }
    
    /**
     * Adds modules to the Settings page. 
     * Sets "required" modules so they cannot be disabled.
     * 
     * @param array $admin_pages
     * @return array 
     */
    function add_module( $admin_pages ){
        
        $defaults  = array();
        $sanatize  = array();
        $metaboxes = array();
        
        foreach( $this->_modules as $module ){
            
            switch ( $module['type'] ) {
                case 'required':

                    $default = '1';
                    $name    = $module['name'];
                    $type    = 'title';

                    break;
                case 'default':

                    $default = '1';
                    $name    = sprintf( __( 'Activate the %s module?', 'gmp' ), $module['name'] );
                    $type    = 'checkbox';

                    break;
                default:

                    $default = '';
                    $name    = sprintf( __( 'Activate the %s module?', 'gmp' ), $module['name'] );
                    $type    = 'checkbox';

                    break;
            }

            $id = 'ntg_'. $module['id'] . '_module';
            
            $defaults[$id] = $default;
            $sanatize[]    = $id;
        
            $metaboxes[]   = array(
                        'name' => $name,
                        'desc' => $module['description'],
                        'id'   => $id,
                        'type' => $type
                        );
        
        }
        
               
        $admin_pages[] = array(
            'settings' => array(
                'page_id'          => 'ntg_module_loader',
                'menu_ops'         => array(
                    'submenu' => array(
                        'parent_slug' => 'genesis',
                        'page_title'  => __('Genesis Media Project', 'gmp'),
                        'menu_title'  => __('Media Project', 'gmp'),
                        'capability'  => 'manage_options',
                    )
                ),
                'page_ops'         => array(
                    
                ),
                'settings_field'   => 'ntg_modules',
                'default_settings' => $defaults,
                
            ),
            'sanatize'   => array(
                'no_html'   => $sanatize
            ),
            'help'       => array(
                
            ),
            'meta_boxes' => array(
                
                'id'         => 'genesis_media_project_meta_box',
                'title'      => __( 'Select Modules', 'gmp' ),
                'context'    => 'main',
                'priority'   => 'high',/**/
                'show_names' => true, // Show field names on the left
                'fields'     => $metaboxes
                
                )
	);
	
	return $admin_pages;
        
    }
    
    /**
     * Checks modules to see if they are required or selected in the Settings
     * Sets $this->_load with the ID for the load_module() method to use.
     * 
     * @param string $type
     * @param string $id 
     */
    function maybe_load_module( $type, $id ){
        
        if( 'required' == $type ){
            
            add_action( 'after_setup_theme', array( $this, 'load_module' ) );
            $this->_load[] = $id; 
            
        }
        
        elseif( $this->get_option( 'ntg_'. $id . '_module', 'ntg_modules' ) ){
            
            add_action( 'after_setup_theme', array( $this, 'load_module' ) );
            $this->_load[] = $id; 
            
        }
        
               
        
    }
    
    /**
     * Loads the modules that are setup in the maybe_load_module() method
     * Loads any classes required by the module if they have not been loaded
     * Loads all registered module files
     */
    function load_module(){
		
		if( $this->not_has_genesis() )
			return;
        
        foreach( $this->_modules as $module ){
            
            if( in_array( $module['id'], $this->_load ) ) {
        
                foreach( $module['uses'] as $folder => $requires ){
                    switch ( $folder ) {
                        case 'classes':

                            foreach( $requires as $required ){

                                    if( 'meta-box-builder' == $required ) 
                                        add_action( 'gmp_init', array( $this, 'add_cmb_class' ) );

                                    elseif( 'post-types-taxonomies' == $required ) {

                                        if ( !class_exists( 'ntg_Post_Type_Taxonomies' ) ) 
                                            require_once( GMP_PLUGIN_DIR . '/classes/post-types-taxonomies/ntg_Post_Type_Taxonomies.php' );

                                    }

                                    elseif( 'admin-builder' == $required ) {

                                        /*
                                        if( ! class_exists( 'NTG_Theme_Settings_Builder' ) && is_admin() )
                                            require_once(GMP_PLUGIN_DIR . '/classes/admin-builder/admin-builder.php');/**/

                                    }

                            }

                            break;

                        default:
                            break;
                    }

                }
                
                //print_r( $module );
                foreach( $module['include'] as $scope => $files ){
                    
                    //print_r( $file );

                    
                        
                        switch( $scope ){
                            case 'admin':

                                if( is_admin() )
                                    foreach( $files as $file )
                                        require_once( $module['directory'] . '/' . $file );

                                break;
                            case 'output':

                                if( ! is_admin() )
                                    foreach( $files as $file )
                                        require_once( $module['directory'] . '/' . $file );

                                break;
                            default:

                                foreach( $files as $file )
                                    require_once( $module['directory'] . '/' . $file );

                                break;

                        }

                    //}
                }
                
            }
            
        }
        
    }
    
    /**
     * Loads the cmb_Meta_Box class if it has not been loaded already. 
     * Resets the order of the Genesis inpost meta boxes to ensure ability to 
     * set the order of the metaboxes.
     */
    function add_cmb_class() {
        
        if ( !class_exists( 'cmb_Meta_Box' ) && is_admin() ) 
            require_once( GMP_PLUGIN_DIR . '/classes/meta-box-builder/init.php' );
        
        remove_action( 'admin_menu', 'genesis_add_inpost_seo_box' );
        add_action( 'admin_menu', 'genesis_add_inpost_seo_box' );
        
        remove_action( 'admin_menu', 'genesis_add_inpost_layout_box' );
        add_action( 'admin_menu', 'genesis_add_inpost_layout_box' );
        
    }
    
    /**
     * Pull an option from the database, return value
     *
     * @since 0.1
     */
    function get_option( $key, $setting = null ) {

        // get setting
        $setting = $setting ? $setting : $this->_setting_field;

        // setup caches
        static $settings_cache = array( );
        static $options_cache = array( );

        // Check options cache
        if ( isset( $options_cache[$setting][$key] ) ) {

            // option has been cached
            return $options_cache[$setting][$key];
        }

        // check settings cache
        if ( isset( $settings_cache[$setting] ) ) {

            // setting has been cached
            $options = apply_filters( $this->_setting_filter, $settings_cache[$setting], $setting );
        } else {

            // set value and cache setting
            $options = $settings_cache[$setting] = apply_filters( $this->_setting_filter, get_option( $setting ), $setting );
        }

        // check for non-existent option
        if ( !is_array( $options ) || !array_key_exists( $key, ( array ) $options ) ) {

            // cache non-existent option
            $options_cache[$setting][$key] = '';

            return '';
        }

        // option has been cached, cache option
        $options_cache[$setting][$key] = stripslashes( wp_kses_decode_entities( $options[$key] ) );

        return $options_cache[$setting][$key];
    }

    /**
     * Pull an option from the database, echo value
     *
     * @since 0.1
     */
    function option( $hook = null, $field = null ) {
        echo gsv_get_option( $hook, $field );
    }
	
	/**
	 * Tests to see if genesis() and by proxy other associated Genesis functions
	 * are available to prevent function not exist errors in specific cases
	 * where Genesis or a Genesis child theme is active, but the theme is not
	 * being loaded, such as when using Premise or a mobile theme plugin.
	 * 
	 * @since 0.9.0.2
	 * 
	 * @return boolean TRUE if genesis() NOT available, else FALSE 
	 */
	function not_has_genesis() {
		
		if( function_exists( 'genesis' ) )
			return;
		
		return true;
		
	}
    
}
?>
