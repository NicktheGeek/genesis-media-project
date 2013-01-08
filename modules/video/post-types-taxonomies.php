<?php

$args = array(
    
    'postTypes'  => array(
        'video' => array( 
			'postType'    => 'video',
			'singleTitle' => _x( 'Video', 'post type singular name', 'gmp' ),
			'pluralTitle' => _x( 'Videos', 'post type general name', 'gmp' )
			)
    ),
    'taxonomies' => array(
        'SlideShow' => array(
            'object_type' => 'video',
            'args'        => array(
				'singleTitle'  => _x( 'Slide Show', 'taxonomy general name', 'gmp' ),
				'pluralTitle'  => _x( 'Slide Shows', 'taxonomy singular name', 'gmp' ),
                'public'       => false,
                'hierarchical' => true
            )
        ),
        'Video Category' => array(
            'object_type' => 'video',
            'args'        => array(
				'singleTitle'  => _x( 'Video Category', 'taxonomy general name', 'gmp' ),
				'pluralTitle'  => _x( 'Video Categories', 'taxonomy singular name', 'gmp' ),
                'hierarchical' => true
            )
        ),
        'Video Tag' => array(
            'object_type' => 'video',
            'args'        => array(
				'singleTitle'  => _x( 'Video Tag', 'taxonomy general name', 'gmp' ),
				'pluralTitle'  => _x( 'Video Tags', 'taxonomy singular name', 'gmp' ),
                'hierarchical' => false
            )
        )
    )
        
);


new ntg_PostTypes_Taxonomies( $args );
