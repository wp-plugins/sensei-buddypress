<?php
/**
 * @package WordPress
 * @subpackage BuddyPress for Sensei
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if( ! class_exists( 'BuddyPress_Sensei_Plugin' ) ):
/**
 *
 * BuddyPress for Sensei Plugin Main Controller
 * **************************************
 *
 *
 */
class BuddyPress_Sensei_Plugin {
    /* Includes
    * ===================================================================
    */

    /**
     * Most WordPress/BuddyPress plugin have the includes in the function
     * method that loads them, we like to keep them up here for easier
     * access.
     * @var array
     */
    private $main_includes = array(
        'bp-sensei-loader',
        'bp-sensei-functions',
        'bp-sensei-courses',
        'bp-sensei-template',
        'bp-sensei-groups',
        'bp-sensei-group-settings',
        'bp-sensei-group-experiences'
    );

    /**
     * Admin includes
     * @var array
     */
    private $admin_includes = array(
        'admin'
    );

    /* Plugin Options
     * ===================================================================
     */

    /**
     * Default options for the plugin, the strings are
     * run through localization functions during instantiation,
     * and after the user saves options the first time they
     * are loaded from the DB.
     *
     * @var array
     */
    private $default_options = array(
        'enabled' => true
    );

    /**
     * This options array is setup during class instantiation, holds
     * default and saved options for the plugin.
     *
     * @var array
     */
    public $options = array();

    /**
     * Is BuddyPress installed and activated?
     * @var boolean
     */
    public $bp_enabled = false;

    /* Version
     * ===================================================================
     */

    /**
     * Plugin codebase version
     * @var string
     */
    public $version = '1.0.0';

    /* Paths
     * ===================================================================
     */

    public $plugin_dir = '';
    public $plugin_url = '';
    public $includes_dir = '';
    public $includes_url = '';
    public $lang_dir = '';
    public $assets_dir = '';
    public $assets_url = '';
    public $templates_dir = '';
    public $templates_url = '';
    private $data;
    /* Singleton
     * ===================================================================
     */

	/**
	 * Main BuddyPress for Sensei Instance.
	 *
	 * BuddyPress for Sensei is great
	 * Please load it only one time
	 * For this, we thank you
	 *
	 * Insures that only one instance of BuddyPress for Sensei exists in memory at any
	 * one time. Also prevents needing to define globals all over the place.
	 *
	 * @since BuddyPress for Sensei (1.0.0)
	 *
	 * @static object $instance
	 * @uses BuddyPress_Sensei_Plugin::setup_globals() Setup the globals needed.
	 * @uses BuddyPress_Sensei_Plugin::setup_actions() Setup the hooks and actions.
	 * @uses BuddyPress_Sensei_Plugin::setup_textdomain() Setup the plugin's language file.
	 * @see buddypress_sensei()
	 *
	 * @return BuddyPress for Sensei The one true BuddyBoss.
	 */
	public static function instance() {
		// Store the instance locally to avoid private static replication
		static $instance = null;

		// Only run these methods if they haven't been run previously
		if ( null === $instance ) {
			$instance = new BuddyPress_Sensei_Plugin;
			$instance->setup_globals();
			$instance->setup_actions();
			$instance->setup_textdomain();
		}

		// Always return the instance
		return $instance;
	}

	/* Magic Methods
	 * ===================================================================
	 */

	/**
	 * A dummy constructor to prevent BuddyPress for Sensei from being loaded more than once.
	 *
	 * @since BuddyPress for Sensei (1.0.0)
	 * @see BuddyPress_Sensei_Plugin::instance()
	 * @see buddypress()
	 */
	private function __construct() { /* Do nothing here */ }

