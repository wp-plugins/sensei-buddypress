<?php
/**
 * @package WordPress
 * @subpackage BuddyPress for Sensei
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if( !class_exists('BuddyPress_Sensei_Loader') ):

	/**
	 *
	 * BuddyPress_Sensei_Loader
	 * ********************
	 *
	 *
	 */
    class BuddyPress_Sensei_Loader {

        /**
         * empty constructor function to ensure a single instance
         */
        public function __construct() {
            // leave empty, see singleton below
        }

        public static function instance() {
            static $instance = null;

            if(null === $instance) {
                $instance = new BuddyPress_Sensei_Loader;
                $instance->setup();
            }
            return $instance;
        }

        /**
         * setup all
         */
        public function setup() {
            // check if sensei activated
            global $woothemes_sensei;
            if( $woothemes_sensei ) {

                add_action( 'bp_init', array( $this, 'bp_sensei_register_member_types' ) );
                add_action( 'bp_members_directory_member_types', array( $this, 'bp_sensei_members_directory' ) );
                add_action( 'bp_pre_user_query_construct',  array( $this, 'bp_sensei_members_query' ), 1, 1 );

                // create a course page
                add_action( 'bp_setup_nav', array( $this, 'bp_sensei_add_new_setup_nav' ), 100 );
                add_action( 'bp_setup_admin_bar', array( $this, 'bp_sensei_add_new_admin_bar' ), 90 );

                // author link filter
                add_filter( 'author_link', array( $this, 'bp_sensei_change_author_link' ), 100, 2 );

                // set member type while user register or role changed
                add_action( 'user_register', array( $this, 'bp_sensei_registration_save' ), 100, 1 );
                add_action( 'set_user_role', array( $this, 'bp_sensei_user_role_change_save' ), 100, 2 );

                // Sensei send message link overwrite
                $sensei_message_class_object = $woothemes_sensei->post_types->messages;
				
                remove_action( 'sensei_course_single_lessons', array( $sensei_message_class_object, 'send_message_link' ), 1 );
                remove_action( 'sensei_breadcrumb', array( $sensei_message_class_object, 'send_message_link' ), 1 );
                remove_action( 'sensei_course_single_meta', array( $sensei_message_class_object, 'send_message_link' ), 14 );

                add_action( 'sensei_course_single_meta', array( $this, 'bp_sensei_send_message_link' ), 110 );
                add_action( 'sensei_breadcrumb', array( $this, 'bp_sensei_send_message_link' ), 100 );

                // activity stream
                add_action( 'sensei_user_course_start', array( $this, 'bp_sensei_user_course_start_activity' ), 100, 2 );
				add_action( 'added_post_meta', array( $this, 'bp_sensei_create_lesson_activity' ), 100, 4 );
                add_action( 'sensei_user_lesson_end', array( $this, 'bp_sensei_user_lesson_end_activity' ), 100, 2 );
				add_action( 'sensei_user_course_end', array( $this, 'bp_sensei_user_course_end_activity' ), 100, 2 );
				add_action( 'comment_post', array( $this, 'bp_sensei_lesson_comment' ), 100, 2 );
				add_action( 'sensei_complete_quiz', array( $this, 'bp_sensei_complete_quiz_activity' ), 100 );
            }
        }

        /**
         * Sensei lesson create activity
         */
        public function bp_sensei_create_lesson_activity( $mid, $object_id, $meta_key, $_meta_value ) {
            $post = get_post( $object_id );
            $user_id = $post->post_author;
            $lesson_id = $post->ID;

            // if post type not lesson, then return
            if( $post->post_type != 'lesson' ) return;

            // return if this lesson is not attached to a course still
            $course_id = get_post_meta( $lesson_id, '_lesson_course', true );
            if( empty( $course_id ) ) return;

            // if already displayed
            $attached_course_id = get_post_meta( $lesson_id, 'attached_course_id', true );
            if( $attached_course_id == $course_id ) return;
			
			$group_attached = get_post_meta( $course_id, 'bp_course_group', true );
			if ( empty( $group_attached ) ) {
				return;
			}
			$bp_sensei_course_activity = groups_get_groupmeta( $group_attached, 'group_extension_course_setting_activities' );
			if ( is_array($bp_sensei_course_activity) && !array_key_exists( 'user_lesson_start', $bp_sensei_course_activity ) ) {
				return;
			}
			
			global $bp;
            $user_link = bp_core_get_userlink( $user_id );
            $lesson_title = get_the_title( $lesson_id );
            $lesson_link = get_permalink( $lesson_id );
            $lesson_link_html = '<a href="' . esc_url( $lesson_link ) . '">' . $lesson_title . '</a>';
            $course_title = get_the_title( $course_id );
            $course_link = get_permalink( $course_id );
            $course_link_html = '<a href="' . esc_url( $course_link ) . '">' . $course_title . '</a>';
            $args = array(
                'type' => 'activity_update',
                'action' => apply_filters( 'bp_sensei_create_lesson_activity',
                    sprintf( __( '%1$s added the lesson %2$s to the course %3$s', 'sensei-buddypress' ),
                        $user_link, $lesson_link_html, $course_link_html ), $user_id, $lesson_id ),
				'item_id' => $group_attached,
				'component'	=> $bp->groups->id,
				'hide_sitewide'	=> true
            );
            $activity_recorded = bp_sensei_record_activity( $args );
            if($activity_recorded) {
                update_post_meta( $lesson_id, 'attached_course_id', $course_id );
				bp_activity_add_meta($activity_recorded, 'bp_sensei_group_activity_markup_courseid', $lesson_id );
            }
        }

        /**
         * Sensei user course start activity
         */
        public function bp_sensei_user_course_start_activity( $user_id, $course_id ) {
			$group_attached = get_post_meta( $course_id, 'bp_course_group', true );
			if ( empty( $group_attached ) ) {
				return;
			}
			$bp_sensei_course_activity = groups_get_groupmeta( $group_attached, 'group_extension_course_setting_activities' );
			if ( !empty($bp_sensei_course_activity) && !array_key_exists( 'user_course_start', $bp_sensei_course_activity ) ) {
				return;
			}
			global $bp;
            $user_link = bp_core_get_userlink( $user_id );
            $course_title = get_the_title( $course_id );
            $course_link = get_permalink( $course_id );
            $course_link_html = '<a href="' . esc_url( $course_link ) . '">' . $course_title . '</a>';
            $args = array(
                'type' => 'activity_update',
                'action' => apply_filters( 'bp_sensei_user_course_start_activity',
                    sprintf( __( '%1$s started taking the course %2$s', 'sensei-buddypress' ),
                        $user_link, $course_link_html ), $user_id, $course_id ),
                'item_id' => $group_attached,
				'component'	=> $bp->groups->id,
				'hide_sitewide'	=> true
            );
			$activity_recorded = bp_sensei_record_activity( $args );
			if($activity_recorded) {
				bp_activity_add_meta($activity_recorded, 'bp_sensei_group_activity_markup_courseid', $course_id );
            }
        }

        /**
         * Sensei user lession end activity
         */
        public function bp_sensei_user_lesson_end_activity( $user_id, $lesson_id ) {
            global $woothemes_sensei,$bp;
            if ( $woothemes_sensei->settings->settings['course_completion'] != 'passed' ) {
                return;
            }
			$course_id = get_post_meta( $lesson_id, '_lesson_course', true );
			$group_attached = get_post_meta( $course_id, 'bp_course_group', true );
			if ( empty( $group_attached ) ) {
				return;
			}
			$bp_sensei_course_activity = groups_get_groupmeta( $group_attached, 'group_extension_course_setting_activities' );
			if ( !empty($bp_sensei_course_activity) && !array_key_exists( 'user_lesson_end', $bp_sensei_course_activity ) ) {
				return;
			}
                $user_link = bp_core_get_userlink( $user_id );
                $lession_title = get_the_title( $lesson_id );
                $lession_link = get_permalink( $lesson_id );
                $lession_link_html = '<a href="' . esc_url( $lession_link ) . '">' . $lession_title . '</a>';
                $args = array(
                    'type' => 'activity_update',
                    'action' => apply_filters( 'bp_sensei_user_lesson_end_activity',
                        sprintf( __( '%1$s completed the lesson %2$s', 'sensei-buddypress' ),
                            $user_link, $lession_link_html ), $user_id, $lesson_id ),
                    'item_id' => $group_attached,
					'component'	=> $bp->groups->id,
					'hide_sitewide'	=> true
                );
				$activity_recorded = bp_sensei_record_activity( $args );
				if($activity_recorded) {
					bp_activity_add_meta($activity_recorded, 'bp_sensei_group_activity_markup_courseid', $lesson_id );
				}
        }

        /**
         * Sensei user course end activity
         */
        public function bp_sensei_user_course_end_activity( $user_id, $course_id ) {
			
            global $bp;
			$group_attached = get_post_meta( $course_id, 'bp_course_group', true );
			if ( empty( $group_attached ) ) {
				return;
			}
			$bp_sensei_course_activity = groups_get_groupmeta( $group_attached, 'group_extension_course_setting_activities' );
			if ( !empty($bp_sensei_course_activity) && !array_key_exists( 'user_course_end', $bp_sensei_course_activity ) ) {
				return;
			}
			
			if ( WooThemes_Sensei_Utils::user_completed_course( intval($course_id), intval($user_id) ) ) {
				
				$user_link = bp_core_get_userlink( $user_id );
				$course_title = get_the_title( $course_id );
				$course_link = get_permalink( $course_id );
				$course_link_html = '<a href="' . esc_url( $course_link ) . '">' . $course_title . '</a>';
				$args = array(
					'type' => 'activity_update',
					'action' => apply_filters( 'bp_sensei_user_course_end_activity',
						sprintf( __( '%1$s completed the course %2$s', 'sensei-buddypress' ),
							$user_link, $course_link_html ), $user_id, $course_id ),
					'item_id' => $group_attached,
					'component'	=> $bp->groups->id,
					'hide_sitewide'	=> true
				);
				$activity_recorded = bp_sensei_record_activity( $args );
				if($activity_recorded) {
					bp_activity_add_meta($activity_recorded, 'bp_sensei_group_activity_markup_courseid', $course_id );
				}
			}
        }
		
		/**
		 * Sensei record lesson comment
		 * @global type $bp
		 * @param type $comment_ID
		 * @param type $commentdata
		 */
		public function bp_sensei_lesson_comment( $comment_ID, $commentdata ) {

			$comment_obj = get_comment( $comment_ID );
			$post_id = $comment_obj->comment_post_ID;
			$post_type = get_post_type( $post_id );

			if ( 'lesson' == $post_type && $commentdata ) {
					
				global $bp;
				$course_id = get_post_meta($post_id,'attached_course_id',true);
				$group_attached = get_post_meta( $course_id, 'bp_course_group', true );
				if ( empty( $group_attached ) ) {
					return;
				}
				$bp_sensei_course_activity = groups_get_groupmeta( $group_attached, 'group_extension_course_setting_activities' );
				if ( !empty($bp_sensei_course_activity) && !array_key_exists( 'user_lesson_comment', $bp_sensei_course_activity ) ) {
					return;
				}

				$user_link = bp_core_get_userlink( $comment_obj->user_id );
				$lession_title = get_the_title( $post_id );
                $lession_link = get_permalink( $post_id );
                $lession_link_html = '<a href="' . esc_url( $lession_link ) . '">' . $lession_title . '</a>';
				$args = array(
					'type' => 'activity_update',
					'action' => apply_filters( 'bp_sensei_user_lesson_comment_activity', sprintf( __( '%1$s commented on lesson %2$s', 'sensei-buddypress' ), $user_link, $lession_link_html ), $comment_obj->user_id, $course_id ),
					'item_id' => $group_attached,
					'component' => $bp->groups->id,
					'hide_sitewide' => true,
					'content' => $comment_obj->comment_content
				);
				$activity_recorded = bp_sensei_record_activity( $args );
				if($activity_recorded) {
					bp_activity_add_meta($activity_recorded, 'bp_sensei_group_activity_markup_courseid', $post_id );
				}
			}
		}
		
		/**
		 * Sensei record quiz activity
		 * @global type $woothemes_sensei
		 * @global type $bp
		 */
		public function bp_sensei_complete_quiz_activity( ) {
			
			if( ! isset( $_POST[ 'quiz_complete' ]) || ! isset( $_POST[ 'sensei_question' ] ) || empty( $_POST[ 'sensei_question' ] ) ) {
				return;
			}
			
			global $bp,$woothemes_sensei;
			
			$quiz_grade = $woothemes_sensei->quiz->data->user_quiz_grade;
			$quiz_pass_mark = $woothemes_sensei->quiz->data->quiz_passmark;
			$quiz_lession_id = $woothemes_sensei->quiz->data->quiz_lesson;
			$course_id = get_post_meta($quiz_lession_id,'attached_course_id',true);
			$group_attached = get_post_meta( $course_id, 'bp_course_group', true );
			if ( empty( $group_attached ) ) {
				return;
			}
			$bp_sensei_course_activity = groups_get_groupmeta( $group_attached, 'group_extension_course_setting_activities' );
			if ( !empty($bp_sensei_course_activity) && !array_key_exists( 'user_quiz_pass', $bp_sensei_course_activity ) ) {
				return;
			}
			
			if ( !($quiz_grade > $quiz_pass_mark) ) { 
				return;
			}
				$user_link = bp_core_get_userlink( get_current_user_id() );
				$lession_title = get_the_title( $quiz_lession_id );
                $lession_link = get_permalink( $quiz_lession_id );
                $lession_link_html = '<a href="' . esc_url( $lession_link ) . '">' . $lession_title . '</a>';
				$args = array(
					'type' => 'activity_update',
					'action' => apply_filters( 'bp_sensei_complete_quiz_activity', sprintf( __( '%1$s has passed the %2$s quiz achieving %3$s %%', 'sensei-buddypress' ), $user_link, $lession_link_html, $quiz_grade ), get_current_user_id(), $quiz_lession_id ),
					'item_id' => $group_attached,
					'component' => $bp->groups->id,
					'hide_sitewide' => true
				);
				$activity_recorded = bp_sensei_record_activity( $args );
				if($activity_recorded) {
					bp_activity_add_meta($activity_recorded, 'bp_sensei_group_activity_markup_courseid', $course_id );
				}
			
			
		}

		/**
         * Sensei send message link override
         */
        public function bp_sensei_send_message_link() {
            global $woothemes_sensei, $post;
            $sensei_message_class_object = $woothemes_sensei->post_types->messages;

            if ( ! ( is_singular( 'course' ) || is_singular( 'lesson' ) ) ) return;

            $html = '';

            if( ! isset( $woothemes_sensei->settings->settings['messages_disable'] ) || ! $woothemes_sensei->settings->settings['messages_disable'] ) {

                if( ! is_user_logged_in() ) return;

                if( isset( $_GET['contact'] ) ) {
                    $html .= $sensei_message_class_object->teacher_contact_form( $post );
                } else {
                    $href = esc_url(add_query_arg( array( 'contact' => $post->post_type ) ));
                    if( bp_is_active( 'messages' ) ){
                        $user_domain   = bp_loggedin_user_domain();
                        $messages_link = trailingslashit( $user_domain . BP_MESSAGES_SLUG );
                        $compose_link = trailingslashit( $messages_link . 'compose' ) . '?receiver=' . bp_core_get_username( $post->post_author );
                        $html .= '<p class="bp-sensei-msg-link"><a class="button send-message-button" href="' . $compose_link . '">' . sprintf( __( 'Contact %1$s Teacher', 'woothemes-sensei' ), ucfirst( $post->post_type ) ) . '</a></p>';
                    }else{
                        $html .= '<p class="bp-sensei-msg-link"><a class="button send-message-button" href="' . $href . '#private_message">' . sprintf( __( 'Contact %1$s Teacher', 'woothemes-sensei' ), ucfirst( $post->post_type ) ) . '</a></p>';
                    }
                }

                if( isset( $sensei_message_class_object->message_notice ) && isset( $sensei_message_class_object->message_notice['type'] ) && isset( $sensei_message_class_object->message_notice['notice'] ) ) {
                    $html .= '<div class="sensei-message ' . $sensei_message_class_object->message_notice['type'] . '">' . $sensei_message_class_object->message_notice['notice'] . '</div>';
                }

            }

            echo $html;
        }

		/**
         * Add new nav items
         */
        public function bp_sensei_add_new_setup_nav() {
            $courses_visibility = buddypress_sensei()->option( 'courses_visibility' );
            if( !$courses_visibility && bp_displayed_user_id() != bp_loggedin_user_id() ) return;

            $all_nav_items = array(
                array(
                    'name' => bp_sensei_profile_courses_name(),
                    'slug' => bp_sensei_profile_courses_slug(),
                    'screen' => 'bp_sensei_active_courses_page',
                    'default_subnav' => bp_sensei_profile_active_courses_slug(),
                )
            );
            $all_subnav_items = array(
                array(
                    'name' => bp_sensei_profile_active_courses_name(),
                    'slug' => bp_sensei_profile_active_courses_slug(),
                    'parent_slug' => bp_sensei_profile_courses_slug(),
                    'screen' => 'bp_sensei_active_courses_page',
                ),
                array(
                    'name' => bp_sensei_profile_completed_courses_name(),
                    'slug' => bp_sensei_profile_completed_courses_slug(),
                    'parent_slug' => bp_sensei_profile_courses_slug(),
                    'screen' => 'bp_sensei_completed_courses_page',
                )
            );
			if( current_user_can( 'manage_sensei_grades' ) ) {
				$all_subnav_items[] = array(
					'name' => bp_sensei_profile_create_courses_name(),
					'slug' => bp_sensei_profile_create_courses_slug(),
					'parent_slug' => bp_sensei_profile_courses_slug(),
					'screen' => 'bp_sensei_create_courses_page'
				);
			}
			
            // create nav item
            foreach( (array) $all_nav_items as $single ) {
                $this->bp_sensei_setup_nav( $single['name'], $single['slug'], $single['screen'], $single['default_subnav'] );
            }
            // create subnav item
            foreach( (array) $all_subnav_items as $single ) {
                $this->bp_sensei_setup_subnav( $single['name'], $single['slug'], $single['parent_slug'], $single['screen'] );
            }
        }

        public function bp_sensei_setup_nav( $name, $slug, $screen, $default_subnav ) {
            bp_core_new_nav_item( array(
                'name' => $name,
                'slug' => $slug,
                'screen_function' => $screen,
                'position' => 80,
                'default_subnav_slug' => $default_subnav,
            ) );
        }

        public function bp_sensei_setup_subnav( $name, $slug, $parent_slug, $screen ) {
            $parent_nav_link = bp_sensei_get_nav_link( $parent_slug );
            bp_core_new_subnav_item( array(
                'name' => $name,
                'slug' => $slug,
                'parent_url' => $parent_nav_link,
                'parent_slug' => $parent_slug,
                'screen_function' => $screen,
                'position' => 80,
            ) );
        }

        /**
         * add new admin bar items
         */
        public function bp_sensei_add_new_admin_bar(){
            $all_post_types = array(
                array(
                    'name' => bp_sensei_profile_courses_name(),
                    'slug' => bp_sensei_profile_courses_slug(),
                    'parent' => 'buddypress',
                    'nav_link' => bp_sensei_adminbar_nav_link(bp_sensei_profile_courses_slug()),
                ),
                array(
                    'name' => bp_sensei_profile_active_courses_name(),
                    'slug' => bp_sensei_profile_active_courses_slug(),
                    'parent' => bp_sensei_profile_courses_slug(),
                    'nav_link' => bp_sensei_adminbar_nav_link(bp_sensei_profile_courses_slug()),
                ),
                array(
                    'name' => bp_sensei_profile_completed_courses_name(),
                    'slug' => bp_sensei_profile_completed_courses_slug(),
                    'parent' => bp_sensei_profile_courses_slug(),
                    'nav_link' => bp_sensei_adminbar_nav_link(bp_sensei_profile_completed_courses_slug(), bp_sensei_profile_courses_slug()),
                )
            );
			
			if( current_user_can( 'manage_sensei_grades' ) ) {
				$all_post_types[] =
				array(
                    'name' => bp_sensei_profile_create_courses_name(),
                    'slug' => bp_sensei_profile_create_courses_slug(),
                    'parent' => bp_sensei_profile_courses_slug(),
                    'nav_link' => bp_sensei_adminbar_nav_link( bp_sensei_profile_create_courses_slug(),bp_sensei_profile_courses_slug()),
                );
			}
			
            foreach( (array) $all_post_types as $single ) {
                $this->bp_sensei_setup_admin_bar( $single['name'], $single['slug'], $single['parent'], $single['nav_link'] );
            }
        }

        public function bp_sensei_setup_admin_bar( $name, $slug, $parent, $nav_link ) {
            global $wp_admin_bar;

            $wp_admin_bar->add_menu( array(
                'parent' => 'my-account-'.$parent,
                'id'     => 'my-account-'.$slug,
                'title'  => $name,
                'href'   => $nav_link,
            ) );
        }

        /**
         * Registering member type for Sensei
         */
        public function bp_sensei_register_member_types() {
            bp_register_member_type( 'student', array(
                'labels' => array(
                    'name'          => __( 'Students', 'sensei-buddypress' ),
                    'singular_name' => __( 'Student', 'sensei-buddypress' ),
                ),
            ) );
            bp_register_member_type( 'teacher', array(
                'labels' => array(
                    'name'          => __( 'Teachers', 'sensei-buddypress' ),
                    'singular_name' => __( 'Teacher', 'sensei-buddypress' ),
                ),
            ) );
        }

        public function bp_sensei_members_directory() {
            ?>
            <li id="members-teacher"><a href="<?php site_url(); ?>bpe-teacher"><?php printf( __( 'Teachers <span>%s</span>', 'sensei-buddypress' ), bp_sensei_members_count_by_type( 'teacher' ) ); ?></a></li>
            <li id="members-student"><a href="<?php site_url(); ?>bpe-student"><?php printf( __( 'Students <span>%s</span>', 'sensei-buddypress' ), bp_sensei_members_count_by_type( 'student' ) ); ?></a></li>
        <?php
        }

        public function bp_sensei_members_query( $query_array ) {
            if( ( isset( $_COOKIE['bp-members-scope'] ) && $_COOKIE['bp-members-scope'] == 'student' ) ||
                ( isset( $_POST['scope'] ) && $_POST['scope'] == 'student' )
            ){
                $type_id = bp_sensei_sql_member_type_id( 'student' );
                $user_ids = bp_sensei_sql_members_by_type( $type_id );
                $all_users_ids = bp_sensei_get_all_users();
                if( ! empty ( $user_ids ) ) {
                    $query_array->query_vars['include'] = $user_ids;
                } else {
                    $query_array->query_vars['exclude'] = $all_users_ids;
                }
            }

            if( ( isset( $_COOKIE['bp-members-scope'] ) && $_COOKIE['bp-members-scope'] == 'teacher' ) ||
                ( isset( $_POST['scope'] ) && $_POST['scope'] == 'teacher' )
            ){
                $type_id = bp_sensei_sql_member_type_id( 'teacher' );
                $user_ids = bp_sensei_sql_members_by_type( $type_id );
                $all_users_ids = bp_sensei_get_all_users();
                if ( ! empty ( $user_ids ) ) {
                    $query_array->query_vars['include'] = $user_ids;
                } else {
                    $query_array->query_vars['exclude'] = $all_users_ids;
                }
            }
        }

        public function bp_sensei_members_count( $count ) {
            if( ( isset( $_COOKIE['bp-members-scope'] ) && $_COOKIE['bp-members-scope'] == 'student' ) ||
                ( isset( $_POST['scope'] ) && $_POST['scope'] == 'student' )
            ){
                $type_id = bp_sensei_sql_member_type_id( 'student' );
                $user_ids = bp_sensei_sql_members_by_type( $type_id );
                $count = count( $user_ids );
            }

            if( ( isset( $_COOKIE['bp-members-scope'] ) && $_COOKIE['bp-members-scope'] == 'teacher' ) ||
                ( isset( $_POST['scope'] ) && $_POST['scope'] == 'teacher' )
            ){
                $type_id = bp_sensei_sql_member_type_id( 'teacher' );
                $user_ids = bp_sensei_sql_members_by_type( $type_id );
                $count = count( $user_ids );
            }

            return $count;
        }

        public function bp_sensei_change_author_link( $link, $author_id ) {
            $post_types = array( 'post' );
            if( ! is_admin() && ! is_singular( $post_types ) && ! is_post_type_archive( $post_types ) ) {
                $link = bp_core_get_user_domain( absint( $author_id ) );
            }
            return $link;
        }

        public function bp_sensei_registration_save( $user_id ) {
            $user_data = get_userdata( $user_id );
            $user_roles = $user_data->roles;
            $member_type = bp_get_member_type( $user_id );
            if( in_array( 'subscriber', $user_roles ) && $member_type != 'student' ) {
                bp_set_member_type( $user_id, 'student' );
            }
            if( in_array( 'teacher', $user_roles ) && $member_type != 'teacher' ) {
                bp_set_member_type( $user_id, 'teacher' );
            }
        }

        public function bp_sensei_user_role_change_save( $user_id, $role ) {
            $member_type = bp_get_member_type( $user_id );
            if( $role == 'subscriber' && $member_type != 'student' ) {
                bp_set_member_type( $user_id, 'student' );
            }
            if( $role == 'teacher' && $member_type != 'teacher' ) {
                bp_set_member_type( $user_id, 'teacher' );
            }
        }


    }
    BuddyPress_Sensei_Loader::instance();

endif;
