<?php

define( 'GMP_TAB_SLIDER_DIR_URI', plugins_url( '/genesis-media-project/modules/tab-slider/' , GMP_PLUGIN_DIR ) );

add_action( 'wp_enqueue_scripts', 'gmp_enqueue_tab_slider_scripts' );
/**
 * Enqueues scripts and styles required for Video Tabs
 */
function gmp_enqueue_tab_slider_scripts() {
    
	if( genesis_get_option( '_gmp_tab_slider_setting_ajax', GMP_SETTINGS_FIELD ) ) {
		wp_enqueue_script( 'gmp-tab-slider-ajax', GMP_TAB_SLIDER_DIR_URI . 'js/tabs-ajax.js' , array(), '0.1', true );
		wp_localize_script( 'gmp-tab-slider-ajax', 'gmpTabAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );  
	}
	else
		wp_enqueue_script( 'gmp-tab-slider', GMP_TAB_SLIDER_DIR_URI . 'js/tabs.js' , array(), '0.1', true );
	
        wp_enqueue_style( 'gmp-tab-slider-css', GMP_TAB_SLIDER_DIR_URI . 'css/tabs.css' , array(), '0.1', 'screen' );
    
} 


/**
 * Builds Video Tabs
 * 
 * @param array $args
 * @return string 
 */
function gmp_get_slideshow( $args = array() ){
    
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
    
    $queryArgs = array(
        'posts_per_page' => $args['slides'],
        'slideshow'      => $args['slideshow'],
        'post_type'      => 'video'
            );
    
       
    
    query_posts( $queryArgs );
    
    if( have_posts() ){
        
        $count = 1;
        
        $slideshow = '<div class="tab_container">';
        
        $tabs      = '<ul class="tabs">'; 
        
        while( have_posts() ){
            the_post();
            
			if( $args['ajax'] ) {
				global $post;
				
				if( 1 >= $count ){
			
				$slideshow .= '<div id="gmp-tab" class="tab_content">
								<div class="video">
									'. gmp_get_video( $args['height'], $args['width'], false, 'tab-large' ) .'
								</div><!-- end .video -->
								<div class="text">
									<h2><a href="'. get_permalink() .'" title="'. get_the_title() .'">'. get_the_title() .'</a></h2>
									'. sprintf( '<p class="byline post-info">%s</p>', apply_filters( 'genesis_post_info', '[post_date] ' . __( 'By', 'gmp' ) . ' [post_author_posts_link] [post_comments] [post_edit]' ) ) .'
									'. get_the_content_limit( 200, $args['more_link'] ) .'
								</div>
							  </div><!-- end .tab_content -->';
				
				}
				
				$tabs     .= '<li>
								<a href="#gmp-video-'. $post->ID .'">
									'. gmp_get_image( array( 'size' => 'tab-thumbnails', 'attr' => array( 'title' => get_the_title() ) ) ) .'
								</a>
							 </li>';
				
			} else {
            $slideshow .= '<div id="tab'.$count.'" class="tab_content">
                            <div class="video">
                                '. gmp_get_video( $args['height'], $args['width'], false, 'tab-large' ) .'
                            </div><!-- end .video -->
                            <div class="text">
                                <h2><a href="'. get_permalink() .'" title="'. get_the_title() .'">'. get_the_title() .'</a></h2>
                                '. sprintf( '<p class="byline post-info">%s</p>', apply_filters( 'genesis_post_info', '[post_date] ' . __( 'By', 'gmp' ) . ' [post_author_posts_link] [post_comments] [post_edit]' ) ) .'
                                '. get_the_content_limit( 200, $args['more_link'] ) .'
                            </div>
                          </div><!-- end .tab_content -->';
            
            $tabs     .= '<li>
                            <a href="#tab'. $count .'">
                                '. gmp_get_image( array( 'size' => 'tab-thumbnails', 'attr' => array( 'title' => get_the_title() ) ) ) .'
                            </a>
                         </li>';
			}
            
            
            ++$count;
        }
        
        $slideshow .= '</div><!-- end .tab_container --><div class="clear"></div>';
        
        $tabs      .= '</ul><!-- end .tabs --><div class="clear"></div>';
        
        
    }
    
    wp_reset_postdata();
    wp_reset_query();
    
    return sprintf( '<div id="gmp-slides" class="slideshow">%s%s</div><!-- end .slideshow -->', $slideshow, $tabs );
    
    
}

/**
 * Wrapper function for gmp_get_slideshow()
 * Echoes gmp_get_slideshow()
 * 
 * @uses gmp_get_slideshow()
 * 
 * @param array $args 
 */
function gmp_slideshow( $args = array() ){
    
    echo gmp_get_slideshow( $args );
    
}

add_shortcode( 'gmp_slideshow', 'gmp_slideshow_shortcode' );
/**
 * Outputs Video Tabs.
 *
 * Supported shortcode attributes are:
 *   slides (number of slides to show, defaults to empty string),
 *   slideshow (slideshow taxonomy to get videos from, default is empty string),
 *
 * @uses gmp_get_slideshow
 *
 * @since 1.0
 *
 * @param array $atts Shortcode attributes
 * @return string Shortcode output
 */
function gmp_slideshow_shortcode( $atts ) {
	
	$slidesPrefix = '_gmp_tab_slider_setting_';

	$defaults = array(
		'slides'  => genesis_get_option( $slidesPrefix .'quantity', GMP_SETTINGS_FIELD ),
		'slideshow' => genesis_get_option( $slidesPrefix .'slideshow', GMP_SETTINGS_FIELD ),
	);
        
	$atts = shortcode_atts( $defaults, $atts );

	return gmp_get_slideshow( $atts );

}
