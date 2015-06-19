<?php
/**
 * @package WordPress
 * @subpackage BuddyPress for Sensei
 */
if ( ! defined( 'ABSPATH' ) )
	exit; // Exit if accessed directly

if ( ! class_exists( 'BuddyPress_Sensei_Groups' ) ) {

	/**
	 *
	 * BuddyPress_Sensei_Groups
	 * ********************
	 *
	 *
	 */
	class BuddyPress_Sensei_Groups {

		/**
		 * empty constructor function to ensure a single instance
		 */
		public function __construct() {
			// leave empty, see singleton below
		}

		public static function instance() {
			static $instance = null;

			if ( null === $instance ) {
				$instance = new BuddyPress_Sensei_Groups;
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
			
			if ( $woothemes_sensei ) {
				add_action( 'add_meta_boxes', array( $this, 'bp_sensei_metabox' ), 1 );
				add_action( 'save_post', array ( $this, 'bp_sensei_save_postdata' ) );
				add_action( 'body_class', array ( $this, 'bp_sensei_group_body_class' ) );
				add_action( 'sensei_user_course_start', array ( $this, 'bp_sensei_user_course_start' ), 10, 2 );
				add_action( 'sensei_user_course_reset', array ( $this, 'bp_sensei_user_course_reset' ), 10, 2 );
				
				add_action('sensei_course_single_meta', array( $this,'bp_sensei_group_discussion_button' ),110 );
				add_action('sensei_breadcrumb', array( $this,'bp_sensei_group_discussion_button' ),110 );
				
				add_filter( 'bp_get_group_type', array( $this, 'bp_sensei_course_group_text' ) );
			}
		}
		
		/**
		 * Sensei course metabox
		 */
		public function bp_sensei_metabox() {

			if ( isset( $_GET[ 'post' ] ) ) {
				$post_id = $_GET[ 'post' ];
			} elseif ( isset( $_POST[ 'post_ID' ] ) ) {
				$post_id = $_POST[ 'post_ID' ];
			}
			add_meta_box( 'bp_course_group', __( 'Course Group', 'sensei-buddypress' ), array( $this, 'bp_sensei_metabox_function' ), 'course', 'side', 'core' );
		}
		
		/**
		 * Sensei metabox html
		 * @param type $post
		 */
		public function bp_sensei_metabox_function( $post ) {
			wp_nonce_field( plugin_basename( __FILE__ ), $post->post_type . '_noncename' );
			$course_group = get_post_meta( $post->ID, 'bp_course_group', true );

			$groups_arr = BP_Groups_Group::get( array(
							'type' => 'alphabetical',
							'per_page' => 999
						) );
			?>

			<p><?php _e( 'Add this course to a BuddyPress group.', 'sensei-buddypress' ); ?></p>
			<select name="bp_course_group" id="bp-course-group">
				<option value="-1"><?php _e( '--Select--', 'sensei-buddypress' ); ?></option>
				<?php
				foreach ( $groups_arr[ 'groups' ] as $group ) {
					$group_status = groups_get_groupmeta( $group->id, 'bp_course_attached', true );
					if ( !empty($group_status) && $course_group != $group->id ) {
						continue;
					}
						
					?><option value="<?php echo $group->id; ?>" <?php echo (( $course_group == $group->id )) ? 'selected' : ''; ?>><?php _e( $group->name, 'sensei-buddypress' ); ?></option><?php
				}
				?>
			</select>
			<h4><a href="<?php echo ( home_url() .'/'. buddypress()->{'groups'}->root_slug .'/create' ); ?>" target="_blank"><?php _e( '&#43; Create New Group', 'sensei-buddypress' ); ?></a></h4><?php
		}
		
		/**
		 * Courses save postadata
		 * @param type $post_id
		 */
		public function bp_sensei_save_postdata( $post_id ) {
			// verify if this is an auto save routine. 
			// If it is our form has not been submitted, so we dont want to do anything
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
				return;

			// verify this came from the our screen and with proper authorization,
			// because save_post can be triggered at other times

			if ( ! wp_verify_nonce( @$_POST[ $_POST[ 'post_type' ] . '_noncename' ], plugin_basename( __FILE__ ) ) )
				return;

			// Check permissions
			if ( ! current_user_can( 'edit_post', $post_id ) )
				return;
			// OK, we're authenticated: we need to find and save the data
			if ( 'course' == $_POST[ 'post_type' ] ) {
				if ( ! current_user_can( 'edit_page', $post_id ) ) {
					return;
				} else {
					$old_group_id = get_post_meta( $post_id, 'bp_course_group', true );
					
					update_post_meta( $post_id, 'bp_course_group', $_POST[ 'bp_course_group' ] );
					
					if ( !empty( $old_group_id ) ) {
						groups_delete_groupmeta( $old_group_id, 'bp_course_attached' );
						bp_sensei_remove_members_group($post_id, $old_group_id );
					}
					if ( $_POST[ 'bp_course_group' ] != '-1' ) {
						groups_add_groupmeta( $_POST[ 'bp_course_group' ], 'bp_course_attached', $post_id );
						bp_sensei_add_members_group($post_id, $_POST[ 'bp_course_group' ] );
						bp_sensei_attach_forum( $_POST[ 'bp_course_group' ] );
						$this->bp_sensei_alter_group_status( $_POST[ 'bp_course_group' ] );
						
						//Adding teacher as admin of group
						bp_sensei_course_teacher_group_admin( $post_id, $_POST[ 'bp_course_group' ]);
						
						bp_sensei_update_group_avatar( $post_id, $_POST[ 'bp_course_group' ] );
					}
				}
			}
		}
		
		/**
		 * Sensei fetch all course members
		 * @param type $course_id
		 * @return type object/array
		 */
		public static function bp_sensei_get_course_members( $course_id ) {
			
			$course_args = array(
				'post_id' => $course_id,
				'type' => 'sensei_course_status',
				'status' => 'any',
			);
			$course_students = WooThemes_Sensei_Utils::sensei_check_for_activity( $course_args,TRUE );
			
			return $course_students;
			
		}
		
		/**
		 * Sensei alter group status
		 * @param type $group_id
		 */
		public function bp_sensei_alter_group_status( $group_id ) {
			$group = groups_get_group( array( 'group_id' => $group_id ) );
			
			if ( 'public' == $group->status ) {
				$group->status = 'private';
				
			} elseif ( 'hidden' == $group->status ) {
				$group->status = 'hidden';
			}
			$group->save();
			
		}
		
		/**
		 * Sensei group class
		 * @param string $classes
		 * @return string
		 */
		public function bp_sensei_group_body_class( $classes = '' ) {
			
			if ( in_array( 'group-settings', $classes ) ) {
				$group = groups_get_current_group();
				$course_attached = groups_get_groupmeta( $group->id, 'bp_course_attached',true );
				if ( !  empty( $course_attached ) ) {
					$classes[] = 'bp-hidepublic';
				}
				
			}
			return $classes;
		}
		
		/**
		 * Sensei add member to group on course start
		 * @param type $user_id
		 * @param type $course_id
		 */
		public function bp_sensei_user_course_start( $user_id, $course_id ) {
			
			$group_attached = get_post_meta( $course_id, 'bp_course_group', true );
			
			if ( !empty( $group_attached ) ) {
				groups_join_group( $group_attached, $user_id );
			}
			
		}
		
		/**
		 * Sensei Remove member on course reset
		 * @param type $user_id
		 * @param type $course_id
		 */
		public function bp_sensei_user_course_reset( $user_id, $course_id ) {
			
			$group_attached = get_post_meta( $course_id, 'bp_course_group', true );
			
			if ( !empty( $group_attached ) ) {
				groups_remove_member( $user_id, $group_attached );
			}
			
		}
		
		/**
		 * Sensei change course group text
		 * @global type $groups_template
		 * @param type $type
		 * @return type
		 */
		public function bp_sensei_course_group_text( $type ) {
			global $groups_template;
			
			if ( empty( $group ) )
				$group =& $groups_template->group;
			
			$group_id = $group->id;
			$course_attached = groups_get_groupmeta( $group_id, 'bp_course_attached', true );
				
			if ( empty( $course_attached ) ) {
				return apply_filters( 'bp_sensei_course_group_text', $type );
			}
			
			if ( 'Private Group' == $type ) {
				$type = __( "Private Course Group", "buddypress" );
			}
			if ( 'Hidden Group' == $type ) {
				$type = __( "Hidden Course Group", "buddypress" );
			}
			
			return apply_filters( 'bp_sensei_course_group_text', $type );
		}
		
		public function bp_sensei_group_discussion_button( ) {
			if ( ! ( is_singular( 'course' ) || is_singular( 'lesson' ) ) || !empty($_GET['contact']) ) return;
            $html = '';
			$course_id = get_the_ID();
			$group_attached = get_post_meta( $course_id, 'bp_course_group', true );
			
			if ( empty($group_attached) || $group_attached == '-1' )	return;
			
			global $bp;
			$group_data = groups_get_slug($group_attached);
			$html = '<p class="bp-group-discussion"><a class="button" href="'. trailingslashit(home_url()).trailingslashit($bp->groups->slug).$group_data .'">'.__('Course Discussion','sensei-buddypress').'</a></p>';
			
			echo $html;
		}
		

	} // End of class

	if ( bp_is_active('groups') ) {
		BuddyPress_Sensei_Groups::instance();
	}
}
