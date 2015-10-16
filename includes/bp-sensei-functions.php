<?php
/**
 * @package WordPress
 * @subpackage BuddyPress for Sensei
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * courses name can be changed from here
 * @return string
 */
function bp_sensei_profile_courses_name() {
	$course_name = apply_filters( 'bp_sensei_profile_courses_name', __( 'Courses', 'sensei-buddypress' ) );
    return $course_name;
}

/**
 * courses slug can be changed from here
 * @return string
 */
function bp_sensei_profile_courses_slug() {
	$course_slug = apply_filters( 'bp_sensei_profile_courses_slug', __( 'courses', 'sensei-buddypress' ) );
    return $course_slug;
}

/**
 * Active Courses name can be changed from here
 * @return string
 */
function bp_sensei_profile_active_courses_name() {
	$course_active_name = apply_filters( 'bp_sensei_profile_active_courses_name', __( 'Active Courses', 'sensei-buddypress' ) );
    return $course_active_name;
}

/**
 * Active Courses slug can be changed from here
 * @return string
 */
function bp_sensei_profile_active_courses_slug() {
	$course_active_slug = apply_filters( 'bp_sensei_profile_active_courses_slug', __( 'active-courses', 'sensei-buddypress' ) );
    return $course_active_slug;
}

/**
 * Completed Courses name can be changed from here
 * @return string
 */
function bp_sensei_profile_completed_courses_name() {
	$course_complete_name = apply_filters( 'bp_sensei_profile_completed_courses_name', __( 'Completed Courses', 'sensei-buddypress' ) );
    return $course_complete_name;
}

/**
 * Create a Course name can be changed from here
 * @return string
 */
function bp_sensei_profile_create_courses_name() {
	$course_name = apply_filters( 'bp_sensei_profile_create_courses_name', __( 'Create a Course', 'sensei-buddypress' ) );
    return $course_name;
}

/**
 * Completed Courses slug can be changed from here
 * @return string
 */
function bp_sensei_profile_completed_courses_slug() {
	$course_completed_slug = apply_filters( 'bp_sensei_profile_completed_courses_slug', __( 'completed-courses', 'sensei-buddypress' ) );
    return $course_completed_slug;
}

/**
 * Create Courses slug can be changed from here
 * @return string
 */
function bp_sensei_profile_create_courses_slug() {
	$course_create_slug = apply_filters( 'bp_sensei_profile_create_courses_slug', __( 'create-courses', 'sensei-buddypress' ) );
    return $course_create_slug;
}

function bp_sensei_get_nav_link( $slug, $parent_slug='' ) {
    $displayed_user_id = bp_displayed_user_id();
    $user_domain = ( ! empty ( $displayed_user_id ) ) ? bp_displayed_user_domain() : bp_loggedin_user_domain();
    if ( !empty ( $parent_slug ) ) {
        $nav_link = trailingslashit( $user_domain . $parent_slug .'/'. $slug );
    } else {
        $nav_link = trailingslashit( $user_domain . $slug );
    }
    return $nav_link;
}

function bp_sensei_adminbar_nav_link( $slug, $parent_slug='' ) {
    $user_domain = bp_loggedin_user_domain();
    if ( !empty ( $parent_slug ) ) {
        $nav_link = trailingslashit( $user_domain . $parent_slug .'/'. $slug );
    } else {
        $nav_link = trailingslashit( $user_domain . $slug );
    }
    return $nav_link;
}

function bp_sensei_get_all_users() {
    global $wpdb;
    $user_ids = array();
    $user_ids = $wpdb->get_col( "SELECT ID FROM {$wpdb->users}" );
    return $user_ids;
}

function bp_sensei_sql_member_type_id( $type_name ) {
    global $wpdb;
    $type_id = $wpdb->get_col( "SELECT t.term_id FROM {$wpdb->prefix}terms t INNER JOIN {$wpdb->prefix}term_taxonomy tt ON t.term_id = tt.term_id WHERE t.slug = '" . $type_name . "' AND  tt.taxonomy = 'bp_member_type' " );
    return ! isset ( $type_id[0] ) ? '' : $type_id[0];
}

