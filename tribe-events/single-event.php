<?php
/**
 * Single Event Template
 * Overriden for the Bimbler blog.
 * 
 * @package TribeEventsCalendar
 * @since  2.1
 * @author Modern Tribe Inc.
 *
 */

if ( !defined('ABSPATH') ) { die('-1'); }

if ( !defined('BIMBLER_AJAX_CLASS') ) {
	define( 'BIMBLER_AJAX_CLASS', 'Bimbler_Ajax' );
}


$event_id = get_the_ID();


function bimbler_logged_in () {

//	return is_user_logged_in ();
	
	$this_user = wp_get_current_user ();
	
	if (empty ($this_user->ID)) {

		//error_log (print_r ($this_user, true));
		
//		error_log ('Rejecting user - not logged-in');
		return false;
	}
	
	return true;
}


function get_event_in_progress ($post_id) {

	return Bimbler_RSVP::get_instance()->is_event_in_progress ($post_id);	

}

function get_ride_page ($post_id) {
	
	$meta_ride_page = get_post_meta ($post_id, '_BimblerRidePage', true);
	
	if (!isset ($meta_ride_page) || empty ($meta_ride_page)) {
		//error_log ('No ride page for event ID ' . $post_id);
	
		// Nothing to do.
		return 0;
	}
	
	return $meta_ride_page;
}

function bimbler_get_avatar_img ($avatar) {

	preg_match( '#src=["|\'](.+)["|\']#Uuis', $avatar, $matches );

	return ( isset( $matches[1] ) && ! empty( $matches[1]) ) ?
	(string) $matches[1] : '';
}


// Determines whether the current user can edit their own event.
function user_is_editor () {

	return Bimbler_RSVP::get_instance()->is_editor ();

}


// Determines whether the current user can edit any event.
function user_is_author () {

	return Bimbler_RSVP::get_instance()->is_author ();

}

/**
 * Adds the photo gallery to the event.
 *
 * @param
 */
function show_gallery () {
	// The current Post (event) ID.
	global $wp_query;
		
	$gallery_id = 0;
	$postid = $wp_query->post->ID;

	// error_log ('Show Gallery.');

	$meta = get_post_meta ($postid, 'bimbler_gallery_id');

	//			print_r ($meta);
		
	if (isset ($meta[0])) {
		$gallery_id = $meta[0];

		//error_log ('Gallery ID: ' . $gallery_id);
	}
		
	// Only show content to logged-in users, and only if we're on an event page.
	if (bimbler_logged_in() && isset ($gallery_id)) {
			
		$html = '<div id="rsvp-gallery">';
		$html .= '<div class="comment-respond">';
		$html .= '	<form action="#tab-event-photos" method="post" id="commentform" class="commentform" enctype="multipart/form-data">';

		if (0 != $gallery_id) {
			//$html .= do_shortcode ('[nggallery id='. $gallery_id .' display_type="photocrati-nextgen_basic_thumbnails"]');
			//$html .= do_shortcode ('[ngg_images gallery_ids="'. $gallery_id .'" display_type="photocrati-nextgen_basic_extended_album"]');

			//$html .= do_shortcode ('[ngg_images gallery_ids="'. $gallery_id .'" display_type="photocrati-nextgen_basic_thumbnails" order_by="pid" order_direction="DESC"]');

//			$html .= do_shortcode ('[justified_image_grid preset=3 row_height=150 lightbox=magnific mobile_lightbox=magnific filterby=on last_row=flexible load_more=once xload_more_text="Show all flowers" load_more_count_text=none load_more_limit=10 ng_gallery='. $gallery_id .']');
			$html .= do_shortcode ('[justified_image_grid row_height=150 lightbox=photoswipe mobile_lightbox=photoswipe filterby=on last_row=flexible load_more=once xload_more_text="Show all flowers" load_more_count_text=none load_more_limit=10 ng_gallery='. $gallery_id .']');


			//$html .= do_shortcode ('[ngg_images gallery_ids="'. $gallery_id .'" display_type="photocrati-nextgen_pro_thumbnail_grid"]');
				
			$html .= '<br><br><br><h4>Upload an Image</h4>';
			$html .= do_shortcode ('[ngg_uploader id='. $gallery_id .']');
		} else {
			
			$html .= '<p>No pictures have been uploaded yet.</p>';
		}

		$html .= '		</form>';
		$html .= '		    </div>';
		$html .= '		</div> <!-- #rsvp-gallery-->';

		echo $html;
	}
}

/**
 * Adds the ride page to the event.
 *
 * @param
 */
