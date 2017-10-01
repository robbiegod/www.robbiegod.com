<?php
/**
***** Crosby Marketing Start Theme functions and definitions.
***** Created on February 1, 2013 to March 6, 2013
*****
***** GENERIC RESPONSIVE STARTER THEME
**/

// run the setup theme function
add_action( 'after_setup_theme', 'genstarter_setup' );

if ( ! function_exists( 'genstarter_setup' ) ):

// setup the defaults
function genstarter_setup() {
	
	// make theme available for translation
	load_theme_textdomain( 'genstarter', get_template_directory() . '/languages' );
	
	// This theme styles the visual editor with editor-style.css to match the theme style.
	add_editor_style();
	
	// Add default posts and comments RSS feed links to <head>.
	add_theme_support( 'automatic-feed-links' );
	
	// register navigation menu position.  You can always add more, but for starters we will have top, primary, and footer navigation
	// Standard positions for the navigation
	register_nav_menu( 'topnavbar', __( 'TopNavBar Menu', 'genstarter' ) ); // short upper right nav
	register_nav_menu( 'primary', __( 'Primary Menu', 'genstarter' ) ); // large primary purple text nav
	register_nav_menu( 'footer', __( 'Footer Menu', 'genstarter' ) ); // footer nav
	
	// add support for the featured image
	add_theme_support( 'post-thumbnails' );
	
	// custom featured image sizes
	// you can alway add more
	// anytime you set a featured image it will generate these sizes
	add_image_size( 'small-thumbnail', 32, 32, true );
	add_image_size( 'medium-thumbnail', 64, 64, true );
	add_image_size( 'large-thumbnail', 128, 128, true );
	
	add_image_size( 'small-image', 320, 240, true );
	add_image_size( 'medium-image', 640, 480, true );
	add_image_size( 'large-image', 800, 600, true );
	add_image_size( 'xlarge-image', 1024, 768, true );
	add_image_size( 'xxlarge-image', 1280, 1024, true );
	add_image_size( '1080p-image', 1920, 1080, true );	

} // end of genstart setup
endif; // ####


// set the length of the excerpt
function genstarter_excerpt_length( $length ) {
	return 40;
}
add_filter( 'excerpt_length', 'genstarter_excerpt_length' );


// Returns a "Continue Reading" link for excerpts
function genstarter_continue_reading_link() {
	return ' <a href="'. esc_url( get_permalink() ) . '">' . __( 'Continue reading <span class="meta-nav">&rarr;</span>', 'ricebowl' ) . '</a>';
}

// Replaces "[...]" (appended to automatically generated excerpts) with an ellipsis and twentyeleven_continue_reading_link().
function genstarter_auto_excerpt_more( $more ) {
	return ' &hellip;' . genstarter_continue_reading_link();
}
add_filter( 'excerpt_more', 'genstarter_auto_excerpt_more' );

// Adds a pretty "Continue Reading" link to custom post excerpts.
function genstarter_custom_excerpt_more( $output ) {
	if ( has_excerpt() && ! is_attachment() ) {
		$output .= genstarter_continue_reading_link();
	}
	return $output;
}
add_filter( 'get_the_excerpt', 'genstarter_custom_excerpt_more' );


// Get our wp_nav_menu() fallback, wp_page_menu(), to show a home link.
function genstarter_page_menu_args( $args ) {
	$args['show_home'] = true;
	return $args;
}
add_filter( 'wp_page_menu_args', 'genstarter_page_menu_args' );


// Display navigation to next/previous pages when applicable
if ( ! function_exists( 'genstarter_content_nav' ) ) :
function genstarter_content_nav( $nav_id ) {
	global $wp_query;

	if ( $wp_query->max_num_pages > 1 ) : ?>
		<nav id="<?php echo $nav_id; ?>">
			<h3 class="assistive-text"><?php _e( 'Post navigation', 'genstarter' ); ?></h3>
			<div class="nav-previous"><?php next_posts_link( __( '<span class="meta-nav">&larr;</span> Older posts', 'genstarter' ) ); ?></div>
			<div class="nav-next"><?php previous_posts_link( __( 'Newer posts <span class="meta-nav">&rarr;</span>', 'genstarter' ) ); ?></div>
		</nav><!-- #nav-above -->
	<?php endif;
}
endif; // genstarter_content_nav


