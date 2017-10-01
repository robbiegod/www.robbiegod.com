<?php 
/*
Plugin Name: IJ UltraFeed
Plugin URI: http://www.cdnmediahub.com/plugins/ij-ultrafeed/
Description: Plugin for importing all social network posts into an ultimate social feed.
Author: Rob Fletcher
Version: 1.1.3
Author URI: http://www.robbiegod.com/
Last Updated: October 01, 2015

=======================================

IJ ULTRAFEED TO DO LIST
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
add_action( 'init', 'ij_ultrafeed_updater_init' );

/**
 * Load and Activate Plugin Updater Class.
 */
function ij_ultrafeed_updater_init() {

    /* Load Plugin Updater */
    require_once( trailingslashit( plugin_dir_path( __FILE__ ) ) . 'includes/plugin-updater.php' );

    /* Updater Config */
    $config = array(
        'base'      => plugin_basename( __FILE__ ), //required
        'dashboard' => false,
        'username'  => false,
        'key'       => '',
        'repo_uri'  => 'http://www.cdnmediahub.com/',
        'repo_slug' => 'ij-ultrafeed',
    );

    /* Load Updater Class */
    new IJ_Ultrafeed_Plugin_Updater( $config );
}
////////////////////////////////////////////////////////////////////
///////////// END OF PLUGIN UPDATE SCRIPT  /////////////////////////
////////////////////////////////////////////////////////////////////

// why do i have to include this? docs say if i am in admin this is already loaded? 
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

// HOOKS
register_activation_hook( __FILE__, 'ijultrafeed_activate' );
register_deactivation_hook( __FILE__, 'ijultrafeed_deactivate' );

// FUNCTIONS
function ijultrafeed_activate() {

	// IJ Ultra Feed Active Variable
	// Add-on plugins will use this value to check if the ultrafeed plugin is installed.  If this value is not present, the other plugins won't work.
	add_option("ijUltraFeed_active", "TRUE");
	
	// ijDeactivation Option
	add_option("ijDeactivation_Option", "2");
	// CAUTION: By setting this option to YES when you deactivate the plugin you will lose all of your stored posts, so make sure you set this wisely.
	// 1=yes; 2=no; Do you want to delete the temp table when you deactivate the plugin?

	// new theme support
	add_option("ij_theme", "none");
	
	// get current author ID
	$current_user = wp_get_current_user();
	add_option("ij_authorID", $current_user->ID); 

	
	
	// setup the ijultrafeed database table
	global $wpdb;
	$table_name = $wpdb->prefix . "ijultrafeed";
	if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
		$sql = "CREATE TABLE " . $table_name . " (
		  `ijID` bigint(20) NOT NULL auto_increment,
		  `ijAuthorID` bigint(20) NOT NULL default '1',
		  `ijDate` datetime NOT NULL default '0000-00-00 00:00:00',
		  `ijDescription` longtext,
		  `ijTitle` text,
		  `ijMedia` text,
		  `ijMediaType` varchar(20) NOT NULL default 'image',
		  `ijUsername` varchar(25) NOT NULL default 'Author',
      `ijPostCreator` varchar(50),
		  `ijSlug` varchar(200),
		  `ijPostType` varchar(20) NOT NULL default 'Tweet',
 		  `ijImported` varchar(1) NOT NULL default '2',
		  `ijUKID` varchar(255) NOT NULL,
		  `ijStatus` varchar(20) NOT NULL default 'Published',
		  PRIMARY KEY ID (ijID),
		  UNIQUE KEY `ijUKID` (`ijUKID`)
		);";
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
	}

}

function ijultrafeed_deactivate() {
	
	// remove this option when the ultrafeed plugin is disabled.
	delete_option("ijUltraFeed_active");
	delete_option("ij_theme");
	
	// delete the authorID
	delete_option("ij_authorID");
	
	// set the var up
	$ijDeactivationOption = get_option('ijDeactivation_Option');
	
	if($ijDeactivationOption == "1") {
		// yes delete the table
		global $wpdb;
		$table_name = $wpdb->prefix . "ijultrafeed";
		$wpdb->query("DROP TABLE IF EXISTS $table_name");
		
		// remove the option
		delete_option("ijDeactivation_Option");

	} // do not delete the table so we are going to not do anything there;
} // close deactivation