function show_ride_page () {
	// The current Post (event) ID.
	global $wp_query;
	$post_id = $wp_query->post->ID;

	//return null;
		
	// Only show content to logged-in users, and only if we're on an event page.
//	if (is_user_logged_in() && is_single()) {
	if (bimbler_logged_in()) {

		$meta_ride_page = get_post_meta ($post_id, '_BimblerRidePage', true);

		if (!isset ($meta_ride_page) || empty ($meta_ride_page)) {
			//error_log ('No ride page for event ID ' . $post_id);

			// Nothing to do.
			return null;
		}
			
		//error_log ('Got page meta ' . $meta_ride_page . ' for event ID ' . $post_id);
			
		$post_object = get_post ($meta_ride_page);

		if (!isset($post_object)) {
			error_log ('Cannot get post object for event ID '. $meta_ride_page);
			return null;
		}

		//var_dump ($post_object->post_content);
		
//		if (current_user_can ('manage_options')) {
		if (user_is_editor ()) {
			echo '<h3>Ride Details <a href="' . site_url () . '/wp-admin/post.php?post=' . $meta_ride_page . '&action=edit" target="_external"><i class="fa fa-pencil"></i></a></h3>';
		} else {
			echo '<h3>Ride Details</h3>';
		}
		
		echo apply_filters( 'the_content', $post_object->post_content);

		echo '<br><br><br>';
	}
}

function bimber_show_map_page () {
	
	global $wp_query;
	$post_id = $wp_query->post->ID;
	
	$content = '';
	
	$rwgps_id = Bimbler_RSVP::get_instance()->get_rwgps_id ($post_id);

	if (0 == $rwgps_id) {
		
		$content .= '<span>This event does not yet have a map.</span>';
		
	} else {
		
		// [iframe src="//ridewithgps.com/routes/782261/embed" height="800px" width="100%" frameborder="0"]
		$iframe = sprintf('[iframe src="//ridewithgps.com/routes/%1$s/embed" height="800px" width="100%" frameborder="0"]', $rwgps_id);
		
		$content .= do_shortcode ($iframe);
	}
	
	echo $content;
}

function show_rsvp_tablex () {
	
	echo '<p>Hello.</p>';
}

/*
 * Determines if the user can execute Ajax, and checks if the Ajax Bimbler plugin is loaded, 
 * sees if the user is a host.
*/
function can_modify_attendance ($event_id = null) {

	return Bimbler_RSVP::get_instance()->can_modify_attendance($event_id);

}

function show_yes_rsvp_badge ($user_id, $event_id, $rsvp_id = null, $user_is_host = false, $guests = null, $comment = null, $attended = null, $has_event_passed = null) {
	$html = '';
	
	$user_info   = get_userdata ($user_id);

	$avatar = '';
		
	if (isset ($user_info->user_login)) {
		$avatar .= get_avatar ($user_id, null, null, $user_info->user_login);
	}

	$html .= '<li class="AvatarListSide bimbler-avatar-narrow">';
		
	// Output an innocuous DIV if the user cannot amend attendance, or if the Ajax module is not loaded.
	if (!can_modify_attendance ($event_id)) {
		$html .= '<div class="rsvp-checkin-container-noajax">';
	}
	else {
		// Store the RSVP ID.
		$html .= '<div class="rsvp-checkin-container" id="'. $rsvp_id .'">';
	}
		
	// Work around the iOS bug...
	$html .= '							<img src="' . bimbler_get_avatar_img($avatar) . '" style="width:64 !important;  height:64 !important;" class="avatar avatar-96 wp-user-avatar wp-user-avatar-96 alignnone photo">' . PHP_EOL;

	// Only show if the event has ended or we're admin / host.
	if (can_modify_attendance ($event_id) || $has_event_passed)
	{
		$html .= '<div class="rsvp-checkin-indicator" id="rsvp-checkin-indicator-'. $rsvp_id .'">'; // Content will be replaced by Ajax.

		if (!isset ($attended)) {
			$html .= '<div class="rsvp-checkin-indicator-none"><i class="fa-question-circle"></i></div>';
		} else if ('Y' == $attended) {
			$html .= '<div class="rsvp-checkin-indicator-yes"><i class="fa-check-circle"></i></div>';
		}
		else {
			$html .= '<div class="rsvp-checkin-indicator-no"><i class="fa-times-circle"></i></div>';
		}

		$html .= '</div>';
	}

	$html .= '</div> <!-- rsvp-checkin-container -->';

	if (isset ($user_info->user_nicename)) {
		$html .= '<p><a href="/profile/' . urlencode ($user_info->user_nicename) .'/">' . $user_info->nickname;

		if ($guests > 0) {
			$html .= ' + ' . $guests;
		}

		$html .= '</a>';

		if ($user_is_host) {
			$html .= '<br>(Host)'; 
		}

		if (!empty($comment)) {
			$html .= '<br><em>' . $comment . '</em>'; 
		}

		$html .= '</p>';
	}
		
	$html .= '</li>';
						
	return $html;
}

