<?php 
/*
Plugin Name: IJ UltraFeed Gogramit
Plugin URI: http://www.crosbymarketing.com/
Description: Plugin for grabbing instagram photos by hashtag.
Author: Rob Fletcher
Version: 0.1.9
Author URI: http://www.crosbymarketing.com/
Last Updated: September 30, 2015

IJ ULTRAFEED GOGRAMIT TO DO LIST
See the README.md file for the task list.

PLUGIN UPDATE LOG
See the README.md file for the update log.

*/
?>
<?php
////////////////////////////////////////////////////////////////////
///////// Function to update the plugins    ////////////////////////
////////////////////////////////////////////////////////////////////
/* hook updater to init */
add_action( 'init', 'ij_ultrafeed_gogramit_updater_init' );

/**
 * Load and Activate Plugin Updater Class.
 */
function ij_ultrafeed_gogramit_updater_init() {

    /* Load Plugin Updater */
    require_once( trailingslashit( plugin_dir_path( __FILE__ ) ) . 'includes/plugin-updater.php' );

    /* Updater Config */
    $config = array(
        'base'      => plugin_basename( __FILE__ ), //required
        'dashboard' => false,
        'username'  => false,
        'key'       => '',
        'repo_uri'  => 'http://www.cdnmediahub.com/',
        'repo_slug' => 'ij-ultrafeed-gogramit',
    );

    /* Load Updater Class */
    new IJ_Ultrafeed_Gogramit_Plugin_Updater( $config );
}
////////////////////////////////////////////////////////////////////
///////////// END OF PLUGIN UPDATE SCRIPT  /////////////////////////
////////////////////////////////////////////////////////////////////



// why do i have to include this? docs say if i am in admin this is already loaded? 
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

// check for ij_ultrafeed
if (is_plugin_active('ij-ultrafeed/ij-ultrafeed.php')) {
	
	// register the plugin activation/deactivation hooks
	// but only if the ultrafeed plugin is installed.
	register_activation_hook( __FILE__, 'ij_gogramit_activate' );
	register_deactivation_hook( __FILE__, 'ij_gogramit_deactivate' );
	
	
} else {

	function ijultrafeed_ggit_not_installed(){
			echo '<div class="error"><p>WARNING: Before you can proceed, please install IJ UltraFeed.</p></div>';
		}
	add_action('admin_notices', 'ijultrafeed_ggit_not_installed');
	
}

// let's activate 
function ij_gogramit_activate() {
	
	// setup a client at instagram.com/developers; save your client id, secret and enter them in wp-admin.
	add_option("ij_client_id", "Client ID");
	add_option("ij_client_secret", "Client Secret");
	
	// other variables
	add_option("ij_the_hashtag", "robbiegod");
	add_option("ij_the_limit", "5");
	add_option("ij_show_desc", "YES");
	
	// get the user id
	// have to add a check for this so we don't duplicate it.
	$current_user = wp_get_current_user();
	add_option("ij_authorID", $current_user->ID);
	
}


function ij_gogramit_deactivate() {

	// kill these vars upon deactivation
	delete_option("ij_client_id");
	delete_option("ij_client_secret");
	delete_option("ij_the_hashtag");
	delete_option("ij_the_limit");
	delete_option("ij_show_desc");
	delete_option("ij_authorID");	
	
}


// schedule the event and run the manupdate function
// sync_files_15min is the trigger.
// quarthour is the custom interval i added above
// ijultrafeed_get_tweet_feed is the function that I want to run every 15mins
// This code should run the function every 15minutes once it is set.
add_action('sync_files_hook', 'ijultrafeed_get_gogramit_feed');

if( !wp_next_scheduled( 'sync_files_hook' ) ) {
	wp_schedule_event(time(), '15minutes', 'sync_files_hook');
}

// Let's setup the admin panel
add_action('admin_menu', 'ijultrafeed_admin_submenu_gogramit', 11);
function ijultrafeed_admin_submenu_gogramit() {

	add_submenu_page( 'ijultrafeed-admin-options', 'Gogramit Get Feed', 'Gogramit Get Feed', 'manage_options', 'ijultrafeed-get-gogramit-feed', 'ijultrafeed_get_gogramit_feed');

}



