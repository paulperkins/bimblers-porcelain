<?php
 /*
Template Name: Bimbler Event Page Template
Template Description: A custom template to display the Bimbler sidebar.
*/

get_header();

if ( have_posts() ) {
	while ( have_posts() ) {
		the_post();

		//get all the page meta data (settings) needed (function located in unctions/meta.php)
		$pexeto_page=pexeto_get_post_meta( $post->ID, array( 'slider', 'layout', 'show_title', 'sidebar' ) );

		// If we're displaying the calendar or the event list, use the full-width page template.
		if (!is_single() && (tribe_is_past() || tribe_is_upcoming() || tribe_is_month())) {
			$pexeto_page['layout'] = 'full';
		}		
		
		//include the before content template
		locate_template( array( 'includes/html-before-content.php' ), true, true );

		?>
		<div class="content-box">
		<?php
		//display the page content
		the_content();
		wp_link_pages();

		//print sharing
		echo pexeto_get_share_btns_html( $post->ID, 'page' );

		?>
		<div class="clear"></div>
		</div>
		<?php

		if ( pexeto_option( 'page_comments' ) ) {
			//include the comments template
			comments_template();
		}

	}
}

// Force the use of the sidebar for events. Will be overriden if template type is 'full'.
$pexeto_page['sidebar'] = 'bimblereventssidebar';

//include the after content template
locate_template( array( 'includes/html-after-content.php' ), true, true );

get_footer();
?>