function show_no_rsvp_badge ($user_id, $event_id, $comment = null, $attended = null) {

	$html = '';
	
	$user_info   = get_userdata ($user_id);
	
	if (isset ($user_info->user_login)) {
		$avatar = get_avatar ($user_id, null, null, $user_info->user_login);

		$html .= '<li class="AvatarListSide bimbler-avatar-narrow"><div class="permalink"></div>';

		// Work around the iOS bug...
		$html .= '							<img src="' . bimbler_get_avatar_img($avatar) . '" style="width:64 !important;  height:64 !important;" class="avatar avatar-96 wp-user-avatar wp-user-avatar-96 alignnone photo">' . PHP_EOL;
		
		$html .= '<p><a href="/profile/' . urlencode ($user_info->user_nicename) .'/">' . $user_info->nickname;

		$html .= '</a>';
		
		if (!empty($comment)) {
			$html .= '<br><em>' . $comment . '</em>'; 
		}
		
		$html .= '<p></li>';
	}
	
	return $html;	
}

/**
 * Adds the RSVP list to the event.
 *
 * @param
 */

function show_rsvp_table () {
	// The current Post (event) ID.
	global $wp_query;
	$postid = $wp_query->post->ID;

	global $wpdb;
	global $rsvp_db_table;

	$has_event_passed = false;

	// Only show content to logged-in users, and only if we're on an event page.
	//	if (is_user_logged_in() && is_single())
//	if (is_single())
	if (bimbler_logged_in())
	{
		$html = '<div id="rsvp-list" class="widget">';
		$html .= '		    <h3 id="reply-title" class="comment-reply-title">Who\'s Coming</h3>';

		$rsvps_y = Bimbler_RSVP::get_instance()->get_event_rsvp_object ($postid, 'Y');
		$rsvps_n = Bimbler_RSVP::get_instance()->get_event_rsvp_object ($postid, 'N');
		$count_rsvps = Bimbler_RSVP::get_instance()->count_rsvps ($postid);
		$count_atts = Bimbler_RSVP::get_instance()->count_attendees ($postid);

		$host_users = Bimbler_RSVP::get_instance()->get_event_host_users ($postid);
		
		if (null === $count_atts) {
			$count_atts = 0;
		}

		$has_event_passed = Bimbler_RSVP::get_instance()->has_event_passed ($postid);

		$html .= '<div class="bimbler-count-tags" style="overflow-y: hidden;">';
		$html .= '  <div style="float: left;">RSVPs:&nbsp; </div>';
		$html .= '  <div id="yes-count" style="float: left;">' . $count_rsvps .'</div>';
		$html .= '</div>';

		if (can_modify_attendance ($postid)) {
			$html .= '<div class="bimbler-count-tags" style="overflow-y: hidden;">';
				
			if ($has_event_passed) {
				$html .= '  <div style="float: left;">Attended:&nbsp; </div>';
			} else {
				$html .= '  <div style="float: left;">Confirmed:&nbsp; </div>';
			}
				
			$html .= '  <div id="attendee-count" style="float: left;">' . $count_atts .'</div>';
			$html .= '</div>';
		}

		$html .= '<div id="AvatarListSide" class="AvatarListSide-wrap">';

		if ((0 == count ($rsvps_y)) && (0 == count ($rsvps_n)))
		{
			$html .= '<p>No RSVPs yet.</p>';
		}
		else if (!bimbler_logged_in())
		{
			$html .= "<p>You must be logged in to see RSVPs.</p>";
		}
		else
		{
			// Show Yes RSVPs.
			$rsvps = $rsvps_y;
	
			if ($count_rsvps > 0)
			{
				$html .= '		    <ul>';
	
				foreach ( $rsvps as $rsvp) {
					
					$html .= show_yes_rsvp_badge (	$rsvp->user_id, 
													$postid, 
													$rsvp->id, 
													in_array ($rsvp->user_id, $host_users), 
													$rsvp->guests, 
													stripslashes ($rsvp->comment), 
													$rsvp->attended,
													$has_event_passed);
				}
	
				$html .= '		    </ul>';
	
			}
			// Show No RSVPs.
			$rsvps = $rsvps_n;
	
			$count = count($rsvps_n);
				
			if ($count > 0)
			{
				if (1 == $count) {
					$html .= '<p>'. count($rsvps) .' not attending:</p>';
				} else {
					$html .= '<p>'. count($rsvps) .' not attending:</p>';
				}
					
				$html .= '		    <ul>';
					
				foreach ( $rsvps_n as $rsvp) {
	
					$html .= show_no_rsvp_badge (	$rsvp->user_id, 
													$postid,
													stripslashes ($rsvp->comment));
				}
					
				$html .= '		    </ul>';
			}
		}
	
		//$html .= '		</form>';
		$html .= '		    </div> <!-- #rsvp-list-->';
		$html .= '		</div><!-- #footer Wrap-->';
	
		echo $html;
	}
}


