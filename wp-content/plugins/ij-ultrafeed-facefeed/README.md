# IJ Ultrafeed Facefeed
This plugins grabs your posts from Facebook.

### Changelog

- 0.2.4 - sept 30, 2015 - removed all additional sdks except 4.0.12. current one being used.
- 0.2.3 - sept 30, 2015 - created readme.md, cleaned up plugin, moved changelog and task list to readme.md
- 0.2.2 - dec 04, 2014 - added import feed; tested; update the table schema.
- 0.2.1 - dec 04, 2014 - removed authentication; added facebook session; two types of feeds - individuals/businesses - grabbing a page feed now;
- 0.2.0 - dec 02, 2014 - output parts of the feed inside a loop. making progress now!
- 0.1.9 - dec 01, 2014 - can now get the facebook feed from the user the current user.
- 0.1.8 - nov 19, 2014 - fix logout from facebook function.
- 0.1.7 - nov 19, 2014 - updated facebook php sdk to v4.0.12;
- 0.1.6 - oct 28, 2014 - updated facebook php sdk to v4.0.11;
- 0.1.5 - june 2, 2014 - updated facebook php sdk to v4;
- 0.1.4 - dec 27, 2013 - changed method to use FQL type query
- 0.1.3 - dec 19, 2013 - fixed the blank message index by doing a check for blanks and taking the description value if message is blank.
- 0.1.2 - dec 19, 2013 - changed the post type check to be more specific so i can get my status updates.
- 0.1.1 - dec 19, 2013 - initial version of the fb plugin is complete.
- 0.1.0 - dec 19, 2013 - fixed bug issues with the import process; adding missing value for status.
- 0.0.9 - dec 19, 2013 - added session_start() as the first line of the plugin, this removed the errors i was getting; now i'm getting new errors.
- 0.0.8 - dec 19, 2013 - switch to facebook php sdk from github; added session_start().
- 0.0.7 - dec 13, 2013 - update class name to WP_Facebook.
- 0.0.6 - dec 13, 2013 - installed the wordpress facebook plugin; changed to facebook-php-sdk for wordpress from fb plugin;
- 0.0.5 - dec 12, 2013 - bug fixes; line 172 & 219.
- 0.0.4 - dec 11, 2013 - minor bug fix; changed updater function in the includes plugin-updater file.
- 0.0.3 - dec 11, 2013 - minor bug fix; changed updater function name.
- 0.0.2 - dec 11, 2013 - beta plugin completed; removed authorID from activation because its added in the ultrafeed plugin; clean up.
- 0.0.1 - dec 10, 2013 - plugin file created.


### Task List

1. Add uninstall hook

### NOTES
I had just installed v4 of the Facebook PHP SDK, which requires PHP 5.4. I had setup a CDN MEDIA HUB facebook app. I updated CDN MEDIA HUB to PHP 5.4.
