<?php
/*
    Template Name: don - finalisation
*/

$donation = $_POST['montant_don']; // montant du don

$category = get_term_by( 'slug', 'dons', 'product_cat' );

global $post;

$img_id = get_post_thumbnail_id( $post->ID ); // Recupere l'image en Une de la page "Don-Finalisation"

function get_donation_name( $price ) {
    $today = date("Ymd_His");
    $result = "don-" . $today . "-" . get_current_user_id() . "-" . $price;

    return $result;
}

$objProduct = new WC_Product();

$objProduct->set_name( get_donation_name( $donation ) );
$objProduct->set_status("publish");  // publish pour permettre des achats anonymes
$objProduct->set_catalog_visibility('hidden'); // add the product visibility status
$objProduct->set_description("Don pour Evangile et liberté de " . $donation . " €");
$objProduct->set_sku(""); //can be blank in case you don't have sku, but You can't add duplicate sku's
$objProduct->set_price($donation); // set product price
$objProduct->set_regular_price($donation); // set product regular price
$objProduct->set_manage_stock(true); // true or false
$objProduct->set_stock_quantity(1);
$objProduct->set_stock_status('instock'); // in stock or out of stock value
$objProduct->set_backorders('no');
$objProduct->set_reviews_allowed(false);
$objProduct->set_sold_individually(false);
$objProduct->set_category_ids(array($category->term_id)); // array of category ids, You can get category id from WooCommerce Product Category Section of Wordpress Admin
$objProduct->set_image_id($img_id);
$objProduct->set_virtual(true);

$product_id = $objProduct->save(); // it will save the product and return the generated product id

update_post_meta( $product_id, '_membership_product_autocomplete', true );


WC()->cart->add_to_cart( $product_id );

wp_redirect( get_permalink( wc_get_page_id('checkout') ) );
exit();
