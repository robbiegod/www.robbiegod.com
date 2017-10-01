# IJ Ultrafeed
This the core plugin for creating a large scale social feed using a variety of sources.

### Changelog

- 1.1.1 - added new column to table to better handle twitter. RT PostCreator.
- 1.1.0 - added new option for twitter to show full card in the feed.
- 1.0.9 - added README.md; removed changelog and task list from plugin.
- 1.0.8 - update copy; changed and fixed comments; added to github
- 1.0.7 - updated the show feed short code
- 1.0.6 - updated ijUKID to varchar(255) - Facebook IDs are larger than a BIGINT can handle.
- 1.0.5 - updated data table to show facebook output in the media column.
- 1.0.4 - updated the crosby and light themes
- 1.0.3 - increased the number of characters for the username
- 1.0.2 - added notes
- 1.0.1 - added Facefeed settings panel
- 1.0.0 - replaced the auto-update function
- 0.9.9 - updated plugin checks to match the update slug names of the other plugins
- 0.9.8 - removed the ij_tweet_theme variable; fixed bug with the ij_theme setting, but continue testing
- 0.9.7 - removed the dev stuff for plugin updates
- 0.9.6 - testing new auto update functions
- 0.9.5 - renamed filename to match folder name
- 0.9.4 - fixed ijtheme variable bug; line 490; moved the variable outside of the function; fixed
- 0.9.3 - added option to not load a theme
- 0.9.2 - merged settings panel from tweets
- 0.9.1 - merged settings panel from gogramit
- 0.9.0 - add admin.plugin line
- 0.8.9 - added new column to show status of the asset;  We adding in a way to hide items
- 0.8.8 - enhanced the shortcode to display gogramits a special format.  Display thumbnail and link to the larger image
- 0.8.7 - added username field; more updates coming
- 0.8.6 - instagram added videos; Added MediaType to distinguish between videos & images; Effects the output;
- 0.8.5 - added cron schedule portion so its available to all plugins
- 0.8.4 - added new column - ijMedia; This is used to add the image / video urls from whatever source
- 0.8.3 - changed the position on the menu to decimal; added cron function so its available to all plugins
- 0.8.2 - Add shortcode to display the feed: ijShowFeed
- 0.8.1 - Changed the name of the table; We are now going to keep things seperate from wordpress posts


### Task List

[ ] merge the view feed to one panel in the admin; This means all new plugins will only have a get feed panel.

[ ] Gogramit: see about broken images; maybe it's a privacy setting? only saw one so might not be a big deal; or maybe the _5 and _7 is not totally consistent.

[ ] remove ijImported column since we are not importing into wordpress posts anymore.

[ ] Facefeed added; Update special display rules in the shortcode for Facefeed.

[ ] setup install / uninstall hooks.

[ ] Move db setup to the install function

[ ] Move delete data option to the uninstall hook.
