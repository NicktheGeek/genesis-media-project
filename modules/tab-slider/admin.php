<?php

// Include & setup custom metabox and fields
add_filter( 'ntg_settings_builder', 'gmp_video_tab_slider_admin_settings' );
/**
 * Builds video tabs settings page
 * 
 * @param array $admin_pages
 * @return array 
 */
function gmp_video_tab_slider_admin_settings( $admin_pages ) {
    
    $prefix = '_gmp_tab_slider_setting_';
    
        $admin_pages[] = array(
            'settings' => array(
                'page_id'          => 'gmp-tab-slider-settings',
                'menu_ops'         => array(
                    'submenu' => array(
                        'parent_slug' => 'edit.php?post_type=video',
                        'page_title'  => __('Genesis Media Project Video Tab Slider Setting', 'gmp'),
                        'menu_title'  => __('Tabs Settings', 'gmp'),
                        'capability'  => 'manage_options',
                    )
                ),
                'page_ops'         => array(
                    
                ),
                'settings_field'   => GMP_SETTINGS_FIELD,
                'default_settings' => array(
                    $prefix . 'quantity'         => '5',
                    $prefix . 'slideshow'        => '',
					$prefix . 'ajax'             => true,
                    $prefix . 'thumbnail_height' => 100,
                    $prefix . 'thumbnail_width'  => 150,
                    $prefix . 'video_height'     => 270,
                    $prefix . 'video_width'      => 530,
                ),
                
            ),
            'sanatize' => array(
                'no_html'   => array(
                    $prefix . 'quantity',
                    $prefix . 'slideshow',
					$prefix . 'ajax',
                    $prefix . 'thumbnail_height',
                    $prefix . 'thumbnail_width',
                    $prefix . 'video_height',
                    $prefix . 'video_width',
                )
            ),
            'help'       => array(
                'tab' => array(
                    array(
                        'id'        => 'add_slideshow_theme',
                        'title'     => __( 'Add Slideshow to theme', 'gmp' ),
                        'content'   => sprintf( __( '%1$sUse %2$s to add this to your theme.%3$sIf you do not specify the number of slides, the default from the settings will be used.%4$sIf you do not specify a slideshow by slug then the latest videos will be shown%5$s', 'gmp' ), '<p>', '<code>'. esc_html( '<?php if ( function_exists( \'gmp_slideshow\' ) ) { gmp_slideshow( array( \'slides\' => \'\', \'slideshow\' => \'\' ); } ?>' ) .'</code>', '</p><p>', '</p><p>', '</p>' )
                        ),
                    array(
                        'id'        => 'add_slideshow_content',
                        'title'     => __( 'Add Slideshow to Page/Post', 'gmp' ),
                        'content'   => sprintf( __( '%1$sUse %2$s to add the slideshow to page or post content.%3$sIf you do not specify the number of slides, the default from the settings will be used.%4$sIf you do not specify a slideshow by slug then the latest videos will be shown.%5$s', 'gmp' ), '<p>', '<code>[gmp_slideshow slides="" slideshow=""]</code>', '</p><p>', '</p><p>', '</p>' )
                        ),
                    array(
                        'id'        => 'add_slideshow_sidebar',
                        'title'     => __( 'Add Slideshow to Widgeted Area', 'gmp' ),
                        'content'   => sprintf( __( '%1$sAdd the GMP Tab Slider widget to any sidebar to display in a sidebar.%2$s', 'gmp' ), '<p>', '</p>' )
                        )
                ),
                'sidebar' => array(
                        sprintf( '<p>%s <a href="#">%s</a></p>', __( 'For more details, please visit the module page', 'gmp' ), __( 'Genesis Media Project Tab Slider', 'gmp' ) )
                )
            ),
            'meta_boxes' => array(
                
                'id'         => 'gmp-tab-slider-options',
                'title'      => 'General Settings',
                'context'    => 'main',
                'priority'   => 'high',/**/
                'show_names' => true, // Show field names on the left
                'fields'     => array(
                    
                    array(
                        'name' => __( 'Number of Videos', 'gmp' ),
                        'desc' => '',
                        'id'   => $prefix . 'quantity',
                        'type' => 'text_small'
                    ),
                    array(
                        'name'     => __("Default Slideshow:", 'gmp'),
                        'desc'     => __( 'If no slideshow is selected, all videos will be used', 'gmp' ),
                        'id'       => $prefix . 'slideshow',
                        'type'     => 'taxonomy_select',
                        'taxonomy' => 'slideshow'
                    ),
					array(
                        'name' => __( 'Use Ajax to load slides', 'gmp' ),
                        'desc' => '',
                        'id'   => $prefix . 'ajax',
                        'type' => 'checkbox'
                    ),
                ))
	);
	
	return $admin_pages;
}

// creating Ajax call for WordPress  
add_action( 'wp_ajax_nopriv_gmpAjaxVideoTab', 'gmp_ajax_video_tab' );  
add_action( 'wp_ajax_gmpAjaxVideoTab', 'gmp_ajax_video_tab' ); 
function gmp_ajax_video_tab(){  
	
	$id = isset($_POST['data']) ? $_POST['data'] : '';
	
	$args = array();
	
	$slidesPrefix = '_gmp_tab_slider_setting_';
    
    $defaults = array(
        'slides'    => genesis_get_option( $slidesPrefix .'quantity', GMP_SETTINGS_FIELD ),
        'slideshow' => genesis_get_option( $slidesPrefix .'slideshow', GMP_SETTINGS_FIELD ),
        'height'    => 270,
        'width'     => 530,
        'more_link' => sprintf( '[%s]', __( 'Read More', 'gmp') ),
		'ajax'      => genesis_get_option( $slidesPrefix .'ajax', GMP_SETTINGS_FIELD )
    );
    
    $args = wp_parse_args( $args, $defaults );
	
	$slide = 'Cannot Find Video';
	
	if( $id ){
		
		query_posts( array( 'post_type' => 'any', 'post__in' => array( $id ) ) );
		
		if( have_posts() ){
			
			require_once( GMP_PLUGIN_DIR . '/modules/video/output.php');
			
			while( have_posts() ){
				the_post();
				
				$slide = '<div class="video">
                                '. gmp_get_video( $args['height'], $args['width'], false, 'tab-large' ) .'
                            </div><!-- end .video -->
                            <div class="text">
                                <h2><a href="'. get_permalink() .'" title="'. get_the_title() .'">'. get_the_title() .'</a></h2>
                                '. sprintf( '<p class="byline post-info">%s</p>', apply_filters( 'genesis_post_info', '[post_date] ' . __( 'By', 'gmp' ) . ' [post_author_posts_link] [post_comments] [post_edit]' ) ) .'
                                '. get_the_content_limit( 200, $args['more_link'] ) .'
                            </div>';
				
			}
		}
	}
	

	// Return the String  
	die($slide);  
   
  }  