// ADD A CUSTOM EVENT INTERVAL. SCHEDULE EVENT TO READ THE FEED EVERY 15 MINS
// CAN CALL THIS NOW FROM OTHER PLUGINS w/o INCLUDING THIS PORTION
add_filter( 'cron_schedules', 'ij_set_cron_interval' );

function ij_set_cron_interval( $schedules ) {
    $schedules['15minutes'] = array(
        'interval' => 900, // 15 mins in seconds
        'display'  => __( 'Every 15 minutes' ),
    );
	return $schedules;
}
/* end of filter for cron */



// Check table for ijPostCreator column
// this column was added in v1.1.1
// Alter table added to patch the table
function ijultrafeed_dbcheck_ijpostcreator() {
	
	global $wpdb;
	$table_name = $wpdb->prefix . "ijultrafeed";
	$tablecheck = $wpdb->get_results( "SELECT ijPostCreator FROM `$table_name` LIMIT 1" );
	
	if (!$tablecheck) {
		
		$patchtable = $wpdb->query("ALTER TABLE `$table_name` ADD `ijPostCreator` varchar(50)");
		echo "<p>Database has been updated. A new column has been added to the table.";
		
	} else {
		echo "<p>Database is up to date.</p>";
	
	}
	
}
add_action( 'ijultrafeed_dbcheck_ijpostcreator_hook', 'ijultrafeed_dbcheck_ijpostcreator', 10, 2 );



// add_action('admin_menu', 'function_name');
// add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position );
// add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function );

// Let's setup the admin panel
add_action('admin_menu', 'ijultrafeed_admin_menu');
function ijultrafeed_admin_menu() {
	// icon
	$icon = plugin_dir_url(__FILE__) . '/images/ij_icon.png';

	add_menu_page('IJ UltraFeed', 'IJ UltraFeed', 'manage_options', 'ijultrafeed-admin-options', 'ijultrafeed_admin_options', $icon, 99.01);
	add_submenu_page('ijultrafeed-admin-options', 'IJ UltraFeed Settings', 'IJ UltraFeed Settings', 'manage_options', 'ijultrafeed-settings', 'ijultrafeed_settings');
	add_submenu_page( 'ijultrafeed-admin-options', 'IJ UltraFeed View Feed', 'IJ UltraFeed View Feed', 'manage_options', 'ijultrafeed-view-feed', 'ijultrafeed_view_feed');
}

// Menu Functions
function ijultrafeed_admin_options() {
	global $title;
	$ij_theme = get_option("ij_theme");
	
	
	echo "<div class=\"wrap\">";
	echo "<h2>" . $title . "</h2>";
	echo "<p>You've successfully installed the IJ UltraFeed plugin.  The basic purpose of this plugin is to build the container for your social feeds.  I'll be releasing other plugins that will be dependent on this plugins functionality.</p>";
	echo "<p>For starters, you should go to the settings panel and decide if you deactivate this plugin, should it also remove all of your data as well. Go do that now.  You can always come back to this screen.</p>";
	echo "<p>The next step is you will want to go to the wordpress plugin store and search IJ My Twitter Feed.  This is the first plugin that works with this plugin.  What its going to let you do is grab your twitter feed and display it on your wordpress website anywhere you want using a shortcode.</p>";
	echo "<p>If you run into any problems along the way, email me at robbiegod@live.com. Try to be as descriptive as you possible can.</p>";
	echo "<p>The last update brings a shortcode called [ijShowFeed].</p>";
	echo "<p>You are currently using the { " . $ij_theme . " } theme.</p>";
	echo "</div>";	
	
}

