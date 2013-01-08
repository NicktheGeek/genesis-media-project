<?php

define( 'GMP_VIDEO_DIR_URI', plugins_url( '/genesis-media-project/modules/video/' , GMP_PLUGIN_DIR ) );


add_action( 'wp_enqueue_scripts', 'gmp_enqueue_video_scripts' );
/**
 * Loads the Flowplayer video script, required for selfhosted videos.
 */
function gmp_enqueue_video_scripts() {
    
        wp_enqueue_script( 'gmp-flowplayer', GMP_VIDEO_DIR_URI . 'js/flowplayer/flowplayer-3.2.9.min.js' , array(), '3.2.6', false );
        wp_enqueue_script( 'gmp-flowplayer-ipad', GMP_VIDEO_DIR_URI . 'js/flowplayer/flowplayer.ipad-3.2.2.min.js' , array( 'gmp-flowplayer' ), '3.2.2', false );
        wp_enqueue_script( 'gmp-fitvid-lib', GMP_VIDEO_DIR_URI . 'js/fitvids/jquery.fitvids.js' , array( 'jquery' ), '1.0.0', false );
        wp_enqueue_script( 'gmp-fitvid-exec', GMP_VIDEO_DIR_URI . 'js/fitvids/jquery.fitvids.exec.js' , array( 'jquery', 'gmp-fitvid-lib'  ), '1.0.0', true );

}

/**
 * Gets image. Currently just a wrapper function for genesis_get_image()
 * 
 * @todo Add additional markup for overlay when overlay module is included
 * @uses genesis_get_image()
 * @param array $args
 * @return string 
 */
function gmp_get_image( $args ){
    return genesis_get_image( $args );
}

/**
 * Wrapper function for gmp_get_image.
 * Echos returned value from gmp_get_image()
 * 
 * @uses gmp_get_image()
 * @param array $args 
 */
function gmp_image( $args ){
    echo gmp_get_image( $args );
}

/**
 * Gets and formats video from custom field if available.
 * 
 * @uses gmp_video_from_uri()
 * @uses gmp_video_from_file()
 * @uses gmp_format_video()
 * @param numeral $height
 * @param numeral $width
 * @return string 
 */
function gmp_get_video( $height='', $width='', $inlineCSS=true, $imagesize='video' ){
    
    $video = '';
    
    if( genesis_get_custom_field('_gmp_video_uri') ){
        $video = gmp_video_from_uri( genesis_get_custom_field('_gmp_video_uri'), $height, $width );
        
        $video = '<div class="gmp-fit-video">'. gmp_format_video( $video ) .'</div>';
    }
    elseif( genesis_get_custom_field('_gmp_video_embed') ){
        $video = '<div class="gmp-fit-video">'. gmp_format_video( htmlspecialchars_decode( genesis_get_custom_field('_gmp_video_embed') ) ) . '</div>';
    }
    elseif( genesis_get_custom_field('_gmp_video_file') ){
        $video = gmp_video_from_file( genesis_get_custom_field('_gmp_video_file'), $height, $width, $inlineCSS, $imagesize );
    }
    
    return $video;
}

/**
 * Wrapper function for gmp_get_video()
 * Echos value returned from gmp_get_video()
 * 
 * @uses gmp_get_video()
 * @param string $height
 * @param string $width 
 */
function gmp_video( $height='', $width='', $inlineCSS=true, $imagesize='video'  ){
    echo gmp_get_video( $height, $width, $inlineCSS=true, $imagesize='video' );
}

/**
 * Uses video uri to return video from service
 * 
 * @global numeral $content_width
 * @global object $wp_embed
 * @global numeral $gmp_medium
 * @global numeral $gmp_narrow
 * @global numeral $gmp_wide
 * @param string $uri URI of the video that should be retrieved
 * @param numeral $height
 * @param numeral $width
 * @return string 
 */