function bp_sensei_sql_members_by_type( $type_id ) {
    global $wpdb;
    $student_ids = $wpdb->get_col( "SELECT u.ID FROM {$wpdb->users} u INNER JOIN {$wpdb->prefix}term_relationships r ON u.ID = r.object_id WHERE u.user_status = 0 AND r.term_taxonomy_id = " . $type_id );
    return $student_ids;
}

function bp_sensei_sql_members_count_by_type( $type_id ) {
    global $wpdb;
    $student_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->users} u INNER JOIN {$wpdb->prefix}term_relationships r ON u.ID = r.object_id WHERE u.user_status = 0 AND r.term_taxonomy_id = " . $type_id );
    return $student_count;
}

function bp_sensei_members_count_by_type( $type_name ) {
    $type_id = bp_sensei_sql_member_type_id( $type_name );
    $student_ids = bp_sensei_sql_members_by_type( $type_id );
    $members_count = count( $student_ids );
    return $members_count;
}

/**
 * Record an activity item
 */
function bp_sensei_record_activity( $args = '' ) {
    global $bp;

    if ( !function_exists( 'bp_activity_add' ) ) return false;

    $defaults = array(
        'id' => false,
        'user_id' => $bp->loggedin_user->id,
        'action' => '',
        'content' => '',
        'primary_link' => '',
        'component' => $bp->profile->id,
        'type' => false,
        'item_id' => false,
        'secondary_item_id' => false,
        'recorded_time' => gmdate( "Y-m-d H:i:s" ),
        'hide_sitewide' => false
    );

    $r = wp_parse_args( $args, $defaults );
    extract( $r );

    $activity_id = bp_activity_add( array(
        'id' => $id,
        'user_id' => $user_id,
        'action' => $action,
        'content' => $content,
        'primary_link' => $primary_link,
        'component' => $component,
        'type' => $type,
        'item_id' => $item_id,
        'secondary_item_id' => $secondary_item_id,
        'recorded_time' => $recorded_time,
        'hide_sitewide' => $hide_sitewide
    ) );
	
	bp_activity_add_meta( $activity_id, 'bp_sensei_group_activity_markup', 'true' );
	
	return $activity_id;
}

/**
 * get active courses html
 * @return array
 */
