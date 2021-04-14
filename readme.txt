=== BoldGrid Inspirations ===
Contributors: boldgrid, imh_brad, joemoto, rramo012, timph
Tags: inspiration, customization, build, create, design
Requires at least: 4.4
Tested up to: 5.6
Requires PHP: 5.4
Stable tag: 2.6.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

BoldGrid Inspirations is an inspiration-driven plugin to assist with creating a fresh new website, or to customize an existing website.

== Description ==

BoldGrid Inspirations is an inspiration-driven plugin to assist with creating a fresh new website, or to customize an existing website.

The first phase is Inspiration; the guided tool creates your base website.  If you already have a website, then you can skip this step.

The second phase is Customization; tools to transform your website into your vision.

== Requirements ==

* PHP 5.4 or higher.  PHP 7.3 or higher is recommended.
* PHP cURL.
* PHP setting "opcache.save_comments = 1"
* PHP setting "opcache.load_comments = 1"

== Installation ==

1. Upload the entire boldgrid-inspirations folder to the /wp-content/plugins/ directory.

2. Activate the plugin through the Plugins menu in WordPress.

3. You will find the Inspirations menu in your WordPress Dashboard / admin panel.

== Changelog ==

= 2.6.2 =

Release date: April 14th, 2021

* Update: Add support for footer-menu locations

= 2.6.1 =

Release date: February 16th, 2021

* Update: Disable custom menu by default.
* Bug fix: Add "My Inspirations" link in case js does not redirect.

= 2.6.0 =

Release date: January 25th, 2021

* New feature: Theme deployments can now include theme specific plugins.

= 2.5.2 =

Release date: December 8th, 2020

* Bug fix: Fixed Gutenberg detection, avoid errors when adding media.
* Bug fix: Avoid js errors when editor screens have no ability to add media.

= 2.5.1 =

Release date: October 23rd, 2020

* Bug fix: Fixed "Only variables should be passed by reference" in class-boldgrid-inspirations-purchase-for-publish.php.

= 2.5.0 =

Release date: October 17th, 2020

* New feature: Invoicing and Caching options added as Inspirations features.

= 2.4.4 =

Release date: September 22nd, 2020

* Update: Updated dependencies.

= 2.4.3 =

Release date: August 11th, 2020

* Bug fix: WordPress 5.5 compatibility changes.
* Update: Optimized usage of set_staging_installed.
* Update: Optimized "get total coin cost".
* Update: Updated links in login footer
* Update: Updated dependencies.

= 2.4.2 =

Release date: June 15th, 2020

* Update: Updated dependencies.

= 2.4.1 =

Release date: May 29th, 2020

* Update: Updated dependencies.

= 2.4.0 =

Release date: May 18th, 2020

* Update: Changes needed for Crio.
* Bug fix: Attribution page will not show excpert.
* Bug fix: Fixed display / position of BoldGrid Connect image search results.

= 2.3.1 =

Release date: March 12th, 2020

* Bug fix: Unable to click button on email confirmation page.
* Update: Removed "Add new from GridBlocks" feature.

= 2.3.0 =

Release date: December 17th, 2019

* Bug fix: Added noindex to the attribution page.
* Update: Changed the form plugin from WPForms to weForms.
* Update: Updated dependencies.

= 2.2.2 =

Release date: November 22st, 2019

* Update: BoldGrid Backup is now Total Upkeep - updating references.
* Update: Updated dependency boldgrid/library to 2.10.6.

= 2.2.1 =

Release date: October 15th, 2019

* Update:  Updated dependency boldgrid/library to 2.10.4.

= 2.2.0 =

Release date: September 17th, 2019

* New feature: Recommend the BoldGrid Backup plugin for users needing to transfer a site.
* Update: Updating dependencies

= 2.1.1 =

Release date: September 5th, 2019

* Update: Updating dependencies

= 2.1.0 =

Release date: August 29th, 2019

* Update: Removed the "Welcome to BoldGrid" dashboard widget.
* Update: Add notice to dashboard widget.
* Update: Remove news widget from dashboard.

= 2.0.7 =

Release date: August 16, 2019

* Bug fix: Fixing compact warnings

= 2.0.6 =

Release date: August 1st, 2019

* Update: Updated dependencies

= 2.0.5 =

Release date: July 25th, 2019

* Update: Added a switch for toggling branding of the login page.
* Update: Cleaned up logic on purchase coins page.
* Update: Updated dependencies

= 2.0.4 =

Release date: July 2nd, 2019

* Update: Replaced the BoldGrid RSS feed widget on the dashboard with one in the updated library package.
* Update: Updated dependencies.

= 2.0.3 =

Release date: May 21st, 2019

* Bug fix: Fixing "Call to undefined method getAttribute" error.
* Bug fix: Replacing deprecated filter on login: login_headertitle / login_headertext.

= 2.0.2 =

Release date: Apr 23nd, 2019

* Bug fix: Fixing usage of php's empty function for php < 5.5

= 2.0.1 =

Release date: Apr 19th, 2019

* Bug fix: Fixing class property declaration for php < 5.6

= 2.0.0 =

Release date: Apr 16th, 2019

* Update: Made translation ready. Text domain is boldgrid-inspirations.
* Update: Inspirations process with full screen mode and design updates.
* New feature: Inspirations dashboard.
* New feature: German translations added - de_DE.

= 1.6.5 =

Release date: Jan 29th, 2019

* Bug fix:                       Pages fail to install on Pages > Add New.

= 1.6.4 =

Release date: Dec 5th, 2018

* Bug fix:                       Unable to save "boldgrid_menu_option" on settings page.
* Bug fix:                       Fixed updating plugin via ajax.

= 1.6.3 =

