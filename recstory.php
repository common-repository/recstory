<?php
/**
 * Plugin Name: Recommended Stories for WordPress
 * Plugin URI: http://richardconsulting.ro/blog/?p=938
 * Description: Get latest sticky posts and push them as recommended stories
 * Author: Richard Vencu
 * Author URI: http://richardconsulting.ro
 * Version: 0.1.6
 * License: GPLv2
 *
 *  Copyright 2011  Richard Vencu  (email : richard.vencu@richardconsulting.ro)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
$ecstory_all_blogs = array();
$postId = 0;

register_activation_hook ( __FILE__ , 'recstory_install' );

register_deactivation_hook ( __FILE__ , 'recstory_deactivation' );

add_action( 'after_setup_theme', 'recstory_setup' );

require_once('recstory_post_metabox.php');

function recstory_install() {

	/* Declare default values */
	$recstory_options = array(
	
		'thumb' => 1,

		'number' => 2,

		'turnoffstickyfromloop' => 1,
		
		'scrollposition' => 0.5,
		
		'singular' => 1,

		'frontpage' => 1,

		'search' => 1,
		
		'archive' => 1,
		
		'author' => 1,

		'category' => 1,

		'tag' => 1,
		
		'loggedin' => 0,
		
		'customtitle' => 0,
		
		'title' => 'Recommended Articles',
		
		'close' => 'Close',
		
		'customposts' => 0,
		
		'disableSCPTfilter' => 0

	);
	
	/* At first activation push values to database */
	if ( is_multisite() ) {

		global $recstory_all_blogs;
	
		recstory_retrieve_blogs();
	
		foreach ($recstory_all_blogs as $blog) {
			if ( !get_blog_option($blog , 'recstory_options') )
				update_blog_option ($blog , 'recstory_options' , $recstory_options);
		}
	} else {
		if ( !get_option('recstory_options') )
			update_option ('recstory_options',$recstory_options);
	}	

}

function recstory_deactivation() {

	/* Nothing to do here yet */
}
 