function bp_sensei_get_active_courses_html() {

    $user = get_userdata( bp_displayed_user_id() );
    $manage = bp_displayed_user_id() == bp_loggedin_user_id() ? true : false;
    global $woothemes_sensei, $post, $wp_query, $course, $my_courses_page, $my_courses_section;

// Build Output HTML
    $complete_html = $active_html = '';

    if( $user ) {

        $my_courses_page = true;

        // Allow action to be run before My Courses content has loaded
        do_action( 'sensei_before_my_courses', $user->ID );

        // Logic for Active and Completed Courses
        $per_page = 20;
        if ( isset( $woothemes_sensei->settings->settings[ 'my_course_amount' ] ) && ( 0 < absint( $woothemes_sensei->settings->settings[ 'my_course_amount' ] ) ) ) {
            $per_page = absint( $woothemes_sensei->settings->settings[ 'my_course_amount' ] );
        }

        $course_statuses = WooThemes_Sensei_Utils::sensei_check_for_activity( array( 'user_id' => $user->ID, 'type' => 'sensei_course_status' ), true );
		// User may only be on 1 Course
		if ( !is_array($course_statuses) ) {
			$course_statuses = array( $course_statuses );
		}
		
        $completed_ids = $active_ids = array();
        foreach( $course_statuses as $course_status ) {
            if ( WooThemes_Sensei_Utils::user_completed_course( $course_status, $user->ID ) ) {
                $completed_ids[] = $course_status->comment_post_ID;
            } else {
                $active_ids[] = $course_status->comment_post_ID;
            }
        }

        $active_count = $completed_count = 0;

        $active_courses = array();
        if ( 0 < intval( count( $active_ids ) ) ) {
            $my_courses_section = 'active';
            $active_courses = $woothemes_sensei->post_types->course->course_query( $per_page, 'usercourses', $active_ids );
            $active_count = count( $active_ids );
        } // End If Statement

        $completed_courses = array();
        if ( 0 < intval( count( $completed_ids ) ) ) {
            $my_courses_section = 'completed';
            $completed_courses = $woothemes_sensei->post_types->course->course_query( $per_page, 'usercourses', $completed_ids );
            $completed_count = count( $completed_ids );
        } // End If Statement
        $lesson_count = 1;

        $active_page = 1;
        if( isset( $_GET['active_page'] ) && 0 < intval( $_GET['active_page'] ) ) {
            $active_page = $_GET['active_page'];
        }

        $completed_page = 1;
        if( isset( $_GET['completed_page'] ) && 0 < intval( $_GET['completed_page'] ) ) {
            $completed_page = $_GET['completed_page'];
        }
        foreach ( $active_courses as $course_item ) {
            $course = $course_item;

            $course_lessons = $woothemes_sensei->post_types->course->course_lessons( $course_item->ID );
            $lessons_completed = 0;
            foreach ( $course_lessons as $lesson ) {
                if ( WooThemes_Sensei_Utils::user_completed_lesson( $lesson->ID, $user->ID ) ) {
                    ++$lessons_completed;
                }
            }

            // Get Course Categories
            $category_output = get_the_term_list( $course_item->ID, 'course-category', '', ', ', '' );

            $active_html .= '<article class="' . esc_attr( join( ' ', get_post_class( array( 'course', 'post' ), $course_item->ID ) ) ) . '">';

            // Image
            $active_html .= $woothemes_sensei->post_types->course->course_image( absint( $course_item->ID ) );

            // Title
            $active_html .= '<header>';

            $active_html .= '<h2><a href="' . esc_url( get_permalink( absint( $course_item->ID ) ) ) . '" title="' . esc_attr( $course_item->post_title ) . '">' . esc_html( $course_item->post_title ) . '</a></h2>';

            $active_html .= '</header>';

            $active_html .= '<section class="entry">';

            $active_html .= '<p class="sensei-course-meta">';

            // Author
            $user_info = get_userdata( absint( $course_item->post_author ) );
            if ( isset( $woothemes_sensei->settings->settings[ 'course_author' ] ) && ( $woothemes_sensei->settings->settings[ 'course_author' ] ) ) {
                $active_html .= '<span class="course-author"><a href="' . esc_url( get_author_posts_url( absint( $course_item->post_author ) ) ) . '" title="' . esc_attr( $user_info->display_name ) . '">' . __( 'by ', 'sensei-buddypress' ) . esc_html( $user_info->display_name ) . '</a></span>';
            } // End If Statement
            // Lesson count for this author
            $lesson_count = $woothemes_sensei->post_types->course->course_lesson_count( absint( $course_item->ID ) );
            // Handle Division by Zero
            if ( 0 == $lesson_count ) {
                $lesson_count = 1;
            } // End If Statement
            $active_html .= '<span class="course-lesson-count">' . $lesson_count . '&nbsp;' . apply_filters( 'sensei_lessons_text', __( 'Lessons', 'sensei-buddypress' ) ) . '</span>';
            // Course Categories
            if ( '' != $category_output ) {
                $active_html .= '<span class="course-category">' . sprintf( __( 'in %s', 'sensei-buddypress' ), $category_output ) . '</span>';
            } // End If Statement
            $active_html .= '<span class="course-lesson-progress">' . sprintf( __( '%1$d of %2$d lessons completed', 'sensei-buddypress' ) , $lessons_completed, $lesson_count  ) . '</span>';

            $active_html .= '</p>';

            $active_html .= '<p class="course-excerpt">' . apply_filters( 'get_the_excerpt', $course_item->post_excerpt ) . '</p>';

            $progress_percentage = abs( round( ( doubleval( $lessons_completed ) * 100 ) / ( $lesson_count ), 0 ) );

            if ( 50 < $progress_percentage ) { $class = ' green'; } elseif ( 25 <= $progress_percentage && 50 >= $progress_percentage ) { $class = ' orange'; } else { $class = ' red'; }

            /* if ( 0 == $progress_percentage ) { $progress_percentage = 5; } */

            $active_html .= '<div class="meter' . esc_attr( $class ) . '"><span style="width: ' . $progress_percentage . '%">' . $progress_percentage . '%</span></div>';

            $active_html .= '</section>';

            if( $manage ) {

                $active_html .= '<section class="entry-actions">';

                $active_html .= '<form method="POST" action="' . esc_url_raw(remove_query_arg( array( 'active_page', 'completed_page' ) )) . '">';

                $active_html .= '<input type="hidden" name="' . esc_attr( 'woothemes_sensei_complete_course_noonce' ) . '" id="' . esc_attr( 'woothemes_sensei_complete_course_noonce' ) . '" value="' . esc_attr( wp_create_nonce( 'woothemes_sensei_complete_course_noonce' ) ) . '" />';

                $active_html .= '<input type="hidden" name="course_complete_id" id="course-complete-id" value="' . esc_attr( absint( $course_item->ID ) ) . '" />';

                if ( 0 < absint( count( $course_lessons ) ) && $woothemes_sensei->settings->settings['course_completion'] == 'complete' ) {
                    $active_html .= '<span><input name="course_complete" type="submit" class="course-complete" value="' . apply_filters( 'sensei_mark_as_complete_text', __( 'Mark as Complete', 'woothemes-sensei' ) ) . '"/></span>';
                } // End If Statement

                $course_purchased = false;
                if ( WooThemes_Sensei_Utils::sensei_is_woocommerce_activated() ) {
                    // Get the product ID
                    $wc_post_id = get_post_meta( absint( $course_item->ID ), '_course_woocommerce_product', true );
                    if ( 0 < $wc_post_id ) {
                        $course_purchased = WooThemes_Sensei_Utils::sensei_customer_bought_product( $user->user_email, $user->ID, $wc_post_id );
                    } // End If Statement
                } // End If Statement

                if ( !$course_purchased ) {
                    $active_html .= '<span><input name="course_complete" type="submit" class="course-delete" value="' . apply_filters( 'sensei_delete_course_text', __( 'Delete Course', 'woothemes-sensei' ) ) . '"/></span>';
                } // End If Statement

                $active_html .= '</form>';

                $active_html .= '</section>';
            }

            $active_html .= '</article>';
        }

        // Active pagination
        if( $active_count > $per_page ) {

            $current_page = 1;
            if( isset( $_GET['active_page'] ) && 0 < intval( $_GET['active_page'] ) ) {
                $current_page = $_GET['active_page'];
            }

            $active_html .= '<nav class="pagination woo-pagination">';
            $total_pages = ceil( $active_count / $per_page );

            $link = '';

            if( $current_page > 0 ) {
                $prev_link = esc_url(add_query_arg( 'active_page', $current_page - 1 ));
                $active_html .= '<a class="prev page-numbers" href="' . $prev_link . '">' . __( 'Previous' , 'sensei-buddypress' ) . '</a> ';
            }

            for ( $i = 1; $i <= $total_pages; $i++ ) {
                $link = esc_url(add_query_arg( 'active_page', $i ));

                if( $i == $current_page ) {
                    $active_html .= '<span class="page-numbers current">' . $i . '</span> ';
                } else {
                    $active_html .= '<a class="page-numbers" href="' . $link . '">' . $i . '</a> ';
                }
            }

            if( $current_page < $total_pages ) {
                $next_link = esc_url(add_query_arg( 'active_page', $current_page + 1 ));
                $active_html .= '<a class="next page-numbers" href="' . $next_link . '">' . __( 'Next' , 'sensei-buddypress' ) . '</a> ';
            }

            $active_html .= '</nav>';
        }


    } // End If Statement

    if( $manage ) {
        $no_active_message = apply_filters( 'sensei_no_active_courses_user_text', __( 'You have no active courses.', 'sensei-buddypress' ) );
    } else {
        $no_active_message = apply_filters( 'sensei_no_active_courses_learner_text', __( 'This learner has no active courses.', 'sensei-buddypress' ) );
    }

    $return_data = array(
        'active_html' => $active_html,
        'no_active_message' => $no_active_message
    );
    return $return_data;
}

