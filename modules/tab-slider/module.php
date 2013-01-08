<?php


add_filter( 'ntg_module_loader', 'gmp_load_video_tab_slider' );
/**
 * Loads the video module. 
 * This is a required module.
 * 
 * @param array $load_modules
 * @return array 
 */
function gmp_load_video_tab_slider( $load_modules ){
    
    $load_modules['video-tab-slider'] = array(
        'name'           => __( 'Video Tab Slider', 'gmp' ),
        'id'             => 'video-tabs',
        'description'    => __( 'Adds the video tab slider. This may be loaded as a shortcode, widget, or theme function.', 'gmp' ),
        'directory'      => GMP_PLUGIN_DIR . '/modules/tab-slider',
        'type'           => 'optional',
        'setting_field'  => GMP_SETTINGS_FIELD,
        'setting_filter' => 'gmp_options',
        'include'        => array(
            'admin'  => array(
                'admin.php'
            ),
            'output' => array(
                'output.php'
            ),
            'both'   => array(
                'functions.php',
                'widgets/tab-slider.php'  
            )
        ),
        'uses'           => array(
            'classes' => array ( 
                'admin-builder'
                )
        ),
        'requires' => array(
            'video'
        )
    );
    
    return $load_modules;
}