// IJ ULTRAFEED SETTINGS
function ijultrafeed_settings() {
	global $title;
	
// when you submit the form below, these values get updated.
$ijmytweetfeed_hidden = false;
if(isset($_POST['ijultrafeed_hidden'])) {
	
	//DATA sent from the form
	$ijDeactivation_Option = $_POST['ijDeactivation_Option'];
	$ij_theme = $_POST["ij_theme"];
	
	// update it
	update_option('ijDeactivation_Option', $ijDeactivation_Option);
	update_option('ij_theme', $ij_theme);
	
	
	// tweets post values and update only if plugin is installed
	if (is_plugin_active('ij-ultrafeed-tweets/ij-ultrafeed-tweets.php')) {
		
		// DATA sent from the form; clean up the default options;  
		$ijConsumer_Key = $_POST["ij_consumer_key"];
		$ijConsumer_Secret = $_POST["ij_consumer_secret"];
		$ijAccess_Token = $_POST["ij_access_token"];
		$ijAccess_Token_Secret = $_POST["ij_access_token_secret"];
    $ijTwitter_Card_Style = $_POST["ij_twitter_card_style"];
	
		// update the values
		update_option('ij_consumer_key', $ijConsumer_Key);
		update_option('ij_consumer_secret', $ijConsumer_Secret);
		update_option('ij_access_token', $ijAccess_Token);
		update_option('ij_access_token_secret', $ijAccess_Token_Secret);
    update_option('ij_twitter_card_style', $ijTwitter_Card_Style);
		
		echo "<div class=\"updated\"><p><strong>Your Twitter feed is now configured.</strong></p></div>";
	
	}
	
	// gogramit post values and update only if plugin is installed
	if (is_plugin_active('ij-ultrafeed-gogramit/ij-ultrafeed-gogramit.php')) {
		
		//DATA sent from the form; clean up the default options   
		$ij_client_id = $_POST["ij_client_id"];
		$ij_client_secret = $_POST["ij_client_secret"];
		$ij_the_hashtag = $_POST["ij_the_hashtag"];
		$ij_the_limit = $_POST["ij_the_limit"];
		$ij_show_desc = $_POST["ij_show_desc"];
	
		// update the values
		update_option('ij_client_id', $ij_client_id);
		update_option('ij_client_secret', $ij_client_secret);
		update_option('ij_the_hashtag', $ij_the_hashtag);
		update_option('ij_the_limit', $ij_the_limit);
		update_option('ij_show_desc', $ij_show_desc); 
	
	}
	
	
	// facefeed post values and update only if plugin is installed
	if (is_plugin_active('ij-ultrafeed-facefeed/ij-ultrafeed-facefeed.php')) {
		
		//DATA sent from the form; clean up the default options   
		$ijFaceFeedAppID = $_POST["ij_facefeed_app_id"];
		$ijFaceFeedSecretKey = $_POST["ij_facefeed_secret_key"];
		$ijFaceFeedUser = $_POST["ij_facefeed_fbusername"];

	    // update the values
		update_option('ij_facefeed_app_id', $ijFaceFeedAppID);
		update_option('ij_facefeed_secret_key', $ijFaceFeedSecretKey);
		update_option('ij_facefeed_fbusername', $ijFaceFeedUser);
		
		echo "<div class=\"updated\"><p><strong>Your Facefeed is now configured.</strong></p></div>";
	
	}
	echo "<div class=\"updated\"><p><strong>Your option has been saved</strong></p></div>";
	
} else {
	
	//Normal page display and setup the currently stored values
	$ijDeactivation_Option = get_option('ijDeactivation_Option');
	$ij_theme = get_option('ij_theme');
	
	// tweets post values and update only if plugin is installed
	if (is_plugin_active('ij-ultrafeed-tweets/ij-ultrafeed-tweets.php')) {
		
		//Normal page display and setup the currently stored values
		$ijConsumer_Key = get_option('ij_consumer_key');
		$ijConsumer_Secret = get_option('ij_consumer_secret');
		$ijAccess_Token = get_option('ij_access_token');
		$ijAccess_Token_Secret = get_option('ij_access_token_secret');
    $ijTwitter_Card_Style = get_option('ij_twitter_card_style');
		
	}
	
	// Gogramit normal page display and setup the currently stored values
	if (is_plugin_active('ij-ultrafeed-gogramit/ij-ultrafeed-gogramit.php')) {
	
		$ij_client_id = get_option('ij_client_id');
		$ij_client_secret = get_option('ij_client_secret');
		$ij_the_hashtag = get_option('ij_the_hashtag');
		$ij_the_limit = get_option('ij_the_limit');
		$ij_show_desc = get_option('ij_show_desc');
		
	}
	
	
	// facefeed normal page display and setup the currently stored values
	if (is_plugin_active('ij-ultrafeed-facefeed/ij-ultrafeed-facefeed.php')) {
		
		$ijFaceFeedAppID = get_option('ij_facefeed_app_id');
		$ijFaceFeedSecretKey = get_option('ij_facefeed_secret_key');
		$ijFaceFeedUser = get_option('ij_facefeed_fbusername');
		
	}
	
	
	
}// close if
?>

<div class="wrap">
<h2><?php echo $title; ?></h2>
<p>IJ UltraFeed Settings Screen</p>

<form name="ijultrafeed_settingsform" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
<input type="hidden" name="ijultrafeed_hidden" value="Y">

<p><?php _e("If you deactivate this plugin, do you want to delete all of the data in the table? Enter a 1 for YES and a 2 for NO."); ?><br/>
<input type="text" name="ijDeactivation_Option" value="<?php echo $ijDeactivation_Option; ?>" /></p>

<h2><?php _e("Theme Support - Choose a theme"); ?></h2>
<input type="radio" name="ij_theme" value="none" <?php if($ij_theme == 'none') { ?>checked="checked"<?php } ?> /> No Theme (styles are handled by the website)<br/>
<input type="radio" name="ij_theme" value="light" <?php if($ij_theme == 'light') { ?>checked="checked"<?php } ?> /> Light<br/>
<input type="radio" name="ij_theme" value="dark" <?php if($ij_theme == 'dark') { ?>checked="checked"<?php } ?> /> Dark<br/>
<input type="radio" name="ij_theme" value="gray" <?php if($ij_theme == 'gray') { ?>checked="checked"<?php } ?> /> Gray<br/>
<input type="radio" name="ij_theme" value="crosby" <?php if($ij_theme == 'crosby') { ?>checked="checked"<?php } ?> /> Crosby<br/></p>

<h2><?php _e("Database Update?"); ?></h2>
<?php do_action( 'ijultrafeed_dbcheck_ijpostcreator_hook'); ?>


<?php if (is_plugin_active('ij-ultrafeed-tweets/ij-ultrafeed-tweets.php')) { ?>
<h2>Tweets Setting</h2>
<p><?php _e("Consumer Key"); ?><br/>
<input type="text" name="ij_consumer_key" value="<?php echo $ijConsumer_Key; ?>" /></p>

<p><?php _e("Consumer Secret"); ?><br/>
<input type="text" name="ij_consumer_secret" value="<?php echo $ijConsumer_Secret; ?>" /></p>

<p><?php _e("Access Token"); ?><br/>
<input type="text" name="ij_access_token" value="<?php echo $ijAccess_Token; ?>" /></p>

<p><?php _e("Access Token Secret"); ?><br/>
<input type="text" name="ij_access_token_secret" value="<?php echo $ijAccess_Token_Secret; ?>" /></p>

<h2><?php _e("Theme Support - Choose a theme"); ?></h2>
<input type="radio" name="ij_twitter_card_style" value="classic" <?php if($ijTwitter_Card_Style == 'classic') { ?>checked="checked"<?php } ?> /> Classic (strictly tweet message, no additional buttons)<br/>
<input type="radio" name="ij_twitter_card_style" value="full" <?php if($ijTwitter_Card_Style == 'full') { ?>checked="checked"<?php } ?> /> Full Tweet Card (shows photo, favorites button, retweet button)<br/>



<?php } ?>




<?php
// check for ij-ultrafeed_gogramit plugin being installed.  If it is show the settings
if (is_plugin_active('ij-ultrafeed-gogramit/ij-ultrafeed-gogramit.php')) { ?>

<h2>Gogramit Settings</h2>

<input type="hidden" name="ijultrafeed_gogramithidden" value="Y">

<p><?php _e("Client ID"); ?><br/>
<input type="text" name="ij_client_id" value="<?php echo $ij_client_id; ?>" /></p>

<p><?php _e("Client Secret"); ?><br/>
<input type="text" name="ij_client_secret" value="<?php echo $ij_client_secret; ?>" /></p>

<p><?php _e("Hashtag"); ?><br/>
<input type="text" name="ij_the_hashtag" value="<?php echo $ij_the_hashtag; ?>" /></p>

<p><?php _e("Number of Images to return; Keep this low. 10 photos max."); ?><br/>
<input type="text" name="ij_the_limit" value="<?php echo $ij_the_limit; ?>" /></p>

<p><?php _e("Show Description Below Image? Type YES to show the description, leave blank to hide it."); ?><br/>
<input type="text" name="ij_show_desc" value="<?php echo $ij_show_desc; ?>" /></p>


<?php } ?>


<?php if (is_plugin_active('ij-ultrafeed-facefeed/ij-ultrafeed-facefeed.php')) { ?>
		
<h2>Facefeed Setting</h2>
<p>You must setup a Facebook app and also provide your facebook username of the owner of the feed you want to import.</p>
<p><?php _e("Facebook App ID*"); ?><br/>
<input type="text" name="ij_facefeed_app_id" value="<?php echo $ijFaceFeedAppID; ?>" /></p>

<p><?php _e("Facebook Secret Key*"); ?><br/>
<input type="text" name="ij_facefeed_secret_key" value="<?php echo $ijFaceFeedSecretKey; ?>" /></p>

<p><?php _e("Facebook Username*"); ?><br/>
<input type="text" name="ij_facefeed_fbusername" value="<?php echo $ijFaceFeedUser; ?>" /></p>

<?php } ?>



<p class="submit">
<input type="submit" name="Submit" value="<?php _e('Update Options', 'ijultrafeed_settingsform' ) ?>" />
</p>
</form>

</div>

<?php	
} // close settings functions


