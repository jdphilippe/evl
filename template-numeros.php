<?php
/*
Template Name: Index des numéros
*/


get_header();
?>

<div class="content-wrap">
	<div class="content">
		<?php tie_breadcrumbs();

		if ( ! have_posts() ) { ?>
			<div id="post-0" class="post not-found post-listing">
				<h1 class="post-title"><?php _e( 'Not Found', 'tie' ); ?></h1>
				<div class="entry">
					<p><?php _e( 'Apologies, but no results were found for the requested archive. Perhaps searching will help find a related post.', 'tie' ); ?></p>
					<?php get_search_form(); ?>
				</div>
			</div>
		<?php }

		if ( have_posts() )
		    while ( have_posts() ) :
		        the_post();

		$get_meta = get_post_custom($post->ID);

		//Above Post Banner
		if ( empty( $get_meta["tie_hide_above"][0] ) ){
			if ( ! empty( $get_meta["tie_banner_above"][0] ) )
			    echo '<div class="e3lan-post">' .htmlspecialchars_decode($get_meta["tie_banner_above"][0]) .'</div>';
			else
			    tie_banner('banner_above' , '<div class="e3lan-post">' , '</div>' );
		}
		?>

		<article class="post-listing post">
			<?php get_template_part( 'includes/post-head' ); ?>
			<div class="post-inner">
				<h1 class="post-title"><?php the_title(); ?></h1>
				<p class="post-meta"></p>
				<div class="clear"></div>
				<div class="entry">
					<?php
                        the_content();
					    wp_link_pages( array( 'before' => '<div class="page-link">' . __( 'Pages:', 'tie' ), 'after' => '</div>' ) );

					    $list_num = get_terms( "numero" );

                        $list_int_num = array();
						foreach ( $list_num as $this_num ) {
						    $numeroObject = get_term_by('id',$this_num->term_id,'numero');
						    $list_int_num[] = (INT) $numeroObject->slug;
						}

                        array_multisort($list_int_num, $list_num);
						$list_num = array_reverse($list_num);
						$i = 0;
						foreach ($list_num as $this_num) {
							if ( $i % 3 == 0 ) { ?>
								<div class="clear"></div>
                            <?php } ?>

							<div class="col-third">
								<?php
									$numeroid = $this_num->term_id;
									$numero = get_term_by('id',$numeroid,'numero');
									$link   = get_term_link((INT)$numeroid,'numero');
									$name = $numero->name;
								?>
								<center>
								<?php

                                printMediumCorver($numeroid, null);
                                ?>

								</center>
							</div>
					<?php $i++; } ?>



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
