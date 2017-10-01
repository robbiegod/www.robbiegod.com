<?php

/*
Plugin Name: Skype share button
Plugin URI: https://www.skype.com/en/create-share-buttons
Description: Get people sharing and talking about your website on Skype. We've made it faster and easier for people to share content from your site, straight into a Skype chat. It is easy to integrate and supports both desktop and mobile sites.
Version: 1.0.2
Author: Skype
Author URI: https://www.skype.com
Text Domain: skype-share
Domain Path: /lang
*/

defined( 'ABSPATH' ) or die( 'Access restricted!' );

// Variables definition
$skype_share_id = "skype-share";
$skype_share_info_url = "https://www.skype.com/en/create-share-buttons";
$skype_share_faq_url = "https://go.skype.com/share.button.faq";
$skype_share_tos_url = "https://go.skype.com/skype.buttons.legal";

$skype_share_button_html_snippet = "<div class='skype-share' data-href='{ARTICLE_URL}' data-lang='{BUTTON_LANGUAGE}' data-style='{BUTTON_STYLE}' data-source='WordPress' ></div><div style='clear:both;padding-bottom:10px;'></div>";

$skype_share_button_js_script = "<script>
		// Place this code in the head section of your HTML file 
		(function(r, d, s) {
			r.loadSkypeWebSdkAsync = r.loadSkypeWebSdkAsync || function(p) {
				var js, sjs = d.getElementsByTagName(s)[0];
				if (d.getElementById(p.id)) { return; }
				js = d.createElement(s);
				js.id = p.id;
				js.src = p.scriptToLoad;
				js.onload = p.callback
				sjs.parentNode.insertBefore(js, sjs);
			};
			var p = {
				scriptToLoad: 'https://swx.cdn.skype.com/shared/v/latest/skypewebsdk.js',
				id: 'skype_web_sdk'
			};
			r.loadSkypeWebSdkAsync(p);
		})(window, document, 'script');
		</script>";

$language_file = "languages_list.php";
$language_path = "/lang";

// WordPress Hook-ups
add_action("admin_init", "skype_share_settings");
add_action("admin_menu", "skype_share_menu_item");
add_action('wp_head','hook_skype_share_javascript');
add_action('plugins_loaded', 'wan_load_textdomain');
add_filter("the_content", "add_skype_share_button");
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'skype_share_settings_link' );

// Set l10n text domain
function wan_load_textdomain() {
	global $skype_share_id, $language_path;
	
	load_plugin_textdomain($skype_share_id, false, basename( dirname( __FILE__ ) ) . $language_path );
}

// Add settings link on plugin page
function skype_share_settings_link($links) { 
	global $skype_share_id;
	
	$settings_link = '<a href="' . admin_url( 'options-general.php?page=' . $skype_share_id) . '">' . __("Settings", $skype_share_id) . '</a>'; 
	array_push($links, $settings_link); 
	return $links; 
}

// Skype share button administration page menu item

function skype_share_menu_item()
{
	global $skype_share_id;
	
  	add_submenu_page("options-general.php", __("Skype share button", $skype_share_id), __("Skype share button", $skype_share_id), "manage_options", $skype_share_id, "skype_share_page"); 
}

function skype_share_page()
{
	global $skype_share_id, $skype_share_faq_url, $skype_share_tos_url;
   ?>
      <div class="wrap">
         <h1><?php _e("Skype share button options", $skype_share_id); ?></h1>
         <form method="post" action="options.php">
            <?php
               settings_fields("skype_share_config_section");
               do_settings_sections( $skype_share_id);
               submit_button(); 
            ?>
         </form>
		 <p>
            <?php
                $link = sprintf( wp_kses( __( 'Learn more, check out the <a href="%s" target="_blank">FAQ</a>.', $skype_share_id ), array(  'a' => array( 'href' => array(), 'target' => array('_blank') ) ) ), esc_url( $skype_share_faq_url ) );
                echo $link;
            ?>
		</p>
		<p>
            <?php
                $link = sprintf( wp_kses( __( 'Your use of Skype buttons is subject to the <a href="%s" target="_blank">Terms of Use</a>.', $skype_share_id ), array(  'a' => array( 'href' => array(), 'target' => array('_blank') ) ) ), esc_url( $skype_share_tos_url ) );
                echo $link;
             ?>
		</p>
      </div>
   <?php
}

// Settings page definition

function skype_share_settings()
{
	global $skype_share_id;
	
    add_settings_section("skype_share_config_section", "", null,  $skype_share_id);
    add_settings_field("skype-share-button-enabled", __("Enable share button", $skype_share_id), "skype_share_button_checkbox",  $skype_share_id, "skype_share_config_section");
	add_settings_field("skype-share-button-style", __("Button style", $skype_share_id), "skype_share_button_style_dropdown",  $skype_share_id, "skype_share_config_section");
    add_settings_field("skype-share-position", __("Show share button at", $skype_share_id), "skype_share_button_position_dropdown",  $skype_share_id, "skype_share_config_section");
	add_settings_field("skype-share-language", __("Share button language", $skype_share_id), "skype_share_button_language_dropdown",  $skype_share_id, "skype_share_config_section");
	register_setting("skype_share_config_section", "skype-share-button-enabled");
	register_setting("skype_share_config_section", "skype-share-button-style");
	register_setting("skype_share_config_section", "skype-share-position");
	register_setting("skype_share_config_section", "skype-share-language");
}

