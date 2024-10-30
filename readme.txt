=== Laiser Tag ===
Contributors: pcis
Tags: tagging, semantic data, open calais, taxonomy, content optimization
Requires at least: 4.6
Tested up to: 5.4
Stable tag: 1.2.5
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Laiser Tag is an automated tagging plugin that uses the Open Calais API to generate tags for created content within a WordPress Site.

== Description ==

Turbocharge your structured content to help your users find what they are looking for and increase visibility on the internet.  Laiser Tag is fast, easy to use and proven to be effective.

Laiser Tag is an automated tagging plugin for WordPress that uses the Open Calais API by fetching semantic data (people, places, events, etc.) relevant to the body of a content post in WordPress and converting that data into associated tags. Laiser Tag is developed and supported by by Pacific Coast Information Systems.

WordPress administrators install the plugin, and obtain an Open Calais API key which is used to send tag requests that are used in populating tags automatically on all specified WordPress content.

Features:

* Fully automatic tag generation for WordPress content, ideal for sites with large amounts of information and publishing activity.
* Open Calais API integration
* Selectable top-level categories within WordPress
* Customizable relevance setting
* Optional automatic batch processing and edited content re-tagging
* Manual batch initiation
* Tag blacklist for excluding unwanted/incorrect tags

== Usage ==

Laiser Tag is fully automatic, you can set it and forget it. As new content is created, Laiser Tag will automatically utilize the Open Calais system to determine appropriate tags, which are then added to WordPress and to the site maps.

== Extend Functionality ==

=== Laiser Tag Plus ===

Utilize the Laiser Tag Plus plugin to get content authoring tools that allow you to see what tags are available for your content while you are creating it. The Laiser Tag Plus plugin also allows you to preview Flickr images which are relevant to your posts, and use the Open Calais PermID tags.

=== Laiser Tag Insights* ===

Measure the benefits of your structured content by linking your Google Webmaster account to Laiser Tag Insights to visualize organic search and content performance. Identify trends and content that is resonating with your audience.

*requires the Laiser Tag Wordpress Plugin

== Installation ==

1. Obtain an Open Calais API key by going to http://www.opencalais.com/ and registering
2. Upload the plugin files to the your plugin directory, directory, or install the plugin through the WordPress plugins screen.
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Use the Settings->Laiser Tag screen to configure the plugin
4. Paste your OpenCalais API key in the top field.
5. Select the top categories within your WordPress site you wish to limit tagging activity to. (If none selected, all will be tagged.)
6. Use the slider to select the desired relevance score percentage of the tags generated. In general, scores of 80% are considered high, 50% moderate, and 20% low.
7. If you wish to re-tag existing content after editing, check the "Add Tags on Post Update" box.
8. Review batch tagging value. Recommended batch sizes are between 50 and 200, to prevent free Open Calais accounts from exceeding daily request limits. Automatic batches are run every 60 minutes.

Note: At the bottom of the plugin settings, it will display the amount of content remaining that has not yet been tagged. To initiate a batch process manually, click the "Run Batch Process" button.


== Frequently Asked Questions ==

= What is Open Calais? =

Open Calais is a free web service provided by Refinitiv. The API is used for incorporating semantic functionality into content management systems, applications, websites and even blogs. It enables users to create and attach rich semantic metadata (tags) from their submitted unstructured text.

= Why should I use Laiser Tag? =

Unlike other WordPress tagging plugins, Laiser Tag automates the tagging process by analyzing your content after you've saved and published it. There is no intervention required on your part, Laiser Tag takes care of the work. It's useful for everyone, but especially useful for highly automated websites which aggregate a lot of content, or create large amounts of content in short periods of time. It's also especially good if you simply want a reliable web service to manage the tagging of your site's content automatically.

= Can I use other tagging API's other than Open Calais? =

No. Not at this time, however in future versions other tagging options may be made available.

= How often does the batch tagging process run? =

The batch process runs once every 60 minutes, taking the newest content first. You can manually initiate a batch process by clicking the "Run Batch Process" button on the plugin settings screen. Only one batch may run at a time.

= The batch process keeps saying "Cannot run batch process; batch already started" and my posts are not being tagged. =

The batch process uses a limiter file to make sure only one can run at a time. Deactivate and reactivate the plugin to reset this file and the batch process should start normally.

== Screenshots ==

1. Batch Tagging Output
2. Admin Settings 1
3. Admin Settings 2

== Changelog ==

= 1.2.5 =
* Added a longer timeout to OpenCalais API calls in order to address an issue with longer posts being processed

= 1.2.4 =
* Added an option to disable the batch tagging process
* Added a tag blacklist
* Posts which cannot be tagged due to Open Calais returning an error will now not be retried in the batch process. These posts will be retried on manual editing if the option is enabled.


= 1.2.3 =
* Fixed issues related to changes in the OpenCalais API

= 1.2.2 =
* Tested compatibility up to WP 5.4

= 1.2.1 =
* Improved error handling
* Improved functionality for checking if the tagging cron is already running

= 1.2.0 =
* Major UI changes; enhanced display and ease of use

= 1.1.2 =
* Moved the log files into the plugin folder
* Added a check to remove the process file on plugin deactivation

= 1.1.1 =
* Updated documentation

= 1.1.0 =
* Fixed a template issue
* Added the tag sitemap functionality

= 1.0.8 =
* Fixed an issue where Open Calais adds 'Draft:' before tag names

= 1.0.7 =
* Minor text fixes

= 1.0.6 =
* Minor text fixes; corrected an issue with PHP versions lower than 5.4

= 1.0.5 =
* Fixed a bug with a generic plugin version constant name

= 1.0.4 =
* Fixed an issue affecting plugin use on PHP versions <5.5

= 1.0.3 =
* Added an additional check to remove the limiter file on plugin activation in case of errors preventing the batch from running

= 1.0.2 =
* Cleaned up the logging files
* Improved error and batch processing feedback
* Added a one time switch to reset processed untagged posts

= 1.0.1 =
* Added a check to truncate content longer than 100KB

= 1.0 =
* Initial version.