Release date: Dec 4th, 2018

* Bug fix:                       Coin Budget help was not toggling when clicked.

= 1.6.2 =

Release date: Nov 26th, 2018

* Bug fix:      JIRA BGINSP-33   Fixed missing build for library dependencies; Updated production build process to use composer post-autoload-dump hook.

= 1.6.1 =

Release date: Nov 20th, 2018

* Update:       JIRA BGCONN-20   Removed update settings; have been moved to the BoldGrid Library packages.
* Bug fix:      JIRA BGTHEME-558 Fixed conflict between tgmpa plugin installer and the BoldGrid custom update classes.
* Bug fix:                       BoldGrid Connect Search / WP 5.0 fix.
* Bug fix:                       Attribution page not being rebuilt / WP 5.0 fix.
* Bug fix:                       BoldGrid Connect Search in the Customizer / WP 5.0 fix.
* Bug fix:                       Recommended image sizes not working as expected.

= 1.6.0 =
* Update:                       Updated BoldGrid library to version 2.4.2.
* New feature:  JIRA BGINSP-24  Log data when there may be a connection or Ajax error.

= 1.5.8 =
* Bug fix:      JIRA BGINSP-16  Warnings and notices within inspiration w/ Crio theme
* Bug fix:      JIRA BGINSP-23  Fixed issue: Connect Search may load with connection notice.

= 1.5.7 =
* Update:                       Updated to library version 2.3.5.

= 1.5.6 =
* Bug fix:      JIRA BGINSP-14  Fatal in PHP >=7.1.0 when creating internal preview builds.
* Bug fix:      JIRA WPB-3767   Prevent invalid API calls for check-version.
* Update:       JIRA BGBKUP-180 Auto update code moved to library and removed from Inspirations.
* Update:       JIRA WPB-3730   Updated library dependency to ^2.0.0.
* Update:       JIRA BGINSP-3   Forcing display of Connect Key prompt admin notice on the Inspirations page, even if dismissed, until key is entered.
* Update:       JIRA WPB-3684   Updated composer.json, due to package changes.
* New feature:  JIRA BGCNTRL-46 Added filters for manipulating Dashboard help in trial sites.

= 1.5.5 =
* Bug fix:      JIRA BGSTAGE-32 Fixed staging plugin install.

= 1.5.4 =
* Update:       JIRA BGINSP-4   Removed admin notice recommending plugin installations.

= 1.5.3 =
* New feature:  JIRA WPB-3643   Ensure that deployment does not install new wporg plugins if old ones are installed.

= 1.5.2 =
* Bug fix:      JIRA WPB-3587   Menu assignment after deployment broken in WP 4.9.
* Bug fix:      JIRA WPB-3570   Inspirations Select button misplaced in WP 4.9.
* Bug fix:      JIRA WPB-3593   Changes require to help Staging support new Customizer scheduler.

= 1.5.1 =
* Update:                       Updates to library.

= 1.5 =
* Update:                       Bump version.

= 1.4.11 =
* Update:                       Bump version.

= 1.4.10 =
* Bug fix:		JIRA WPB-3336	All and Default categories do not align.
* Bug fix:		JIRA WPB-3337	On a fresh install, Pages - New From GridBlocks fails.
* Bug fix:		JIRA WPB-3333	Image search only searching one provider instead of all.
* Bug fix:		JIRA WPB-3346	Edit Image button not working for attachment.
* Bug fix:		JIRA WPB-3387	Loading GridBlocks just spins.
* Update:		JIRA WPB-3352	Purchase coins through BoldGrid Central.
* Update:		JIRA WPB-3355	Add data-image-url attribute.
* Update:		JIRA WPB-3382	More descriptive creative commons icon.
* Update:		JIRA WPB-3384	Add License details to attachment details.
* Update:		JIRA WPB-3383	Filter out boldgrid-gridblock-set-preview-page.

= 1.4.9 =
* Bug fix:		JIRA WPB-3318	When forcing a preferred form plugin install, first check if plugin is installed before trying to activate.
* Bug fix:		JIRA WPB-3312	Ensure activation of preferred form plugin.  Added filter for preferred slug.
* Bug fix:		JIRA WPB-3317	New page from GridBlocks not working.
* Update:		JIRA WPB-3252	Disable 'default' category and configure 'showcase'.
* Bug fix:		JIRA WPB-3332	New from gridblocks button not showing.

= 1.4.8 =
* New feature:	JIRA WPB-3200	Added WPForms support.
* Update:		JIRA WPB-3292	Updated plugin URI.
* Update:   JIRA WPB-3296 Add Inspirations as first menu item child.
* Bug Fix:  JIRA WPB-3274 Plugins > Add New Updates fail in modals.
* New feature:	JIRA WPB-3293	Resize images during deployment vs imgr server.

= 1.4.7 =
* Update:      JIRA WPB-3243	Change feedback admin notice display frequency.
* Update:      JIRA WPB-3264 Adding twitch social media option.
* New Feature: Added BoldGrid Library to plugin.

= 1.4.6 =
* Bug fix:		JIRA WPB-3179	Gradient style being lost during normal deployment.
* Bug fix:      JIRA WPB-3180   Open WordPress/BoldGrid links in attribution page in new tab.

= 1.4.5 =
* Bug fix:		JIRA WPB-3161	Fixed auto plugin update.
* Bug fix:		JIRA WPB-3162	Fixed issue creating .htaccess file in deployment.
* Bug fix:		JIRA WPB-3171	As an author, when installing a site I do not want background images to be processed.
* Bug fix:		JIRA WPB-3176	Background gradient / url bug during deplyment.

