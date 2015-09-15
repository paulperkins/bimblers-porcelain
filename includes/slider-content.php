<?php
/**
 * Prints the Content slider in header.
 */

global $slider_data, $post, $pexeto_scripts, $pexeto_content_sizes, $pexeto;

$img_width = pexeto_get_column_width(2, 'content_slider', 'full');
$img_height = pexeto_option( 'content_img_height' );
$slider_height = pexeto_option('content_slider_height');
$slider_div_id='content-slider-'.$post->ID;
//get the slider items(posts)
$slider_items=$slider_data['posts']; 

$first_color = get_post_meta($slider_items[0]->ID, PEXETO_CUSTOM_PREFIX.'bg_color', true);
$color_style= $first_color ? 'style="background-color:#'.$first_color.'";':'';
$color_style='';


$bimbler_gallery_id = 175;//146;

	function bimbler_get_gallery_pics ($gallery_id) {
		global $wpdb;
		global $nggdb;
		
		$pics = $nggdb->get_gallery($gallery_id, 'sortorder', 'ASC', true, 0, 0);
		
		//error_log ('Gallery ' . $gallery_id . ' has ' . count($pics) . ' pics.');
			
		return $pics;
	}

?>

<div class="content-slider-wrapper" <?php echo $color_style; ?>>
<!--<div class="content-slider cols-wrapper cols-2" id="<?php //echo $slider_div_id; ?>" > -->
<div class="content-slider cols-wrapper cols-1" id="<?php echo $slider_div_id; ?>" >
	<div class="section-boxed">
	<ul id="cs-slider-ul" style="min-height:<?php echo $slider_height; ?>px;">
		<?php

		$bimbler_cover_pics = bimbler_get_gallery_pics ($bimbler_gallery_id);
		
		shuffle ($bimbler_cover_pics);
		
		$bimbler_posts = Bimbler_RSVP::get_instance()->get_upcoming_events (5);

		//set the meta key values for each item that will be retrieved
		$data_keys = array('layout', 'animation', 'bg_color', 'text_color', 'bg_image_url', 
			'bg_image_opacity', 'image_url', 'main_title', 'small_title', 'description', 
			'but_one_text', 'but_one_link', 'but_two_text', 'but_two_link', 'video_url', 'bg_align', 'bg_style');

		foreach ( $slider_items as $item ) {

			//get the meta values for the current item
			$item_data = pexeto_get_multi_meta_values($item->ID, $data_keys, PEXETO_CUSTOM_PREFIX);	

			//get the image URL
			$imgurl=$item_data['image_url'];
			
			if ( pexeto_option( 'content_auto_resize' )=='true' && !empty($imgurl)) {
				// $imgurl=pexeto_get_resized_image( $imgurl, $img_width , pexeto_option( 'content_img_height' ) );
				$imgurl = pexeto_get_resized_image( $imgurl, $img_width, $img_height, true );
			}

			$slide_layout = $item_data['layout'];
			if(empty($item_data['animation'])) $item_data['animation'] = 'random';

			//set the data attribute to the slide with the slide settings
			$slide_data='';
			$slide_data_keys = array('bg_image_url', 'bg_image_opacity', 'layout', 'animation', 'bg_color');
			foreach ($slide_data_keys as $key) {

				if ('bg_image_url' == $key) {				
					if (($title == '[bimblers_next_ride]') && class_exists('Bimbler_RSVP')) {
	
						$slide_data.=' data-'.$key.'="'.$bimbler_cover_pics[0]->imageURL.'"';
						
					} else if (($title == '[bimblers_next_ride_1]') && class_exists('Bimbler_RSVP')) {
	
						$slide_data.=' data-'.$key.'="'.$bimbler_cover_pics[1]->imageURL.'"';
	 
					} else if (($title == '[bimblers_next_ride_2]') && class_exists('Bimbler_RSVP')) {
	
						$slide_data.=' data-'.$key.'="'.$bimbler_cover_pics[2]->imageURL.'"';
	
					} else if (($title == '[bimblers_next_ride_3]') && class_exists('Bimbler_RSVP')) {
	
						$slide_data.=' data-'.$key.'="'.$bimbler_cover_pics[3]->imageURL.'"';

					} else if (($title == '[bimblers_next_ride_4]') && class_exists('Bimbler_RSVP')) {
	
						$slide_data.=' data-'.$key.'="'.$bimbler_cover_pics[4]->imageURL.'"';

					} else {
						$slide_data.=' data-'.$key.'="'.$item_data[$key].'"';
					} 
				}	else {
					$slide_data.=' data-'.$key.'="'.$item_data[$key].'"';
				}
			}

			if(!empty($item_data['bg_align'])){
				$slide_data.=' data-bg_align="'.pexeto_get_align_value_by_id($item_data['bg_align']).'"';
			}
			if(!empty($item_data['bg_style']) && $item_data['bg_style']!='default'){
				$slide_data.=' data-bg_style="'.$item_data['bg_style'].'"';
			}


			//apply the slide text color if it is set
			$style = isset($item_data['text_color']) ? 'style="color:#'.$item_data['text_color'].';"' : '';

			
			$content_class = "cs-content-";
			$media_class = "cs-content-";
			$media_type = 'img';

			if($slide_layout=='centered'){
				$content_class.='centered';
				$media_type = "none";
			}elseif($slide_layout=='img-text' || $slide_layout=='video-text'){
				$content_class.='right';
				$media_class.='left';
			}elseif($slide_layout=='text-img' || $slide_layout=='text-video'){
				$content_class.='left';
				$media_class.='right';
			}

			if($slide_layout=='text-video' || $slide_layout=='video-text'){
				$media_type = 'video';
				$media_class.=' cs-type-video';
				if($item_data['video_url']){
					$video_id = pexeto_get_youtube_video_id($item_data['video_url']);
					if($video_id){
						$slide_data.=' data-video="'.$video_id.'"';
					}
				}
			}

			$title = empty($item_data['main_title']) ? '' : $item_data['main_title'];

			// Show the welcome message.
			if ((strstr ($item_data['small_title'], '[bimblers_welcome]')) && class_exists('Bimbler_RSVP')) {
				
				if (is_user_logged_in()) {
					
					global $current_user;
					get_currentuserinfo();

					$item_data['small_title'] = 'Welcome back, ' . $current_user->first_name;
				} else {

					$item_data['small_title'] = '';
					
				}
				
			}

			// Fetch the next ride details.
			if ((strstr ($title, '[bimblers_next_ride')) && class_exists('Bimbler_RSVP')) {
				
				if ($title == '[bimblers_next_ride]') {

					$event = $bimbler_posts[0];
				
				} else if ($title == '[bimblers_next_ride_1]') {

					$event = $bimbler_posts[1];
 
				} else if ($title == '[bimblers_next_ride_2]') {

					$event = $bimbler_posts[2];

				} else if ($title == '[bimblers_next_ride_3]') {

					$event = $bimbler_posts[3];

				} else if ($title == '[bimblers_next_ride_4]') {

					$event = $bimbler_posts[4];
				}
				
				$date_str = 'D j M';
	
				//error_log ('Fetching next ride details');
				
				//$posts = Bimbler_RSVP::get_instance()->get_next_event();
			
				$ride_title = $event->post_title; 
				$ride_url 	= get_permalink ($event->ID);
				$ride_rwgps = Bimbler_RSVP::get_instance()->get_rwgps_id ($event->ID);
				$ride_start_date = tribe_get_start_date($event->ID, false, $date_str);
				$ride_excerpt = $event->post_excerpt; // Only use the excerpt - post_content is generally too long.
				
				//Don't show the route if the user is not logged in.
				if (!is_user_logged_in()) {
					$ride_rwgps = 0;
				}
				
				$item_data['main_title'] = substr($ride_title, 0, 47);
				$item_data['description'] = $ride_excerpt;
				$item_data['small_title'] = 'Coming up: ' . $ride_start_date;
				$item_data['but_one_link'] = $ride_url;
			}
			
			if (empty ($item_data['small_title'])) {
				$item_data['small_title'] = '&nbsp;';
			}
			?>

			 <li<?php echo $slide_data; ?> class="cs-layout-<?php echo $slide_layout; ?>" <?php echo $style; ?>>

				<!-- first slider box -->
				<div class="<?php echo $content_class; ?> col cs-content">
					<?php if ( !empty( $item_data['small_title'] ) ){ 
							//display the small title
						 	?><p class="cs-small-title cs-element"><?php echo $item_data['small_title']; ?></p>

					<?php } if ( !empty( $title ) ) { 
							//display the main title
							?> <h1 class="cs-title cs-element"><?php echo $item_data['main_title']; ?></h1>

					<?php } if ( !empty( $item_data['description'] ) ) {
							//display the description text
							?><p class="cs-element"><?php echo $item_data['description']; ?></p>
							<p class="clear"></p>
					
					<?php } if ( !empty( $item_data['but_one_text'] ) && !empty( $item_data['but_one_link'] ) ) {
							//display the first button
							?><a href="<?php echo esc_url( $item_data['but_one_link'] ); ?>" class="button cs-element"><?php echo $item_data['but_one_text']; ?></a>

					<?php } if ( !empty( $item_data['but_two_text'] ) && !empty( $item_data['but_two_link'] ) ) {  
							//display the second button
							?><a href="<?php echo esc_url( $item_data['but_two_link'] ); ?>" class="button btn-alt cs-element"><?php echo $item_data['but_two_text']; ?></a>
					<?php } ?>
				</div>
				<!-- second slider box -->
				
				<?php if($slide_layout!=='centered'){ ?>
				<div class="<?php echo $media_class; ?> col nomargin">
					<?php 
					if($media_type=='img'){
					if(!empty($imgurl)){ ?>
						<img src="<?php echo $imgurl; ?>" class="cs-element" alt="<?php echo esc_attr( $title ); ?>"/>
					<?php } 
				} else if($media_type=='video'){
					?>

					<div class="cs-video video-wrap"></div><?php
				} ?>
				</div>
				<?php } ?>
			</li><?php

			
		}

		//set the slider initialization arguments
		$args = array();
		$args['autoplay'] = pexeto_option('content_autoplay');
		$args['pauseOnHover'] = pexeto_option('content_pause_hover');
		$args['animationSpeed'] = intval(pexeto_option('content_speed'));
		$args['animationInterval'] = intval(pexeto_option('content_interval'));

		$exclude_navigation = pexeto_option('exclude_content_navigation');
		$args['buttons'] = in_array('buttons', $exclude_navigation) ? false : true;
		$args['arrows'] = in_array('arrows', $exclude_navigation) ? false : true;

		//add the slider to the scripts to print
		$pexeto_scripts['contentslider']=array( 'selector'=>'#'.$slider_div_id, 'options'=>$args );

		?>
	</ul>
</div>
</div>
</div>