function gmp_video_from_uri( $uri, $height='', $width='' ){
    global $content_width, $wp_embed, $gmp_medium, $gmp_narrow, $gmp_wide;
    
    if( ! isset( $content_width ) )
        $content_width = 560;
    
    $gmp_width = '';
    
    //*
    if( isset( $gmp_medium ) && isset( $gmp_narrow ) && isset( $gmp_wide ) && ! $width ){
            switch ( genesis_site_layout() ) {
		case 'full-width-content':
			$gmp_width = $gmp_wide;
			break;
		case 'content-sidebar-sidebar':
		case 'sidebar-content-sidebar':
		case 'sidebar-sidebar-content':
			$gmp_width = $gmp_narrow;
			break;
		default:
			$gmp_width = $gmp_medium;
                    break;
            }
    }/**/
    
    $content_width = $gmp_width ? $gmp_width : $content_width;
    $width  = $width            ? $width     : $content_width;
    $height = $height           ? $height    : $width*2;
    
    $video = '[embed width="' . $width . '" height="' . $height . '"  ]' . $uri. '[/embed]';
    
    $video = $wp_embed->run_shortcode( $video );
    
    return $video;
    
}

/**
 * Builds html for selfhosted videos using the flowplayer video player
 * 
 * @global numeral $content_width
 * @global numeral $gmp_medium
 * @global numeral $gmp_narrow
 * @global numeral $gmp_wide
 * @param string $uri URI of the video that should be retrieved
 * @param numeral $height
 * @param numeral $width
 * @return string 
 */
function gmp_video_from_file( $file, $height='', $width='', $inlineCSS=true, $imagesize='video'  ){
    
    global $content_width, $gmp_medium, $gmp_narrow, $gmp_wide;
    
    if( ! isset( $content_width ) )
        $content_width = 560;
    
    $gmp_width = '';
    
    if( ! $width && isset( $gmp_medium ) && isset( $gmp_narrow ) && isset( $gmp_wide ) ){
            switch ( genesis_site_layout() ) {
		case 'full-width-content':
			$gmp_width = $gmp_wide;
			break;
		case 'content-sidebar-sidebar':
		case 'sidebar-content-sidebar':
		case 'sidebar-sidebar-content':
			$gmp_width = $gmp_narrow;
			break;
		default:
			$gmp_width = $gmp_medium;
            }
    }
    
    $content_width = $gmp_width ? $gmp_width : $content_width;
    $width  = $width            ? $width     : $content_width;
    $height = $height           ? $height    : ceil( $width*.5625 );
    
        $id          = get_the_id();
		
        $image       = gmp_get_image( array( 'size' => $imagesize, 'format' => 'url' ) );
        $simulate    = gmp_is_mobile_browser() ? 'simulateiDevice: true' : '';
        $playlistImg = $image     ? '{ url: "'. $image .'", scaling: "orig" },' : '';
        $style       = $inlineCSS ? 'style="display:block;width:'. $width .'px;height:'. $height .'px;background:url(\''. $image .'\');"' : 'style="display:block;background:url(\''. $image .'\');"';
        
        $video =    '<a href="'. $file .'" class="flowplayer-video" '. $style .' id="player-'. $id .'"></a>
                    <script type="text/javascript">'.
                        'flowplayer("player-'. $id .'", {src:"'. GMP_VIDEO_DIR_URI .'js/flowplayer/flowplayer-3.2.10.swf", wmode: \'opaque\'}, {
                            playlist: [
                            
                                '. $playlistImg .'
                                
                                {
                                    url: "'. $file .'",
                                    autoPlay: false,
                                    autoBuffering: true
                                }
                                
                           ]
                        }).ipad( '.$simulate.' );'.
                    '</script>';
    
    return $video;
}

/**
 * Formats video to fix wmode
 * 
 * @param string $video
 * @return string 
 */