/**
 * get completed courses html
 * @return array
 */
function bp_sensei_get_completed_courses_html() {
    $user = get_userdata( bp_displayed_user_id() );
    $manage = bp_displayed_user_id() == bp_loggedin_user_id() ? true : false;
    global $woothemes_sensei, $post, $wp_query, $course, $my_courses_page, $my_courses_section;

// Build Output HTML
    $complete_html = $active_html = '';

    if( $user ) {

        $my_courses_page = true;

        // Allow action to be run before My Courses content has loaded
        do_action( 'sensei_before_my_courses', $user->ID );

        // Logic for Active and Completed Courses
        $per_page = 20;
        if ( isset( $woothemes_sensei->settings->settings[ 'my_course_amount' ] ) && ( 0 < absint( $woothemes_sensei->settings->settings[ 'my_course_amount' ] ) ) ) {
            $per_page = absint( $woothemes_sensei->settings->settings[ 'my_course_amount' ] );
        }

        $course_statuses = WooThemes_Sensei_Utils::sensei_check_for_activity( array( 'user_id' => $user->ID, 'type' => 'sensei_course_status' ), true );
		// User may only be on 1 Course
		if ( !is_array($course_statuses) ) {
			$course_statuses = array( $course_statuses );
		}
		
        $completed_ids = $active_ids = array();
        foreach( $course_statuses as $course_status ) {
            if ( WooThemes_Sensei_Utils::user_completed_course( $course_status, $user->ID ) ) {
                $completed_ids[] = $course_status->comment_post_ID;
            } else {
                $active_ids[] = $course_status->comment_post_ID;
            }
        }

        $active_count = $completed_count = 0;

        $active_courses = array();
        if ( 0 < intval( count( $active_ids ) ) ) {
            $my_courses_section = 'active';
            $active_courses = $woothemes_sensei->post_types->course->course_query( $per_page, 'usercourses', $active_ids );
            $active_count = count( $active_ids );
        } // End If Statement

        $completed_courses = array();
        if ( 0 < intval( count( $completed_ids ) ) ) {
            $my_courses_section = 'completed';
            $completed_courses = $woothemes_sensei->post_types->course->course_query( $per_page, 'usercourses', $completed_ids );
            $completed_count = count( $completed_ids );
        } // End If Statement
        $lesson_count = 1;

        $active_page = 1;
        if( isset( $_GET['active_page'] ) && 0 < intval( $_GET['active_page'] ) ) {
            $active_page = $_GET['active_page'];
        }

        $completed_page = 1;
        if( isset( $_GET['completed_page'] ) && 0 < intval( $_GET['completed_page'] ) ) {
            $completed_page = $_GET['completed_page'];
        }

        foreach ( $completed_courses as $course_item ) {
            $course = $course_item;

            // Get Course Categories
            $category_output = get_the_term_list( $course_item->ID, 'course-category', '', ', ', '' );

            $complete_html .= '<article class="' . join( ' ', get_post_class( array( 'course', 'post' ), $course_item->ID ) ) . '">';

            // Image
            $complete_html .= $woothemes_sensei->post_types->course->course_image( absint( $course_item->ID ) );

            // Title
            $complete_html .= '<header>';

            $complete_html .= '<h2><a href="' . esc_url( get_permalink( absint( $course_item->ID ) ) ) . '" title="' . esc_attr( $course_item->post_title ) . '">' . esc_html( $course_item->post_title ) . '</a></h2>';

            $complete_html .= '</header>';

            $complete_html .= '<section class="entry">';

            $complete_html .= '<p class="sensei-course-meta">';

            // Author
            $user_info = get_userdata( absint( $course_item->post_author ) );
            if ( isset( $woothemes_sensei->settings->settings[ 'course_author' ] ) && ( $woothemes_sensei->settings->settings[ 'course_author' ] ) ) {
                $complete_html .= '<span class="course-author">' . __( 'by ', 'sensei-buddypress' ) . '<a href="' . esc_url( get_author_posts_url( absint( $course_item->post_author ) ) ) . '" title="' . esc_attr( $user_info->display_name ) . '">' . esc_html( $user_info->display_name ) . '</a></span>';
            } // End If Statement

            // Lesson count for this author
            $complete_html .= '<span class="course-lesson-count">' . $woothemes_sensei->post_types->course->course_lesson_count( absint( $course_item->ID ) ) . '&nbsp;' . apply_filters( 'sensei_lessons_text', __( 'Lessons', 'sensei-buddypress' ) ) . '</span>';
            // Course Categories
            if ( '' != $category_output ) {
                $complete_html .= '<span class="course-category">' . sprintf( __( 'in %s', 'sensei-buddypress' ), $category_output ) . '</span>';
            } // End If Statement

            $complete_html .= '</p>';

            $complete_html .= '<p class="course-excerpt">' . apply_filters( 'get_the_excerpt', $course_item->post_excerpt ) . '</p>';

            $complete_html .= '<div class="meter green"><span style="width: 100%">100%</span></div>';

            if( $manage ) {
                $has_quizzes = count( $woothemes_sensei->post_types->course->course_quizzes( $course_item->ID ) ) > 0 ? true : false;
                // Output only if there is content to display
                if ( has_filter( 'sensei_results_links' ) || false != $has_quizzes ) {
                    $complete_html .= '<p class="sensei-results-links">';
                    $results_link = '';
                    if( false != $has_quizzes ) {
                        $results_link = '<a class="button view-results" href="' . $woothemes_sensei->course_results->get_permalink( $course_item->ID ) . '">' . apply_filters( 'sensei_view_results_text', __( 'View results', 'sensei-buddypress' ) ) . '</a>';
                    }
                    $complete_html .= apply_filters( 'sensei_results_links', $results_link );
                    $complete_html .= '</p>';
                }
            }

            $complete_html .= '</section>';

            $complete_html .= '</article>';
        }

        // Active pagination
        if( $completed_count > $per_page ) {

            $current_page = 1;
            if( isset( $_GET['completed_page'] ) && 0 < intval( $_GET['completed_page'] ) ) {
                $current_page = $_GET['completed_page'];
            }

            $complete_html .= '<nav class="pagination woo-pagination">';
            $total_pages = ceil( $completed_count / $per_page );

            $link = '';

            if( $current_page > 0 ) {
                $prev_link = esc_url(add_query_arg( 'completed_page', $current_page - 1 ));
                $complete_html .= '<a class="prev page-numbers" href="' . $prev_link . '">' . __( 'Previous' , 'sensei-buddypress' ) . '</a> ';
            }

            for ( $i = 1; $i <= $total_pages; $i++ ) {
                $link = esc_url(add_query_arg( 'completed_page', $i ));

                if( $i == $current_page ) {
                    $complete_html .= '<span class="page-numbers current">' . $i . '</span> ';
                } else {
                    $complete_html .= '<a class="page-numbers" href="' . $link . '">' . $i . '</a> ';
                }
            }

            if( $current_page < $total_pages ) {
                $next_link = esc_url(add_query_arg( 'completed_page', $current_page + 1 ));
                $complete_html .= '<a class="next page-numbers" href="' . $next_link . '">' . __( 'Next' , 'sensei-buddypress' ) . '</a> ';
            }

            $complete_html .= '</nav>';
        }

    } // End If Statement

    if( $manage ) {
        $no_complete_message = apply_filters( 'sensei_no_complete_courses_user_text', __( 'You have not completed any courses yet.', 'sensei-buddypress' ) );
    } else {
        $no_complete_message = apply_filters( 'sensei_no_complete_courses_learner_text', __( 'This learner has not completed any courses yet.', 'sensei-buddypress' ) );
    }

    $return_data = array(
        'complete_html' => $complete_html,
        'no_complete_message' => $no_complete_message
    );
    return $return_data;
}

