<?php
/**
 * @package WordPress
 * @subpackage BuddyPress for Sensei
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function bp_sensei_active_courses_page() {
    add_action( 'bp_template_title', 'bp_sensei_active_courses_page_title' );
    add_action( 'bp_template_content', 'bp_sensei_active_courses_page_content' );
    bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'members/single/plugins' ) );
}

function bp_sensei_active_courses_page_title() {
    _e( 'Active Courses', 'sensei-buddypress' );
}

function bp_sensei_active_courses_page_content() {
    do_action( 'template_notices' );
    bp_sensei_load_template( 'active-courses' );
}


function bp_sensei_completed_courses_page() {
    add_action( 'bp_template_title', 'bp_sensei_completed_courses_page_title' );
    add_action( 'bp_template_content', 'bp_sensei_completed_courses_page_content' );
    bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'members/single/plugins' ) );
}

function bp_sensei_completed_courses_page_title() {
    _e( 'Completed Courses', 'sensei-buddypress' );
}

function bp_sensei_completed_courses_page_content() {
    do_action( 'template_notices' );
    bp_sensei_load_template( 'completed-courses' );
}

function bp_sensei_create_courses_page() {
	
	do_action('bp_sensei_create_courses_page');
	
	wp_redirect(admin_url( 'post-new.php?post_type=course' ));
}

