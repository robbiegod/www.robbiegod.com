<?php 
/*
Plugin Name: IJ UltraFeed Tweets
Plugin URI: http://www.cdnmediahub.com/plugins/ij-ultrafeed-tweets/
Description: Plugin for importing tweets from my twitter feed to UltraFeed and displaying them on your Wordpress website.
Author: Rob Fletcher
Version: 0.3.4
Author URI: http://www.crosbymarketing.com/
Last Updated: October 01, 2015
	
IJ ULTRAFEED TWEETS TO DO
See the README.md file for the task list.

PLUGIN UPDATE LOG
See the README.md file for the change log.

*/
?>
<?php
////////////////////////////////////////////////////////////////////
///////// Function to update the plugins    ////////////////////////
////////////////////////////////////////////////////////////////////
/* hook updater to init */
add_action( 'init', 'ij_ultrafeed_tweets_updater_init' );

/**
 * Load and Activate Plugin Updater Class.
 */
function ij_ultrafeed_tweets_updater_init() {

    /* Load Plugin Updater */
    require_once( trailingslashit( plugin_dir_path( __FILE__ ) ) . 'includes/plugin-updater.php' );

    /* Updater Config */
    $config = array(
        'base'      => plugin_basename( __FILE__ ), //required
        'dashboard' => false,
        'username'  => false,
        'key'       => '',
        'repo_uri'  => 'http://www.cdnmediahub.com/',
        'repo_slug' => 'ij-ultrafeed-tweets',
    );

    /* Load Updater Class */
    new IJ_Ultrafeed_Tweets_Plugin_Updater( $config );
}
////////////////////////////////////////////////////////////////////
///////////// END OF PLUGIN UPDATE SCRIPT  /////////////////////////
////////////////////////////////////////////////////////////////////



// why do i have to include this? docs say if i am in admin this is already loaded? 
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );


////////////////////////
/// START OF PLUGIN  ///
////////////////////////

// check for ij_ultrafeed
if (is_plugin_active('ij-ultrafeed/ij-ultrafeed.php')) {
	
	// register the plugin activation/deactivation hooks
	// but only if the ultrafeed plugin is installed.
	register_activation_hook( __FILE__, 'ijtweets4uf_activate' );
	register_deactivation_hook( __FILE__, 'ijtweets4uf_deactivate' );
	
	
} else {

	function ijultrafeed_tweets_not_installed(){
			echo '<div class="error"><p>WARNING: Before you can proceed, please install IJ UltraFeed.</p></div>';
		}
	add_action('admin_notices', 'ijultrafeed_tweets_not_installed');
	
}

// schedule the event and run the manupdate function
// sync_files_15min is the trigger.
// quarthour is the custom interval i added above
// ijultrafeed_get_tweet_feed is the function that I want to run every 15mins
// This code should run the function every 15minutes once it is set.
add_action('sync_files_hook', 'ijultrafeed_get_tweet_feed');
if( !wp_next_scheduled( 'sync_files_hook' ) ) {
	wp_schedule_event(time(), '15minutes', 'sync_files_hook');
}



// Now if the pass checks out ok, then run the activation function and let's this train rolling.

// FUNCTIONS
function ijtweets4uf_activate() {
	
	// admin notice on activation
	add_action( 'admin_notices', 'ij_ultrafeed_tweets_activation_note' );
		
  // Set the default options. We will later add a panel to wordpress that will allow to change these values
  add_option("ij_consumer_key", "Consumer Key");
  add_option("ij_consumer_secret", "Consumer Secret"); 
  add_option("ij_access_token", "Access Token"); 
  add_option("ij_access_token_secret", "Access Token Secret");
  
  
} // end activate function

function ijtweets4uf_deactivate() {
	// clean up the default options   
	delete_option("ij_consumer_key");
	delete_option("ij_consumer_secret"); 
	delete_option("ij_access_token"); 
	delete_option("ij_access_token_secret");

}

// Let's setup the admin panel
add_action('admin_menu', 'ijultrafeed_admin_submenu_tweets', 11);
function ijultrafeed_admin_submenu_tweets() {

	add_submenu_page( 'ijultrafeed-admin-options', 'Twitter Get Feed', 'Twitter Get Feed', 'manage_options', 'ijultrafeed-get-tweet-feed', 'ijultrafeed_get_tweet_feed');
}