if ( ! function_exists( 'genstarter_entry_meta' ) ) :
/**
 * Prints HTML with meta information for current post: categories, tags, permalink, author, and date.
 *
 * Create your own twentytwelve_entry_meta() to override in a child theme.
 *
 * @since Twenty Twelve 1.0
 */
function genstarter_entry_meta() {
	// Translators: used between list items, there is a space after the comma.
	$categories_list = get_the_category_list( __( ', ', 'genstarter' ) );

	// Translators: used between list items, there is a space after the comma.
	$tag_list = get_the_tag_list( '', __( ', ', 'genstarter' ) );

	$date = sprintf( '<a href="%1$s" title="%2$s" rel="bookmark"><time class="entry-date" datetime="%3$s">%4$s</time></a>',
		esc_url( get_permalink() ),
		esc_attr( get_the_time() ),
		esc_attr( get_the_date( 'c' ) ),
		esc_html( get_the_date() )
	);

	$author = sprintf( '<span class="author vcard"><a class="url fn n" href="%1$s" title="%2$s" rel="author">%3$s</a></span>',
		esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ),
		esc_attr( sprintf( __( 'View all posts by %s', 'genstarter' ), get_the_author() ) ),
		get_the_author()
	);

	// Translators: 1 is category, 2 is tag, 3 is the date and 4 is the author's name.
	if ( $tag_list ) {
		$utility_text = __( 'This entry was posted in %1$s and tagged %2$s on %3$s<span class="by-author"> by %4$s</span>.', 'genstarter' );
	} elseif ( $categories_list ) {
		$utility_text = __( 'This entry was posted in %1$s on %3$s<span class="by-author"> by %4$s</span>.', 'genstarter' );
	} else {
		$utility_text = __( 'This entry was posted on %3$s<span class="by-author"> by %4$s</span>.', 'genstarter' );
	}

	printf(
		$utility_text,
		$categories_list,
		$tag_list,
		$date,
		$author
	);
}
endif;
// ##################### //



// Register a widget
// You can register more...
function genstarter_widgets_init() {

	register_sidebar( array(
		'name' => __( 'Sidebar 1', 'genstarter' ),
		'id' => 'sidebar-1',
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget' => "</div>",
		'before_title' => '<h3 class="widget-title">',
		'after_title' => '</h3>'
	) );
}
add_action( 'widgets_init', 'genstarter_widgets_init' );



// Prints HTML with meta information for the current post-date/time and author.
//
// @since Twenty Eleven 1.0 // we renamed our function

if ( ! function_exists( 'genstarter_posted_on' ) ) :

function genstarter_posted_on() {
	printf( __( '<span class="sep">Posted on </span><a href="%1$s" title="%2$s" rel="bookmark"><time class="entry-date" datetime="%3$s" pubdate>%4$s</time></a><span class="by-author"> <span class="sep"> by </span> <span class="author vcard"><a class="url fn n" href="%5$s" title="%6$s" rel="author">%7$s</a></span></span>', 'genstarter' ),
		esc_url( get_permalink() ),
		esc_attr( get_the_time() ),
		esc_attr( get_the_date( 'c' ) ),
		esc_html( get_the_date() ),
		esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ),
		esc_attr( sprintf( __( 'View all posts by %s', 'genstarter' ), get_the_author() ) ),
		get_the_author()
	);
}
endif;


/*
Plugin Name: Image P tag remover
Description: Plugin to remove p tags from around images in content outputting, after WP autop filter has added them. (oh the irony)
Version: 1.0
Author: Fublo Ltd
Author URI: http://fublo.net/
*/
// I hate when unnecessary code is added.  Wrapping an image with a p is weird. So, this little snippet of code removes the auto-p tag from images.
function filter_ptags_on_images($content)
{
    // do a regular expression replace...
    // find all p tags that have just
    // <p>maybe some white space<img all stuff up to /> then maybe whitespace </p>
    // replace it with just the image tag...
    return preg_replace('/<p>\s*(<a .*>)?\s*(<img .* \/>)\s*(<\/a>)?\s*<\/p>/iU', '\1\2\3', $content);
}

// we want it to be run after the autop stuff... 10 is default.
add_filter('the_content', 'filter_ptags_on_images');