// import the feed function
function ijultrafeed_get_gogramit_feed() {
	global $title;
	global $wpdb;
	
	// Let's first check to make sure our Twitter API values are setup correctly.  We will do this by checking that the values are not the defaults.
	//Normal page display and setup the currently stored values
	$ij_client_id = get_option('ij_client_id');
	$ij_client_secret = get_option('ij_client_secret');
	$ij_the_hashtag = strtolower(get_option('ij_the_hashtag'));
	$ij_the_limit = get_option('ij_the_limit');
	$ij_authorID = get_option('ij_authorID');
	
if($ij_client_id != "Client ID" && $ij_client_secret != "Client Secret") {
		
echo "<h2>".$title."</h2>";

// Get class for Instagram
// More examples here: https://github.com/cosenary/Instagram-PHP-API
// Updated to v2.1 of the instagram.class.php; 6/2/2014.
require_once 'instagram.class.php';

// Initialize class with client_id
// Register at http://instagram.com/developer/ and replace client_id with your own
$instagram = new Instagram($ij_client_id);

// Get latest photos according to #hashtag keyword
$media = $instagram->getTagMedia($ij_the_hashtag);

// set the ultrafeed table name
$tablename = $wpdb->prefix . "ijultrafeed";

// loop
foreach(array_slice($media->data, 0, $ij_the_limit) as $data) {
	
	// set the vars
	$ijID = ""; // just leave this blank always
	$tAuthorID = get_option('ij_AuthorID');
	$tPubDate = htmlentities(date("Y-m-d  H:i:s",$data->caption->created_time));
	$tContent = $data->caption->text;
	$tTitle = "gogramit-".$data->id;
	$tMedia = $data->images->standard_resolution->url;
	$tMediaType = $data->type;
	$tUsername = $data->user->username;
	$tPostName = "gogramit-".$data->id;
	$tPostType = "Gogramit";
	$tImported = "2";
	$tID = $data->id;
	
	// set video or image
	/*
	if($tMediaType == "image") {
		$tMedia = $data->images->standard_resolution->url;
	} else {
		$tMedia = $data->videos->standard_resolution->url;		
	}
	*/

// for now, i added this to filter out instagram videos.
if($tMediaType == "image") {	
	// Prepare query
    $sql = $wpdb->prepare("
        INSERT INTO $tablename (`ijAuthorID`, `ijDate`, `ijDescription`, `ijTitle`,  `ijMedia`, `ijMediaType`, `ijUsername`, `ijSlug`, `ijPostType`, `ijImported`, `ijUKID`) 
        VALUES (%d, %s, %s, %s, %s, %s, %s, %s, %s, %d, %s) ON DUPLICATE KEY UPDATE ijImported = 1", 
		$tAuthorID, $tPubDate, $tContent, $tTitle, $tMedia, $tMediaType, $tUsername, $tPostName, $tPostType, $tImported, $tID
    );

    // Execute query
    $wpdb->query($sql);
	
}
	

} // ends foreach

// SHOW A MESSAGE IF NOTHING IS IMPORTED or IF SOMETHING IS IMPORTED
if($wpdb->insert_id != "0") {

		echo "<h2>Congratulations!</h2>";
		echo "<p>It seems everything has gone smooth.  You are ready to add your shortcode to your website to display your Gogramit feed.</p>";
		
} else {

		echo "<h2>All hope might not be lost...</h2>";
		echo "<p>Either there was a problem or you don't have any new Gogramits to import. Let's hope for the latter. Check the View Feed panel to verify your Gogramits were imported.</p>";
	
}
$wpdb->flush(); // cleanup

} else {
		// Seriously set those values buddy.

		function ij_setkeys_notice() {
    	
			if (current_user_can( 'install_plugins' )) {
				echo '<div class="error"><p>WARNING: You need to setup the Client Key before you can proceed, so get to it buddy. Hey, then you can come back here and get going. So hurry back now you hear?!</p></div>';
			}

		}
		add_action('admin_notices', 'ij_setkeys_notice');
	} // close if


} // close ijultrafeed_get_gogramit_feed
?>