///////////////////////////////////////////////
//  Global Function to View/Admin the Feed   //
///////////////////////////////////////////////
function ijultrafeed_view_feed() {
	global $title;
	
// statement to update the current photo; this will hide the photo from view.
if(isset($_POST['ijultrafeed_row2delete'])) {
	
	global $wpdb;
	$tablename = $wpdb->prefix . "ijultrafeed";

	// Default usage.
	$wpdb->update( $tablename, array('ijStatus' => 'Hidden'), array( 'ijID' => $_POST['ijultrafeed_row2delete'] ), array('%s'), array( '%d' ) );
	$wpdb->flush();	
	
}
?>
<div class="wrap">
<h2><?php echo $title; ?></h2>
<p>View and Admin the feed.</p>

<table border="1">
<tr>
<td><strong>ijID</strong></td>
<td><strong>ijAuthorID</strong></td>
<td><strong>ijDate</strong></td>
<td><strong>ijTitle</strong></td>
<td width="100"><strong>ijDescription</strong></td>
<td><strong>ijMedia</strong></td>
<td><strong>ijMediaType</strong></td>
<td><strong>ijUsername</strong></td>
<td><strong>ijPostCreator</strong></td>
<td><strong>ijSlug</strong></td>
<td><strong>ijPostType</strong></td>
<td><strong>ijUKID</strong></td>
<td><strong>ijStatus</strong></td>
<td><strong>ACTION</strong></td>
</tr>
<?php
global $wpdb;
$tablename = $wpdb->prefix . "ijultrafeed";

$myrows = $wpdb->get_results( "SELECT * FROM $tablename WHERE ijStatus = 'Published' ORDER BY ijDate DESC" );

if (!$myrows) {
	echo "<tr>";
		echo "<td colspan='13'>Sorry, there is no data to display.</td>";
	echo "</tr>";
	
} else {

foreach ( $myrows as $myrow )	{
	
		$ijID = $myrow->ijID;
		$ijAuthorID = $myrow->ijAuthorID;
		$ijDate = $myrow->ijDate;
		$ijTitle = $myrow->ijTitle;
		$ijDescription = html_entity_decode($myrow->ijDescription);
		$ijMediaType = $myrow->ijMediaType;
		$ijUsername = $myrow->ijUsername;
    	$ijPostCreator = $myrow->ijPostCreator;
		$ijSlug = $myrow->ijSlug;
		$ijPostType = $myrow->ijPostType;
		$ijUKID = $myrow->ijUKID;
		$ijStatus = $myrow->ijStatus;
		
		if($ijPostType == "Gogramit") {
			$ijMedia = str_replace('_7', '_5', $myrow->ijMedia); // show the thumbnail, we store the large image
		} else {
			$ijMedia = $myrow->ijMedia;
		}
	
		
		echo "<tr>";
		echo "<td>".$ijID."</td>";
		echo "<td>".$ijAuthorID."</td>";
		echo "<td>".$ijDate."</td>";
		echo "<td>".$ijTitle."</td>";
		echo "<td>".$ijDescription."</td>";
		if($ijPostType == "Gogramit") { 
			echo "<td><img src='".$ijMedia."' border='0'></td>"; 
		} else if ($ijPostType == "Facebook") {
			echo "<td>".$ijMedia."</td>";		
		} else { 
			echo "<td>No Media</td>"; 
		} 
		echo "<td>".$ijMediaType."</td>";
		echo "<td>".$ijUsername."</td>";
    	echo "<td>".$ijPostCreator."</td>";
		echo "<td>".$ijSlug."</td>";
		echo "<td>".$ijPostType."</td>";
		echo "<td>".$ijUKID."</td>";
		echo "<td>".$ijStatus."</td>";
		echo "<td>";
		
		echo "<form name='ijultrafeed_delete_row_by_id' method='post' action='".str_replace( '%7E', '~', $_SERVER['REQUEST_URI'])."'>";
		echo "<input type='hidden' name='ijultrafeed_row2delete' value='".$ijID."'>";
		echo "<input type='submit' name='Submit' value='UNPUBLISH'>";
		echo "</form>";
		echo "</td>";
		echo "</tr>"; 
	}
}
?>
</table>

<?php } // End of the View/Admin Panel