/*
function ij_ultrafeed_tweets_activation_note() {
	
	$ijConsumer_Key = get_option('ij_consumer_key');
	$ijConsumer_Secret = get_option('ij_consumer_secret');
	$ijAccess_Token = get_option('ij_access_token');
	$ijAccess_Token_Secret = get_option('ij_access_token_secret');
	
	if($ijConsumer_Key == "Consumer Key" || $ijConsumer_Key == "" || $ijConsumer_Secret == "Consumer Secret" || $ijConsumer_Secret == "" || $ijAccess_Token == "Access Token" || $ijAccess_Token == "" || $ijAccess_Token_Secret == "Access Token Secret" || $ijAccess_Token_Secret == "") {	
		$adminnotice = "<p>Don't forget to add your Twitter API keys in the IJ Ultrafeed Wordpress Admin Panel.</p>";
		return $adminnotice;
	}
}
*/



// import the feed function
function ijultrafeed_get_tweet_feed() {
	global $title;
	global $wpdb;
	
	// Let's first check to make sure our Twitter API values are setup correctly.  We will do this by checking that the values are not the defaults.
	$ijConsumer_Key = get_option('ij_consumer_key');
	$ijConsumer_Secret = get_option('ij_consumer_secret');
	$ijAccess_Token = get_option('ij_access_token');
	$ijAccess_Token_Secret = get_option('ij_access_token_secret');
	$ijAuthorID = get_option('ij_authorID');
	
if($ijConsumer_Key != "Consumer Key" || $ijConsumer_Secret != "Consumer Secret" || $ijAccess_Token != "Access Token" || $ijAccess_Token_Secret != "Access Token Secret" || $ijConsumer_Key != "" || $ijConsumer_Secret != "" || $ijAccess_Token != "" || $ijAccess_Token_Secret != "") {
		
echo "<h2>".$title."</h2>";
		
// create OAuth Signature
$oauth_hash = '';
$oauth_hash .= 'oauth_consumer_key='.$ijConsumer_Key.'&';
$oauth_hash .= 'oauth_nonce=' . time() . '&';
$oauth_hash .= 'oauth_signature_method=HMAC-SHA1&';
$oauth_hash .= 'oauth_timestamp=' . time() . '&';
$oauth_hash .= 'oauth_token='.$ijAccess_Token.'&';
$oauth_hash .= 'oauth_version=1.0';
$base = '';
$base .= 'GET';
$base .= '&';
$base .= rawurlencode('https://api.twitter.com/1.1/statuses/user_timeline.json');
$base .= '&';
$base .= rawurlencode($oauth_hash);
$key = '';
$key .= rawurlencode($ijConsumer_Secret);
$key .= '&';
$key .= rawurlencode($ijAccess_Token_Secret);
$signature = base64_encode(hash_hmac('sha1', $base, $key, true));
$signature = rawurlencode($signature);

// contruct cURL headers
$oauth_header = '';
$oauth_header .= 'oauth_consumer_key="'.$ijConsumer_Key.'", ';
$oauth_header .= 'oauth_nonce="' . time() . '", ';
$oauth_header .= 'oauth_signature="' . $signature . '", ';
$oauth_header .= 'oauth_signature_method="HMAC-SHA1", ';
$oauth_header .= 'oauth_timestamp="' . time() . '", ';
$oauth_header .= 'oauth_token="'.$ijAccess_Token.'", ';
$oauth_header .= 'oauth_version="1.0", ';
$curl_header = array("Authorization: Oauth {$oauth_header}", 'Expect:');

// Make the cURL request
$curl_request = curl_init();
curl_setopt($curl_request, CURLOPT_HTTPHEADER, $curl_header);
curl_setopt($curl_request, CURLOPT_HEADER, false);
curl_setopt($curl_request, CURLOPT_URL, 'https://api.twitter.com/1.1/statuses/user_timeline.json');
curl_setopt($curl_request, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl_request, CURLOPT_SSL_VERIFYPEER, false);
$json = curl_exec($curl_request);
curl_close($curl_request);

print_r($json);

$tweets = json_decode($json, TRUE);



// This pretty little function links all of the urls, hashtags etc on twitter from the post.
function linkify_twitter_status($status_text)
{
  // linkify URLs
  $status_text = preg_replace(
    '/(https?:\/\/\S+)/',
    '&lt;a target="_blank" href="\1"&gt;\1&lt;/a&gt;',
    $status_text
  );

  // linkify twitter users
  $status_text = preg_replace(
    '/(^|\s)@(\w+)/',
    '\1@&lt;a target="_blank" href="http://twitter.com/\2"&gt;\2&lt;/a&gt;',
    $status_text
  );

  // linkify tags
  $status_text = preg_replace(
    '/(^|\s)#(\w+)/',
    '\1#&lt;a target="_blank" href="https://twitter.com/search?q=\2"&gt;\2&lt;/a&gt;',
    $status_text
  );

  return $status_text;
}

// maybe use this function to get the current userID, but maybe we should store it in an option during activation.  Add that later.
//$userID = get_the_author_meta('ID');


// Setup the array
$values = array();

// set the temp table name
$tablename = $wpdb->prefix . "ijultrafeed";

// loop through the array
foreach ($tweets as $tweet) {
	
	
    // set the tweet text and retweet text if it exists.
    if(isset($tweet['text'])) {	
        $check_tweet_status = $tweet['text'];
    }

    if(isset($tweet['retweeted_status']['text'])) {
        $check_retweeted_status = $tweet['retweeted_status']['text'];
    }
    

    // set the value of the tweet text, process it late down here
    if(isset($check_retweeted_status)) {
        $status_text = $check_retweeted_status;
    } else {
       $status_text = $check_tweet_status; 
    }

	
	$current_user = wp_get_current_user();

	$ijID = ""; // just leave this blank always
	$tAuthor = $current_user->ID;
	$tPubDate = date("Y-m-d  H:i:s", strtotime($tweet['created_at'])); // date article was published -> post_date, post_date_gmt
	$tDescription = htmlSpecialChars(html_entity_decode(linkify_twitter_status($status_text))); // main tweet text filtered to link all stuff (found this code)
	$tTitle = "title-".$tweet['id']; // the title, which for our purposes is not needed, just needs to be unique
	$tMedia = "none";
	$tMediaTtype = "none";
	$tUsername = $tweet['user']['name'];
    if(isset($tweet['retweeted_status']['user']['screen_name'])) { $tPostCreator = $tweet['retweeted_status']['user']['screen_name']; } else { $tPostCreator = $tweet['user']['name']; }
	$tSlug = "tweet-".$tweet['id'];  // this is the slug - this must be unique
	$tPostType = "Tweet";
	$tImported = 2;
	$tUKID = $tweet['id_str'];
	$tStatus = "Published";
	

	// Prepare query
    $sql = $wpdb->prepare("
        INSERT INTO $tablename (`ijAuthorID`, `ijDate`, `ijDescription`, `ijTitle`, `ijMedia`, `ijMediaType`, `ijUsername`, `ijPostCreator`, `ijSlug`, `ijPostType`, `ijImported`, `ijUKID`, `ijStatus`) 
        VALUES (%d, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %d, %s) ON DUPLICATE KEY UPDATE ijImported = 1", 
		$tAuthor, $tPubDate, $tDescription, $tTitle, $tMedia, $tMediaTtype, $tUsername, $tPostCreator, $tSlug, $tPostType, $tImported, $tUKID, $tStatus
    );

    // Execute query
    $wpdb->query($sql);

} // ends foreach


// SHOW A MESSAGE IF NOTHING IS IMPORTED or IF SOMETHING IS IMPORTED
	if($wpdb->insert_id != "0") {
		// you know we thought we were going to import these into wordpress pots, but realized we have more control if we keep our own table.  So, we are just going to build it that way.  We will maintain our own table.

		echo "<h2>Congratulations!</h2>";
		echo "<p>It seems everything has gone smooth.  You are ready to add your shortcode to your website to display your tweets.</p>";
		
	} else {

		echo "<h2>All hope might not be lost...</h2>";
		echo "<p>Either there was a problem or you don't have any new tweets to import. Let's hope for the latter. Check the View Feed panel to verify your tweets were imported.  If you havent tweeted anything new since the last time we ran this operation, well then there is nothing new to import.</p>";
		echo $wpdb->last_error;
	
	}
$wpdb->flush(); // cleanup


} else {

// Seriously set those values buddy.
function ij_setkeys_notice() {
	if (current_user_can( 'install_plugins' )) {
		echo '<div class="error"><p>WARNING: You need to setup the API Key before you can proceed, so get to it. Hey, then you can come back here.</p></div>';
	}
}
add_action('admin_notices', 'ij_setkeys_notice');

	}
} // close ijultrafeed_get_tweet_feed


// load twitter widget js on activation & function to load js scripts
function load_js_scripts(){
    wp_register_script( 'twitter-widget-js', plugins_url( '/js/twitter.widget.js', __FILE__ ) );
    wp_enqueue_script( 'twitter-widget-js' );
}
add_action( 'wp_enqueue_scripts', 'load_js_scripts' );
// end of my plugin - sweet success!
?>