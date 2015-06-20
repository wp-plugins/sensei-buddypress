=== BuddyPress for Sensei ===
Contributors: buddyboss
Donate link: http://www.buddyboss.com/donate/
Tags: buddypress, sensei, woothemes sensei, woothemes, lms, learning management system, learning, courses, courseware, education, social networking, activity, profiles, messaging, friends, groups, forums, notifications, settings, social, community, networks, networking
Requires at least: 3.8
Tested up to: 4.2.2
Stable tag: 1.0.6
Sensei requires at least: 1.8.0
Sensei tested up to: 1.8.2
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

BuddyPress for Sensei integrates the WooThemes Sensei plugin with BuddyPress, so you can add groups, activity, members, and forums to your courses.

== Description ==

Want your students and teachers to interact with each other? **BuddyPress for Sensei** turns your course driven website into a social education platform, creating a seamless bridge between BuddyPress and [Sensei](http://www.woothemes.com/products/sensei/).

= BuddyPress Groups =

Easily tie any Sensei Course to a BuddyPress Group.

* Course participants automatically get added as members to the group
* Group becomes private to course participants and invited members
* Course Lessons become visible at the group
* Course activity gets streamed into the group feed (optional)
* Forum gets added to the group (if bbPress is enabled)
* Course featured image becomes group avatar (if no group avatar has been set)

= BuddyPress Activity =

Activity is one of the core features of BuddyPress, allowing users to interact with each other in real-time. When a course is tied to a group, course actions are optionally added to the group’s activity stream. Group admins can determine which actions to allow in their group, including:

* User starts a course
* User completes a course
* User creates a lesson
* User completes a lesson
* User passes a quiz
* User comments on single lesson page

= BuddyPress Members =

* Your members can view their Active and Completed courses in their profiles.
* Teachers and Students get added as filterable tabs in your Members directory.
* Teachers get a quick link in their profile to the “Add Course” area in the WordPress dashboard.
* All Sensei user links redirect to member profiles (instead of author pages).

= BuddyPress Messages =

Sensei comes with a basic messaging component. If BuddyPress Messaging is enabled, all Sensei messaging is replaced with native BuddyPress messaging, giving users a consistent experience and more powerful messaging capabilities.

= bbPress Forums =

If bbPress is enabled, a forum will be tied to every “course group” automatically, using the same name as the course for the forum title. Group members can then discuss the course in one central location.

= About BuddyBoss =

BuddyPress for Sensei is built by the experienced developers at BuddyBoss who also offer premium [BuddyPress themes](http://www.buddyboss.com/themes/ "BuddyPress themes from BuddyBoss") and [plugins](http://www.buddyboss.com/plugins/ "BuddyPress plugins from BuddyBoss") to build your social network.

== Installation ==

= Before Installing =

1. Make sure you have [WooThemes Sensei](http://www.woothemes.com/products/sensei/) installed and activated.
2. Make sure you have [BuddyPress](https://wordpress.org/plugins/buddypress/) installed and activated.

= From your WordPress dashboard =

1. Visit 'Plugins > Add New'
2. Search for 'BuddyPress for Sensei'
3. Activate BuddyPress for Sensei from your Plugins page.

= From WordPress.org =

1. Download BuddyPress for Sensei.
2. Upload the 'sensei-buddypress' directory to your '/wp-content/plugins/' directory, using your favorite method (ftp, sftp, etc...)
3. Activate BuddyPress for Sensei from your Plugins page.

= Configuration =

1. Enable 'User Groups' at 'Settings > BuddyPress > Components'
2. Enable Forums for Groups by downloading [bbPress](https://wordpress.org/plugins/bbpress/).
3. Visit 'Settings > BP for Sensei' and select your desired options.

== Frequently Asked Questions ==

= Does it come with the Sensei plugin? =

No, it does not. You will need to purchase [WooThemes Sensei](http://www.woothemes.com/products/sensei/) separately.

= Where can I find documentation and tutorials? =

For help setting up and configuring any BuddyBoss plugin please refer to our [tutorials](http://www.buddyboss.com/tutorials/).

= Does this plugin require BuddyPress? =

Yes, it requires [BuddyPress](https://wordpress.org/plugins/buddypress/) to work.

= Will it work with my theme? =

Yes, BuddyPress for Sensei should work with any theme, and will adopt your BuddyPress styling along with CSS from the Sensei plugin. It may require some styling to make it match perfectly, depending on your theme.

= Does it come with a language translation file? =

Yes. Want to translate BuddyPress for Sensei into your own language? We'll be happy to add your translation into the plugin :)

= Where can I request customizations? =

For BuddyPress customizations, submit your request at [BuddyBoss](http://www.buddyboss.com/buddypress-developers/).

== Screenshots ==

1. **Course Group** - Associate Sensei courses with BuddyPress groups.
2. **Members Index** - Display Teachers and Students in separate tabs in your Members directory. 
3. **Course Group Settings** - Add a course to a BuddyPress group and configure its activity actions.
4. **Plugin Settings** - Configure the core plugin options.

== Changelog ==

= 1.0.6 =
* Fixed error on Course Group creation with bbPress disabled

= 1.0.5 =
* Fixed error on Settings page with BuddyPress disabled
* Added notice if Sensei is not installed
* Better CSS compatibility with certain themes
* Removed "Course Discussion" button from Compose Message screen

= 1.0.4 =
* Plugin loads correctly with bbPress disabled

= 1.0.3 =
* Plugin loads correctly with BuddyPress Groups disabled

= 1.0.2 =
* Updated readme

= 1.0.1 =
* Updated readme

= 1.0.0 =
* Initial public release
