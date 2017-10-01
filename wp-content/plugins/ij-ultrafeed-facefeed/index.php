<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Facebook Demos</title>
</head>

<body>
<?php
/**
 * Copyright 2011 Facebook, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may
 * not use this file except in compliance with the License. You may obtain
 * a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations
 * under the License.
 */

require 'facebook-php-sdk-master/src/facebook.php';

// Create our Application instance (replace this with your appId and secret).
$fb_config = array(
  'appId'  => '1403595696543555',
  'secret' => '606fa41eb14f5ef55e728af56b6b5e27',
	'cookie' => true
);

// get feed
$facebook = new Facebook($fb_config);
$page_feed = $facebook->api(
	'/CrosbyMarketing/feed',
	'GET',
	array(
		'access_token' => $_SESSION['active']['access_token']
	)
);

// This pretty little function links all of the urls, hashtags etc on twitter from the post.
function linkify_status($status_text)
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
    '\1@&lt;a target="_blank" href="https://www.facebook.com/\2"&gt;\2&lt;/a&gt;',
    $status_text
  );

  // linkify tags
  $status_text = preg_replace(
    '/(^|\s)#(\w+)/',
    '\1#&lt;a target="_blank" href="https://www.facebook.com/hashtag/\2"&gt;\2&lt;/a&gt;',
    $status_text
  );

  return $status_text;
}


foreach($page_feed['data'] as $post):
	if( ($post['type'] == 'status' || $post['type'] == 'link') && !isset($post['story'])):
		
		$status_text = $post['message'];
		$tContent = htmlSpecialChars(html_entity_decode(linkify_status($status_text)));
		$tUsername = $post['from']['name'];
		echo $tUsername;
		

endif;
endforeach;


// Make the cURL request
/*
$curl_request = curl_init($datafeed);
curl_setopt($curl_request, CURLOPT_POST, false );
curl_setopt($curl_request, CURLOPT_FOLLOWLOCATION, true );
curl_setopt($curl_request, CURLOPT_HEADER, false);
curl_setopt($curl_request, CURLOPT_URL, $datafeed);
curl_setopt($curl_request, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl_request, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; .NET CLR 1.0.3705; .NET CLR 1.1.4322; Media Center PC 4.0)');
$json = curl_exec($curl_request);
curl_close($curl_request);
$fbresponse = json_decode($json, TRUE);
print_r($fbresponse);
*/



/*
foreach($fb_response->data as $item){
echo 'Message: ' . $item->message . '<br />';//there is no name returned on a comment
echo 'From ID: ' . $item->from->id . '<br />';
 echo 'From Name: ' . $item->from->name . '<br />';
 echo 'Message: ' . $item->message . '<br />';
 echo 'Timestamp: ' . $item->created_time . '<br /><br />';
}
*/







?>


</body>
</html>