function gmp_format_video( $video ){
    
    if( strpos( $video, 'iframe' ) ) {
        $video = preg_replace_callback('/(src=")([^"]*)/mi', 'gmp_process_iframe_matches', $video);
        $video = str_replace( 'allowfullscreen', '', $video );
    }
    
    elseif( strpos( $video, 'embed' ) )
        $video = gmp_process_embed_video( $video );
    
    return $video;
}

/**
 * preg_replace_callback() call back to insert wmode for iframe
 * 
 * @param array $matches
 * @return string 
 */
function gmp_process_iframe_matches ($matches) {
    # get rid of full matching string; all parts are already covered
    array_shift($matches);
    
    //print_r($matches[2]);

    if (strpos( $matches[1], '?' )) {
        # link already contains $_GET parameters
        $matches[1] .= '&wmode=opaque';
    } else {
        # starting a new list of parameters
        $matches[1] .= '?wmode=opaque';
    }

    $matches[1] = htmlspecialchars( $matches[1] );
    
    return implode($matches);
}

/**
 * Fixes wmode for embedded video
 * 
 * @param string $video
 * @return string 
 */
function gmp_process_embed_video($video) {

    $patterns = array( '/<\/param><embed/', '/allowscriptaccess="always"/' );
    $replacements = array( '</param><param name="wmode" value="transparent"></param><embed', 'wmode="transparent" allowscriptaccess="always"' );

    return preg_replace( $patterns, $replacements, $video );

}

add_filter( 'the_content', 'gmp_single_video_content', 15 );
/**
 * Adds video to the top of the video single page
 * 
 * @todo add option for displaying the video above, below, or insert manually via shortcode
 * @param type $content
 * @return type 
 */
function gmp_single_video_content( $content ){
    
    if( ! is_singular( 'video' ) )
        return $content;
    
    return '<div class="gmp-video">'. gmp_get_video() . '</div>' . $content;
    
}

/**
 * Checks to see if browser viewing site is a known mobile browser.
 * 
 * @return boolean 
 */
function gmp_is_mobile_browser() {
    $mobile_browser = '0';
 
if (preg_match('/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone|android)/i', strtolower($_SERVER['HTTP_USER_AGENT']))) {
    $mobile_browser++;
}
 
if ((strpos(strtolower($_SERVER['HTTP_ACCEPT']),'application/vnd.wap.xhtml+xml') > 0) or ((isset($_SERVER['HTTP_X_WAP_PROFILE']) or isset($_SERVER['HTTP_PROFILE'])))) {
    $mobile_browser++;
}    
 
$mobile_ua = strtolower(substr($_SERVER['HTTP_USER_AGENT'], 0, 4));
$mobile_agents = array(
    'w3c ','acs-','alav','alca','amoi','audi','avan','benq','bird','blac',
    'blaz','brew','cell','cldc','cmd-','dang','doco','eric','hipt','inno',
    'ipaq','java','jigs','kddi','keji','leno','lg-c','lg-d','lg-g','lge-',
    'maui','maxo','midp','mits','mmef','mobi','mot-','moto','mwbp','nec-',
    'newt','noki','oper','palm','pana','pant','phil','play','port','prox',
    'qwap','sage','sams','sany','sch-','sec-','send','seri','sgh-','shar',
    'sie-','siem','smal','smar','sony','sph-','symb','t-mo','teli','tim-',
    'tosh','tsm-','upg1','upsi','vk-v','voda','wap-','wapa','wapi','wapp',
    'wapr','webc','winw','winw','xda ','xda-');
 
if (in_array($mobile_ua,$mobile_agents)) {
    $mobile_browser++;
}
 
if ( isset( $_SERVER['ALL_HTTP'] ) && strpos(strtolower($_SERVER['ALL_HTTP']),'OperaMini') > 0) {
    $mobile_browser++;
}
 
if (strpos(strtolower($_SERVER['HTTP_USER_AGENT']),'windows') > 0) {
    $mobile_browser = 0;
}
 
if ($mobile_browser > 0) {
   return true;
}
else {
   return false;
} 
}