/**
 * Displays the RSVP buttons for the current event.
 *
 * @param
 */
function bimbler_show_rsvp_form() {

	global $wp_query;
	$postid = $wp_query->post->ID;

	$rsvps_open = true;
	
	$html = '';
		
	$meta_rsvps_open = get_post_meta ($postid, 'bimbler_rsvps_open', true);

	if ( isset($meta_rsvps_open)) {
		if ('No' == $meta_rsvps_open) {
			$rsvps_open = false;
		}
	}
		
	// Only show content to logged-in users, and only if we're on an event page.
//	if (is_user_logged_in() && is_single() && !Bimbler_RSVP::get_instance()->has_event_passed ($postid)) {
//	if (is_user_logged_in() && is_single()) {
	if (bimbler_logged_in()) {

		global $current_user;
		get_currentuserinfo();

		$user_id = $current_user->ID;

		$html .= '		    <h3 id="reply-title" class="comment-reply-title">Your RSVP</h3>';

		$rsvp = Bimbler_RSVP::get_instance()->get_current_rsvp_object ($postid, $user_id);
		
		// If the event has passed OR
		// meta values say that the event has closed and we've not RSVPd yes
		// then don't allow RSVP changes.
		if (Bimbler_RSVP::get_instance()->has_event_passed ($postid) ||
				(!$rsvps_open && ((null == $rsvp) || ('N' == $rsvp->rsvp)))) {

			$html .= '<div id="rsvp-form">';
			$html .= '<div id="respond" class="comment-respond">';
			$html .= '	<form action="#tab-event-rsvps" method="post" id="commentform" class="commentform" enctype="multipart/form-data">';
			//$html .= '	<h3 id="reply-title" class="comment-reply-title">RSVP</h3>';
			$html .= '<p>RSVPs are no longer open.</p>';
			$html .= '	</form>';
			$html .= '</div> <!--#rsvp-respond-->';
			$html .= '</div> <!-- #rsvp-form -->';
				
		} else {

			if (null == $rsvp) {
				$status = 'You have not RSVPd.';
			}
			else {
				if ('Y' == $rsvp->rsvp) {
					$status = 'You have RSVPd \'yes\'.';
				} else {
					$status = 'You have RSVPd \'no\'.';
				}
			}
				
			$html .= '<div id="rsvp-form">';
			$html .= '<div id="respond" class="comment-respond">';
			$html .= '	<form action="#tab-event-rsvps" method="post" id="commentform" class="commentform" enctype="multipart/form-data">';
			//$html .= '	<h3 id="reply-title" class="comment-reply-title">RSVP</h3>';
			$html .= '<p>'. $status .'</p>';
			//				$html .= '<div class="woo-sc-box tick rounded full>'. $status .'</div>';
			$html .= wp_nonce_field('rsvp', 'rsvp_nonce', true, true);
			//				$html .= '	<p class="comment-form-comment">RSVP Comment:<label for="comment">Comment</label><input type="text" id="comment" name="comment" aria-required="true"></input></p>';
			$html .= '	<p class="form-submit">';

			if (!isset ($rsvp) || ('Y' != $rsvp->rsvp)) {
				$html .= '  <input type="checkbox" name="accept_terms" value="accept">Check here to confirm that you have read, understand and agree to the &#039;Assumption of Risk&#039; statement, that you have examined the proposed route, and that you are satisfied that you can complete the route.<br>';
			}

			$html .= '<div class="col-sm-5">';
			$html .= '<span>Guests:</span>';
			$html .= '		<select class="form-control" id="rsvp_guests" name="rsvp_guests"';
			if (isset ($rsvp) &&  ('Y' == $rsvp->rsvp)) {
				$html .= ' disabled';
			}
			$html .= '>';

			$i = 0;
			for ($i = 0; $i < 5; $i++) {
				$html .= '			<option';

				if (isset ($rsvp) && ($i == $rsvp->guests)) {
					$html .= ' selected';
				}
				
				$html .= '>' . $i . '</option>';
			}
			$html .= '		</select>';

			$html .= '</div>';

			$html .= '<p>&nbsp;</p>';

			$html .= '  <input class="form-control" name="submit" type="submit" id="submit" value="RSVP Yes" ';
			if (isset ($rsvp) && ('Y' == $rsvp->rsvp)) {
				$html .= ' style="background: #cccccc;" disabled ';
			}
			else {
				$html .= ' style="background: #6aab2d;"';
			}
			$html .= '>';


			$html .= '<input type="hidden" name="rsvp_post_id" id="rsvp_post_id" value="'. $postid .'">';


			$html .= '	<input class="form-control" name="submit" type="submit" id="submit" value="RSVP No" ';

			if (isset ($rsvp) &&  ('N' == $rsvp->rsvp)) {
				$html .= ' style="background: #cccccc;"  disabled ';
			}
			else {
				$html .= ' style="background: #f75300;"';
			}
			$html .= '><input type="hidden" name="rsvp_post_id" id="rsvp_post_id" value="'. $postid .'">';

			$html .= '	</p></form>';
			$html .= '</div> <!--#rsvp-respond-->';
			$html .= '</div> <!-- #rsvp-form -->';

//				$html .= '<h3>Gallery</h3>'. wppa_albums(1);

		}

	}/* else {// end if RSVPs open.

		$html .= '<div id="rsvp-form">';
		$html .= '<div id="respond" class="comment-respond">';
		$html .= '	<form method="post" id="commentform" class="commentform" enctype="multipart/form-data">';
		//$html .= '	<h3 id="reply-title" class="comment-reply-title">RSVP</h3>';
		$html .= '<p>RSVPs are no longer open.</p>';
		$html .= '	</form>';
		$html .= '</div> <!--#rsvp-respond-->';
		$html .= '</div> <!-- #rsvp-form -->';
	} */
	
	echo $html;

} // end add_rsvp_form