= 1.4.4 =
* Bug fix:		JIRA WPB-3151	Added check and load before using get_plugin_data() for updates.
* Bug fix:		JIRA WPB-3141	Fixed invalid updates for BoldGrid Prime theme.
* Bug fix:		JIRA WPB-3158	Deployment's gallery updates are not saved.

= 1.4.3 =
* New feature:	JIRA WPB-3106	As an Author, I can set background images for elements.
* New feature:	JIRA WPB-3095	Update generic builds to display per theme channel.

= 1.4.2 =
* Bug fix:		JIRA WPB-2745	Fixed upgrade notices displaying when activation version was not recorded.
* Update:		JIRA WPB-3019	Updating attribution link creation.

= 1.4.1 =
* New feature:	JIRA WPB-3044	Automatically get Unsplash attribution.
* Update:		JIRA WPB-3043	Updating plugin description.

= 1.4.0.1 =
* Bug fix:		JIRA WPB-3232	Deploy Staging menu item missing.
* Bug fix:		JIRA WPB-3233	BoldGrid Connect Search missing from new image widget.

= 1.4 =
* Update:		JIRA WPB-2936	Updating YouTube videos for BoldGrid Dashboard's new release.
* Bug fix:		JIRA WPB-2927	Social media menu disappears.
* Update:		JIRA WPB-2949	Configure blog using categories.
* Bug fix:		JIRA WPB-2950	Added max height for reseller logos on login page.
* Bug fix:		JIRA WPB-2925	Sidebar widgets don't match between preview and installed site.
* Bug fix:		JIRA WPB-2951	Images in staging posts not being downloaded.
* Bug fix:		JIRA WPB-2955	Backwards compatibility - maps taking up 200px empty space.
* Bug fix:		JIRA WPB-2984	Attribution page 404.

= 1.3.9 =
* Bug fix:		JIRA WPB-2912	Fixed issue when installing plugins from the Tools Import page.
* Bug fix:		JIRA WPB-2916	Inspirations deploy fatal error if an old forked plugin had the original installed.
* Bug fix:		JIRA WPB-2905	If installing via Author, do not update pages with survey data.
* Bug fix:		JIRA WPB-2910	Unterminated entity reference bug.
* Update:		JIRA WPB-2913	Validate email address in survey.
* Bug fix:		JIRA WPB-2404	iframe timeout in step 2 of Inspirations.
* Bug fix:		JIRA WPB-2173	Error deleting image and redownloading.
* Bug fix:		JIRA WPB-2635	Start over staging affecting active site.
* Bug fix:		JIRA WPB-2493	Publish private posts during staging deployment.
* Bug fix:		JIRA WPB-2796	Social media urls end in /username, go to 404s.

= 1.3.8 =
* Bug fix:		JIRA WPB-2892	Fixed plugin update checks for some scenarios (WP-CLI, Plesk, etc).
* Update:		JIRA WPB-2900	Update verbiage of build coin cost.
* Bug fix:		JIRA WPB-2901	Scroll bars not visible on preview iframe in Chrome.
* Bug fix:                      Removing CTA hooks.

= 1.3.7 =
* Update:		JIRA WPB-2819	Use switch instead of checkbox for Demo.
* Bug fix:		JIRA WPB-2780	Theme screenshots opening directly, rather than within gallery.
* Update:		JIRA WPB-2825	Adjust do not display formatting.
* Update:       JIRA WPB-2829   Updating hook to resolve BoldGrid SEO plugin conflicts.
* Update:		JIRA WPB-2837	Remove loading image after selecting theme in Gallery.
* Update:		JIRA WPB-2839	Minor verbiage change for Add a blog.
* Update:		JIRA WPB-2785	Entering words with apostrophe in it for Site title displays with a Backslash.
* Bug fix:		JIRA WPB-2848	Customize link takes users back to Inspirations.
* Bug fix:		JIRA WPB-2527	'New from GridBlocks' preview page appearing in cart.
* Bug fix:		JIRA WPB-2862	Survey, invalid argument supplied for foreach.
* Bug fix:      JIRA WPB-2601   Inspirations Internet Explorer/ Stuck on loading themes.
* Bug fix:		JIRA WPB-2854	Downloading Image spinner never stops spinning.

= 1.3.6 =
* Bug fix:		JIRA WPB-2772	PHP warnings on deploy in WordPress 4.3.7.
* Bug fix:		JIRA WPB-2766	Plesk and WP-CLI were not getting private repo updates.
* Update:		JIRA WPB-2763	Update email and address on Contact Us page.
* Update:		JIRA WPB-2764	Remove option to add a map.
* Update:		JIRA WPB-2765	Allow iframes for preview builds.
* New feature:	JIRA WPB-2771	Update footer-company-details widget with survey data.
* New feature:	JIRA WPB-2777	Add an Install sample blog checkbox.
* New feature:	JIRA WPB-2778	Setup a blog during deployment.
* Bug fix:		JIRA WPB-2792	Staged posts (private posts) are trashed when starting over active site.
* Update:		JIRA WPB-2800	Ensure 'Install a blog' works with Staging.
* Update:		JIRA WPB-2801	Remove milestones classes.
* Bug fix:		JIRA WPB-2779	Survey not working with Staging.
* Update:		JIRA WPB-2805	Preview builds w & w/o blogs.
* New feature:	JIRA WPB-2806	Add filter for Inspirations configs.
* Bug fix:		JIRA WPB-2808	Do not request generic builds when requesting blog as well.

