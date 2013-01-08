<?php


add_filter( 'ntg_module_loader', 'gmp_load_video' );
/**
 * Loads the video module. 
 * This is a required module.
 * 
 * @param array $load_modules
 * @return array 
 */
function gmp_load_video( $load_modules ){
    
    $load_modules['video'] = array(
        'name'           => __( 'Video Support', 'gmp' ),
        'id'             => 'video',
        'description'    => __( 'Adds video post type and meta boxes for the videos as well as loads all functions to allow videos to work well with Genesis.', 'gmp' ),
        'directory'      => GMP_PLUGIN_DIR . '/modules/video',
        'type'           => 'required',
        'setting_field'  => GMP_SETTINGS_FIELD,
        'setting_filter' => 'gmp_options',
        'include'        => array(
            'admin'  => array(
                //'admin.php',
                'meta-boxes.php',
                'video-thumbnail.php'
            ),
            'both'   => array(
                'functions.php',
                'post-types-taxonomies.php'  
            ),
            'output' => array(
                'output.php'
            )
        ),
        'uses'           => array(
            'classes' => array ( 
                'admin-builder',
                'meta-box-builder',
                'post-types-taxonomies'
                )
        )
    );
    
    return $load_modules;
}
