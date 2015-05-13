<?php 
/*
Template Name: Bimbler Event Page Template
Template Description: A custom template to display the Bimbler sidebar.
*/

 get_header(); 
 
 ?>

<section class="content">
	
	<?php //get_template_part('inc/page-title'); ?>
	
	<div class="xpad group">
		
		<?php while ( have_posts() ): the_post(); ?>
		
			<article <?php post_class('group'); ?>>
				
				<?php get_template_part('inc/page-image'); ?>
				
				<div class="xentry xthemeform">
					<?php the_content(); ?>
					<div class="clear"></div>
				</div><!--/.entry-->
				
			</article>
	
	<div class="pad group">
			
			<?php /*if ( ot_get_option('page-comments') == 'on' ) {*/ comments_template('/comments.php',true); //} ?>
			
		<?php endwhile; ?>
		
	</div><!--/.pad-->
	
</section><!--/.content-->

<?php  get_sidebar('bimblers'); ?>

<?php get_footer(); ?>