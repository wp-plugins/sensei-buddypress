<?php
/**
 * @package WordPress
 * @subpackage BuddyPress for Sensei
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function bp_sensei_load_template( $template ) {
    $template .= '.php';
    if( file_exists( STYLESHEETPATH.'/bp-sensei/'.$template ) )
        include_once( STYLESHEETPATH.'/bp-sensei/'.$template );
    else if( file_exists( TEMPLATEPATH.'bp-sensei/'.$template ) )
        include_once( TEMPLATEPATH.'/bp-sensei/'.$template );
    else{
        $template_dir = apply_filters( 'bp_sensei_templates_dir_filter', buddypress_sensei()->templates_dir );
        include_once trailingslashit( $template_dir ) . $template;
    }
}

function bp_sensei_buffer_template_part( $template, $echo=true ) {
    ob_start();

    bp_sensei_load_template( $template );
    // Get the output buffer contents
    $output = ob_get_clean();

    // Echo or return the output buffer contents
    if ( true === $echo ) {
        echo $output;
    } else {
        return $output;
    }
}
