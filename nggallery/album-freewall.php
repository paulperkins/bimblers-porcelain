<?php 
/**
Custom Mortensen Template Page for the album overview (extended)
You can check the contents when you insert the tag <?php var_dump($variable) ?>
If you would like to show the timestamp of the image ,you can use <?php echo $exif['created_timestamp'] ?>
**/

define( 'MAILUSERS_BIMBLER_FREEWALL_CLASS', 'Bimbler_Freewall' );


?>
<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?><?php if (!empty ($galleries)) : ?>
 
<?php 			
	//echo '<h1>Bimbler Gallery Freewall</h1>';
				
	
	// Do the fancy stuff if the Bimbler Freewall plugin is loaded.
	// Display an error message otherwise.

	if (!class_exists(MAILUSERS_BIMBLER_FREEWALL_CLASS))
	{
		echo '<div class="bimbler-alert-box error"><span>Error: </span>The Bimbler Freewall plugin must be active for this page to work. Please give the admin a slap!</div>';
		return;
	}


	echo '
<script type="text/javascript">
	jQuery(document).ready(function () {
		app.setup({
			share: 1,
			color: 1,
			layout: 1,
			events: 1,
			methods: 1,
			options: 1,
			preload: 1,
			drillhole: 1
		});
	});
</script>
	
<style>
a {
	text-decoration: none;
	color: white;
}

</style>';
	
	?>
<!-- 		<div class="layout">
			<div class="filter-items">
				<div class="filter-label" active>All block</div>
				<div class="filter-label" data-filter=".2014">2014</div>
				<div class="filter-label" data-filter=".2013">2013</div>
				<div class="filter-label" data-filter=".2012">2012</div>
				<div class="filter-label" data-filter=".2011">2011</div>
				<div class="filter-label" data-filter=".2010">2010</div>
				<div class="filter-label" data-filter=".MISC_PICS">Misc.</div>
			</div>
		</div> -->
	<?php 
					
	echo ' <div id="freewall" class="free-wall">';
	
	foreach ($galleries as $gallery){

		$gallery_date = '';
		
		// Get the date from the gallery title - 'YYYY.MM.DD'.
		$pattern = '/(\d{4}).\d{2}.\d{2}/';
		if (preg_match ($pattern, $gallery->title, $matches)) {
			// Get the year component.
			$gallery_date = $matches[1];
				
		}
	
	
		//echo '<div class="cell size11" style="background-image: uri (' . nextgen_esc_url($gallery->previewurl) .');">';
		//echo '<div class="cell size11"  style="background-image: url (\'' . nextgen_esc_url($gallery->previewurl) .'\');" data-fixSize=1>';
		echo '<div class="brick size11" data-fixSize=0>';
		echo '	<div class="cover">';
		echo '		<a class="float-left" href="'. nextgen_esc_url($gallery->pagelink) .'">';
		echo '		<p>'. $gallery->title .'</p>';
		//echo '		<div>Images layout</div>';
		//echo '		<div class="read-more">View gallery ...</div>';
		echo '		</a>';
		echo '	</div>';
		echo '</div>';
	}

	echo '</div>';
	
	echo '
<script type="text/javascript">
	var wall = new freewall("#freewall");
	wall.reset({
		//selector: \'.brick\',
		selector: \'.cell\',
		animate: true,
		cellW: 250,
		cellH: 250,
		onResize: function() {
			wall.fitWidth();
		}
		});
	wall.fixSize({
		width: 250,
		height: 250
		});
		
	wall.fitWidth();
	// for scroll bar appear;
	//$(this).trigger("resize");
</script>';
				
	?>
			 
<?php endif; ?>