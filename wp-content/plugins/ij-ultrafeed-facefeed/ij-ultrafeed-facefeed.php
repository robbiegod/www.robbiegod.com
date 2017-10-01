<?php 
/*
Plugin Name: IJ UltraFeed FaceFeed
Plugin URI: http://www.cdnmediahub.com/plugins/ij-ultrafeed-facefeed/
Description: Plugin for importing facebook posts from your personal feed to UltraFeed and displaying them on your Wordpress website. A Facebook app is required.
Author: Rob Fletcher
Version: 0.2.4
Author URI: http://www.crosbymarketing.com/
Last Updated: September 30, 2015

PLUGIN TO-DO LIST
See the README.md file for the task list.

PLUGIN UPDATE LOG
See the README.md file for the update log.

*/
?>
<?php

// All of these includes/use must be near the top of the page.
// Autoload the required files
include( 'facebook-php-sdk-v4.0.12/autoload.php' );

use Facebook\HttpClients\FacebookHttpable;
use Facebook\HttpClients\FacebookCurl;
use Facebook\HttpClients\FacebookCurlHttpClient;
use Facebook\Entities\AccessToken;
use Facebook\Entities\SignedRequest;
use Facebook\FacebookSession;
use Facebook\FacebookRedirectLoginHelper;
use Facebook\FacebookRequest;
use Facebook\FacebookResponse;
use Facebook\FacebookSDKException;
use Facebook\FacebookRequestException;
use Facebook\FacebookOtherException;
use Facebook\FacebookAuthorizationException;
use Facebook\GraphUser;
use Facebook\GraphObject;
use Facebook\GraphSessionInfo;

if ( is_admin() ):
	// start session
	session_start();
	
endif;

////////////////////////////////////////////////////////////////////
///////// Function to update the plugins    ////////////////////////
////////////////////////////////////////////////////////////////////
/* hook updater to init */
add_action( 'init', 'ij_ultrafeed_facefeed_updater_init' );

/**
 * Load and Activate Plugin Updater Class.
 */
function ij_ultrafeed_facefeed_updater_init() {

    /* Load Plugin Updater */
    require_once( trailingslashit( plugin_dir_path( __FILE__ ) ) . 'includes/plugin-updater.php' );

    /* Updater Config */
    $config = array(
        'base'      => plugin_basename( __FILE__ ), //required
        'dashboard' => false,
        'username'  => false,
        'key'       => '',
        'repo_uri'  => 'http://www.cdnmediahub.com/',
        'repo_slug' => 'ij-ultrafeed-facefeed',
    );

    /* Load Updater Class */
    new IJ_Ultrafeed_Facefeed_Plugin_Updater( $config );
}
////////////////////////////////////////////////////////////////////
///////////// END OF PLUGIN UPDATE SCRIPT  /////////////////////////
////////////////////////////////////////////////////////////////////

// why do i have to include this? docs say if i am in admin this is already loaded? 
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

// path to plugin constant
// $pluginpath = plugin_dir_path( __FILE__ );
$pluginsurl = plugins_url('/', __FILE__);
define('MY_AWESOME_PLUGIN_PATH', $pluginsurl);



////////////////////////
/// START OF PLUGIN  ///
////////////////////////

// check for ij_ultrafeed
if (is_plugin_active('ij-ultrafeed/ij-ultrafeed.php')) {
	
	// register the plugin activation/deactivation hooks
	// but only if the ultrafeed plugin is installed.
	register_activation_hook( __FILE__, 'ijfacefeed_activate' );
	register_deactivation_hook( __FILE__, 'ijfacefeed_deactivate' );
	
	
} else {

	function ijultrafeed_not_installed(){
			echo '<div class="error"><p>WARNING: Before you can proceed, please install IJ UltraFeed.</p></div>';
		}
	add_action('admin_notices', 'ijultrafeed_not_installed');
	
}

// schedule the event and run the manupdate function
// sync_files_hook is the trigger.
// quarthour is the custom interval i added above
// ijultrafeed_get_tweet_feed is the function that I want to run every 15mins
// This code should run the function every 15minutes once it is set.
add_action('sync_files_hook', 'ijultrafeed_get_facefeed');
if( !wp_next_scheduled( 'sync_files_hook' ) ) {
	wp_schedule_event(time(), '15minutes', 'sync_files_hook');
}