function recstory_setup() {

	/* Load translation */
	load_plugin_textdomain ('recstory', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

	/* Read options */
	$recstory_options = get_option('recstory_options');

	/* Add filters, actions, and theme-supported features. */

	/* Add theme-supported features. */
	if ($recstory_options['thumb'] == 1)
		add_theme_support( 'post-thumbnails' );

	/* Add custom actions. */

	add_action ('loop_start','recstory_displayon');
	add_action('admin_menu','recstory_admin_page');
	add_action('admin_init', 'recstory_admin_init');
	add_action ('wpmu_new_blog','recstory_init_newblog');
	        
	/* Add custom filters. */
	if ($recstory_options['turnoffstickyfromloop'] == 1)
		add_filter( 'pre_get_posts' , 'recstory_nosticky' );
	if ($recstory_options['disableSCPTfilter'] == 1)
		remove_filter('pre_get_posts', 'super_sticky_posts_filter');
}

function recstory_init_newblog() {
	global $blog_id;
	/* Declare default values */
	$recstory_options = array(
	
		'thumb' => 1,

		'number' => 2,

		'turnoffstickyfromloop' => 1,
		
		'scrollposition' => 0.5,
		
		'singular' => 1,

		'frontpage' => 1,

		'search' => 1,
		
		'archive' => 1,
		
		'author' => 1,

		'category' => 1,

		'tag' => 1,
		
		'loggedin' => 0,
		
		'customtitle' => 0,
		
		'title' => 'Recommended Articles',
		
		'close' => 'Close',
		
		'customposts' => 0,

		'disableSCPTfilter' => 0

	);
	update_blog_option ($blog_id , 'recstory_options' , $recstory_options);
}

function recstory_displayon() {
	global $blog_id;
	global $post;
	global $postID;
	
	$postID = $post->ID;
	$singular = 0;
	

	/* Read options */
	if (is_multisite())
		$recstory_options = get_blog_option($blog_id , 'recstory_options');
	else
		$recstory_options = get_option('recstory_options');
		
	if ( is_singular() ) {
		$singular = 1;
		if (get_post_meta($post->ID, 'recstory_exclude', true))
			$singular = 0;
	}

	if ( ( $recstory_options['loggedin'] == 1 && is_user_logged_in() ) || $recstory_options['loggedin'] == 0) {
		if ( ( $recstory_options['singular'] == 1 && $singular == 1 && !is_front_page() ) ||
		( $recstory_options['frontpage'] == 1 && is_front_page() ) ||
		( $recstory_options['search'] == 1 && is_search() ) ||
		( $recstory_options['archive'] == 1 && is_archive() ) ||
		( $recstory_options['category'] == 1 && is_category() ) ||
		( $recstory_options['author'] == 1 && is_author() ) ||
		( $recstory_options['tag'] == 1 && is_tag() )
		) {
			add_action('wp_footer','recstory_load');
			add_action('wp_footer', 'recstory_box');
		}
	}	
}

function recstory_box() {
	global $blog_id;

	/* Read options */
	if (is_multisite())
		$recstory_options = get_blog_option($blog_id , 'recstory_options');
	else
		$recstory_options = get_option('recstory_options');
	
	/* Get all sticky posts */
	if (is_multisite())
		$recstory_sticky = get_blog_option($blog_id , 'sticky_posts' );
	else
		$recstory_sticky = get_option( 'sticky_posts' );
		
	/* Eliminate current post id from sticky array - also works when a sticky post is in the top of the loop */
	foreach( $recstory_sticky as $key => $value ) {
	
		if( $value == $recstory_id ) {
		
		unset( $recstory_sticky[$key] );	
		}
	}

	/* 	create a new array with the keys reordered accordingly... */
	$recstory_sticky = array_values($recstory_sticky);
	
	$recstory_content = '<script type="text/javascript">var scrollposition=' . $recstory_options['scrollposition'] . ';</script>';
	
	/* Continue only if the sticky array is not empty */
	if ( $recstory_sticky ) {

		/* Sort the stickies with the newest ones at the top */
		rsort( $recstory_sticky );

		/* Get the 'number' newest stickies */
		$recstory_sticky = array_slice( $recstory_sticky, 0, $recstory_options['number'] );

		/* Query sticky posts */
		$temp_query = $wp_query;
		
		if ($recstory_options['customposts'] == 1) 
			query_posts(array( 'post__in' => $recstory_sticky, 'ignore_sticky_posts' => 1, 'post_type' => 'any' ));
		else
			query_posts(array( 'post__in' => $recstory_sticky, 'ignore_sticky_posts' => 1 ));
		
		if ($recstory_options['customtitle'] == 1) {
			$recstory_title = $recstory_options['title'];
			$recstory_close = $recstory_options['close'];
		}
		else {
			$recstory_title = __('Recommended Articles','recstory');
			$recstory_close = __('Close','recstory');
		}

		/* Draw the animated box */
		$recstory_content = $recstory_content . '<div id="animbox" style="right: 0px; "><h6>' . $recstory_title . '</h6><ul class="recstory">';
		
		if (have_posts()) {

			while (have_posts()) {

				the_post();

				$recstory_content = $recstory_content . '<li id=post_'. $post->id .'>';
		
				if ($options['thumb'] == 1)
					$recstory_content = $recstory_content . get_the_post_thumbnail($post->id, array(32,32));
		
				$recstory_content = $recstory_content .' <a href="'. get_permalink($post->id) .'">' . get_the_title($post->id) . '</a></li>';
			}
		}
		
		$recstory_content = $recstory_content . '</ul><button id="closex" type="button">' . $recstory_close . '</button></div>';
		
		$wp_query = $temp_query;
	}
	
	echo $recstory_content;
}

function recstory_load () {

	/* enqueue css and js files */
	wp_enqueue_style('recstory_style', plugins_url( '/css/recstory.css', __FILE__ ) );

	wp_register_script( 'recstory_js', plugins_url( '/js/recstory.js', __FILE__ ) , array('jquery'), '');

    wp_enqueue_script( 'recstory_js' );
}

function recstory_nosticky($query) {

	/* This function eliminates the sticky attribute from any loop query */
	$query->set( 'ignore_sticky_posts' , 1 );

	return $query;
}

/* Setup the admin options page */
function recstory_admin_page() {

	add_options_page (

		__('Rec Stories Settings Page','recstory'),
		
		__('Rec Stories','recstory'),
		
		'manage_options',
		
		__FILE__,
		
		'recstory_admin_settings_page'
	);
}

/*  Draw the option page */
function recstory_admin_settings_page() {

	?>
	
	<div class="wrap">
	
		<?php screen_icon(); ?>
		
		<h2><?php _e('Recommended stories for WordPress','recstory'); ?></h2>
		
		<form action="options.php" method="post">
		
			<?php settings_fields('recstory_options'); ?>
			
			<?php do_settings_sections('recstory'); ?>
			
			<p><input name="Submit" type="submit" value="<?php _e('Save Changes','recstory'); ?>" /></p>
			
		</form>
		
	</div>
	
	<?php
}

/* Register and define the settings */
function recstory_admin_init(){

	register_setting(
		'recstory_options',
		'recstory_options',
		'recstory_validate_options'
	);
	
	add_settings_section(
		'recstory_main',
		__('Recommended Stories for WordPress Settings','recstory'),
		'recstory_section_text',
		'recstory'
	);
	
	add_settings_field(
		'recstory_number',
		__('Number of links to display in the animated box','recstory'),
		'recstory_setting_input',
		'recstory',
		'recstory_main'
	);
	
	add_settings_field(
		'recstory_thumb',
		__('Activate theme\'s thumbnail support?','recstory'),
		'recstory_setting_checkbox1',
		'recstory',
		'recstory_main'
	);
	
	add_settings_field(
		'recstory_turnoff',
		__('Turn off sticky attribute in main loops?','recstory'),
		'recstory_setting_checkbox2',
		'recstory',
		'recstory_main'
	);
	
	add_settings_field(
		'recstory_scroll_pos',
		__('Percent of vertical scrolling where to display the animated box','recstory'),
		'recstory_setting_radio1',
		'recstory',
		'recstory_main'
	);
	
	add_settings_section(
		'recstory_display',
		__('Where to display the animated box','recstory'),
		'recstory_section_text',
		'recstory'
	);
	
	add_settings_field(
		'recstory_single',
		__('Display on single pages','recstory'),
		'recstory_setting_checkbox3',
		'recstory',
		'recstory_display'
	);
	
	add_settings_field(
		'recstory_frontpage',
		__('Display on frontpage','recstory'),
		'recstory_setting_checkbox4',
		'recstory',
		'recstory_display'
	);
	
	add_settings_field(
		'recstory_search',
		__('Display on search page','recstory'),
		'recstory_setting_checkbox5',
		'recstory',
		'recstory_display'
	);
	
	add_settings_field(
		'recstory_archive',
		__('Display on archive pages','recstory'),
		'recstory_setting_checkbox6',
		'recstory',
		'recstory_display'
	);
	
	add_settings_field(
		'recstory_category',
		__('Display on category pages','recstory'),
		'recstory_setting_checkbox7',
		'recstory',
		'recstory_display'
	);
	
	add_settings_field(
		'recstory_author',
		__('Display on author pages','recstory'),
		'recstory_setting_checkbox8',
		'recstory',
		'recstory_display'
	);
	
	add_settings_field(
		'recstory_tag',
		__('Display on tag pages','recstory'),
		'recstory_setting_checkbox9',
		'recstory',
		'recstory_display'
	);
	
	add_settings_section(
		'recstory_logged',
		__('When to display the animated box','recstory'),
		'recstory_section_text',
		'recstory'
	);
	
	add_settings_field(
		'recstory_members',
		__('Display only if users are logged in','recstory'),
		'recstory_setting_checkbox10',
		'recstory',
		'recstory_logged'
	);
	
	add_settings_section(
		'recstory_custom',
		__('Use custom titles in the animated box','recstory'),
		'recstory_section_text',
		'recstory'
	);
	
	add_settings_field(
		'recstory_customtitle',
		__('Check to use custom titles below. Uncheck to use default titles','recstory'),
		'recstory_setting_checkbox11',
		'recstory',
		'recstory_custom'
	);
	
	add_settings_field(
		'recstory_title',
		__('Custom title of the animated box','recstory'),
		'recstory_setting_input1',
		'recstory',
		'recstory_custom'
	);
	add_settings_field(
		'recstory_close',
		__('Custom title of the close box','recstory'),
		'recstory_setting_input2',
		'recstory',
		'recstory_custom'
	);
	
	add_settings_section(
		'recstory_customposttypes',
		__('Use custom posts in the animated box','recstory'),
		'recstory_section_text',
		'recstory'
	);
	
	add_settings_field(
		'recstory_customposts',
		__('Check to use custom post types as recommended articles','recstory'),
		'recstory_setting_checkbox12',
		'recstory',
		'recstory_customposttypes'
	);	
	
	add_settings_field(
		'recstory_disableSCPTfilter',
		__('Check to disable Sticky Custom Post Types plugin\'s query filter if the frontpage becomes a mess after activating this plugin','recstory'),
		'recstory_setting_checkbox13',
		'recstory',
		'recstory_customposttypes'
	);	
}

/*  Draw the section header */
function recstory_section_text() {

	echo '<p>' . __('Enter your settings below.','recstory') . '</p>';
}

/* Display and fill the form fields */
function recstory_setting_input() {

	global $blog_id;

	/* Read options */
	if (is_multisite())
		$recstory_options = get_blog_option($blog_id , 'recstory_options');
	else
		$recstory_options = get_option('recstory_options');
	
	$text_string = $recstory_options['number'];
	
	/* Echo the field */
	echo "<input id='number' name='recstory_options[number]' type='text' value='$text_string' />";
}

function recstory_setting_input1() {

	global $blog_id;

	/* Read options */
	if (is_multisite())
		$recstory_options = get_blog_option($blog_id , 'recstory_options');
	else
		$recstory_options = get_option('recstory_options');
	
	$text_string = $recstory_options['title'];
	
	/* Echo the field */
	echo "<input id='title' name='recstory_options[title]' type='text' value='$text_string' />";
}

function recstory_setting_input2() {

	global $blog_id;

	/* Read options */
	if (is_multisite())
		$recstory_options = get_blog_option($blog_id , 'recstory_options');
	else
		$recstory_options = get_option('recstory_options');
	
	$text_string = $recstory_options['close'];
	
	/* Echo the field */
	echo "<input id='close' name='recstory_options[close]' type='text' value='$text_string' />";
}

function recstory_setting_checkbox1() {

	global $blog_id;

	/* Read options */
	if (is_multisite())
		$recstory_options = get_blog_option($blog_id , 'recstory_options');
	else
		$recstory_options = get_option('recstory_options');
	
	$text_string = $recstory_options['thumb'];
	
	/* Echo the field */
	echo "<input id='thumb' name='recstory_options[thumb]' type='checkbox' value='1' ";
	
	checked( 1 == $text_string );
	
	echo " />";
}

function recstory_setting_checkbox2() {

	global $blog_id;

	/* Read options */
	if (is_multisite())
		$recstory_options = get_blog_option($blog_id , 'recstory_options');
	else
		$recstory_options = get_option('recstory_options');
	
	$text_string = $recstory_options['turnoffstickyfromloop'];
	
	/* Echo the field */
	echo "<input id='turnoffstickyfromloop' name='recstory_options[turnoffstickyfromloop]' type='checkbox' value='1' ";
	
	checked( 1 == $text_string );
	
	echo " />";
}

function recstory_setting_radio1() {

	global $blog_id;

	/* Read options */
	if (is_multisite())
		$recstory_options = get_blog_option($blog_id , 'recstory_options');
	else
		$recstory_options = get_option('recstory_options');
	
	$text_string = $recstory_options['scrollposition'];
	
	/* Echo the field */
	echo "<input name='recstory_options[scrollposition]' type='radio' value='0.1' ";
	checked( 0.1 == $text_string );
	echo " /> 10%<br />";
	echo "<input name='recstory_options[scrollposition]' type='radio' value='0.2' ";
	checked( 0.2 == $text_string );
	echo " /> 20%<br />";
	echo "<input name='recstory_options[scrollposition]' type='radio' value='0.3' ";
	checked( 0.3 == $text_string );
	echo " /> 30%<br />";
	echo "<input name='recstory_options[scrollposition]' type='radio' value='0.4' ";
	checked( 0.4 == $text_string );
	echo " /> 40%<br />";
	echo "<input name='recstory_options[scrollposition]' type='radio' value='0.5' ";
	checked( 0.5 == $text_string );
	echo " /> 50%<br />";
	echo "<input name='recstory_options[scrollposition]' type='radio' value='0.6' ";
	checked( 0.6 == $text_string );
	echo " /> 60%<br />";
	echo "<input name='recstory_options[scrollposition]' type='radio' value='0.7' ";
	checked( 0.7 == $text_string );
	echo " /> 70%<br />";
	echo "<input name='recstory_options[scrollposition]' type='radio' value='0.8' ";
	checked( 0.8 == $text_string );
	echo " /> 80%<br />";
	echo "<input name='recstory_options[scrollposition]' type='radio' value='0.9' ";
	checked( 0.9 == $text_string );
	echo " /> 90%";
}

function recstory_setting_checkbox3() {

	global $blog_id;

	/* Read options */
	if (is_multisite())
		$recstory_options = get_blog_option($blog_id , 'recstory_options');
	else
		$recstory_options = get_option('recstory_options');
	
	$text_string = $recstory_options['singular'];
	
	/* Echo the field */
	echo "<input id='singular' name='recstory_options[singular]' type='checkbox' value='1' ";
	
	checked( 1 == $text_string );
	
	echo " />";
}

function recstory_setting_checkbox4() {

	global $blog_id;

	/* Read options */
	if (is_multisite())
		$recstory_options = get_blog_option($blog_id , 'recstory_options');
	else
		$recstory_options = get_option('recstory_options');
	
	$text_string = $recstory_options['frontpage'];
	
	/* Echo the field */
	echo "<input id='frontpage' name='recstory_options[frontpage]' type='checkbox' value='1' ";
	
	checked( 1 == $text_string );
	
	echo " />";
}

function recstory_setting_checkbox5() {

	global $blog_id;

	/* Read options */
	if (is_multisite())
		$recstory_options = get_blog_option($blog_id , 'recstory_options');
	else
		$recstory_options = get_option('recstory_options');
	
	$text_string = $recstory_options['search'];
	
	/* Echo the field */
	echo "<input id='search' name='recstory_options[search]' type='checkbox' value='1' ";
	
	checked( 1 == $text_string );
	
	echo " />";
}

function recstory_setting_checkbox6() {

	global $blog_id;

	/* Read options */
	if (is_multisite())
		$recstory_options = get_blog_option($blog_id , 'recstory_options');
	else
		$recstory_options = get_option('recstory_options');
	
	$text_string = $recstory_options['archive'];
	
	/* Echo the field */
	echo "<input id='archive' name='recstory_options[archive]' type='checkbox' value='1' ";
	
	checked( 1 == $text_string );
	
	echo " />";
}

function recstory_setting_checkbox7() {

	global $blog_id;

	/* Read options */
	if (is_multisite())
		$recstory_options = get_blog_option($blog_id , 'recstory_options');
	else
		$recstory_options = get_option('recstory_options');
	
	$text_string = $recstory_options['category'];
	
	/* Echo the field */
	echo "<input id='category' name='recstory_options[category]' type='checkbox' value='1' ";
	
	checked( 1 == $text_string );
	
	echo " />";
}

function recstory_setting_checkbox8() {

	global $blog_id;

	/* Read options */
	if (is_multisite())
		$recstory_options = get_blog_option($blog_id , 'recstory_options');
	else
		$recstory_options = get_option('recstory_options');
	
	$text_string = $recstory_options['author'];
	
	/* Echo the field */
	echo "<input id='author' name='recstory_options[author]' type='checkbox' value='1' ";
	
	checked( 1 == $text_string );
	
	echo " />";
}

function recstory_setting_checkbox9() {

	global $blog_id;

	/* Read options */
	if (is_multisite())
		$recstory_options = get_blog_option($blog_id , 'recstory_options');
	else
		$recstory_options = get_option('recstory_options');
	
	$text_string = $recstory_options['tag'];
	
	/* Echo the field */
	echo "<input id='tag' name='recstory_options[tag]' type='checkbox' value='1' ";
	
	checked( 1 == $text_string );
	
	echo " />";
}

function recstory_setting_checkbox10() {

	global $blog_id;

	/* Read options */
	if (is_multisite())
		$recstory_options = get_blog_option($blog_id , 'recstory_options');
	else
		$recstory_options = get_option('recstory_options');
	
	$text_string = $recstory_options['loggedin'];
	
	/* Echo the field */
	echo "<input id='loggedin' name='recstory_options[loggedin]' type='checkbox' value='1' ";
	
	checked( 1 == $text_string );
	
	echo " />";
}

function recstory_setting_checkbox11() {

	global $blog_id;

	/* Read options */
	if (is_multisite())
		$recstory_options = get_blog_option($blog_id , 'recstory_options');
	else
		$recstory_options = get_option('recstory_options');
	
	$text_string = $recstory_options['customtitle'];
	
	/* Echo the field */
	echo "<input id='customtitle' name='recstory_options[customtitle]' type='checkbox' value='1' ";
	
	checked( 1 == $text_string );
	
	echo " />";
}

function recstory_setting_checkbox12() {

	global $blog_id;

	/* Read options */
	if (is_multisite())
		$recstory_options = get_blog_option($blog_id , 'recstory_options');
	else
		$recstory_options = get_option('recstory_options');
	
	$text_string = $recstory_options['customposts'];
	
	/* Echo the field */
	echo "<input id='customposts' name='recstory_options[customposts]' type='checkbox' value='1' ";
	
	checked( 1 == $text_string );
	
	echo " />";
}

function recstory_setting_checkbox13() {

	global $blog_id;

	/* Read options */
	if (is_multisite())
		$recstory_options = get_blog_option($blog_id , 'recstory_options');
	else
		$recstory_options = get_option('recstory_options');
	
	$text_string = $recstory_options['disableSCPTfilter'];
	
	/* Echo the field */
	echo "<input id='disableSCPTfilter' name='recstory_options[disableSCPTfilter]' type='checkbox' value='1' ";
	
	checked( 1 == $text_string );
	
	echo " />";
}

/* Validate user input */
function recstory_validate_options( $input ) {

	$valid = array();
	
	$valid['number'] = preg_replace( '/[^0-9]/', '', $input['number'] );
	
	if( $valid['number'] != $input['number'] ) {
	
		add_settings_error(
			'recstory_text_string',
			'recstory_texterror',
			__('Incorrect value entered for number of sticky posts!','recstory'),
			'error'
		);
	}
	
	$valid['thumb'] = 0;
	
	if( isset( $input['thumb'] ) && ( 1 == $input['thumb'] ) )
	
        $valid['thumb'] = 1;
	
	$valid['turnoffstickyfromloop'] = 0;
	
	if( isset( $input['turnoffstickyfromloop'] ) && ( 1 == $input['turnoffstickyfromloop'] ) )
	
        $valid['turnoffstickyfromloop'] = 1;

	$valid['scrollposition'] = 0.5;
	
	if( isset( $input['scrollposition'] ) ) 
	
		$valid['scrollposition'] = $input['scrollposition'];
		

	$valid['singular'] = 0;
	
	if( isset( $input['singular'] ) && ( 1 == $input['singular'] ) )
	
        $valid['singular'] = 1;

	$valid['frontpage'] = 0;
	
	if( isset( $input['frontpage'] ) && ( 1 == $input['frontpage'] ) )
	
        $valid['frontpage'] = 1;
		
	$valid['search'] = 0;
	
	if( isset( $input['search'] ) && ( 1 == $input['search'] ) )
	
        $valid['search'] = 1;
		
	$valid['archive'] = 0;
	
	if( isset( $input['archive'] ) && ( 1 == $input['archive'] ) )
	
        $valid['archive'] = 1;
		
	$valid['category'] = 0;
	
	if( isset( $input['category'] ) && ( 1 == $input['category'] ) )
	
        $valid['category'] = 1;
		
	$valid['tag'] = 0;
	
	if( isset( $input['tag'] ) && ( 1 == $input['tag'] ) )
	
        $valid['tag'] = 1;
		
	$valid['author'] = 0;
	
	if( isset( $input['author'] ) && ( 1 == $input['author'] ) )
	
        $valid['author'] = 1;
		
	$valid['loggedin'] = 0;
	
	if( isset( $input['loggedin'] ) && ( 1 == $input['loggedin'] ) )
	
        $valid['loggedin'] = 1;

	if( isset( $input['customtitle'] ) && ( 1 == $input['customtitle'] ) )
	
        $valid['customtitle'] = 1;
	
	if( isset( $input['title'] ) )
	
		$valid['title'] = $input['title'];
	
	if( isset( $input['close'] ) )

		$valid['close'] = $input ['close'];
		
	if( isset( $input['customposts'] ) && ( 1 == $input['customposts'] ) )
	
        $valid['customposts'] = 1;
		
	if( isset( $input['disableSCPTfilter'] ) && ( 1 == $input['disableSCPTfilter'] ) )
	
        $valid['disableSCPTfilter'] = 1;
		
	return $valid;
}

function recstory_retrieve_blogs() {
	/* Retrieve all blog ids */

	global $wpdb, $recstory_all_blogs;

	$sql = "SELECT blog_id FROM $wpdb->blogs";

	$recstory_all_blogs = $wpdb->get_col($wpdb->prepare($sql));
}
?>