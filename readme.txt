=== Plugin Name ===
Contributors: rvencu
Donate link: 
Tags: sticky, box, animation, jQuery
Requires at least: 2.7
Tested up to: 3.3.1
Stable tag: 0.1.6

This plugin can list a selectable number of latest sticky posts, and push their links into an animated box 

== Description ==

This plugin can list a selectable number of links to the latest sticky posts, within an animated box that enters the screen when it is scrolled down more than a specified percent of the page height. Admins can set the parameters below:

1. the *percent of vertical scroll* where the animated box appears
1. the *number* of recent sticky posts to list
1. if the posts have thumbnail there is an option to turn on / turn off the *thumbnail theme capability*
1. since the sticky posts are presented this way, maybe there is no need to keep them on the front page anymore, so the third option allows to *disable the sticky property of posts in the main loops*
1. the module can be disabled for specific posts, pages or custom posts from the post editor via a specific metabox

**Other features**

1. when displaying a post that is sticky, the corresponding link is eliminated from the animated box
1. if the sticky posts list remains empty, the animated box is not displayed anymore

== Installation ==

1. Upload `recstory.zip` to the `/wp-content/plugins/` directory
1. Unzip the archive
1. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==

= The animated box is not animated, it just appears at all times =
This is most probable a jQuery conflict with the theme or with another plugin. Try to switch to the default theme and / or disable other plugins to check where the conflict is.

== Screenshots ==

1. Animated box with recommended stories
2. Admin interface

== Changelog ==

= 0.1.6 =
1. Multisite compatibility
1. Option to use custom post types as recommended articles (experimental - requires other plugins to activate stiky function for custom post types such as http://wordpress.org/extend/plugins/sticky-custom-post-types/)

= 0.1.5 =
1. Reverted to ANSI encoding (against WordPress Codex recommendations) to avoid some errors with some host environments
1. Added option to use custom titles for the animated box and the close button
1. Fixed some CSS issues with Opera

= 0.1.4 =
1. Fixed some issues with plugin definition

= 0.1.3 =
1. Added admin options to display only on single pages, search pages, archives, category pages, tag pages, front page, author pages
1. Added admin options to display only for logged in users

= 0.1.2 =
1. Added metabox to remove recommended stories from specific posts, pages or custom posts id's
1. Fixed some minor bugs that triggered javascript errors in certain conditions

= 0.1.1 =
1. Added option to modify the percent of vertical scroll where the animated box appears

= 0.1 =
1. Incipient version