= 1.3.5 =
* Testing:		JIRA WPB-2744	Tested on WordPress 4.7.
* Update:		JIRA WPB-2376	Filter the bgtfw contact blocks.
* Update:		JIRA WPB-2476	Update case of Company name.
* Update:		JIRA WPB-2747	Update Add a map verbiage.
* Update:		JIRA WPB-2749	Add a map to my Contact page.
* Bug fix:		JIRA WPB-2658	Fix spacing issues atop Inspirations.
* Bug fix:		JIRA WPB-2751	Show all in the smaller view of inspirations doesn't work anymore.
* Bug fix:		JIRA WPB-2757	Apostrophe and other strange characters installed via Inspirations.
* Bug fix:		JIRA WPB-2759	DOMDocument::loadHTML(): htmlParseEntityRef: expecting ';'.

= 1.3.4 =
* Bug fix:		JIRA WPB-2696	Remove placeholders from survey.
* New feature:	JIRA WPB-2697	Update phone numbers in widgets.
* New feature:	JIRA WPB-2699	Use phone number entered during survey.
* Update:		JIRA WPB-2704	Adjust format of how social media icons are saved.
* New feature:	JIRA WPB-2705	Use survey social media items when creating menu.
* Update:		JIRA WPB-2711	Show optional message in survey.
* Update:		JIRA WPB-2712	Have plus sign toggle more social icons.
* Update:		JIRA WPB-2723	Update phone in pages.

= 1.3.3 =
* Bug fix:		JIRA WPB-2627	Back / next buttons should not be clickable.
* Bug fix:		JIRA WPB-2625	Behavior of last image's next button in Inspirations.

= 1.3.2 =
* Update:		JIRA WPB-2582	Always show arrows in Inspirations gallery.
* Update:		JIRA WPB-2583	Ensure first letter in theme's title attribute is capitalized.
* Update:		JIRA WPB-2599	Add placeholder for 4th step to Inspirations.
* Update:		JIRA WPB-2551	Duplicate images.
* New feature:	JIRA WPB-2603	Add initial version of survey.
* Bug fix:		JIRA WPB-2622	Inspirations - Step 4 - Go back button installs site.

= 1.3.1 =
* Misc:			JIRA WPB-2503	Added plugin requirements to readme.txt file.
* Bug fix:		JIRA WPB-2539	Fix possible duplicate connection issue notice from ajax.js call.
* Bug fix:		JIRA WPB-2558	Don't display feedback widget if user hasn't entered their key.
* Bug fix:		JIRA WPB-2559	Don't allow widgets to drag into welcome box area.
* Bug fix:		JIRA WPB-2555	Images in search results flicker.
* Update:		JIRA WPB-2563	Convert Attribution page to use custom post type.
* Update:		JIRA WPB-2568	Added fancybox and large previews to Inspirations.
* Update:		JIRA WPB-2570	Milestone blogname change.
* Update:		JIRA WPB-2574	Milestone Social Media Change.
* Update:		JIRA WPB-2578	Milestone Contact Info Footer Change.

= 1.3 =
* Bug fix:		JIRA WPB-2544	Disable 'Install' button after clicking it.

= 1.2.13 =
* Bug fix:		JIRA WPB-2531	Javascript error checking needed for mine count.

= 1.2.12 =
* Update:		JIRA WPB-2472	Added update notice for 1.3.
* Bug fix:		JIRA WPB-2486	Incorrect page count on All Pages.
* Bug fix:		JIRA WPB-2467	With staging disabled, Customize goes to "Change Themes".
* Update:		JIRA WPB-2488	Remove 'Permanently delete pages instead of sending to trash'.
* Update:		JIRA WPB-2490	Move default option to 'install as staging'.
* Update:		JIRA WPB-2491	Add 'Customize > Active Theme' navigation to Inspirations.
* Bug fix:		JIRA WPB-2496	Require comment text in feedback form.
* Update:		JIRA WPB-2229	Update error reporting when purchasing images.
* Update:		JIRA WPB-2498	Change 'Company Name' to theme name.
* Update:		JIRA WPB-2497	Add new dashboard videos.
* Bug fix:		JIRA WPB-2376	'No search results' method is not cleared in BGCS.

= 1.2.11 =
* Bug fix:      JIRA WPB-2468   Switching between boldgrid admin menu and standard wp menu no longer works.
* Bug fix:		JIRA WPB-2477	If you have an existing site non BG, no route for staging exists.

= 1.2.10 =
* Bug fix:		JIRA WPB-2446	Fixed missing build id passed on site install.
* Bug fix:		JIRA WPB-2426	Insert Gridblock button is missing.
* Bug fix:		JIRA WPB-2443	When starting over, I get a blank page.
* Bug fix:		JIRA WBP-2445	Inspirations is not fetching cached themes.
* Update:		JIRA WPB-2458	Update 'Recommended' verbiage in last step of Inspirations.

= 1.2.9 =
* Misc:			JIRA WPB-2420	Added EOF line breaks.
* Bug fix:		JIRA WPB-2387	Fixed issue with AJAX theme updates and BG theme slugs duplicated in the WP repo.
* Bug fix:		JIRA WPB-2324	Attribution should not show in 404 sitemap.
* Bug fix:		JIRA WPB-2403	No plugins recommended still showing notice.
* Update:		JIRA WPB-2416	Text changes for confirmation section of Inspirations.
* Update:		JIRA WPB-2417	Add additional text to deployment success page for staging.
* Bug fix:		JIRA WPB-2421	Message showing when it shouldn't - We've recognized that you haven't installed...
* Bug fix:		JIRA WPB-2112	BoldGrid Connect Search missing for galleries.
* Bug fix:		JIRA WPB-2422	Fixed CSS Loading graphic animation in chrome to display properly.
* Bug fix:		JIRA WPB-2401	Gallery not displaying correctly in Chrome && FF.
* Bug fix:		JIRA WPB-2423	Trying to get property of non-object in ...stock-photography.php.

