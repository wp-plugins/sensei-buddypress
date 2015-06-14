<?php
/**
 * Plugin Name: BuddyPress for Sensei
 * Plugin URI:  http://woothemes.com/products/sensei-buddypress/
 * Description: Integrate the WooThemes Sensei plugin with BuddyPress, so you can add social activity to your education site.
 * Author:      BuddyBoss
 * Author URI:  http://buddyboss.com
 * Version:     1.0.0
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * ========================================================================
 * CONSTANTS
 * ========================================================================
 */
// Codebase version
if ( !defined( 'BUDDYPRESS_SENSEI_PLUGIN_VERSION' ) ) {
  define( 'BUDDYPRESS_SENSEI_PLUGIN_VERSION', '1' );
}

// Database version
if ( !defined( 'BUDDYPRESS_SENSEI_PLUGIN_DB_VERSION' ) ) {
  define( 'BUDDYPRESS_SENSEI_PLUGIN_DB_VERSION', 1 );
}

// Directory
if ( !defined( 'BUDDYPRESS_SENSEI_PLUGIN_DIR' ) ) {
  define( 'BUDDYPRESS_SENSEI_PLUGIN_DIR', trailingslashit( plugin_dir_path( __FILE__ ) ) );
}

// Url
if ( !defined( 'BUDDYPRESS_SENSEI_PLUGIN_URL' ) ) {
  $plugin_url = plugin_dir_url( __FILE__ );

  // If we're using https, update the protocol. Workaround for WP13941, WP15928, WP19037.
  if ( is_ssl() )
    $plugin_url = str_replace( 'http://', 'https://', $plugin_url );

  define( 'BUDDYPRESS_SENSEI_PLUGIN_URL', $plugin_url );
}

// File
if ( !defined( 'BUDDYPRESS_SENSEI_PLUGIN_FILE' ) ) {
  define( 'BUDDYPRESS_SENSEI_PLUGIN_FILE', __FILE__ );
}

/**
 * ========================================================================
 * MAIN FUNCTIONS
 * ========================================================================
 */

/**
 * Main
 *
 * @return void
 */
function BUDDYPRESS_SENSEI_init() {
  global $bp, $BUDDYPRESS_SENSEI;

  $main_include  = BUDDYPRESS_SENSEI_PLUGIN_DIR  . 'includes/main-class.php';

  try {
    if ( file_exists( $main_include ) ) {
      require( $main_include );
    }
    else {
      $msg = sprintf( __( "Couldn't load main class at:<br/>%s", 'sensei-buddypress' ), $main_include );
      throw new Exception( $msg, 404 );
    }
  }
  catch( Exception $e ) {
    $msg = sprintf( __( "<h1>Fatal error:</h1><hr/><pre>%s</pre>", 'sensei-buddypress' ), $e->getMessage() );
    echo $msg;
  }

  $BUDDYPRESS_SENSEI = BuddyPress_Sensei_Plugin::instance();

}
add_action( 'plugins_loaded', 'BUDDYPRESS_SENSEI_init' );

/**
 * Must be called after hook 'plugins_loaded'
 * @return BuddyPress for Sensei Plugin main controller object
 */
function buddypress_sensei() {
  global $BUDDYPRESS_SENSEI;

  $BUDDYPRESS_SENSEI->bp_sensei_loader = BuddyPress_Sensei_Loader::instance();
  $BUDDYPRESS_SENSEI->bp_sensei_groups = BuddyPress_Sensei_Groups::instance();

  return $BUDDYPRESS_SENSEI;
}