//Overiding Sensei message archive link
function bp_sensei_redirect_messages_archive_link( $link, $post_type ) {
	
	if ( $post_type == 'sensei_message' && bp_is_active( 'messages' ) ) {
		
		$link = trailingslashit( bp_loggedin_user_domain() . BP_MESSAGES_SLUG );
		return $link;
	} 
	return $link;
}

add_filter('post_type_archive_link','bp_sensei_redirect_messages_archive_link',10,2);

/**
 * Sensei Update group avatar with course avatar
 * @global type $bp
 * @param type $course_id
 * @param type $group_id
 */
function bp_sensei_update_group_avatar( $course_id, $group_id ) {

	require_once(ABSPATH . "wp-admin" . '/includes/image.php');
	require_once(ABSPATH . "wp-admin" . '/includes/file.php');
	require_once(ABSPATH . "wp-admin" . '/includes/media.php');

	$group_avatar = bp_get_group_has_avatar( $group_id );
	if ( ! empty( $group_avatar ) ) {
		return;
	}

	$attached_media_id = get_post_thumbnail_id( $course_id, $group_id );
	
	if ( empty($attached_media_id) ) {
		return;
	}
	
	$attachment_src = wp_get_attachment_image_src( $attached_media_id, 'full' );

	$wp_upload_dir = wp_upload_dir();
	$tfile = uniqid() . '.jpg';
	file_put_contents( $wp_upload_dir[ "basedir" ] . "/" . $tfile, file_get_contents( $attachment_src[0] ) );

	$temp_file = download_url( $wp_upload_dir[ "baseurl" ] . "/" . $tfile, 5 );

	if ( ! is_wp_error( $temp_file ) ) {

		// array based on $_FILE as seen in PHP file uploads
		$file = array(
			'name' => basename( $tfile ), // ex: wp-header-logo.png
			'type' => 'image/png',
			'tmp_name' => $temp_file,
			'error' => 0,
			'size' => filesize( $temp_file ),
		);

		$_FILES[ "file" ] = $file;
	}

	global $bp;
	if ( ! isset( $bp->groups->current_group ) || ! isset( $bp->groups->current_group->id ) ) {
		//required for groups_avatar_upload_dir function
		$bp->groups->current_group = new stdClass();
		$bp->groups->current_group->id = $group_id;
	}

	if ( ! isset( $bp->avatar_admin ) )
		$bp->avatar_admin = new stdClass ();

	$original_action = $_POST[ 'action' ];
	$_POST[ 'action' ] = 'bp_avatar_upload';
	// Pass the file to the avatar upload handler
	if ( bp_core_avatar_handle_upload( $_FILES, 'groups_avatar_upload_dir' ) ) {
		//avatar upload was successful
		//do cropping
		list($width, $height, $type, $attr) = getimagesize( $bp->avatar_admin->image->url );
		$args = array(
			'object' => 'group',
			'avatar_dir' => 'group-avatars',
			'item_id' => $bp->groups->current_group->id,
			'original_file' => bp_get_avatar_to_crop_src(),
			'crop_x' => 0,
			'crop_y' => 0,
			'crop_h' => $height,
			'crop_w' => $width
		);

		bp_core_avatar_handle_crop( $args );
	}
	$_POST[ 'action' ] = $original_action;
}

	/**
	 * Sensei Inserts a new forum and attachs it to the group
	 * @param type $group_id
	 */
	function bp_sensei_attach_forum( $group_id ) {
		if ( class_exists('bbPress') && bp_is_group_forums_active() ) {
			if ( $group->enable_forum == '1' ) {
				$forum_id = bbp_insert_forum( array( 'post_title' => $group->name ) );
				bbp_add_forum_id_to_group( $group_id, $forum_id );
				bbp_add_group_id_to_forum( $forum_id, $group_id );
				bp_sensei_enable_disable_group_forum( '1', $group_id );
			}

		}
	}

	/**
	 * Sensei Group forum enable/disable
	 * @param type $enable
	 * @param type $group_id
	 */
	function bp_sensei_enable_disable_group_forum( $enable, $group_id ) {
		$group = groups_get_group( array( 'group_id' => $group_id ) );
		$group->enable_forum = $enable;
		$group->save();
	}

	/**
	 * Sensei activity filter
	 * @param type $has_activities
	 * @param type $activities
	 * @return type array
	 */
	function bp_sensei_activity_filter( $has_activities, $activities ) {

		if ( bp_current_component() != 'activity' ) {
			return $has_activities;
		}
		$remove_from_stream = false;
		
		foreach ( $activities->activities as $key => $activity ) {

			if ( $activity->component == 'groups' ) {
				$act_visibility = bp_activity_get_meta( $activity->id, 'bp_sensei_group_activity_markup',true );
				if ( !empty( $act_visibility ) ) {
					$remove_from_stream = true;
				}
			}

			if ( $remove_from_stream && isset( $activities->activity_count ) ) {
				$activities->activity_count = $activities->activity_count - 1;
				unset( $activities->activities[ $key ] );
				$remove_from_stream = false;
			}
		}

		$activities_new = array_values( $activities->activities );
		$activities->activities = $activities_new;

		return $has_activities;
	}
	
	add_action( 'bp_has_activities', 'bp_sensei_activity_filter', 100, 2 );

	/**
	 * Add course teacher as group admin
	 * @param type $course_id
	 * @param type $group_id
	 */
	function bp_sensei_course_teacher_group_admin( $course_id, $group_id ) {

		$teacher = get_post_field( 'post_author', $course_id );
		groups_join_group( $group_id, $teacher );
		$member = new BP_Groups_Member( $teacher, $group_id );
		$member->promote( 'admin' );

	}
	
	/**
	 * Sensei add members to groups
	 * @param type $course_id
	 * @param type $group_id
	 */
	function bp_sensei_add_members_group( $course_id, $group_id ) {

		$course_students = BuddyPress_Sensei_Groups::bp_sensei_get_course_members( $course_id );
		if ( empty( $course_students ) ) {
			return;
		}
		if ( is_array( $course_students ) ) {
			foreach ( $course_students as $course_students_id ) {
				groups_join_group( $group_id, $course_students_id->user_id );
			}
		} else {
			groups_join_group( $group_id, $course_students->user_id );
		}
	}

	/**
	 * Sensei removes members from group
	 * @param type $course_id
	 * @param type $group_id
	 */
	function bp_sensei_remove_members_group( $course_id, $group_id ) {

		$course_students = BuddyPress_Sensei_Groups::bp_sensei_get_course_members( $course_id );

		if ( empty( $course_students ) ) {
			return;
		}
		if ( is_array( $course_students ) ) {
			foreach ( $course_students as $course_students_id ) {
				groups_remove_member( $course_students_id->user_id, $group_id );
			}
		} else {
			groups_remove_member( $course_students->user_id, $group_id );
		}
	}