// Now if the pass checks out ok, then run the activation function and let's this train rolling.
// ACTIVATE / DEACTIVATE FUNCTIONS
function ijfacefeed_activate() {
	
	// admin notice on activation
	add_action( 'admin_notices', 'ij_ultrafeed_tweets_activation_note' );
		
  // Set the default options. We will later add a panel to wordpress that will allow to change these values
  add_option("ij_facefeed_app_id", "Facebook App ID");
  add_option("ij_facefeed_secret_key", "Facebook Secret Key"); 
  add_option("ij_facefeed_fbusername", "Facebook Username");
	
	
} // end activate function

function ijfacefeed_deactivate() {
	// clean up the default options   
	delete_option("ij_facefeed_app_id");
	delete_option("ij_facefeed_secret_key"); 
	delete_option("ij_facefeed_fbusername"); 

}

// SETUP ADMIN PANEL
add_action('admin_menu', 'ijultrafeed_admin_submenu_facefeed', 11);
function ijultrafeed_admin_submenu_facefeed() {

	add_submenu_page( 'ijultrafeed-admin-options', 'Facefeed Get Feed', 'Facefeed Get Feed', 'manage_options', 'ijultrafeed-get-facefeed', 'ijultrafeed_get_facefeed');
	
	// Setup the Hidden Facebook Logout Page, this is hidden from the left menu
	add_submenu_page( NULL, 'Facefeed Get Feed', 'Facebook Logout', 'manage_options', 'ijultrafeed-facebook-logout','ijultrafeed_facebook_logout'
    );
	
}