// SHORTCODES
// Let's get to shortcoding.  This is an easy way to add the tweets to your blog, just use this shortcode. Place the shortcode in any content block or use doshortcode to run the shortcode from your code.
// The only options available at the moment is the number of tweets you want to display. By default we will display 2 tweets.
function ij_show_feed( $atts ) {
	extract( shortcode_atts( array(
		'numresults' => '20', // by default: show 20 results
		'Type' => '' // by default: show all post types
	), $atts ) );
	
global $wpdb;
$tablename = $wpdb->prefix . "ijultrafeed";

// If the $Type is set, filter the results;
if($Type == "") {
	$myrows = $wpdb->get_results( "SELECT * FROM $tablename WHERE ijStatus = 'Published' ORDER BY ijDate DESC LIMIT $numresults" );
} else {
	$myrows = $wpdb->get_results( "SELECT * FROM $tablename WHERE ijPostType = '$Type' AND ijStatus = 'Published' ORDER BY ijDate DESC LIMIT $numresults" );
}
$classcount = 0;

if (!$myrows) {
	echo "<p>Sorry, you currently don't have anything in your feed. Try tweeting something!</p>";	
} else {


echo "<div class='ijFeed feed-content'><ul class='media'>";
foreach ( $myrows as $myrow ) 
{
	
		$ijID = $myrow->ijID;
		$ijAuthorID = $myrow->ijAuthorID;
		$ijDate = $myrow->ijDate;
		$ijDescription = html_entity_decode($myrow->ijDescription);
		$ijTitle = $myrow->ijTitle;
		$ijMedia = $myrow->ijMedia;
		$ijMediaType = $myrow->ijMediaType;
		$ijUsername = $myrow->ijUsername;
    $ijPostCreator = $myrow->ijPostCreator;
		$ijSlug = $myrow->ijSlug;
		$ijPostType = $myrow->ijPostType;
		$ijImported = $myrow->ijImported;
		$ijUKID = $myrow->ijUKID;
		$ijStatus = $myrow->ijStatus;
		$classcount++;

        $ijTwitter_Card_Style = get_option('ij_twitter_card_style');


	if($ijPostType == "Gogramit") {
		
// get show desc option value
$showdesc = get_option("ij_show_desc");

// output the gramitfeed	
	echo "<li class='".$ijPostType." gram".$classcount."'>";
	if($ijMedia != "" && $ijMediaType == "image") { echo "<a href='".$ijMedia."' class='thumbnail'><img src='".str_replace('_7', '_5', $ijMedia)."' alt='".$ijDescription." submitted by ".$ijUsername."' title='".$ijDescription." submitted by ".$ijUsername."' border='0'></a>";  }
	if($showdesc == "YES") { echo "<p class='ijText'>".$ijDescription."</p>"; 
	echo "<p class='ijDate'>".$ijDate."</p>"; }
	echo "</li>";
	
	} else if ($ijPostType == "Facebook") {

	// output the facebook feed	
	echo "<li class='".$ijPostType." facefeed".$classcount."'>";
	
		if($ijMedia != "" && $ijMediaType == "link") {
			
			echo $ijMedia;
			echo "<p>".$ijDescription."</p>";
			echo "<p class='ijDate'>".$ijDate."</p>";
				
		} else if ($ijMedia != "" && $ijMediaType == "photo") {
			
			echo $ijMedia;
			echo "<p class='ijDate'>".$ijDate."</p>";
			
			
		} else if ($ijMedia != "" && $ijMediaType == "video") {
			
			echo $ijMedia;
			echo "<p>".$ijDescription."</p>";
			echo "<p class='ijDate'>".$ijDate."</p>";
			
		} else {
	
		echo "<p class='ijText'>".$ijDescription."</p>";
		echo "<p class='ijDate'>".$ijDate."</p>";
	
		}

	echo "</li>";

	// DEFAULT OUTPUT IS FOR TWITTER
	} else {
	    
		if($ijTwitter_Card_Style == "classic") {
        	echo "<li class='".$ijPostType."'>";
		    echo "<p class='ijText'>".$ijDescription."</p>";
		    echo "<p class='ijDate'>".$ijDate."</p>";
		    echo "</li>";            
        } else {
            /* Example 

            <blockquote class="twitter-tweet" lang="en">
                <p lang="en" dir="ltr">Suggestion: For each major update <a href="https://twitter.com/Microsoft">@Microsoft</a> must create a new ninja cat on an animal! Android has sweets, we wants <a href="https://twitter.com/hashtag/NinjaCatForUpdates?src=hash">#NinjaCatForUpdates</a></p>&mdash; Rudy Huyn (@RudyHuyn) <a href="https://twitter.com/RudyHuyn/status/646532685226201088">September 23, 2015</a>
            </blockquote>
<script async src="//platform.twitter.com/widgets.js" charset="utf-8"></script>

            */
			// Create a twitter link to display the url in the post
            $tLink = "https://twitter.com/".$ijPostCreator."/status/".$ijUKID;
			//echo '<div class="'.$ijPostType.'-nobars">';
			echo '<div id="tweet-container"></div>';
			echo "<script async charset='utf-8'>
					twttr.ready(function (twttr) {
						twttr.widgets.createTweet('".$ijUKID."', document.getElementById('tweet-container'));
					});</script>";
			//echo "</div>";
            /* echo '<script async src="//platform.twitter.com/widgets.js" charset="utf-8"></script>'; */
			
            

        }		
		
	} // endif
	
} // end foreach


echo "</ul></div>";

	
	} // endif
	
} // end function
add_shortcode("ijShowFeed", "ij_show_feed");



/////////////////////////////////////////////////////
// ADDED GLOBAL THEME SUPPORT                      //
/////////////////////////////////////////////////////
global $ij_theme;
$ij_theme = get_option('ij_theme');

// Load our custom theme into the header of wordpress
function ij_load_theme() {
	$ij_theme = get_option('ij_theme');	
	
	if($ij_theme != "none") {
	// Register the style like this for a plugin:
	wp_register_style( 'ijultrafeed-theme', plugins_url( '/themes/'.trim($ij_theme).'/ij_'.trim($ij_theme).'.css', __FILE__ ), array(), '20130630', 'all' );
	
	// For either a plugin or a theme, you can then enqueue the style:
	wp_enqueue_style( 'ijultrafeed-theme' );
	}
}
if($ij_theme != "none") { add_action( 'wp_enqueue_scripts', 'ij_load_theme' ); }

?>