= 1.2.8 =
* Bug fix:		JIRA WPB-2398	Error requesting free key.
* Bug fix:		JIRA WPB-2399	Only show feedback widget to admins.

= 1.2.7 =
* Bug fix:		JIRA WPB-2389	Fixed saving BoldGrid Settings.
* Bug fix:		JIRA WPB-2388	Removed duplicate boldgrid_activate().
* Update:		JIRA WPB-2390	Update verbiage for inspirations install success.
* Bug fix:		JIRA WPB-2391	Hide BoldGrid Welcome Panel if key isn't entered yet.
* Bug fix:		JIRA WPB-2392	If key is less than 32 char don't make call to validate.
* Bug fix:		JIRA WPB-2393	Error messages should be removed when resubmitting keys.
* Bug fix:		JIRA WPB-2394	Cursor for show/hide log should be a pointer.
* Update:		JIRA WPB-2395	Update login page styling.
* Bug fix:		JIRA WPB-2396	Remove staging from recommended plugin notices.
* Bug fix:		JIRA WPB-2327	Check if framework is handling plugin recommendations before recommending.

= 1.2.6 =
* Misc:			JIRA WPB-2344	Updated readme.txt for Tested up to 4.6.1.
* Bug fix:		JIRA WPB-2336	Load BoldGrid settings from the correct WP option (site/blog).
* Bug fix:		JIRA WPB-2248	Removed 'New From GridBlocks' button on edit submission page.
* Bug fix:		JIRA WPB-2332	Reset scroll position on step 2 of Inspirations to top.
* Bug fix:		JIRA WPB-2339	Remove notices from Inspirations page.
* Update:		JIRA WPB-2208	Removed tutorials from Inspirations.
* Update:		JIRA WPB-2359	Order 'Category Filter' by category display order.
* Update:		JIRA WPB-2360	Sort themes by category and then order within category.
* Update:		JIRA WPB-2368	Read version constant from plugin file.
* Update:		JIRA WPB-2361	Add BoldGrid Connect Search to Editor's background image tool.
* Update:		JIRA WPB-2354	Preview button needs to always be visible in mobile view.
* Update:		JIRA WPB-2355	Remove extraneous 'Preview' button.
* Bug fix:		JIRA WPB-2364	Inspirations not respecting theme release channel.
* Bug fix:		JIRA WPB-2370	Color in screenshot does not match preview.
* Bug fix:		JIRA WPB-2373	Duplicate themes in Inspirations.
* Bug fix:		JIRA WPB-2379	Wrong budget passed when going form step 2 to step 1.
* Update:		JIRA WPB-2380	Remove references to tutorials in deployment congrats message.
* Bug fix:		JIRA WPB-2383	Image Search tab appears when clicking 'Add GridBlock'.

= 1.2.5 =
* Bug fix:		JIRA WPB-2325	Added wrapper to handle mb_convert_encoding() if mbstring is not loaded.
* Bug fix:		JIRA WPB-2313	Disabled GridBlocks in network admin pages.
* New feature:	JIRA WPB-2268	Changed to resized preview screenshots for Inspirations Design First concept.
* New feature:	JIRA WPB-2287	Adjust device preview buttons in step 2 to behave like those in editor.
* New feature:	JIRA WPB-2291	Auto install staging in final step if user chooses staging.
* Update:		JIRA WPB-2290	Changed 'Install' button to 'Next'.
* Bug fix:		JIRA WPB-2289	Continuously clicking category in step 1 shuffles themes.
* Update:		JIRA WPB-2267	Added message to Inspirations when no generic themes are available.
* Update:		JIRA WPB-2315	Added error handling for malformed ajax results for call to /api/build/get-generic.
* Update:		JIRA WPB-2316	Add error handling for failures to fetch categories.
* Update:		JIRA WPB-2317	Add error handling for failures to fetch pagesets.
* Update:		JIRA WPB-2319	Check user capabilities before prompting for api key.
* Update:		JIRA WPB-2320	Ensure user has permission to edit page before allowing download_and_insert_into_page.
* Update:		JIRA WPB-2322	Sanitize user feedback before adding to options table.
* Update:		JIRA WPB-2323	Allow admin notices to be dismissed per user.
* Update:		JIRA WPB-2326	Update 'update' class to utilize Admin Notices class.
* Update:		JIRA WPB-2327	Check user capabilities before showing admin notices.
* Update:		JIRA WPB-2331	Update confirmation messages.

= 1.2.4 =
* Bug fix:		JIRA WPB-2269	Typo fix in Boldgrid_Inspirations_Dependency_Plugins::print_uninstalled_plugins().
* Bug fix:		JIRA WPB-2270	New From GridBlocks became unavailable.

= 1.2.3 =
* New feature:	JIRA WPB-2172	For preview generic builds, added an option for identification for purges, etc.
* Bug fix:		JIRA WPB-2263	For preview sites under multisite, set the admin email address using the network admin email address.
* Bug fix:		JIRA WPB-2223	Reworked API key validation and connection issue notices, formatting.
* Misc:			JIRA WPB-2256	Updated readme.txt for Tested up to: 4.6.
* Rework:		JIRA WPB-2150	Moved API methods to a new class, formatting, and phpcs rework.
* Bug fix:      JIRA WPB-2224	Hide the email address field when widget is loaded.
* Bug fix:		JIRA WPB-2225	Fixed jQuery Migrate deprecated warning.
* Update:		JIRA WPB-2245	Changed feed to pull from dashboard tag on blog.
* Bug fix:		JIRA WPB-2265	Uncaught TypeError: IMHWPB.BaseAdmin is not a constructor.
* Bug fix:		JIRA WBP-2236	Errors everywhere when logging in as an Editor.
* Bug fix:		JIRA WPB-2234	Add current_user_can checks to Boldgrid_Inspirations->set_api_key_callback().
* Bug fix:		JIRA WPB-2237	Limit ajax requests by user.
* Bug fix:		JIRA WPB-2240	Limit printing of configs in head.