// import the feed function
function ijultrafeed_get_facefeed() {
	
	global $title;
	global $wpdb;

	// Setup Facebook App Details
	$ijFaceFeedAppID = get_option('ij_facefeed_app_id');
	$ijFaceFeedSecretKey = get_option('ij_facefeed_secret_key');
	$ijFaceFeedUser = get_option('ij_facefeed_fbusername');
	$ijAuthorID = get_option('ij_authorID');
	
	
if($ijFaceFeedAppID === "Facebook App ID" || $ijFaceFeedSecretKey === "Facebook Secret Key" || $ijFaceFeedUser === "Facebook Username" || $ijFaceFeedAppID == "" || $ijFaceFeedSecretKey == "" || $ijFaceFeedUser == "") {
	
	// Facebook app admin warning
	echo '<div class="error"><p>WARNING: You must set up a Facebook App and obtain the Facebook App ID, Secret Key, and the Facebook Username for this plugin to work. Visit the IJ Ultrafeed Setting panel and fill in the details.</p></div>';


} else {
	

	
echo "<h2>".$title."</h2>";

// FACEBOOK PHP SDK v4
// This page shows what column names are in the status
// https://developers.facebook.com/docs/reference/fql/status/
// https://www.webniraj.com/2014/05/01/facebook-api-php-sdk-updated-to-v4-0-0/
//
// https://developers.facebook.com/docs/facebook-login/permissions/v2.2#reference-read_stream
// http://code.tutsplus.com/tutorials/wrangling-with-the-facebook-graph-api--net-23059
//
// https://developers.facebook.com/docs/graph-api/using-graph-api/v2.2
//
//
FacebookSession::setDefaultApplication($ijFaceFeedAppID, $ijFaceFeedSecretKey);

// If you're making app-level requests:
$session = FacebookSession::newAppSession();

// To validate the session:
try {
  $session->validate();
} catch (FacebookRequestException $ex) {
  // Session not valid, Graph API returned an exception with the reason.
  echo $ex->getMessage();
} catch (\Exception $ex) {
  // Graph API returned info, but it may mismatch the current app or have expired.
  echo $ex->getMessage();
}

// graph api request for user data
$request = (new FacebookRequest( 
		$session, 
		'GET', 
		'/'.$ijFaceFeedUser.'/posts?limit=5' 
))->execute();
	
$response = $request->getGraphObject()->asArray();

// set the temp table name
$tablename = $wpdb->prefix . "ijultrafeed";

foreach ( $response['data'] as $r ) {
	if( isset($r->message) ) {
		
	$shavedID = str_replace('_', '', $r->id);
		
	$current_user = wp_get_current_user();

	$ijID = ""; // just leave this blank always
	$fbAuthor = $current_user->ID;
	$fbPubDate = date("Y-m-d H:i:s", strtotime($r->created_time)); // date article was published -> post_date, post_date_gmt
	$fbMsg = $r->message; 
	$fbTitle = "title-".$shavedID; // the title, which for our purposes is not needed, just needs to be unique
	
	$fbMediaType = $r->type;
	
	if( $fbMediaType == "photo") {
	
		$fbPicture = $r->picture;
		$fblink = $r->link;
		$fbMessage = $r->message;
		
		$fbMediaParsed = '<a href="'.$fblink.'" target="_blank"><img src="'.$fbPicture.'" border="0"><p><small>'.$fbMessage.'</small></p></a>';
	
	} else if ( $fbMediaType == "video" ) { 
		
		if( isset($r->name) ) { $fbName = $r->name; }
		$fbLink = $r->link;
		$fbSource = $r->source;
		$fbPicture = $r->picture;
		
		
		if (strpos($fbLink,'facebook') !== false) {
    	
			// this video comes from facebook, so build the player
			// facebook feed does not contain a "name" for videos shared to facebook
			$fbMediaParsed = '<video controls>';
			$fbMediaParsed .= '<source src="'.$fbSource.'" type="video/mp4">';
			$fbMediaParsed .= '</video>';
		
		} else {
			
			// this video comes from another source like youtube, vimeo or something, so we will handle it differently
			$fbMediaParsed = '<a href="'.$fbSource.'" target="_blank"><img src="'.$fbPicture.'" alt="'.$fbName.'"></a>';
		
		}
	
	} else if ( $fbMediaType == "link") {
		
		$fbName = $r->name;
		$fbLink = $r->link;
		$fbDescription = $r->description;
		
		$fbMediaParsed = '<a href="'.$fbLink.'" title="'.$fbName.'">';
		$fbMediaParsed .= '<h5>'.$fbName.'</h5>';
		$fbMediaParsed .= '<p>'.$fbDescription.'</p>';
		$fbMediaParsed .= '<a href="'.$fbLink.'" title="'.$fbName.'"></a>';
		
	}
	
	$fbMediaParsed = $fbMediaParsed;
	
	$fbUsername = $r->from->name;
	$fbSlug = "facebook-".$shavedID;  // this is the slug - this must be unique
	$fbPostType = "Facebook";
	$fbImported = "2";
	$fbUKID = $shavedID;
	$fbStatus = "Published";
	
	// DEBUG
	/*
	echo $fbAuthor . "<br/>";
	echo $fbPubDate . "<br/>";
	echo $fbMsg . "<br/>";
	echo $fbTitle . "<br/>";
	echo $fbMediaType . "<br/>";
	echo $fbMediaParsed. "<br/>";	
	echo $fbUsername . "<br/>";
	echo $fbSlug . "<br/>";
	echo $fbPostType . "<br/>";
	echo "UKID: ".$fbUKID. "<br/>";
	echo $fbStatus . "<br/>";
	*/

$wpdb->show_errors = TRUE;
	
// Prepare query
$sql = $wpdb->prepare("
	INSERT INTO ".$tablename."
	(`ijAuthorID`, `ijDate`, `ijDescription`, `ijTitle`, `ijMedia`, `ijMediaType`, `ijUsername`, `ijSlug`, `ijPostType`, `ijImported`, `ijUKID`, `ijStatus`) 
	VALUES (%d, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s) ON DUPLICATE KEY UPDATE ijImported = 1", 
	$fbAuthor, $fbPubDate, $fbMsg, $fbTitle, $fbMediaParsed, $fbMediaType, $fbUsername, $fbSlug, $fbPostType, $fbImported, $fbUKID, $fbStatus);

    // Execute query
    $wpdb->query($sql);
		
		// DEBUG
		/*
		$wpdb->show_errors();
		$wpdb->print_error();
		*/
		//echo "<hr>";
	
	} // ends if
	
	
} // ends foreach

// SHOW A MESSAGE IF NOTHING IS IMPORTED or IF SOMETHING IS IMPORTED
	if($wpdb->insert_id != "0") {
		// you know we thought we were going to import these into wordpress pots, but realized we have more control if we keep our own table.  So, we are just going to build it that way.  We will maintain our own table.

		echo "<h2>Congratulations!</h2>";
		echo "<p>It seems everything has gone smooth.  You are ready to add your shortcode to your website to display the feed.</p>";
		
	} else {

		echo "<h2>All hope might not be lost...</h2>";
		echo "<p>Either there was a problem or you don't have any new items to import. Let's hope for the latter. Check the View Feed panel to verify your posts were imported.  If you havent posted anything new since the last time we ran this operation, well then there is nothing new to import.</p>";
	
	}
$wpdb->flush(); // cleanup

	}

} // close primary function

// ### end of the plugin ### //
?>