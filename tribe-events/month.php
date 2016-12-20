<?php
/**
 * Month View Template
 * The wrapper template for month view. 
 *
 * Override this template in your own theme by creating a file at [your-theme]/tribe-events/month.php
 *
 * @package TribeEventsCalendar
 * @since  3.0
 * @author Modern Tribe Inc.
 *
 */

if ( !defined('ABSPATH') ) { die('-1'); } ?>

<?php do_action( 'tribe_events_before_template' ) ?>

<!-- Tribe Bar -->
<?php tribe_get_template_part( 'modules/bar' ); ?>

<div class="pad group">
<!-- Main Events Content -->
<?php tribe_get_template_part('month/content'); ?>
</div>

<?php
	if (is_user_logged_in()) {
?>	
		<a href="/events/?ical=1"><i class="fa fa-calendar-plus-o"></i> Add to calendar.</a>
<?php	
	} 
?>

<?php do_action( 'tribe_events_after_template' ) ?>