= 1.2.2 =
* Bug fix:		JIRA WPB-2058	Added wrap class to the tutorials page.
* Bug fix:		JIRA WPB-2184	In PHP 5.2, deactivate and die properly.
* New feature:  				Added BoldGrid news widget to dashboard.
* Bug fix: 		JIRA WPB-1994	Fixed issue with WP Theme Editor not being available.
* New feature: 					Added BoldGrid Feedback widget.
* Bug fix:		JIRA WPB-2169	Connect Search defaults to smallest image size when no recommended sizes available.
* Bug fix:    JIRA WPB-2192 Allow bug report to correctly show parent themes if submitted.

= 1.2.1 =
* Bug fix:		JIRA WPB-2160	New From GridBlocks, multiple pages are installed.
* Update:						Changed text of getting and entering connect keys.
* Security:		JIRA WPB-2151	Disabled autocomplete for API key entry fields.
* Bug fix:		JIRA WPB-2145	Fixing issue with theme screenshots on Chrome Ubuntu.

= 1.2 =
* Bug fix:		JIRA WPB-2119	For asset downloads, when Imagick is loaded, set the thread limit to 1.
* Bug fix:		JIRA WPB-2125	Fixing issue where theme was overwritten without version change.
* Bug fix:		JIRA WPB-2104	Go back button hides all themes (Inspirations > Add Theme).
* Bug fix:		JIRA WPB-2107	BoldGrid Connect Search overlapping footer (Dashboard > Media).
* Bug fix:		JIRA WPB-2109	Session issues when starting over and importing active site.
* Bug fix:		JIRA WPB-2116	Changes to the order of images in a gallery are not saving.
* Bug fix:		JIRA WPB-2134	Staging's boldgrid_attribution option and 'Uninitialized string offset' Notice.
* Bug fix:		JIRA WPB-2135	Image not replaced in Page & Post Editor after using Connect Search.

= 1.1.8 =
* Bug fix:		JIRA WPB-2058	Added wrap class to Inspirations, so admin notices are displayed at the top.
* Bug fix:		JIRA WPB-2041	Fixed BoldGrid theme update check in WordPress 4.6.
* Testing:		JIRA WPB-2046	Tested on WordPress 4.5.3.
* New feature:	JIRA WPB-599	Added options for plugin and theme auto-updates via WordPress autoupdater.
* Update:		JIRA WPB-2008	Deploy class updated to allow for is_generic flag.
* Bug fix:		JIRA WPB-1950	Prevent a portait image from displaying atop 'Crop Image' and 'Skip Cropping' buttons.

= 1.1.7 =
* Bug fix:		JIRA WPB-2032	Fixed issue when activating key.  Added nonce to api key form.
* Rework:		JIRA WPB-2030	Updated the "I don't have an API key" section.
* New feature:	JIRA WPB-2029	Added TOS box to API key submission form.
* New feature:	JIRA WPB-1905	Added capability for auto-updates of boldgrid-inspirations by API response.
* Bug fix:		JIRA WPB-2002	Fixed theme update issue where upgrader says is up to date at times.
* Bug fix:		JIRA WPB-2006	Pdes and Homepage not installing correctly on Inpirations Theme Only installs.

= 1.1.6 =
* New feature:	JIRA WPB-1839	Users can now change their theme release channel.
* Security fix:	JIRA WPB-1977	Validate nonce for feedback form diagnostic data callback and form submit.
* Bug fix:		JIRA WPB-1955	Fatal error: Class 'Boldgrid_Staging_Plugin' not found.

= 1.1.5 =
* Bug fix:		JIRA WPB-1914	Staged image used on Active page not showing in cart.

= 1.1.4 =
* Bug fix:		JIRA WPB-1886	Fixed feedback notice being displayed too often (more than a week after submitting).
* New feature:	JIRA WPB-1183	Refresh the Library Tab after downloading an image.
* Update:		JIRA WPB-1865	Update style of 'Transactions' pages to better incorporate BoldGrid Staging's nav menu.
* Update:		JIRA WPB-1884	Passed WordPress 4.5.1 testing.
* Bug fix:		JIRA WPB-1855	Do not display feedback notice on update or setting pages.
* Bug fix:		JIRA WPB-1860	Fixed horizontal line through screenshot in step 2.
* Bug fix:		JIRA WPB-1863	Cart does not look for watermarked images used within staged pages.
* Bug fix:		JIRA WPB-1891	View / Download of images within receipts not working for images purchased via Staging.
* Bug fix:		JIRA WPB-1893	JS errors in console when viewing attachments.
* Bug fix:		JIRA WPB-1900	Attribution shows in menu when menu generated using wp_page_menu.

= 1.1.3 =
* Bug fix:		JIRA WPB-1824	Fixed order of plugin deactivation and uninstall in Start Over process.
* Bug fix:		JIRA WPB-1814	Fixed PHP notice in page and post editor for In Menu when there is a corrupted nav menu array.
* Bug fix:		JIRA WPB-1823	Fixed display of "Themes" H1 and the additional themes bar when choosing active or staging before installing a theme.
* Bug fix:		JIRA WPB-1840	Fixing thumbnail presentation in inspirations and add new theme.

= 1.1.2.3 =
* Update:				Sync version. See version 1.1.1.1.

= 1.1.2.2 =
* Bug fix:		JIRA WPB-1833	Fixed checking for previously downloaded assets in deployment when using multisite (wp-preview).

