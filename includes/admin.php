<?php
/**
 * @package WordPress
 * @subpackage BuddyPress for Sensei
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if (!class_exists('BuddyPress_Sensei_Admin')):

    /**
     *
     * BuddyPress_Sensei_Admin
     * ********************
     *
     *
     */
    class BuddyPress_Sensei_Admin {
        /* Options/Load
         * ===================================================================
         */

        /**
         * Plugin options
         *
         * @var array
         */
        public $options = array();

        /**
         * Empty constructor function to ensure a single instance
         */
        public function __construct() {
            // ... leave empty, see Singleton below
        }

        /* Singleton
         * ===================================================================
         */

        /**
         * Admin singleton
         *
         * @since BuddyPress for Sensei (1.0.0)
         *
         * @param  array  $options [description]
         *
         * @uses BuddyPress_Sensei_Admin::setup() Init admin class
         *
         * @return object Admin class
         */
        public static function instance() {
            static $instance = null;

            if (null === $instance) {
                $instance = new BuddyPress_Sensei_Admin;
                $instance->setup();
            }

            return $instance;
        }

        /* Utility functions
         * ===================================================================
         */

        /**
         * Get option
         *
         * @since BuddyPress for Sensei (1.0.0)
         *
         * @param  string $key Option key
         *
         * @uses BuddyPress_Sensei_Admin::option() Get option
         *
         * @return mixed      Option value
         */
        public function option($key) {
            $value = buddypress_sensei()->option( $key );
            return $value;
        }

        /* Actions/Init
         * ===================================================================
         */

        /**
         * Setup admin class
         *
         * @since BuddyPress for Sensei (1.0.0)
         *
         * @uses buddypress_sensei() Get options from main BuddyPress_Sensei_Admin class
         * @uses is_admin() Ensures we're in the admin area
         * @uses curent_user_can() Checks for permissions
         * @uses add_action() Add hooks
         */
        public function setup() {
            if ( ( !is_admin() && ! is_network_admin() ) || !current_user_can( 'manage_options' ) ) {
                return;
            }

            $actions = array(
                'admin_init',
                'admin_menu',
                'network_admin_menu',
            );

            if ( isset( $_GET['page'] ) && ( $_GET['page'] == 'sensei-buddypress/includes/admin.php' ) ) {
                $actions[] = 'admin_enqueue_scripts';
            }

            foreach ( (array) $actions as $action ) {
                add_action( $action, array( $this, $action ) );
            }

            // add setting link
            $buddyboss = BuddyPress_Sensei_Plugin::instance();
            $plugin = $buddyboss->basename;
            
            add_filter( "plugin_action_links_$plugin", array( $this, 'plugin_settings_link' ) );
        }

        /**
         * Register admin settings
         *
         * @since BuddyPress for Sensei (1.0.0)
         *
         * @uses register_setting() Register plugin options
         * @uses add_settings_section() Add settings page option sections
         * @uses add_settings_field() Add settings page option
         */
        public function admin_init() {
            register_setting( 'buddypress_sensei_plugin_options', 'buddypress_sensei_plugin_options' );
            add_settings_section( 'general_section', __( 'General Settings', 'sensei-buddypress' ), array( $this, 'section_general' ), __FILE__ );

            add_settings_field( 'courses_visibility_option', __( 'Visibility', 'sensei-buddypress' ), array( $this, 'courses_visibility_option' ), __FILE__ , 'general_section' );
            add_settings_field( 'convert_subscribers_option', __( 'User Roles', 'sensei-buddypress' ), array( $this, 'convert_subscribers_option' ), __FILE__ , 'general_section' );
            add_settings_field( 'convert_teachers_option', '', array( $this, 'convert_teachers_option' ), __FILE__ , 'general_section' );
        }

        /**
         * Add plugin settings page
         *
         * @since BuddyPress for Sensei (1.0.0)
         *
         * @uses add_options_page() Add plugin settings page
         */
        public function admin_menu() {
            add_options_page( __( 'BuddyPress for Sensei', 'sensei-buddypress' ), __( 'BP for Sensei', 'sensei-buddypress' ), 'manage_options', __FILE__, array( $this, 'options_page' ) );
        }

        /**
         * Add plugin settings page
         *
         * @since BuddyPress for Sensei (1.0.0)
         *
         * @uses BuddyPress_Sensei_Admin::admin_menu() Add settings page option sections
         */
        public function network_admin_menu() {
            return $this->admin_menu();
        }

        // Add settings link on plugin page
        function plugin_settings_link( $links ) {
            $settings_link = '<a href="' . admin_url( "options-general.php?page=".__FILE__ ) . '">' . __( 'Settings', 'sensei-buddypress' ) . '</a>';
            array_unshift( $links, $settings_link );
            return $links;
        }

        /**
         * Register admin scripts
         *
         * @since BuddyPress for Sensei (1.0.0)
         *
         * @uses wp_enqueue_script() Enqueue admin script
         * @uses wp_enqueue_style() Enqueue admin style
         * @uses buddypress_sensei()->assets_url Get plugin URL
         */
        public function admin_enqueue_scripts() {
            $js = buddypress_sensei()->assets_url . '/js/';
            $css = buddypress_sensei()->assets_url . '/css/';
        }

        /* Settings Page + Sections
         * ===================================================================
         */

        /**
         * Render settings page
         *
         * @since BuddyPress for Sensei (1.0.0)
         *
         * @uses do_settings_sections() Render settings sections
         * @uses settings_fields() Render settings fields
         * @uses esc_attr_e() Escape and localize text
         */
        
        public function options_page() {
        ?>
            <div class="wrap">
                <h2><?php _e( 'BuddyPress for Sensei', 'sensei-buddypress' ); ?></h2>
                <form action="options.php" method="post">
                <?php settings_fields('buddypress_sensei_plugin_options'); ?>
                <?php do_settings_sections( __FILE__ ); ?>

                    <p class="submit">
                        <input name="Submit" type="submit" class="button-primary" value="<?php esc_attr_e( 'Save Changes', 'sensei-buddypress' ); ?>" />
                    </p>
                </form>
            </div>

            <?php
        }

        public function courses_visibility_option() {
            $value = buddypress_sensei()->option( 'courses_visibility' );
            $checked = '';
            if ( $value ) {
                $checked = ' checked="checked" ';
            }
            echo "<input " . $checked . " id='courses_visibility' name='buddypress_sensei_plugin_options[courses_visibility]' type='checkbox' />  ";
            _e( 'Display <em>Profile > Courses</em> content publicly', 'sensei-buddypress' );
        }

        public function convert_subscribers_option() {
            $value = buddypress_sensei()->option( 'convert_subscribers' );
            $checked = '';
            if ( $value ) {
                $this->convert_users_to_bp_member_type( 'subscriber', 'student' );
                $checked = ' checked="checked" ';
            } else {
                $this->remove_convertion_users_to_bp_member_type( 'subscriber', 'student' );
            }
            echo "<input " . $checked . " id='convert_subscribers' name='buddypress_sensei_plugin_options[convert_subscribers]' type='checkbox' />  ";
            _e( 'Convert subscribers to user role Student', 'sensei-buddypress' );
        }

        public function convert_teachers_option() {
            $value = buddypress_sensei()->option( 'convert_teachers' );
            $checked = '';
            if ( $value ) {
                $this->convert_users_to_bp_member_type( 'teacher', 'teacher' );
                $checked = ' checked="checked" ';
            } else {
                $this->remove_convertion_users_to_bp_member_type( 'teacher', 'teacher' );
            }
            echo "<input " . $checked . " id='convert_teachers' name='buddypress_sensei_plugin_options[convert_teachers]' type='checkbox' />  ";
            _e( 'Convert teachers to user role Teacher', 'sensei-buddypress' );
        }

        public function convert_users_to_bp_member_type( $role, $bp_member_tpe ) {
            $all_users = get_users( 'role=' . $role );
            foreach ( (array) $all_users as $user ) {
                $member_type = bp_get_member_type( $user->ID );
                if($member_type != $bp_member_tpe) {
                    bp_set_member_type( $user->ID, $bp_member_tpe );
                }
            }
        }

        public function remove_convertion_users_to_bp_member_type( $role, $bp_member_tpe ) {
            $subscribers = get_users( 'role=' . $role );
            foreach ( (array) $subscribers as $user ) {
                $member_type = bp_get_member_type( $user->ID );
                if ( $member_type == $bp_member_tpe ) {
                    bp_set_member_type( $user->ID, '' );
                }
            }
        }

        /**
         * General settings section
         *
         * @since BuddyPress for Sensei (1.0.0)
         */
        public function section_general(){

        }

    }

// End class BuddyPress_Sensei_Admin
endif;