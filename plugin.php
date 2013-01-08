<?php

/*
  Plugin Name: Genesis Media Project
  Plugin URI: http://DesignsByNicktheGeek.com
  Version: 0.9.0.2
  Author: Nick_theGeek
  Author URI: http://DesignsByNicktheGeek.com
  Description: Adds video integration to Genesis via a video custom post type. Additionally, adds the option of building slideshows from the videos and displaying them via a widget, shortcode, or template tag.
  Text Domain: gmp
  Domain Path /languages/
 */

/*
 * To Do:
 *      Create and setup screen shots
 *      Document all functions, classes, and files
 *      Setup options for video tabs
 * 
 */


/* Prevent direct access to the plugin */
if ( !defined( 'ABSPATH' ) ) {
    wp_die( __( 'Sorry, you are not allowed to access this page directly.', 'gmp' ) );
}

define( 'GMP_PLUGIN_DIR', dirname( __FILE__ ) );
define( 'GMP_SETTINGS_FIELD', 'gmp-settings' );

add_action( 'admin_init', 'register_gmp_settings' );

/**
 * This registers the settings field
 */
function register_gmp_settings() {
    register_setting( GMP_SETTINGS_FIELD, GMP_SETTINGS_FIELD );
}

register_activation_hook( __FILE__, 'gmp_activation_check' );

/**
 * Checks for minimum Genesis Theme version before allowing plugin to activate
 *
 * @author Nathan Rice
 * @uses gmp_truncate()
 * @since 0.1
 * @version 0.1
 */
function gmp_activation_check() {

    $latest = '1.8';

    $theme_info = get_theme_data( TEMPLATEPATH . '/style.css' );

    if ( basename( TEMPLATEPATH ) != 'genesis' ) {
        deactivate_plugins( plugin_basename( __FILE__ ) ); // Deactivate ourself
        wp_die( sprintf( __( 'Sorry, you can\'t activate unless you have installed %1$sGenesis%2$s', 'gmp' ), '<a href="http://designsbynickthegeek.com/go/genesis">', '</a>' ) );
    }

    $version = gmp_truncate( $theme_info['Version'], 3 );

    if ( version_compare( $version, $latest, '<' ) ) {
        deactivate_plugins( plugin_basename( __FILE__ ) ); // Deactivate ourself
        wp_die( sprintf( __( 'Sorry, you can\'t activate without %1$sGenesis %2$s%3$s or greater', 'gmp' ), '<a href="http://designsbynickthegeek.com/go/genesis">', $latest, '</a>' ) );
    }
}

/**
 *
 * Used to cutoff a string to a set length if it exceeds the specified length
 *
 * @author Nick Croft
 * @since 0.1
 * @version 0.1
 * @param string $str Any string that might need to be shortened
 * @param string $length Any whole integer
 * @return string
 */
function gmp_truncate( $str, $length=10 ) {

    if ( strlen( $str ) > $length ) {
        return substr( $str, 0, $length );
    } else {
        $res = $str;
    }

    return $res;
}




/** Load textdomain for translation */
load_plugin_textdomain( 'gmp', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

if( ! class_exists( 'NTG_Module_Loader' ) )
        require_once( GMP_PLUGIN_DIR . '/classes/module-loader/module-loader.php');

$modules = array(
    'video',
    'tab-slider'
);

ntg_add_modules( $modules );


add_filter( 'content_width', 'gmp_get_content_sizes', 5, 3 );
/** Gets the content widths if available for use in setting content width based on layout
 *
 * @author Nick Croft
 * @since 0.1
 * @version 0.1
 * 
 * @global numeral $gmp_medium
 * @global numeral $gmp_narrow
 * @global numeral $gmp_wide
 * @param numeral $medium
 * @param numeral $narrow
 * @param numeral $wide
 * @return numeral 
 */
function gmp_get_content_sizes( $medium, $narrow, $wide ){
    global $gmp_medium, $gmp_narrow, $gmp_wide;
    
    $gmp_medium = $medium;
    $gmp_narrow = $narrow;
    $gmp_wide   = $wide;
    
    return $medium;
}