function show_venue_map () {

	$content = '';

	$map_id = 'bimbler-next-ride-map';
	$map_style = "width: 100%; height: 270px;";

	global $wp_query;
	$post_id = $wp_query->post->ID;

	$content = '';

	//$rwgps_id = $this->get_rwgps_id ($post_id);

	$content .= '<div class="tribe-events-meta-group tribe-events-meta-group-organizer vcard" style="width:100%;">' . PHP_EOL;
	$content .= '	<h3 class="tribe-events-single-section-title">Venue Map</h3>' . PHP_EOL;


	$venue_name = tribe_get_venue($post_id); // Note: this is already URL-encoded.

	$venue_address = Bimbler_RSVP::get_instance()->get_venue_address($post_id);

	if (empty ($venue_address)) {

		$content .= "<p>This event does not yet have a venue.</p>";

	} else {

//				$content .= '<div class="next-ride" id="' .  $map_id . '" data-rwgps-id="' . $rwgps_id . '" style="' . $map_style . '" data-venue-address="' . urlencode($venue_address) . '" data-venue-name="' . $venue_name . '">' . PHP_EOL;
		$content .= '<div class="next-ride" id="' .  $map_id . '"                                   style="' . $map_style . '" data-venue-address="' . urlencode($venue_address) . '" data-venue-name="' . $venue_name . '">' . PHP_EOL;
		$content .= '</div><!--/.next-ride -->' . PHP_EOL;
	}

	$content .= '</div>' . PHP_EOL;

	return $content;
}


function show_summary_page () {
	
?>
			<?php 
//				if (current_user_can( 'manage_options')) {																																				
				if (user_is_editor()) {																																				
					the_title( '<h1 class="post-title">', '<a href="' . site_url () . '/wp-admin/post.php?post=' . get_the_ID() . '&action=edit" target="_external"><i class="fa fa-pencil"></i></a>&nbsp;<a href="' . site_url () . '/wp-admin/admin.php?page=mailusers-send-to-group-page&rsvp_event_id=' . get_the_ID() . '" target="_external"><i class="fa fa-envelope-o"></i></a></h1>' );
				} else {
					the_title( '<h1 class="post-title">', '</h1>' );
				}
			?>
			
	  <div id="tribe-events-content" class="tribe-events-single"> 
			
			
	<!-- 	<div class="tribe-events-schedule updated published tribe-clearfix">
			<?php echo tribe_events_event_schedule_details( get_the_ID(), '<h3>', '</h3>'); ?>
			<?php  if ( tribe_get_cost() ) :  ?>
				<span class="tribe-events-divider">|</span>
				<span class="tribe-events-cost"><?php echo tribe_get_cost( null, true ) ?></span>
			<?php endif; ?>
		</div> -->
	
				<div class="entry">
		
		
					<?php while ( have_posts() ) :  the_post(); ?>
						<div id="post-<?php the_ID(); ?>" <?php post_class('vevent'); ?>>
							<!-- Event featured image -->
							<?php echo tribe_event_featured_image(); ?>
				
							<!-- Event content -->
							<?php do_action( 'tribe_events_single_event_before_the_content' ) ?>
							<div class="tribe-events-single-event-description tribe-events-content entry-content description">
								<?php the_content(); ?>
							</div><!-- .tribe-events-single-event-description -->
							<?php do_action( 'tribe_events_single_event_after_the_content' ) ?>
				
							<!-- Event meta -->
							<?php do_action( 'tribe_events_single_event_before_the_meta' ) ?>
				
				
								<?php tribe_get_template_part( 'modules/meta' ); ?>

								<?php echo show_venue_map (); ?>	
				
							<?php do_action( 'tribe_events_single_event_after_the_meta' ) ?>
							</div><!-- .hentry .vevent -->
						<?php if( get_post_type() == Tribe__Events__Main::POSTTYPE && tribe_get_option( 'showComments', false ) ) comments_template() ?>
					<?php endwhile; ?>
		
				</div>
			
	  </div> <!-- #tribe-events-content -->
		
	<?php
		
}

