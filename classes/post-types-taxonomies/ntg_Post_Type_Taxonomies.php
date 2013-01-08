<?php
/**
 * NTG Post Types and Taxonomies Creator
 *
 * This file registers all post types and taxonomies
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
 * Registers Post Types and Taxonomies
 *
 * @author Nick
 */
class ntg_PostTypes_Taxonomies {
    
    private $_postTypes;
    private $_taxonomies;
    
    /**
     * Sets up the variables for _PostTypes and _taxonomies
     * Initializes the methods to create the post type or taxonomy as needed
     * 
     * @param array $args 
     */
    function __construct( $args ){
        
        $this->_postTypes  = isset( $args['postTypes'] ) ? $args['postTypes'] : '';
        $this->_taxonomies = isset( $args['taxonomies'] ) ? $args['taxonomies'] : '';
        
        if( is_array( $this->_postTypes ) ) 
                add_action( 'init', array( $this, 'postType_builder' ) );

        
        if( is_array( $this->_taxonomies ) )
                add_action( 'init', array( $this, 'taxonomy_builder' ) );
        
    }
    
    /**
     * Builds the post types
     * 
     * @uses $this->register_post_type()
     */
    function postType_builder(){
        
        foreach( $this->_postTypes as $title => $args )
            $this->register_post_type ( $title, $args );
        
        
    }
    
    /**
     * Registers a post type with default values which can be overridden as needed.
     * 
     * @author Nick the Geek
     * @link http://DesignsByNicktheGeek.com
     * 
     * @uses sanitize_title() WordPress function that formats text for use as a slug
     * @uses wp_parse_args() WordPress function that merges two arrays and parses the values to override defaults
     * @uses register_post_type() WordPress function for registering a new post type
     * 
     * @param string $title title of the post type. This will be automatically converted for plural and slug use
     * @param array $args overrides the defaults
     */
    function register_post_type( $title, $args = array() ){

        $sanitizedTitle = sanitize_title( $title );
        $title = $args['singleTitle'];
        $puralTitle = $args['pluralTitle'];

        $defaults = array(
                'labels' => array(
                    'name'               => $puralTitle,
                    'singular_name'      => _x( $title, 'post type singular name', 'gmp' ),
                    'add_new'            => __( 'Add New', 'gmp' ),
                    'add_new_item'       => sprintf( __( 'Add New %s'            , 'gmp' ), $title      ),
                    'edit_item'          => sprintf( __( 'Edit %s'               , 'gmp' ), $title      ),
                    'new_item'           => sprintf( __( 'New %s'                , 'gmp' ), $title      ),
                    'view_item'          => sprintf( __( 'View %s'               , 'gmp' ), $title      ),
                    'search_items'       => sprintf( __( 'Search %s'             , 'gmp' ), $puralTitle ),
                    'not_found'          => sprintf( __( 'No %s found'           , 'gmp' ), $puralTitle ),
                    'not_found_in_trash' => sprintf( __( 'No %s found in trash'  , 'gmp' ), $puralTitle ),
                    'parent_item_colon'  => sprintf( __( 'Parent %s'             , 'gmp' ), $title      )
                ),
                '_builtin'      => false,
                'public'        => true,
                'hierarchical'  => false,
                'taxonomies'    => array( ),
                'query_var'     => $sanitizedTitle,
                'menu_position' => 6,
                'supports'      => array( 'title', 'editor', 'thumbnail', 'author', 'comments', 'genesis-seo', 'genesis-layouts' ),
                'rewrite'       => array( 'slug' => $sanitizedTitle ),
                'has_archive'   => true
            );

        $args = wp_parse_args( $args, $defaults );

        $postType = isset( $args['postType'] ) ? $args['postType'] : $sanitizedTitle;

        register_post_type( $postType, $args );

    }
    
    /**
     * Builds the taxonomy
     * 
     * @uses $this->register_taxonomy()
     */
    function taxonomy_builder(){
        
        foreach( $this->_taxonomies as $taxonomy => $args ){
            
            $object_type = isset( $args['object_type'] ) ? $args['object_type'] : '';
            $args = isset( $args['args'] ) ? $args['args'] : array();
            
            $this->register_taxonomy( $taxonomy, $object_type, $args );
            
        }
         
    }
    
    /**
     * Registers a taxonomy with default values which can be overridden as needed.
     * 
     * @author Nick the Geek
     * @link http://DesignsByNicktheGeek.com
     * 
     * @uses sanitize_title() WordPress function that formats text for use as a slug
     * @uses wp_parse_args() WordPress function that merges two arrays and parses the values to override defaults
     * @uses register_taxonomye() WordPress function for registering a new taxonomy
     * 
     * @param string $taxonomy title of the taxonomy. This will be automatically converted for plural and slug use
     * @param array $args overrides the defaults
     */
    function register_taxonomy( $taxonomy, $object_type, $args ){
        
        $sanitizedTaxonomy = sanitize_title( $taxonomy );
		$title = $args['singleTitle'];
        $puralTitle = $args['pluralTitle'];

        $defaults = array(
                'labels' => array(
                    'name'                       => $puralTitle,
                    'singular_name'              => $title,
                    'search_items'               => sprintf( __( 'Search %s'                   , 'gmp' ), $puralTitle ),
                    'popular_items'              => sprintf( __( 'Popular %s'                  , 'gmp' ), $puralTitle ),
                    'all_items'                  => sprintf( __( 'All %s'                      , 'gmp' ), $puralTitle ),
                    'parent_item'                => sprintf( __( 'Parent %s'                   , 'gmp' ), $title      ),
                    'parent_item_colon'          => sprintf( __( 'Parent %s:'                  , 'gmp' ), $title      ),
                    'edit_item'                  => sprintf( __( 'Edit %s'                     , 'gmp' ), $title      ),
                    'update_item'                => sprintf( __( 'Update %s'                   , 'gmp' ), $title      ),
                    'add_new_item'               => sprintf( __( 'Add New %s'                  , 'gmp' ), $title      ),
                    'new_item_name'              => sprintf( __( 'New %s Name'                 , 'gmp' ), $title      ),
                    'separate_items_with_commas' => sprintf( __( 'Seperate %s with commas'     , 'gmp' ), $title      ),
                    'add_or_remove_items'        => sprintf( __( 'Add or remove %s'            , 'gmp' ), $puralTitle ),
                    'choose_from_most_used'      => sprintf( __( 'Choose from the most used %s', 'gmp' ), $puralTitle )
                ),
                'public'            => true,
                'show_in_nav_menus' => true,
                'show_ui'           => true,
                'show_tagcloud'     => false,
                'hierarchical'      => false,
                'query_var'         => $sanitizedTaxonomy,
                'rewrite'           => array( 'slug' => $sanitizedTaxonomy ),
                '_builtin'          => false
                
            );

        $args = wp_parse_args( $args, $defaults );

        $taxonomy = isset( $args['taxonomy'] ) ? $args['taxonomy'] : $sanitizedTaxonomy;

        register_taxonomy( $taxonomy, $object_type, $args );
        
        
    }
    
    /**
     * Generates plural version of title
     * 
     * @param string $title
     * @return string 
     */
    function plural_title( $title ){
        
        return'y' == substr($title,-1) ? rtrim($title, 'y') . 'ies' : $title . 's';
        
    }
    
}