function skype_share_button_checkbox()
{  
   ?>
        <input type="checkbox" name="skype-share-button-enabled" value="1" <?php checked(get_option('skype-share-button-enabled'), 1, true); ?> />
   <?php
}

function skype_share_button_style_dropdown() {
	global $skype_share_id;
	
	?>
		<select name='skype-share-button-style' >
			<option title='<?php _e("Large Share", $skype_share_id); ?>' value='large' <?php if (get_option('skype-share-button-style') == 'large') { echo('selected="selected"'); } ?> ><?php _e("Large Share", $skype_share_id); ?></option>
			<option title='<?php _e("Small Share", $skype_share_id); ?>' value='small' <?php if (get_option('skype-share-button-style') == 'small') { echo('selected="selected"'); } ?> ><?php _e("Small Share", $skype_share_id); ?></option>
			<option title='<?php _e("Circle Icon", $skype_share_id); ?>' value='circle' <?php if (get_option('skype-share-button-style') == 'circle') { echo('selected="selected"'); } ?> ><?php _e("Circle Icon", $skype_share_id); ?></option>
			<option title='<?php _e("Square Icon", $skype_share_id); ?>' value='square' <?php if (get_option('skype-share-button-style') == 'square') { echo('selected="selected"'); } ?> ><?php _e("Square Icon", $skype_share_id); ?></option>
		</select>
	<?php
}

function skype_share_button_position_dropdown() {
	global $skype_share_id;
	
	?>
		<select name='skype-share-position' >
			<option title='<?php _e("bottom of article", $skype_share_id); ?>' value='bottom' <?php if (get_option('skype-share-position') == 'bottom') { echo('selected="selected"'); } ?> ><?php _e("bottom of article", $skype_share_id); ?></option>
			<option title='<?php _e("top of article", $skype_share_id); ?>' value='top' <?php if (get_option('skype-share-position') == 'top') { echo('selected="selected"'); } ?> ><?php _e("top of article", $skype_share_id); ?></option>
			<option title='<?php _e("both bottom and top", $skype_share_id); ?>' value='top_bottom' <?php if (get_option('skype-share-position') == 'top_bottom') { echo('selected="selected"'); } ?> ><?php _e("both bottom and top", $skype_share_id); ?></option>
		</select>
	<?php
}

function skype_share_button_language_dropdown() {
	
	global $language_file;
	
	$langs = include $language_file;
	
	?>
		<select name='skype-share-language' >
			<option title='auto' value='auto' <?php if (get_option('skype-share-language') == "auto") { echo('selected="selected"'); } ?> >auto</option>
			<?php
			foreach($langs as $lang){
			?>
				<option title='<?php print($lang); ?>' value='<?php print($lang); ?>' <?php if (get_option('skype-share-language') == $lang) { echo('selected="selected"'); } ?> ><?php print($lang); ?></option>
			<?php
			}
			?>
		</select>
	<?php
}

// Add Skype share button under the article

function add_skype_share_button($content)
{
	if(get_option("skype-share-button-enabled") == 1) {
		
		global $post;
		
		// get article url which will be shared by button
		$url = esc_url(get_permalink($post->ID));
		
		// get chosen Skype share button styling
		$button_style = get_option('skype-share-button-style');
		if (!$button_style) {
			$button_style = "large";
		}
		
		// get chosen Skype share button position
		$button_position = get_option('skype-share-position');
		if (!$button_position) {
			$button_position = "bottom";
		}
		
		// get chosen Skype share language
		$button_lang = get_option("skype-share-language");
		if(!$button_lang) {
			$button_lang = "auto";
		}
		
		// prepare button code snipped to be injected under article
		$html = skype_share_generate_button_snippet($url, $button_style, $button_lang);
		
		switch($button_position) {
			case "top":
				$content = $html.$content;
				break;
			case "bottom":
				$content .= $html;
				break;
			case "top_bottom":
				$content = $html . $content . $html;
				break;
			default:
				$content .= $html;
		}
	}
	
    return $content;
}

function skype_share_generate_button_snippet($url, $style, $lang) {
	
	global $skype_share_button_html_snippet;
	
	$snippet = str_replace("{ARTICLE_URL}", $url, $skype_share_button_html_snippet);
	$snippet = str_replace("{BUTTON_STYLE}", $style, $snippet);
	$snippet = str_replace("{BUTTON_LANGUAGE}", $lang, $snippet);
	
	return $snippet;
}

// add Skype share button JS to head

function hook_skype_share_javascript() {

	global $skype_share_button_js_script;

	echo $skype_share_button_js_script;
}

?>