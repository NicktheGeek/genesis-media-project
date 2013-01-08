<?php

global $gmp_wide;

$content_width = isset( $content_width ) ? $content_width : 600;
$width         = isset( $gmp_wide )      ? $gmp_wide      : $content_width;

add_image_size( 'video', $width, ceil( $width*.5625 ), true );