= 1.1.2.1 =
* Bug fix:		JIRA WPB-1817	BoldGrid Connect Search: Was not being added when changing a header image in the Customizer.
* Rework:		JIRA WPB-1541	Removed feedback form bug report diagnostic report items.
* Bug fix:		JIRA WPB-1816	Fixed update class interference with the Add Plugins page.

= 1.1.2 =
* Bug fix:		JIRA WPB-1809	Fixed undefined index "action" for some scenarios.  Optimized update class and addressed CodeSniffer items.
* Rework:		JIRA WPB-1541	Reworked admin feedback notice.
* Rework:		JIRA WPB-1751	Removed analysis processing and optional logging capabilities.  Added support for XHProf.
* Bug fix:		JIRA WPB-1805	Now adds theme update info on the Customizer Themes page.
* Rework:		JIRA WPB-1785	Enabled and reworked image caching for the preview server.
* Rework:		JIRA WPB-1751	Reworked analysis processing.
* Update:		JIRA WPB-1658	Storing more reliable install data through inspirations.
* Bug fix:		JIRA WPB-1787	When not using BoldGrid menu, cart does not dynamically update total page price.
* Update:		JIRA WPB-1754	Remove attribution page from search results.
* Bug fix:		JIRA WPB-1788	webkit css missing from 'new from gridblocks'.
* New feature:	JIRA WPB-1806	Add 'BoldGrid search' tab when replacing an image.

= 1.1.1.1 =
* Bug Fix:						Fixing logo display on login screen.

= 1.1.1 =
* Bug fix:						Fixed analysis include for preview server.
* Bug fix:						New From GridBlocks: Asset download issues.

= 1.1 =
* New feature:	JIRA WPB-1751	Added analysis processing and optional logging capabilities.
* Bug fix:		JIRA WPB-1781	Removed boldgrid_dismissed_admin_notices from Start Over cleanup.
* New feature:	JIRA WPB-1541	Added feedback notice.
* Bug fix:		JIRA WPB-1747	New From GridBlocks: For non BoldGrid themes, only load grid css.
* Bug fix:		JIRA WPB-1760	New From GridBlocks: Ensure page title shows on preview page.
* Update:		JIRA WPB-1779	New From GridBlocks: Update verbiage for 'Downloading GridBlocks'.

= 1.0.12.1 =
* Bug fix:		JIRA WPB-1710	Fixed missing device preview tabs on Add New Theme preview modal.
* Bug fix:		JIRA WPB-1710	Fixed notice dismissal checking.
* Bug fix:		JIRA WPB-1749	On start over, staging menus are not deleted.
* Bug fix:		JIRA WPB-1755	Gallery images not showing in cart.

= 1.0.12 =
* Bug fix:		JIRA WPB-1740	Fixed "In Menu" messages in editor when staging plugin is not active, and fixed saving menu selections.
* New feature:	JIRA WPB-1726	Added optional feedback for GridBlock Add Page.
* Removed Ft:	JIRA WPB-1710	Removed Inspirations Add Pages; replaced by GridBlocks.
* Misc:			JIRA WPB-1361	Added license file.
* New feature:					Don't assign footer contact widget if using base pagesets.
* Bug Fix:		JIRA WPB-1732	Fixing css issues on login screen (firefox).
* Bug Fix:		JIRA WPB-1687	Image search: Title, Caption, Alt Text and Description do not display on new pages.

= 1.0.11 =
* New feature:	JIRA WPB-1699	Added optional feedback for theme activation.
* New feature:  JIRA WPB-1690   Adding BoldGrid themes to All themes install menu.
* Bug fix:		JIRA WPB-1686	Limited items loaded in network admin pages.
* Improvement:	JIRA WPB-1604	Added a "Cancel" link to the "In Menu" section.
* Improvement:	JIRA WPB-1603	Display menu locations in the editor "In Menu" section.
* Bug fix:		JIRA WPB-1602	Corrected capitalization of "None" under "In menu" in the editor.
* Improvement:	JIRA WPB-1664	Gets api_key and site_hash from configs instead of get_option.
* Bug fix:		JIRA WPB-1597	Fixing indefined index error
* New feature:	JIRA WPB-1649	Added reporting of PHP version and mobile ratio.
* Bug fix:		JIRA WPB-1598	'Mine' count on 'all pages' is incorrect.
* Bug fix:		JIRA WPB-1647	JS error with easy-attachment-preview-size.js.
* Bug fix:		JIRA WPB-1651	When the BG menu is turned off, Appearance link should take you to themes.

= 1.0.10 =
* Bug fix:		JIRA WPB-1632	Fixed handling of subcategory_id in deploy_page_sets.
* New feature:	JIRA WPB-1510	Moved adhoc functions.php to class-boldgrid-inspirations-utility.php (class Boldgrid_Inspirations_Utility).
* Rework:		JIRA WPB-1553	Updated require and include statements for standards.
* Bug fix:		JIRA WPB-1563	Updated pages in which wp_iframe-media_upload.css is loaded.
* Bug fix:		JIRA WPB-1549	Resolve attribution page missing attribution for several images.png.

= 1.0.9.2 =
* Bug fix:						Add GridBlock Sets feature disabled.

= 1.0.9.1 =
* Bug fix:		JIRA WPB-1553	Fixed support for PHP 5.2 to deactivate plugin.
* Bug fix:						Prevent click of links in add_new_page_selection previews.
* Bug fix:		JIRA WPB-1554	Fixed undefined JavaScript variable pagenow for customizer link.

