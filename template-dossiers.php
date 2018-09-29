<?php
/*
Template Name: Index des dossiers
*/
?>
<?php get_header(); ?>
<div class="content-wrap">
	<div class="content">
		<?php tie_breadcrumbs() ?>

		<?php if ( ! have_posts() ) : ?>
			<div id="post-0" class="post not-found post-listing">
				<h1 class="post-title"><?php _e( 'Not Found', 'tie' ); ?></h1>
				<div class="entry">
					<p><?php _e( 'Apologies, but no results were found for the requested archive. Perhaps searching will help find a related post.', 'tie' ); ?></p>
					<?php get_search_form(); ?>
				</div>
			</div>
		<?php endif; ?>

		<?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>

		<?php $get_meta = get_post_custom($post->ID);  ?>
		<?php //Above Post Banner
		if( empty( $get_meta["tie_hide_above"][0] ) ){
			if( !empty( $get_meta["tie_banner_above"][0] ) ) echo '<div class="e3lan-post">' .htmlspecialchars_decode($get_meta["tie_banner_above"][0]) .'</div>';
			else tie_banner('banner_above' , '<div class="e3lan-post">' , '</div>' );
		}
		?>

		<article class="post-listing post">
			<?php get_template_part( 'includes/post-head' ); ?>
			<div class="post-inner">
				<h1 class="post-title"><?php the_title(); ?></h1>
				<p class="post-meta"></p>
				<div class="clear"></div>
				<div class="entry">
					<?php the_content(); ?>
					<?php wp_link_pages( array( 'before' => '<div class="page-link">' . __( 'Pages:', 'tie' ), 'after' => '</div>' ) ); ?>

					<?php 
						$list_num = get_terms( "dossier" );
						
						//print_r ($list_num);
						
						$arrayDate = array();
						foreach ($list_num as $this_num) {
							$numoptions = get_option('taxonomy_'.$this_num->term_id);
							$date = $numoptions['nb_date_field_id'];
							//$date = get_tax_meta($this_num->term_id,'nb_date_field_id');
							$arrayDate[] = $date;
						}
						
						//print_r($arrayDate);
						
						array_multisort($arrayDate, $list_num);
						
						//print_r($list_num);
						//print_r($arrayDate);
						
						$list_num = array_reverse($list_num);
						
						?>
						<ul>
						<?php
						
						$i = 0;
						foreach ($list_num as $this_num) {
							
							?>
							<li>
								<?php
									$numeroid = $this_num->term_id;
									$numero = get_term($numeroid);
									$link = get_term_link($numero);
									$name = $numero->name;
									$description = category_description($numeroid);
								?>
								<h4 class="sommaires"><a href="<?php echo $link; ?>"><?php echo $name; ?></a>
								
								<?php 
								if ($description != "") 
									echo "<small>".$description."</small>"; 
								?>
								</h4>
							</li>
						<?php $i++; } ?>
						</ul>


					<?php edit_post_link( __( 'Edit', 'tie' ), '<span class="edit-link">', '</span>' ); ?>
				</div><!-- .entry /-->

			</div><!-- .post-inner -->
		</article><!-- .post-listing -->
		<?php endwhile; ?>

		<?php //Below Post Banner
		if( empty( $get_meta["tie_hide_below"][0] ) ){
			if( !empty( $get_meta["tie_banner_below"][0] ) ) echo '<div class="e3lan-post">' .htmlspecialchars_decode($get_meta["tie_banner_below"][0]) .'</div>';
			else tie_banner('banner_below' , '<div class="e3lan-post">' , '</div>' );
		}
		?>

		<?php comments_template( '', true ); ?>
	</div><!-- .content -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>
