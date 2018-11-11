<?php
/**
 * Created by PhpStorm.
 * User: jd
 * Date: 22/10/18
 * Time: 21:14
 *
 * Si un lecteur arrive sur un article payant et qu'il n'est pas connecté ou abonné, un bouton "Acheter ce numéro" s'affiche.
 * Un clic sur ce bouton apelle cette page avec le lien de l'article en parametre.
 * La valeur du numéro est calculée à partir de la date, puis on redirige le tout vers la boutique, avec le bon numéro à la clé.
 */

//ini_set('display_errors', 1);
//error_reporting(E_ALL);

//$parse_uri = explode( 'wp-content', filter_input(INPUT_SERVER, 'SCRIPT_FILENAME') );
//require_once( $parse_uri[0] . 'wp-load.php' );
require_once( 'wp-load.php' );

$referer = $_GET['url'];
$url = wp_make_link_relative( $referer );

$matches = "";
if (preg_match_all('/\d{4}\/\d{2}\//',$referer,$matches)) {
    $date_numero = $matches[0][0];
    $date_numero .= "01"; // Ajoute le jour, l'URL est au format YYYY/MM

    $split = date_parse_from_format('Y/m', $date_numero);
    $year  = $split['year'];
    $month = $split['month'];
    $date_first_magazine_of_2016 = "2016/01/01"; // Date du numéro de référence. Doit être en janvier d'au moins plus de 12 mois par rapport à aujourd'hui
    $first_magazine_of_2016 = 295; // Numéro associé du journal pour la date de janvier 2016

    $dt_first_magazine_of_2016 = new DateTime($date_first_magazine_of_2016);
    $dt_wanted_magazine = new DateTime($date_numero);
    $interval = $dt_first_magazine_of_2016->diff($dt_wanted_magazine);
    $interval_month = $interval->format('%m');
    $interval_year  = $interval->format('%y');
    $nb_month = 10 * $interval_year + (int)$interval_month;
    $internal_number_of_magazine = strval($first_magazine_of_2016 + $nb_month);

    if ($month >= 8) // Le numéro de aout sort en septembre, celui de juillet sort en juin. En juin: n , en septembre: n + 1
        $internal_number_of_magazine -= 2;

    $new_url = get_site_url() . "/product/e-l-" . $internal_number_of_magazine;
    wp_redirect($new_url);
    exit();
} else {
    die("Decodage de l'URL: la date ne figure pas dedans ou n'est pas au bon format (YYYY/MM) ...");
}