// REGISTER CUSTOM POST TYPES
// You can register more, just duplicate the register_post_type code inside of the function and change the values. You are set!
add_action( 'init', 'create_post_type' );
function create_post_type() {
	
	// You'll want to replace the values below with your own.
	register_post_type( 'genstarter', // change the name
		array(
			'labels' => array(
				'name' => __( 'Gen Starter' ), // change the name
				'singular_name' => __( 'genstarter' ), // change the name
			),
			'public' => true,
			'supports' => array ( 'title', 'editor', 'custom-fields', 'page-attributes', 'thumbnail' ), // do you need all of these options?
			'taxonomies' => array( 'category', 'post_tag' ), // do you need categories and tags?
			'hierarchical' => true,
			'menu_icon' => get_bloginfo( 'template_directory' ) . "/images/icon.png",
			'rewrite' => array ( 'slug' => __( 'genstarters' ) ) // change the name
		)
	);
	
}

// JAVASCRIPT FILES
// no matter what javascript file you are loading, this is the way to go.
// By default, we will load jquery.
// If you want to load more javascript files, just copy the jquery lines and change the values to match your script.
// You can load scripts from CDNs too. just remove the get_bloginfo part and insert the full url where it now says /js/.
/* Load jquery the right way! */
function init_js_scripts() {
	
		wp_deregister_script('jquery'); 
        wp_register_script('jquery', get_bloginfo( 'template_directory' ) . '/js/jquery.1.9.1.min.js', false, '1.9.1'); 
        wp_enqueue_script('jquery');
		
		wp_deregister_script('html5shiv'); 
        wp_register_script('html5shiv', get_bloginfo( 'template_directory' ) . '/js/html5.js', false, '3.6.0'); 
        wp_enqueue_script('html5shiv');

		
}
add_action('wp_enqueue_scripts', 'init_js_scripts');


// SHORTCODES
// This shortcode will allow you to Get A Post | p=106
// Use like this, for example: [getPost id='106']
// It will load the post with ID 106 and output that post using the following code.
// This is just in here to show how this can be done.
// The very last line shows the name of shortcode and the function called.
function getPost( $atts ) {
	extract( shortcode_atts( array(
		'id' => '106'
	), $atts ) );
	
	
$getpost = array( 
	'post_type' => 'post',
	'p' => $id,
	'status' => 'published'
);

$loop = new WP_Query( $getpost );
while ( $loop->have_posts() ) : $loop->the_post();

$vf = "<div class='vf'><div class='vf-padder'>";
$vf .= "<h5>".get_the_title()."</h5>";
$vf .= get_the_content();
$vf .= "</div></div>";

return $vf;

endwhile; wp_reset_query();

}
add_shortcode("getpost", "getPost"); // name of shortcode, function called


// one of my favorite functions.
// GET THE PARENT SLUG
// you'd think this was easier.
function the_parent_slug() {
  global $post;
  if($post->post_parent == 0) return '';
  $post_data = get_post($post->post_parent);
  return $post_data->post_name;
}

// OUTPUT the_slug()
// usage: echo the_slug();
//
// other usage: $myslug = the_slug();
//
// YES! you can pass the value to a php variable!
// That's it.
function the_slug() {
$post_data = get_post($post->ID, ARRAY_A);
$slug = $post_data['post_name'];
return $slug; }

function post_name() {
global $post;
$title = sanitize_title($post->post_title);
echo $title;
}

// Why is this not standard in Wordpress? Who knows?
// check for a single template
// Make a post template by category.
// usage: make a file called 'single-12.php'.
// where 12 is the Category ID number that the post belongs too
// You can then make a custom template for those posts.
add_filter('single_template', create_function('$t', 'foreach( (array) get_the_category() as $cat ) { if ( file_exists(TEMPLATEPATH . "/single-{$cat->term_id}.php") ) return TEMPLATEPATH . "/single-{$cat->term_id}.php"; } return $t;' ));


// custom admin login logo
// you'll want to either comment this out or create your own logo to customize the wordpress admin screen
function custom_login_logo() {
	echo '<style type="text/css">
	h1 a { height:110px !important; background-position: center top !important; background-size: 216px 110px !important; background-image: url('.get_bloginfo('template_directory').'/images/company-logo.png) !important; }
	</style>';
}
add_action('login_head', 'custom_login_logo');





?>