= 1.0.9 =
* Bug fix:		JIRA WPB-1554	Fixed theme link in network dashboard nav menu.
* Bug fix:		JIRA WPB-1590	Fixed JavaScript error for undefined screen info in network dashboard.
* Bug fix:		JIRA WPB-1535	Fixed theme deployment issues.
* New feature:	JIRA WPB-1584	Added an opt-out feedback payload delivery system.
* New feature:	JIRA WPB-1580	Added optional feedback for customizer_start.
* Bug fix:		JIRA WPB-1571	Removed plugin dependency admin notice when editing an attachment (image).
* New feature:	JIRA WPB-1579	Added feedback opt-out in BoldGrid Settings, hidden for now.
* Bug fix:  	JIRA WPB-1575	Addressed an issue causing mismatch color palettes on cached previews
* New feature:	JIRA WPB-1514	Add new pages offers page templates to choose from.

= 1.0.8.1 =
* Bug fix:		JIRA WPB-1553	Fixed PHP version check condition (<5.3).

= 1.0.8 =
* Bug fix:		JIRA WPB-1561	Fixed missing get_plugin_data on update calls.
* New feature:	JIRA WPB-1511	Added dependency plugin notice on editor pages.
* Bug fix:		JIRA WPB-1553	Added support for __DIR__ in PHP <=5.2.
* Bug fix:		JIRA WPB-1371	JSON encoded image data for media download requests.
* New feature:  JIRA WPB-1332   Swapping loading GIF to CSS loading image.
* New feature:	JIRA WPB-1072	Storing static pages on install
* New feature:	JIRA WPB-1539	When deleting a page, remove it from any applicable menus as well.
* New feature	JIRA WPB-1542	Manage menu assignment within editor.
* New feature	JIRA WPB-1555	Add wp-image-## class to images during deployment.
* New feature	JIRA WPB-1557	Add wp-image-## class to images when adding gridblocks.
* Bug fix:		JIRA WPB-1506	Theme naming missing in preview.
* Bug fix:		JIRA WPB-1443	Extra page listed under 'Mine'.
* Bug fix:		JIRA WPB-1560	Install options not available on preview server

= 1.0.7 =
* Rework:		JIRA WPB-1533	Ensured activation data is sent after first login.

= 1.0.7 =
* Rework:		JIRA WPB-1533	Ensured activation data is sent after first login.

= 1.0.6 =
* Rework:		JIRA WPB-1411	Added more output to the deploy log.

= 1.0.5 =
* Bug fix:		JIRA WPB-1462	Fixed position of dependency plugins admin notice.  Also limited to Dashboard and plugins page.
* Bug fix:		JIRA WPB-1290	Fixing issues with galleries leaving empty spaces
* Bug fix:		JIRA WPB-1471	Made deployment plugin installation respect release channel.
* Rework:		JIRA WPB-1452	Remove unneeded call to 'boldgrid_activate_framework' during deployment.
* Bug fix:		JIRA WPB-946	Fixed margin bug on step 2 additional themes.
* Bug fix:		JIRA WPB-1384	Increase width of select input on image search modal.
* Bug fix:		JIRA WPB-1508	BoldGrid Image search box size is inconsistent.

= 1.0.4 =
* Bug fix:		JIRA WPB-1442	Fixing inspiration border styles for wordpress 4.4
* Bug fix:		JIRA WPB-1461	Updating login button styles for wordpress 4.4
* Bug fix:		JIRA WPB-1411	Added initialization and checks for empty image queues in deployment.
* Bug fix:		JIRA WPB-1406	Attribution page still showing in 'All Pages'.
* Bug fix:		JIRA WPB-1451	Active images are showing in Staging attribution page.
* Bug fix:		JIRA WPB-1466   Tabs on tutorials page too small at 1035px - 1482px.

= 1.0.3 =
* New feature:	JIRA WPB-1363	Updated readme.txt for WordPress standards.
* New feature:	JIRA WPB-1389	When starting over theme mods are saved with a flag to recompile sass
* Bug fix:		JIRA WPB-1420	Content of Attribution page is overwriting page saves.

= 1.0.2 =
* Bug fix:		JIRA WPB-1395	Adjusted theme update data; now gets theme uri from theme style.css, download url from api data.
* Rework		JIRA WPB-1374	Updated activation timestamp to use GMT/UTC.
* Bug fix:		JIRA WPB-1377	Reseller option is now set on first call to either the front end or wp_login.
* Bug fix:						Adjusted handling for image purchases when errors occur.
* Bug fix:		JIRA WPB-1365	Purchase link on editing a page goes to wrong link.
* Bug fix:		JIRA WPB-1368	Inspirations step 0 text refers to nonexisting help tabs.
* Rework:		JIRA WPB-1378	Adjusted formatting of footer in Dashboard.
* Rework:		JIRA WPB-1369	Update minus signs on 'Transaction History'.
* New feature:	JIRA WPB-1379	On the transactions page, show the reseller that processed the credits.
* Bug fix:						Count of 'All' pages inaccurate on 'All pages'.
* Bug fix:		JIRA WPB-1367	Updated link for 'Lost your BoldGrid Connect Key?'.

= 1.0.1 =
* Bug fix:		JIRA WPB-1374	Updated activation timestamp to include timezone in UTC.
* Bug fix:						Attribution page shows style tags.
* Bug fix:						Strict Standards fix for wp_kses_allowed_html.
* Bug fix:						Fixed incorrect link.

= 1.0 =
* Initial public release.

== Upgrade Notice ==
= 1.3 =
Version 1.3 has been released with a redesigned Inspiration phase. For more information on this change and others, please visit our blog at https://www.boldgrid.com/boldgrid-1-3-released/ .

= 1.0.2 =
Users should upgrade to version 1.0.2 to ensure proper BoldGrid theme updates.
