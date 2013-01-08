<?php


add_filter( 'cmb_meta_boxes', 'gmp_video_meta_boxes' );
/**
 * Adds inpost metabox for the Video post type
 * 
 * @param array $meta_boxes
 * @return array 
 */
function gmp_video_meta_boxes( $meta_boxes ){
    
    $postTypes = apply_filters( 'gmp_video_meta_boxes_post_types', array( 'video' ) );
    
    $prefix = '_gmp_video_';
    
    $meta_boxes['gmp_video'] = array(
	    'id'         => 'gmp_video_meta',
	    'title'      => __( 'Genesis Media Project Video Options', 'gmp' ),
	    'pages'      => $postTypes, // post type
            'context'    => 'normal',
            'priority'   => 'high',
            'show_names' => true, // Show field names on the left
	    'fields'     => array(
                array(
		        'name' => __( 'Video URI', 'gmp' ),
		        'desc' => __( 'Simply paste the video URI and the theme will get the embed code automatically. If source is supported it will get the video and it will also get the default thumbnail', 'gmp' ),
		        'id'   => $prefix . 'uri',
		        'type' => 'text'
		    ),
                array(
		        'name' => __( 'Video Embed', 'gmp' ),
		        'desc' => __( 'For unsupported video sites you may paste the embed code here, but you will need to adjust the size of the video output.', 'gmp' ),
		        'id'   => $prefix . 'embed',
		        'type' => 'textarea'
		    ),
                array(
		        'name' => __( 'Video File', 'gmp' ),
		        'desc' => __( 'You may put a URI for a video file to be used with the included video player here.', 'gmp' ),
		        'id'   => $prefix . 'file',
		        'type' => 'text'
		    )
	    )
	);
    
    
    return $meta_boxes;
}
