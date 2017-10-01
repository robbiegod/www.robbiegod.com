<?php /* Robbiegod Theme v0.1 - August 25,2012 - Wordpress 3.4.2 */

add_action( 'after_setup_theme', 'setup_robbiegod_theme' );

if ( ! function_exists( 'setup_robbiegod_theme' ) ):

// setup my custom theme && add general setup features here
function setup_robbiegod_theme() {

// This theme uses Featured Images (also known as post thumbnails) for per-post/per-page Custom Header images
if ( function_exists( 'add_theme_support' ) ) {
	add_theme_support( 'post-thumbnails' );
}

}
endif; // setup_robbiegod_theme


// demo shortcode
function makeUrl( $atts , $content = null ) {
extract(shortcode_atts(
    array(
        'href' => '#',
    ), $atts ));
return '<a href="'.$href.'">'.$content.'</a>';
}

add_shortcode('url', 'makeUrl');