/**
	* Adds the tracker canvas.
	*
	* @param
	*/
function render_tracker_canvas ($event_id, $rwgps_id) {
	global $bimbler_mobile_time_str;
	global $bimbler_mobile_day_time_str;
	global $bimbler_mobile_date_str;

	global $current_user;
	get_currentuserinfo();
	
	$content = '';
	
	$nonce = wp_create_nonce('bimbler_locator');
	
	// Test.
	//$rwgps_id = 6463068;
	
	$map_style = 'height: 500px; width: 100%; margin-bottom: 15px;';
	
	$content .= '				<div id="bimbler_tracker_map_canvas" style="' . $map_style . '"';
	
	$content .= ' data-event-id="' . $event_id . '"';
	$content .= ' data-user-id="' . $current_user->ID . '"';
	$content .= ' data-nonce="' . $nonce . '"';
	
	if (isset ($rwgps_id)) {
		$content .= ' data-rwgps-id="' . $rwgps_id . '"';
	}
	
	$content .= '></div>' . PHP_EOL;
	
	return $content;
}


/**
	* Adds the locator tab.
	*
	* @param
	*/
function show_tracker ($event_id) {
	global $bimbler_mobile_time_str;
	global $bimbler_mobile_day_time_str;
	global $bimbler_mobile_date_str;


	$post_id = $event_id;

	$content = '';

	//return null;

	// Only show content to logged-in users, and only if we're on an event page.
	if (is_user_logged_in()) {
			
		$nonce = wp_create_nonce('bimbler_tracker');
		
		$rwgps_id = Bimbler_RSVP::get_instance()->get_rwgps_id ($event_id);

		$content .= '';
			
		$this_rsvp = Bimbler_RSVP::get_instance()->get_current_rsvp ($event_id);
		
		// Only show to admin users, or to those who have RSVPd 'Yes' to this event.
		if (!current_user_can( 'manage_options' ) &&
				(!isset ($this_rsvp) || ('Y' != $this_rsvp))) {
					
			$content .= '<div class="bimbler-alert-box notice"><span>Notice: </span>You must RSVP for this event to see this page.</div>';

		} elseif (!current_user_can( 'manage_options' ) && // Don't show if not in-progress.
				(!Bimbler_RSVP::get_instance()->is_event_in_progress($event_id))) {
				
			$content .= '<div class="bimbler-alert-box notice"><span>Notice: </span>The event will not be starting soon, or finished a while ago.</div>';
			
		} else { // All good - render the tracker.

			$content .= '		<div class="bimbler-tracker">' . PHP_EOL;
			
			$content .= render_tracker_canvas ($event_id, $rwgps_id);
			
			$content .= '		</div> <!-- /bimbler-tracker -->' . PHP_EOL;
			
			// Fake clicking the first tab.
			//$content .= '<script type="text/javascript">jQuery (function (){$(document).trigger(\'showMap\');});(jQuery);</script>' . PHP_EOL;
//			$content .= '<script type="text/javascript">$(\'a\').trigger("shown.tracker.tab");</script>' . PHP_EOL;

$xcontent .= '
<script type="text/javascript">
	jQuery(document).ready(function($)
	{
		$(\'a[data-toggle="bimbler-tab"]\').trigger("shown.tracker.tab");
	});
</script>
';


		}

		global $current_user;
		get_currentuserinfo();

		$this_user_id = $current_user->ID;


		$content .= '<div id="bimbler-whos-who" class="widget">';
		$content .= '		    <h3 id="reply-title" class="comment-reply-title">Who\'s Who (only showing ' . $current_user->user_firstname . ' and event hosts)</h3>';


		// The who's who box.
/*		$content .= '	<div class="panel panel-default">' . PHP_EOL;
		$content .= '		<div class="panel-heading">' . PHP_EOL;
		$content .= '			<h4 class="panel-title">Who\'s who</h4>' . PHP_EOL;
		$content .= '		</div>' . PHP_EOL;
		
		$content .= '		<div class="panel-body">' . PHP_EOL;
*/
		
		$rsvps = Bimbler_RSVP::get_instance()->get_event_rsvp_object ($event_id, 'Y');
		$host_users = Bimbler_RSVP::get_instance()->get_event_host_users ($event_id);
			
		$html = '<div id="AvatarListSide" class="AvatarListSide-wrap">';

		if (0 == count ($rsvps))
		{
			$html .= '<p>No RSVPs yet.</p>';
		} else	{
	
			$html .= '		    <ul>';

			foreach ( $rsvps as $rsvp) {

				$user_info   = get_userdata ($rsvp->user_id);

				// Only show current user and event hosts.
				if (in_array ($user_info->id, $host_users) || ($this_user_id == $user_info->id)) {

					$avatar = '';
						
					if (isset ($user_info->user_login)) {
						$avatar .= get_avatar ($rsvp->user_id, null, null, $user_info->user_login);
					}
	
					$html .= '<li class="AvatarListSide bimbler-avatar-narrow bimbler-avatar-border">';
						
					// Output an innocuous DIV if the user cannot amend attendance, or if the Ajax module is not loaded.
					// Store the RSVP ID.
					//$html .= '<div class="rsvp-checkin-container" id="'. $rsvp->id .'">';
						
					$html .= '							<img src="' . bimbler_get_avatar_img($avatar) . '" style="width:64 !important;  height:64 !important;" class="avatar avatar-96 wp-user-avatar wp-user-avatar-96 alignnone photo bimbler-whoswho-marker" ';
					$html .= 'id="user-avatar-' . $rsvp->user_id . '" data-user-id="' . $rsvp->user_id . '">' . PHP_EOL;
	
					//$html .= '</div>';
	
					if (isset ($user_info->user_nicename)) {
						$html .= '<p>' . $user_info->nickname;
	
						if (in_array ($user_info->id, $host_users)) {
							$html .= '<br>(Host)'; 
						}
	
						$html .= '</p>';
					}
						
					$html .= '</li>';
				
				}
			}

			$html .= '		    </ul>';
		}			
		
		$content .= $html;
		
			
/*		$content .= '		</div>' . PHP_EOL;
		$content .= '	</div>' . PHP_EOL; */
		
		$content .= '</div>' . PHP_EOL;

		//$content .= '<span id="bimbler-debug-output"></span>' . PHP_EOL;
			

	} else {
		$content .= '<h2>Please log in.</h2>' . PHP_EOL;
	}

	echo $content;
}	

function bimbler_create_tabs($tabs,$count, $event_id) {
	//global $event_id;
	
	$titles = array(
			'event-summary'		=> 'Summary',
			'event-details'		=> 'Details',
			'event-map'			=> 'Map',
			'event-rsvps'		=> 'RSVPs',
			'event-photos'		=> 'Photos',
			'event-tracker'		=> 'Tracker'
			//'event-comments'	=> 'Comments'
	);
	$icons = array(
			'event-summary'  	=> 'fa fa-list-ul',
			'event-details'  	=> 'fa fa-list-alt', //fa fa-clock-o',
			'event-map'  		=> 'fa fa-map-marker', //fa fa-clock-o',
			//'event-rsvps' 		=> 'fa fa-check-square-o',
			'event-rsvps' 		=> 'fa fa-users',
			'event-photos' 		=> 'fa fa-camera',
			'event-tracker'		=> 'fa fa-compass'
			//'event-comments'	=> 'fa fa-calendar'
	);

	$counts = array(
			'event-summary'  	=> 0,
			'event-details'  	=> 0,
			'event-map'  		=> 0,
			'event-rsvps' 		=> Bimbler_RSVP::get_instance()->count_rsvps ($event_id),
			'event-photos' 		=> Bimbler_RSVP::get_instance()->get_gallery_pic_count ($event_id),
			'event-tracker'		=> 0
	);

	$text_style = 'none';
	
	$output = sprintf('	<ul class="bimbler-tabs-nav group tab-count-%s">', $count) . PHP_EOL;
	foreach ( $tabs as $tab ) {

		if ($counts[$tab] > 0) {
// Just text				$output .= sprintf('		<li class="bimbler-tab bimbler-badge tab-%1$s"><a href="#tab-%2$s" title="%4$s" data-notifications="%5$s"><span style="display: block;">%4$s</span></a></li>',$tab, $tab, $icons[$tab], $titles[$tab], $counts[$tab]) . PHP_EOL;
			$output .= sprintf('		<li class="bimbler-tab bimbler-badge tab-%1$s"><a href="#tab-%2$s" data-toggle="bimbler-tab" title="%4$s" data-notifications="%5$s"><i class="%3$s"></i><span>%4$s</span></a></li>',$tab, $tab, $icons[$tab], $titles[$tab], $counts[$tab]) . PHP_EOL;
		} else {
// Just text				$output .= sprintf('      <li class="bimbler-tab bimbler-badge tab-%1$s"><a href="#tab-%2$s" title="%4$s"><span style="display: block;">%4$s</span></a></li>',$tab, $tab, $icons[$tab], $titles[$tab]) . PHP_EOL;
			$output .= sprintf('		<li class="bimbler-tab bimbler-badge tab-%1$s"><a href="#tab-%2$s" data-toggle="bimbler-tab" title="%4$s"><i class="%3$s"></i><span>%4$s</span></a></li>',$tab, $tab, $icons[$tab], $titles[$tab]) . PHP_EOL;
		}
	}
	$output .= '	</ul>' . PHP_EOL;
	
	return $output;
}

ob_start();

$output = '';

$tabs = array();
$count = 0;
$order = array(
		'event-summary'		=> 1,
		'event-details'		=> 2,
		'event-map'			=> 3,
		'event-rsvps'		=> 4,
		'event-photos'		=> 5,
		'event-tracker'		=> 6
		//'event-comments'	=> 5
);

$tabs_enabled = array(
		'event-summary'		=> 1,
		'event-details'		=> get_ride_page($event_id), // Returns zero if no page.
		'event-map'			=> 1,
		'event-rsvps'		=> 1,
		'event-photos'		=> 1,
		'event-tracker'		=> 1 //get_event_in_progress ($event_id) 
);

asort($order);
foreach ( $order as $key => $value ) {
	//if ( $instance[$key.'_enable'] ) {
	if ($tabs_enabled[$key]) {
		$tabs[] = $key;
		$count++;
	}
	//}
}

if ( $tabs && ($count > 1) )
{
	$output .= bimbler_create_tabs($tabs,$count, $event_id);
}

$scroller_style = '';

?>
	
<?php 
	// Check if the user is logged-in - this page should only be visible if they are.
	if (!bimbler_logged_in())
	{
		echo '<div class="bimbler-alert-box notice"><span>Notice: </span>You must be logged in to view this page.</div>';
	}
	else
	{
?>

	<div class="bimbler-tabs-container" <?php echo $scroller_style; ?>>
	
		<ul id="tab-event-summary" class="bimbler-tab avatars-enabled group">

			<div class="pad group">

			<?php show_summary_page (); ?>

			</div>
	
		</ul> <!-- tab-event-summary -->

		
		<ul id="tab-event-details" class="bimbler-tab avatars-enabled group">
		
		
			<div class="pad group">
			
			<div class="entry">
		
			<?php show_ride_page (); ?>
		
			</div>
			
			</div>	
		</ul> <!-- tab-event-details -->

		<ul id="tab-event-map" class="bimbler-tab avatars-enabled group">
			<div class="pad group">
		
			<?php bimber_show_map_page(); ?>
			
			</div>	
		</ul> <!-- tab-event-rsvps -->

		
		<ul id="tab-event-rsvps" class="bimbler-tab avatars-enabled group">
			<div class="pad group">
			
			<div class="entry themeform">
			
			<?php show_rsvp_table (); ?>
			
			<?php bimbler_show_rsvp_form (); ?>
		
			</div>
			
			</div>	
		</ul> <!-- tab-event-rsvps -->

		
		<ul id="tab-event-photos" class="bimbler-tab avatars-enabled group">
			<div class="pad group">
			
			<div class="entry themeform">
			
			<?php show_gallery(); ?>
		
			</div>
		
			</div>	
		</ul> <!-- tab-event-photos -->

		
		<ul id="tab-event-comments" class="bimbler-tab avatars-enabled group">
			<div class="pad group">
		
			<h4>Comments Here</h4>
			
			<h4>Comments Done</h4>
		
			</div>	
		</ul> <!-- tab-event-comments -->
		
		<ul id="tab-event-tracker" class="bimbler-tab avatars-enabled group">
			<div class="pad group">
<?php 
			show_tracker($event_id); 
?>
			</div>	
		</ul> <!-- tab-event-tracker -->
		

	</div> <!-- bimbler-tabs-container -->
	
<?php 
	} // Check if the user is logged-in - this page should only be visible if they are.
	
	$output .= ob_get_clean();

	echo $output;
	
?>

