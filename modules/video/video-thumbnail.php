<?php
/*
 * Based on the Video Thumbnails Plugin by Sutherland Boswel. Original Copyright and Credits:
Plugin Name: Video Thumbnails
Plugin URI: http://sutherlandboswell.com/projects/wordpress-video-thumbnails/
Description: Automatically retrieve video thumbnails for your posts and display them in your theme. Currently supports YouTube, Vimeo, Blip.tv, Justin.tv, Dailymotion and Metacafe.
Author: Sutherland Boswell
Author URI: http://sutherlandboswell.com
Version: 1.7.7
License: GPL2
*/
/*  Copyright 2010 Sutherland Boswell  (email : sutherland.boswell@gmail.com

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License, version 2, as 
	published by the Free Software Foundation.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if ( function_exists('curl_init') ) {

// Get Vimeo Thumbnail
function gmp_getVimeoInfo($id, $info = 'thumbnail_large') {
	if (!function_exists('curl_init')) {
		return null;
	} else {
		$ch = curl_init();
		$videoinfo_url = "http://vimeo.com/api/v2/video/$id.php";
		curl_setopt($ch, CURLOPT_URL, $videoinfo_url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		curl_setopt($ch, CURLOPT_FAILONERROR, true); // Return an error for curl_error() processing if HTTP response code >= 400
		$output = unserialize(curl_exec($ch));
		$output = $output[0][$info];
		if (curl_error($ch) != null) {
			$output = new WP_Error( 'vimeo_info_retrieval', sprintf( __( 'Error retrieving video information from the URL %1$s. If opening that URL in your web browser returns anything else than an error page, the problem may be related to your web server and might be something your host administrator can solve.', 'gmp' ), '<a href="' . $videoinfo_url . '">' . $videoinfo_url . '</a>: <code>' . curl_error( $ch ) . '</code>' ) );
		}
		curl_close($ch);
		return $output;
	}
};

// Blip.tv Functions
function gmp_getBliptvInfo($id) {
	$videoinfo_url = "http://blip.tv/players/episode/$id?skin=rss";
	$xml = simplexml_load_file($videoinfo_url);
	if ($xml == false) {
		return new WP_Error( 'dailymotion_info_retrieval', sprintf( __( 'Error retrieving video information from the URL %1$s. If opening that URL in your web browser returns anything else than an error page, the problem may be related to your web server and might be something your host administrator can solve.', 'gmp' ), '<a href="' . $videoinfo_url . '">' . $videoinfo_url . '</a>' ) );
	} else {
		$result = $xml->xpath("/rss/channel/item/media:thumbnail/@url");
		$thumbnail = (string) $result[0]['url'];
		return $thumbnail;
	}
}

// Justin.tv Functions
function gmp_getJustintvInfo($id) {
	$xml = simplexml_load_file("http://api.justin.tv/api/clip/show/$id.xml");
	return (string) $xml->clip->image_url_large;
}

// Get DailyMotion Thumbnail
function gmp_getDailyMotionThumbnail($id) {
	if (!function_exists('curl_init')) {
		return null;
	} else {
		$ch = curl_init();
		$videoinfo_url = "https://api.dailymotion.com/video/$id?fields=thumbnail_url";
		curl_setopt($ch, CURLOPT_URL, $videoinfo_url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
			curl_setopt($ch, CURLOPT_FAILONERROR, true); // Return an error for curl_error() processing if HTTP response code >= 400
		$output = curl_exec($ch);
		$output = json_decode($output);
		$output = $output->thumbnail_url;
			if (curl_error($ch) != null) {
				$output = new WP_Error( 'dailymotion_info_retrieval', sprintf( __( 'Error retrieving video information from the URL %1$s. If opening that URL in your web browser returns anything else than an error page, the problem may be related to your web server and might be something your host administrator can solve.', 'gmp' ), '<a href="' . $videoinfo_url . '">' . $videoinfo_url . '</a>: <code>' . curl_error( $ch ) . '</code>' ) );
			}
		curl_close($ch); // Moved here to allow curl_error() operation above. Was previously below curl_exec() call.
		return $output;
	}
};

// Metacafe
function gmp_getMetacafeThumbnail($id) {
	$videoinfo_url = "http://www.metacafe.com/api/item/$id/";
	$xml = simplexml_load_file($videoinfo_url);
	if ($xml == false) {
		return new WP_Error('dailymotion_info_retrieval', sprintf( __( 'Error retrieving video information from the URL %1$s. If opening that URL in your web browser returns anything else than an error page, the problem may be related to your web server and might be something your host administrator can solve.', 'gmp' ), '<a href="' . $videoinfo_url . '">' . $videoinfo_url . '</a>' ) );
	} else {
	$result = $xml->xpath("/rss/channel/item/media:thumbnail/@url");
	$thumbnail = (string) $result[0]['url'];
	return $thumbnail;
	}
};

//
// The Main Event
//
function gmp_get_video_thumbnail($post_id=null) {
	
	// Get the post ID if none is provided
	if($post_id==null OR $post_id=='') $post_id = get_the_ID();
	
	// Check to see if thumbnail has already been found and still exists as a file
	if( ( ($thumbnail_meta = get_post_meta($post_id, '_gmp_video_thumbnail', true)) != '' ) && wp_remote_retrieve_response_code(wp_remote_head($thumbnail_meta)) === '200'  ) {
		return $thumbnail_meta;
	}
	// If the thumbnail isn't stored in custom meta, fetch a thumbnail
	else {

            global $wp_embed; // = new WP_Embed;
		// Gets the post's content
		$post_array = get_post($post_id); 
                $markup = isset( $_POST['_gmp_video_uri'] )   ? $_POST['_gmp_video_uri']   : null;
                $markup = isset( $_POST['_gmp_video_embed'] ) ? $_POST['_gmp_video_embed'] : $markup;
		$markup = $markup                             ? $markup                    : get_post_meta( $post_id, '_gmp_video_uri', true );
                $markup = $markup                             ? $markup                    : get_post_meta( $post_id, '_gmp_video_embed', true );
		//$markup = apply_filters('the_content',$markup);
                $markup = $wp_embed->run_shortcode( $wp_embed->autoembed( $markup ) );
		$new_thumbnail = null;
                
                //print_r($markup);
		
		// Checks for the old standard YouTube embed
		preg_match('#<object[^>]+>.+?https?://www.youtube.com/[ve]/([A-Za-z0-9\-_]+).+?</object>#s', $markup, $matches);
		
		// More comprehensive search for YouTube embed, redundant but necessary until more testing is completed
		if(!isset($matches[1])) {
			preg_match('#https?://www.youtube.com/[ve]/([A-Za-z0-9\-_]+)#s', $markup, $matches);
		}

		// Checks for YouTube iframe, the new standard since at least 2011
		if(!isset($matches[1])) {
			preg_match('#https?://www.youtube.com/embed/([A-Za-z0-9\-_]+)#s', $markup, $matches);
		}
	
		// Checks for any YouTube URL. After http(s) support a or v for Youtube Lyte and v or vh for Smart Youtube plugin
		if(!isset($matches[1])) {
			preg_match('#(?:https?(?:a|vh?)?://)?(?:www\.)?youtube.com/watch\?v=([A-Za-z0-9\-_]+)#s', $markup, $matches);
		}
                		
		// Checks for any shortened youtu.be URL. After http(s) a or v for Youtube Lyte and v or vh for Smart Youtube plugin
		if(!isset($matches[1])) {
			preg_match('#(?:https?(?:a|vh?)?://)?youtu.be/([A-Za-z0-9\-_]+)#s', $markup, $matches);
		}
		
		// Checks for YouTube Lyte
		if(!isset($matches[1]) && function_exists('lyte_parse')) {
			preg_match('#<div class="lyte" id="([A-Za-z0-9\-_]+)"#s', $markup, $matches);
		}
		
		// If we've found a YouTube video ID, create the thumbnail URL
		if(isset($matches[1])) {
			$youtube_thumbnail = 'http://img.youtube.com/vi/' . $matches[1] . '/0.jpg';
			
			// Check to make sure it's an actual thumbnail
			if (!function_exists('curl_init')) {
				$new_thumbnail = $youtube_thumbnail;
			} else {
				$ch = curl_init($youtube_thumbnail);
				curl_setopt($ch, CURLOPT_NOBODY, true);
				curl_exec($ch);
				$retcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
				// $retcode > 400 -> not found, $retcode = 200, found.
				curl_close($ch);
				if($retcode==200) {
					$new_thumbnail = $youtube_thumbnail;
				}
			}
		}
		
		// Vimeo
		if($new_thumbnail==null) {
		
			// Standard embed code
			preg_match('#<object[^>]+>.+?http://vimeo.com/moogaloop.swf\?clip_id=([A-Za-z0-9\-_]+)&.+?</object>#s', $markup, $matches);
			
			// Find Vimeo embedded with iframe code
			if(!isset($matches[1])) {
				preg_match('#http://player.vimeo.com/video/([0-9]+)#s', $markup, $matches);
			}
			
			// If we still haven't found anything, check for Vimeo embedded with JR_embed
			if(!isset($matches[1])) {
				preg_match('#\[vimeo id=([A-Za-z0-9\-_]+)]#s', $markup, $matches);
			}
	
			// If we still haven't found anything, check for Vimeo URL
			if(!isset($matches[1])) {
				preg_match('#(?:http://)?(?:www\.)?vimeo.com/([A-Za-z0-9\-_]+)#s', $markup, $matches);
			}
	
			// If we still haven't found anything, check for Vimeo shortcode
			if(!isset($matches[1])) {
				preg_match('#\[vimeo clip_id="([A-Za-z0-9\-_]+)"[^>]*]#s', $markup, $matches);
			}
			if(!isset($matches[1])) {
				preg_match('#\[vimeo video_id="([A-Za-z0-9\-_]+)"[^>]*]#s', $markup, $matches);
			}
		
			// Now if we've found a Vimeo ID, let's set the thumbnail URL
			if(isset($matches[1])) {
				$vimeo_thumbnail = gmp_getVimeoInfo($matches[1], $info = 'thumbnail_large');
				if (is_wp_error($vimeo_thumbnail)) {
				  return $vimeo_thumbnail;
				} else if (isset($vimeo_thumbnail)) {
					$new_thumbnail = $vimeo_thumbnail;
				}
			}
		}
		
		// Blip.tv
		if($new_thumbnail==null) {
		
			// Blip.tv embed URL
			preg_match('#http://blip.tv/play/([A-Za-z0-9]+)#s', $markup, $matches);

			// Now if we've found a Blip.tv embed URL, let's set the thumbnail URL
			if(isset($matches[1])) {
				$blip_thumbnail = gmp_getBliptvInfo($matches[1]);
				if (is_wp_error($blip_thumbnail)) {
				  return $blip_thumbnail;
				} else if (isset($blip_thumbnail)) {
		  $new_thumbnail = $blip_thumbnail;
				}
			}
		}
		
		// Justin.tv
		if($new_thumbnail==null) {
		
			// Justin.tv archive ID
			preg_match('#archive_id=([0-9]+)#s', $markup, $matches);

			// Now if we've found a Justin.tv archive ID, let's set the thumbnail URL
			if(isset($matches[1])) {
				$justin_thumbnail = gmp_getJustintvInfo($matches[1]);
				$new_thumbnail = $justin_thumbnail;
			}
		}
		
		// Dailymotion
		if($new_thumbnail==null) {
		
			// Dailymotion flash
			preg_match('#<object[^>]+>.+?http://www.dailymotion.com/swf/video/([A-Za-z0-9]+).+?</object>#s', $markup, $matches);
			
			// Dailymotion url
			if(!isset($matches[1])) {
				preg_match('#(?:https?://)?(?:www\.)?dailymotion.com/video/([A-Za-z0-9]+)#s', $markup, $matches);
			}
			
			// Dailymotion iframe
			if(!isset($matches[1])) {
				preg_match('#https?://www.dailymotion.com/embed/video/([A-Za-z0-9]+)#s', $markup, $matches);
			}

			// Now if we've found a Dailymotion video ID, let's set the thumbnail URL
			if(isset($matches[1])) {
				$dailymotion_thumbnail = gmp_getDailyMotionThumbnail($matches[1]);
			if (is_wp_error($dailymotion_thumbnail)) {
				  return $dailymotion_thumbnail;
				} else if (isset($dailymotion_thumbnail)) {
				$new_thumbnail = strtok($dailymotion_thumbnail, '?');
				}
			}
		}
		
		// Metacafe
		if($new_thumbnail==null) {
		
			// Find ID from Metacafe embed url
			preg_match('#http://www.metacafe.com/fplayer/([A-Za-z0-9\-_]+)/#s', $markup, $matches);

			// Now if we've found a Metacafe video ID, let's set the thumbnail URL
			if(isset($matches[1])) {
				$metacafe_thumbnail = gmp_getMetacafeThumbnail($matches[1]);
			if (is_wp_error($metacafe_thumbnail)) {
				  return $metacafe_thumbnail;
				} else if (isset($metacafe_thumbnail)) {
					$new_thumbnail = strtok($metacafe_thumbnail, '?');
				}
			}
		}
		
		// Return the new thumbnail variable and update meta if one is found
		if($new_thumbnail!=null) {
		
			// Save as Attachment if enabled
			  $error = '';
				$ch = curl_init(); 
				$timeout = 0; 
				curl_setopt ($ch, CURLOPT_URL, $new_thumbnail); 
				curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1); 
				curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout); 
				curl_setopt($ch, CURLOPT_FAILONERROR, true); // Return an error for curl_error() processing if HTTP response code >= 400
				$image_contents = curl_exec($ch);
				if (curl_error($ch) != null || $image_contents == null) {
					$curl_error = '';
					if (curl_error($ch) != null) {
					$curl_error = ": <code>" . curl_error($ch) . "</code>";
					}
				$error = new WP_Error( 'thumbnail_retrieval', sprintf( __( 'Error retrieving a thumbnail from the URL %1$s. If opening that URL in your web browser shows an image, the problem may be related to your web server and might be something your server administrator can solve.', 'gmp' ), '<a href="' . $new_thumbnail . '">' . $new_thumbnail . '</a>' . $curl_error . '' ) );
			}
				curl_close($ch);

		if ($error != null) {
			return $error;
		} else {

			$upload = wp_upload_bits(basename($new_thumbnail), null, $image_contents);

			$new_thumbnail = $upload['url'];

			$filename = $upload['file'];

			$wp_filetype = wp_check_filetype(basename($filename), null );
			$attachment = array(
				'post_mime_type' => $wp_filetype['type'],
				'post_title' => get_the_title($post_id),
				'post_content' => '',
				'post_status' => 'inherit'
			);
			$attach_id = wp_insert_attachment( $attachment, $filename, $post_id );
			// you must first include the image.php file
			// for the function wp_generate_attachment_metadata() to work
			require_once(ABSPATH . "wp-admin" . '/includes/image.php');
			$attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
			wp_update_attachment_metadata( $attach_id,  $attach_data );

		}
			
			
			
			// Add hidden custom field with thumbnail URL
			if(!update_post_meta($post_id, '_gmp_video_thumbnail', $new_thumbnail)) add_post_meta($post_id, '_gmp_video_thumbnail', $new_thumbnail, true);
			
			// Set attachment as featured image if enabled
			if( get_post_meta($post_id, '_thumbnail_id', true) == '' ) {
				if(!update_post_meta($post_id, '_thumbnail_id', $attach_id)) add_post_meta($post_id, '_thumbnail_id', $attach_id, true);
			}
		}
		return $new_thumbnail;

	}
};

// Echo thumbnail
function gmp_video_thumbnail($post_id=null) {
	if( ( $gmp_video_thumbnail = gmp_get_video_thumbnail($post_id) ) == null ) { echo plugins_url() . "/video-thumbnails/default.jpg"; }
	else { echo $gmp_video_thumbnail; }
};

// Create Meta Fields on Edit Page

add_action("admin_init", "gmp_video_thumbnail_admin_init");

function gmp_video_thumbnail_admin_init(){
	$gmp_video_thumbnails_post_types = apply_filters( 'gmp_video_thumnails_support', array( 'video' ) );
	if(is_array($gmp_video_thumbnails_post_types)) {
		foreach ($gmp_video_thumbnails_post_types as $type) {
			add_meta_box("gmp_video_thumbnail", "Video Thumbnail", "gmp_video_thumbnail_admin", $type, "side", "low");
		}
	}
}
 
function gmp_video_thumbnail_admin(){
	global $post;
	$custom = get_post_custom($post->ID);
	$gmp_video_thumbnail = isset( $custom["_gmp_video_thumbnail"][0] ) ? $custom["_gmp_video_thumbnail"][0] : '';
	
	if(isset($gmp_video_thumbnail) && $gmp_video_thumbnail!='') {
		echo '<p id="video-thumbnails-preview"><img src="' . $gmp_video_thumbnail . '" width="100%" /></p>';
	}
	
	if ( get_post_status() == 'publish' || get_post_status() == 'private' ) {
		if(isset($gmp_video_thumbnail) && $gmp_video_thumbnail!='') {
			echo '<p><a href="#" id="video-thumbnails-reset" onclick="gmp_video_thumbnails_reset(\'' . $post->ID . '\');return false;">'. __( 'Reset Video Thumbnail', 'gmp' ) .'</a></p>';
		} else {
			echo '<p id="video-thumbnails-preview">'. __( 'We didn\'t find a video thumbnail for this post.', 'gmp' ) .'</p>';
			echo '<p><a href="#" id="video-thumbnails-reset" onclick="gmp_video_thumbnails_reset(\'' . $post->ID . '\');return false;">'. __( 'Search Again', 'gmp' ) .'</a></p>';
		}
	} else {
		if(isset($gmp_video_thumbnail) && $gmp_video_thumbnail!='') {
			echo '<p><a href="#" id="video-thumbnails-reset" onclick="gmp_video_thumbnails_reset(\'' . $post->ID . '\');return false;">'. __( 'Reset Video Thumbnail', 'gmp' ) .'</a></p>';
		} else {
			echo '<p>'. __( 'A video thumbnail will be found for this post when it is published ', 'gmp' ) .'.</p>';
		}
	}
}

// AJAX Searching

if ( in_array( basename($_SERVER['PHP_SELF']), apply_filters( 'gmp_video_thumbnails_editor_pages', array('post-new.php', 'page-new.php', 'post.php', 'page.php') ) ) ) {
	add_action('admin_head', 'gmp_video_thumbnails_ajax');
}

function gmp_video_thumbnails_ajax() {
?>

<!-- Video Thumbnails Researching Ajax -->
<script type="text/javascript" >
function gmp_video_thumbnails_reset(id) {

	var data = {
		action: 'gmp_video_thumbnails',
		post_id: id
	};
	
	document.getElementById('video-thumbnails-preview').innerHTML = '<?php _e( 'Working', 'gmp' ); ?>&hellip; <img src="<?php echo home_url('wp-admin/images/loading.gif'); ?>"/>';

	// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
	jQuery.post(ajaxurl, data, function(response) {
		document.getElementById('video-thumbnails-preview').innerHTML = response;
	});
};
</script>
<?php
}

add_action('wp_ajax_gmp_video_thumbnails', 'gmp_video_thumbnails_callback');

function gmp_video_thumbnails_callback() {
	global $wpdb; // this is how you get access to the database

	$post_id = $_POST['post_id'];

	delete_post_meta($post_id, '_gmp_video_thumbnail');

	$gmp_video_thumbnail = gmp_get_video_thumbnail($post_id);

	if ( is_wp_error($gmp_video_thumbnail) ) {
		echo $gmp_video_thumbnail->get_error_message();
	} else  if ( $gmp_video_thumbnail != null ){
		echo '<img src="' . $gmp_video_thumbnail . '" width="100%" />';
	} else {
		_e( 'We didn\'t find a video thumbnail for this post. (be sure you have saved changes first)', 'gmp' );
	}

	die();
}

// Find video thumbnail when saving a post, but not on autosave

add_action('new_to_publish', 'save_gmp_video_thumbnail', 99, 1);
add_action('draft_to_publish', 'save_gmp_video_thumbnail', 99, 1);
add_action('pending_to_publish', 'save_gmp_video_thumbnail', 99, 1);
add_action('future_to_publish', 'save_gmp_video_thumbnail', 99, 1);

function save_gmp_video_thumbnail( $post ){
	$post_type = get_post_type( $post->ID );
	$gmp_video_thumbnails_post_types = apply_filters( 'gmp_video_thumnails_support', array( 'video' ) );
	if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) {
		return null;
	} else {
		// Check that Video Thumbnails are enabled for current post type
		if (in_array($post_type, (array) $gmp_video_thumbnails_post_types) || $post_type == $gmp_video_thumbnails_post_types) {
			gmp_get_video_thumbnail($post->ID);
		} else {
			return null;
		}
	}
}


}
