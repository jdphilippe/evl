<?php
/*
Template Name: Authors List
*/

get_header();
tie_breadcrumbs();

//get_template_part( 'includes/post-head' );
?>

<h2>Liste des contributeurs au journal :</h2>
<br>
<table>
    <thead></thead>
    <tbody>
        <?php
        /*
            $all_users = array();
            $roles = unserialize($get_meta["tie_authors"][0]);
            if( !is_array($roles) ) {
                global $wp_roles;
                $roles = $wp_roles->get_names();
            }

            foreach ($roles as $role){
                $all_users = get_users('role=' . $role);
                if ( $users )
                    $all_users = array_merge($all_users, $users);
            }
        */

            $users = get_users();
            $all_users = [];

            foreach ($users as $user) {
                $firstName = get_user_meta($user->ID, 'first_name', true);
                $lastName  = get_user_meta($user->ID, 'last_name', true);
                if ($firstName == "" || $lastName == "")
                    continue;

                $all_users[ $lastName ] = $user;
            }

            ksort($all_users);

            foreach ($all_users as $user) {
                if ( intval( count_user_posts($user->ID, 'post', true) ) == 0 )
                    continue;

                $firstName = get_user_meta($user->ID, 'first_name', true);
                $lastName  = get_user_meta($user->ID, 'last_name', true);
                ?>

                <tr>
                    <td class="author-avatar" width="80">
                        <?php echo get_avatar( get_the_author_meta( 'user_email' , $user->ID ), apply_filters( 'MFW_author_bio_avatar_size', 60 ) ) ; ?>
                    </td><!-- #author-avatar -->
                    <td class="author-description">
                        <h3><a href="<?php echo get_author_posts_url( $user->ID ); ?>"><?php echo $lastName . " " . $firstName ?> </a></h3>
                        <?php the_author_meta( 'description'  , $user->ID ); ?>
                    </td><!-- #author-description -->
                    <td width="40">
                        <?php if ( get_the_author_meta( 'url' , $user->ID) ) : ?>
                        <a class="tooltip" target="_blank" href="<?php the_author_meta( 'url' , $user->ID); ?>" title="<?php echo $user->display_name ?> <?php _e( " 's site", 'tie' ); ?>"><img src="<?php echo get_template_directory_uri(); ?>/images/author_site.png"  width="18" height="18" alt="" /></a>
                        <?php endif ?>
                        <?php if ( get_the_author_meta( 'twitter' , $user->ID) ) : ?>
                        <a class="tooltip" target="_blank" href="https://twitter.com/<?php the_author_meta( 'twitter' , $user->ID ); ?>" title="<?php echo $user->display_name ?><?php _e( '  on Twitter', 'tie' ); ?>"><img src="<?php echo get_template_directory_uri(); ?>/images/author_twitter.png" width="18" height="18" alt="" /></a>
                        <?php endif ?>
                        <?php if ( get_the_author_meta( 'facebook' , $user->ID) ) : ?>
                        <a class="tooltip" target="_blank" href="<?php the_author_meta( 'facebook' , $user->ID ); ?>" title="<?php echo $user->display_name ?><?php _e( '  on Facebook', 'tie' ); ?>"><img src="<?php echo get_template_directory_uri(); ?>/images/author_facebook.png" width="18" height="18" alt="" /></a>
                        <?php endif ?>
                        <?php if ( get_the_author_meta( 'google' , $user->ID) ) : ?>
                        <a class="tooltip" target="_blank" href="<?php the_author_meta( 'google' , $user->ID ); ?>" title="<?php echo $user->display_name ?><?php _e( '  on Google+', 'tie' ); ?>"><img src="<?php echo get_template_directory_uri(); ?>/images/author_google.png" width="18" height="18" alt="" /></a>
                        <?php endif ?>
                        <?php if ( get_the_author_meta( 'linkedin' , $user->ID) ) : ?>
                        <a class="tooltip" target="_blank" href="<?php the_author_meta( 'linkedin' , $user->ID); ?>" title="<?php echo $user->display_name ?><?php _e( '  on Linkedin', 'tie' ); ?>"><img src="<?php echo get_template_directory_uri(); ?>/images/author_linkedin.png" width="18" height="18" alt="" /></a>
                        <?php endif ?>
                        <?php if ( get_the_author_meta( 'flickr' , $user->ID) ) : ?>
                        <a class="tooltip" target="_blank" href="<?php the_author_meta( 'flickr' , $user->ID); ?>" title="<?php echo $user->display_name ?><?php _e( '  on Flickr', 'tie' ); ?>"><img src="<?php echo get_template_directory_uri(); ?>/images/author_flickr.png" width="18" height="18" alt="" /></a>
                        <?php endif ?>
                        <?php if ( get_the_author_meta( 'youtube' , $user->ID) ) : ?>
                        <a class="tooltip" target="_blank" href="<?php the_author_meta( 'youtube' , $user->ID); ?>" title="<?php echo $user->display_name ?><?php _e( '  on YouTube', 'tie' ); ?>"><img src="<?php echo get_template_directory_uri(); ?>/images/author_youtube.png" width="18" height="18" alt="" /></a>
                        <?php endif ?>
                        <?php if ( get_the_author_meta( 'pinterest' , $user->ID) ) : ?>
                        <a class="tooltip" target="_blank" href="<?php the_author_meta( 'pinterest' , $user->ID); ?>" title="<?php echo $user->display_name ?><?php _e( '  on Pinterest', 'tie' ); ?>"><img src="<?php echo get_template_directory_uri(); ?>/images/author_pinterest.png" width="18" height="18" alt="" /></a>
                        <?php endif ?>
                        <?php if ( get_the_author_meta( 'behance' , $user->ID) ) : ?>
                        <a class="tooltip" target="_blank" href="<?php the_author_meta( 'behance', $user->ID ); ?>" title="<?php echo $user->display_name ?><?php _e( '  on Behance', 'tie' ); ?>"><img src="<?php echo get_template_directory_uri(); ?>/images/author_behance.png" width="18" height="18" alt="" /></a>
                        <?php endif ?>
                        <?php if ( get_the_author_meta( 'instagram' , $user->ID ) ) : ?>
                        <a class="tooltip" target="_blank" href="<?php the_author_meta( 'instagram', $user->ID ); ?>" title="<?php echo $user->display_name ?><?php _e( '  on Instagram', 'tie' ); ?>"><img src="<?php echo get_template_directory_uri(); ?>/images/author_instagram.png" width="18" height="18" alt="" /></a>
                        <?php endif ?>
                    </td>
                </tr>
            <?php } ?>
    </tbody>
</table>

<?php get_footer(); ?>