	/**
	 * A dummy magic method to prevent BuddyPress for Sensei from being cloned.
	 *
	 * @since BuddyPress for Sensei (1.0.0)
	 */
	public function __clone() { _doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'sensei-buddypress' ), '1.0.0' ); }

	/**
	 * A dummy magic method to prevent BuddyPress for Sensei from being unserialized.
	 *
	 * @since BuddyPress for Sensei (1.0.0)
	 */
	public function __wakeup() { _doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'sensei-buddypress' ), '1.0.0' ); }

	/**
	 * Magic method for checking the existence of a certain custom field.
	 *
	 * @since BuddyPress for Sensei (1.0.0)
	 */
	public function __isset( $key ) { return isset( $this->data[$key] ); }

	/**
	 * Magic method for getting BuddyPress for Sensei varibles.
	 *
	 * @since BuddyPress for Sensei (1.0.0)
	 */
	public function __get( $key ) { return isset( $this->data[$key] ) ? $this->data[$key] : null; }

	/**
	 * Magic method for setting BuddyPress for Sensei varibles.
	 *
	 * @since BuddyPress for Sensei (1.0.0)
	 */
	public function __set( $key, $value ) { $this->data[$key] = $value; }

	/**
	 * Magic method for unsetting BuddyPress for Sensei variables.
	 *
	 * @since BuddyPress for Sensei (1.0.0)
	 */
	public function __unset( $key ) { if ( isset( $this->data[$key] ) ) unset( $this->data[$key] ); }

	/**
	 * Magic method to prevent notices and errors from invalid method calls.
	 *
	 * @since BuddyPress for Sensei (1.0.0)
	 */
	public function __call( $name = '', $args = array() ) { unset( $name, $args ); return null; }

	/* Plugin Specific, Setup Globals, Actions, Includes
	 * ===================================================================
	 */

    /**
     * Setup BuddyPress for Sensei plugin global variables.
     *
     * @since 1.0.0
     * @access private
     *
     * @uses plugin_dir_path() To generate BuddyPress for Sensei plugin path.
     * @uses plugin_dir_url() To generate BuddyPress for Sensei plugin url.
     * @uses apply_filters() Calls various filters.
     */
    private function setup_globals() {

        global $BUDDYPRESS_SENSEI;

        $saved_options = get_option('buddypress_sensei_plugin_options');
        $saved_options = maybe_unserialize($saved_options);

        $this->options = wp_parse_args($saved_options, $this->default_options);

        // Normalize legacy uppercase keys
        foreach ($this->options as $key => $option) {
            // Delete old entry
            unset($this->options[$key]);

            // Override w/ lowercase key
            $this->options[strtolower($key)] = $option;
        }

        /** Versions ************************************************* */
        $this->version = BUDDYPRESS_SENSEI_PLUGIN_VERSION;

        /** Paths***************************************************** */
        // BuddyPress for Sensei root directory
        $this->file          = BUDDYPRESS_SENSEI_PLUGIN_FILE;
        $this->basename      = plugin_basename( $this->file );
        $this->plugin_dir    = BUDDYPRESS_SENSEI_PLUGIN_DIR;
        $this->plugin_url    = BUDDYPRESS_SENSEI_PLUGIN_URL;

        // Languages
        $this->lang_dir      = dirname( $this->basename ) . '/languages/';

        // Includes
        $this->includes_dir = $this->plugin_dir . 'includes';
        $this->includes_url = $this->plugin_url . 'includes';

        // Templates
        $this->templates_dir = $this->plugin_dir . 'templates';
        $this->templates_url = $this->plugin_url . 'templates';

        // Assets
        $this->assets_dir = $this->plugin_dir . 'assets';
        $this->assets_url = $this->plugin_url . 'assets';
    }

    /**
	 * Set up the default hooks and actions.
	 *
	 * @since BuddyPress for Sensei (1.0.0)
	 * @access private
	 *
	 * @uses register_activation_hook() To register the activation hook.
	 * @uses register_deactivation_hook() To register the deactivation hook.
	 * @uses add_action() To add various actions.
	 */
    public function setup_actions() {
        // Admin
        if (( is_admin() || is_network_admin() ) && current_user_can('manage_options')) {
            $this->load_admin();
        }

        if ( ! is_admin() && ! is_network_admin() ) {
            add_action( 'wp_enqueue_scripts', array( $this, 'bp_sensei_enqueue_styles' ), 11 );
        }

        if ( !$this->is_enabled() ) return;

        global $bp;
        if( $bp ){
            $this->load_main();
			add_action( 'bp_init', array( $this, 'bp_sensei_add_group_course_extension'), 10 );
        }
    }

    public function bp_sensei_enqueue_styles() {
        // CSS > Main
        //wp_enqueue_style( 'sensei-buddypress', $this->assets_url . '/css/sensei-buddypress.css', array(), '1.0.0', 'all' );
        wp_enqueue_style( 'sensei-buddypress', $this->assets_url . '/css/sensei-buddypress.min.css', array(), '1.0.0', 'all' );
        // JS > Main
        //wp_enqueue_script( 'sensei-buddypress', $this->assets_url . '/js/sensei-buddypress.js', array(), '1.0.0', true );
        wp_enqueue_script( 'sensei-buddypress', $this->assets_url . '/js/sensei-buddypress.min.js', array(), '1.0.0', true );
    }

    /**
	 * Load plugin text domain
	 *
	 * @since BuddyPress for Sensei (1.0.0)
	 *
	 * @uses sprintf() Format .mo file
	 * @uses get_locale() Get language
	 * @uses file_exists() Check for language file
	 * @uses load_textdomain() Load language file
	 */
	public function setup_textdomain() {
		$domain = 'sensei-buddypress';
		$locale = apply_filters( 'plugin_locale', get_locale(), $domain );

		//first try to load from wp-content/languages/plugins/ directory
		load_textdomain( $domain, WP_LANG_DIR.'/plugins/'.$domain.'-'.$locale.'.mo' );

		//if not found, then load from sensei-buddypress/languages/ directory
		load_plugin_textdomain( 'sensei-buddypress', false, $this->lang_dir );
	}

    /* Load
    * ===================================================================
    */

    /**
     * Include required admin files.
     *
     * @since Buddypress Sensei (1.0.0)
     * @access private
     *
     * @uses $this->do_includes() Loads array of files in the include folder
     */
    public function load_admin() {
        $this->do_includes( $this->admin_includes );

        $this->admin = BuddyPress_Sensei_Admin::instance();
    }

    /**
     * Include required files.
     *
     * @since Buddypress Sensei (1.0.0)
     * @access private
     *
     * @uses BuddyPress_Sensei_Plugin::do_includes() Loads array of files in the include folder
     */
    private function load_main() {
        $this->do_includes( $this->main_includes );

        BuddyPress_Sensei_Loader::instance();
    }

    /**
     * Include required array of files in the includes directory
     *
     * @since BuddyPress for Sensei (1.0.0)
     *
     * @uses require_once() Loads include file
     */
    public function do_includes( $includes = array() ) {
        foreach ( $includes as $include ) {
            require_once( $this->includes_dir . '/' . $include . '.php' );
        }
    }

    /**
     * Check if the plugin is active and enabled in the plugin's admin options.
     *
     * @since Buddypress Sensei (1.0.0)
     *
     * @uses BuddyPress_Sensei_Plugin::option() Get plugin option
     *
     * @return boolean True when the plugin is active
     */
    public function is_enabled() {
        $is_enabled = $this->option( 'enabled' ) === true || $this->option( 'enabled' ) === 'on';

        return $is_enabled;
    }

    /**
     * Convenience function to access plugin options, returns false by default
     *
     * @since  Buddypress Sensei (1.0.0)
     *
     * @param  string $key Option key

     * @uses apply_filters() Filters option values with 'buddypress_sensei_option' &
     *                       'buddypress_sensei_option_{$option_name}'
     * @uses sprintf() Sanitizes option specific filter
     *
     * @return mixed Option value (false if none/default)
     *
     */
    public function option( $key ) {
        $key = strtolower( $key );
        $option = isset( $this->options[$key] ) ? $this->options[$key] : null;

        // Apply filters on options as they're called for maximum
        // flexibility. Options are are also run through a filter on
        // class instatiation/load.
        // ------------------------
        // This filter is run for every option
        $option = apply_filters( 'buddypress_sensei_option', $option );

        // Option specific filter name is converted to lowercase
        $filter_name = sprintf( 'buddypress_sensei_option_%s', strtolower( $key ) );
        $option = apply_filters( $filter_name, $option );

        return $option;
    }
	
	/**
	 * Load Group Course extension 
	 */
	public function bp_sensei_add_group_course_extension() {
		bp_register_group_extension( 'GType_Course' );
	}


}

endif;