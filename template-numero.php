<?php
/**
 * Created by PhpStorm.
 * User: jd
 * Date: 06/03/19
 * Time: 23:11
 *
 * Template Name: Interieur d'un numero
 */

//$parse_uri = explode( 'wp-content', filter_input(INPUT_SERVER, 'SCRIPT_FILENAME') );
//$path = substr($parse_uri[0], 0, strpos($parse_uri[0], "index.php"));

//$path = preg_replace('/wp-content(?!.*wp-content).*/','',__DIR__);
$path = '/home/data/EvangileEtLiberte/code/wordpress';
require_once( $path . '/wp-load.php' );

get_header();

$posts_array = get_posts (
    array (
        'posts_per_page' => -1,
        'post_type' => 'post',
        'tax_query' => array(
            array (
                'taxonomy' => 'numero',
                'field' => 'slug',
                'terms' => 317,
            )
        )
    )
);

foreach ($posts_array as $p)
    echo $p->post_title . "<br>";

?>
Bienvenue dans ce numéro.
Au sommaire:



<?php get_footer(); ?>
