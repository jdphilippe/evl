<?php

/*add_action( 'send_headers', 'add_header_xua' );
function add_header_xua() {
header( 'Strict-Transport-Security: max-age=0;' );
}*/

/**
 * Include posts from authors in the search results where
 * either their display name or user login matches the query string
 *
 * @author danielbachhuber
 */
add_filter( 'posts_search', 'db_filter_authors_search' );
function db_filter_authors_search( $posts_search ) {

	// Don't modify the query at all if we're not on the search template
	// or if the LIKE is empty
	if ( !is_search() || empty( $posts_search ) )
		return $posts_search;

	global $wpdb;
	// Get all of the users of the blog and see if the search query matches either
	// the display name or the user login
	add_filter( 'pre_user_query', 'db_filter_user_query' );
	$search = sanitize_text_field( get_query_var( 's' ) );
	$args = array(
		'count_total' => false,
		'search' => sprintf( '*%s*', $search ),
		'search_fields' => array(
			'display_name',
			'user_login',
		),
		'fields' => 'ID',
	);
	$matching_users = get_users( $args );
	remove_filter( 'pre_user_query', 'db_filter_user_query' );
	// Don't modify the query if there aren't any matching users
	if ( empty( $matching_users ) )
		return $posts_search;
	// Take a slightly different approach than core where we want all of the posts from these authors
	$posts_search = str_replace( ')))', ")) OR ( {$wpdb->posts}.post_author IN (" . implode( ',', array_map( 'absint', $matching_users ) ) . ")))", $posts_search )." AND {$wpdb->posts}.post_type IN ('post','page')";
	return $posts_search;
}
/**
 * Modify get_users() to search display_name instead of user_nicename
 */
function db_filter_user_query( &$user_query ) {

	if ( is_object( $user_query ) )
		$user_query->query_where = str_replace( "user_nicename LIKE", "display_name LIKE", $user_query->query_where );
	return $user_query;
}

/**
 * Get custom image field for taxonomy
 */
function tip_plugin_get_terms($term_id) {
 	$associations = taxonomy_image_plugin_get_associations();
	$tt_id = absint( $term_id );
	$img_id = false;
	if ( array_key_exists( $tt_id, $associations ) ) {
		$img_id = absint( $associations[$tt_id] );
	}
	return $img_id;
 }


/**
 * Numero
 */
function numero_init() {

	$labels = array(
		'name'              => _x( 'Numéros', 'taxonomy general name' ),
		'singular_name'     => _x( 'Numéro', 'taxonomy singular name' ),
		'search_items'      => __( 'Rechercher les numéros' ),
		'all_items'         => __( 'Tous les numéros' ),
		'parent_item'       => __( 'Numéro parent' ),
		'parent_item_colon' => __( 'Numéro parent :' ),
		'edit_item'         => __( 'Modifier le numéro' ),
		'update_item'       => __( 'Mettre à jour le numéro' ),
		'add_new_item'      => __( 'Ajouter un nouveau numéro' ),
		'new_item_name'     => __( 'Nom du nouveau numéro' ),
		'menu_name'         => __( 'Numéros' ),
	);

	// create a new taxonomy
	register_taxonomy(
		'numero',
		'post',
		array(
			'label' => __( 'Numéro' ),
			'hierarchical' => true,
			'labels' => $labels,
			'rewrite' => array( 'slug' => 'numero' )
		)
	);
}
add_action( 'init', 'numero_init' );

class NumeroWidget extends WP_Widget {
    function NumeroWidget()
    {
        parent::WP_Widget(false, $name = 'MW - Widget Numéros', array("description" => 'Affiche la couverture du dernier numéro'));
    }
    function widget($args, $instance)
    {
		 // Extraction des paramètres du widget
		extract( $args );
		// Récupération de chaque paramètre
		$title = apply_filters('widget_title', $instance['title']);
		// Voir le détail sur ces variables plus bas
		echo $before_widget;
		// On affiche un titre si le paramètre est rempli
		if($title)
			echo $before_title . $title . $after_title;
		/* Début de notre script */

		?>

		<?php

		$args = array(
			'orderby'            => 'name',
			'order'              => 'DESC',
			'taxonomy'           => 'numero',
			'hide_empty'		   => 0
		);

		$categories = get_categories( $args );

		$current = '';

		for ($i=0; $current = $categories[$i];$i++){
			if(get_tax_meta($current->term_id,'nb_date_field_id') <= date("Y-m-d"))
				break;
		}
		$numeroid = $current->term_id;

		//print_r ($current);
		?>


		<?php

       $numero = get_term_by('id',$numeroid,'numero');

       $link = get_term_link((INT)$numeroid,'numero');

		?>
		<div class="mywidget">

        <h3><a href="<?php echo esc_url($link); ?>"><strong><?php echo $numero->name; ?></strong></a></h3>
        <p><a href="<?php echo esc_url($link); ?>"><?php echo $numero->description; ?></a></p>

        <?php
        $term_id = $numero->term_id;
		if( ( $category_image = category_image_src( array('term_id' => $term_id, 'size' =>  'full' )  , false ) ) != null ){
			echo '<center><a href="'.esc_url($link).'"><img src="'.$category_image.'" alt="'.$numero->name.'" class="img-responsive wp-post-image numcover"></a></center>';
		} else {
			echo '<center><a href="'.esc_url($link).'"><img src="/wordpress/wp-content/uploads/2015/03/blank.jpg" alt="'.$numero->name.'" class="img-responsive wp-post-image numcover"></a></center>';
		}

		//$img_id = tip_plugin_get_terms($current->term_taxonomy_id);

       /// echo "<center><a href='".esc_url($link)."'>".wp_get_attachment_image( $img_id, 'medium' )."</a></center>";

		if(pmpro_hasMembershipLevel()){
			$fid = get_tax_meta($numeroid,'nb_file_field_id');
			print ("<center><a href='/wordpress/download.php?fid=".$fid[0]."&id=".$numeroid."'>T&eacute;l&eacute;charger le PDF</a></center>");
		} else {
			print ("<center>Vous devez avoir un abonnement actif pour pouvoir télécharger le numéro en PDF</center>");
		}
        ?>

		</div>
		<?php echo $after_widget;
    }
    function update($new_instance, $old_instance)
    {
        $instance = $old_instance;
		/* Récupération des paramètres envoyés */
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['numeroid'] = $new_instance['numeroid'];
		return $instance;
    }
    function form($instance)
    {
        $title = esc_attr($instance['title']);
		?>
			<p>
				<label for="<?php echo $this->get_field_id('title'); ?>">
					<?php _e('Title:'); ?>
					<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
				</label>
			</p><?php

    }
}
add_action('widgets_init', create_function('', 'return register_widget("NumeroWidget");'));




class CeNumeroWidget extends WP_Widget {
    function CeNumeroWidget()
    {
        parent::WP_Widget(false, $name = 'MW - Widget Ce Numéro', array("description" => 'Affiche la couverture du numéro en cours'));
    }
    function widget($args, $instance)
    {
		 // Extraction des paramètres du widget
		extract( $args );
		// Récupération de chaque paramètre
		$title = apply_filters('widget_title', $instance['title']);



		$postid = get_queried_object()->ID;
		$numeros = get_the_terms($postid,"numero");
		if(!empty($numeros)){
			//print_r($numeros);
			$numero = array_pop($numeros);

			$link = get_term_link((INT)$numero->term_id,'numero');


			// Voir le détail sur ces variables plus bas
			echo $before_widget;
			// On affiche un titre si le paramètre est rempli
			if($title)
				echo $before_title . $title . $after_title;
			/* Début de notre script */

			?>

			<?php

			//print $numeroid;

			$term_id = $numero->term_id;

		   //print_r($numero);

			$fid = get_tax_meta($term_id,'nb_file_field_id');

			?>
			<div class="mywidget">

			<h3><strong><?php echo $numero->name; ?></strong></h3>
			<p><?php echo $numero->description; ?></p>

			<?php
			/*$queried_object = get_queried_object();
			//print($queried_object->ID);
			if(is_single()){
				$myterm = wp_get_post_terms($queried_object->ID,'numero');
				//print_r($myterm[0]->term_taxonomy_id);
				$img_id = tip_plugin_get_terms($myterm[0]->term_taxonomy_id);
			} else {
				//print($queried_object->term_taxonomy_id);
				$img_id = tip_plugin_get_terms($queried_object->term_taxonomy_id);
			}
			//print_r ($img_id);*/

			if( ( $category_image = category_image_src( array('term_id' => $term_id, 'size' =>  'full' )  , false ) ) != null ){
				echo '<center><a href="'.esc_url($link).'"><img src="'.$category_image.'" alt="'.$numero->name.'" class="img-responsive wp-post-image numcover"></a></center>';
			} else {
				echo '<center><a href="'.esc_url($link).'"><img src="/wordpress/wp-content/uploads/2015/03/blank.jpg" alt="'.$numero->name.'" class="img-responsive wp-post-image numcover"></a></center>';
			}


			if((pmpro_hasMembershipLevel() )) { // || (get_tax_meta($term_id,'nb_date_field_id') < date('Y-m-d',strtotime(date("Y-m-d", mktime()) . " - 365 day")))) && intval($fid[0])>0){
				echo "<center><a href='/wordpress/download.php?fid=".$fid[0]."&id=".$term_id."'>".wp_get_attachment_image( $img_id, 'medium' )."</a></center>";
				print ("<center><a href='/wordpress/download.php?fid=".$fid[0]."&id=".$term_id."'>T&eacute;l&eacute;charger le PDF</a></center>");
			} else {
				echo "<center>".wp_get_attachment_image( $img_id, 'medium' )."</center>";
				print ("<center>Vous devez avoir un abonnement actif pour pouvoir télécharger le numéro en PDF</center>");
			}
			?>

			</div>
			<?php echo $after_widget;
		}
    }
    function update($new_instance, $old_instance)
    {
        $instance = $old_instance;
		/* Récupération des paramètres envoyés */
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['numeroid'] = $new_instance['numeroid'];
		return $instance;
    }
    function form($instance)
    {
        $title = esc_attr($instance['title']);
		?>
			<p>
				<label for="<?php echo $this->get_field_id('title'); ?>">
					<?php _e('Title:'); ?>
					<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
				</label>
			</p><?php
    }
}
add_action('widgets_init', create_function('', 'return register_widget("CeNumeroWidget");'));




/**
 * Dossiers
 */
function dossier_init() {
	// create a new taxonomy

	$labels = array(
		'name'              => _x( 'Dossiers', 'taxonomy general name' ),
		'singular_name'     => _x( 'Dossier', 'taxonomy singular name' ),
		'search_items'      => __( 'Rechercher les dossiers' ),
		'all_items'         => __( 'Tous les dossiers' ),
		'parent_item'       => __( 'Dossier parent' ),
		'parent_item_colon' => __( 'Dossier parent :' ),
		'edit_item'         => __( 'Modifier le dossier' ),
		'update_item'       => __( 'Mettre à jour le dossier' ),
		'add_new_item'      => __( 'Ajouter un nouveau dossier' ),
		'new_item_name'     => __( 'Nom du nouveau dossier' ),
		'menu_name'         => __( 'Dossiers' ),
	);

	register_taxonomy(
		'dossier',
		'post',
		array(
			'label' => __( 'Dossier' ),
			'hierarchical' => true,
			'labels' => $labels,
			'rewrite' => array( 'slug' => 'dossier' )
		)
	);
}
add_action( 'init', 'dossier_init' );



/**
 * Champ numéro d'abonné
 */
add_action( 'show_user_profile', 'my_show_extra_profile_fields' );
add_action( 'edit_user_profile', 'my_show_extra_profile_fields' );

function my_show_extra_profile_fields( $user ) { ?>

	<h3>Abonnement</h3>

	<table class="form-table">

		<tr>
			<th><label for="numero">Num&eacute;ro d'abonn&eacute;</label></th>

			<td>
				<input type="text" name="numero" id="numero" value="<?php echo esc_attr( get_the_author_meta( 'numero', $user->ID ) ); ?>" class="regular-text" /><br />
				<span class="description">Veuillez entrer votre num&eacute;ro d'abonn&eacute;.</span>
			</td>
		</tr>

	</table>
<?php }

add_action( 'personal_options_update', 'my_save_extra_profile_fields' );
add_action( 'edit_user_profile_update', 'my_save_extra_profile_fields' );

function my_save_extra_profile_fields( $user_id ) {

	if ( !current_user_can( 'edit_user', $user_id ) )
		return false;

	/* Copy and paste this line for additional fields. Make sure to change 'twitter' to the field ID. */
	update_usermeta( $user_id, 'numero', $_POST['numero'] );
}

add_action( 'admin_menu', 'wp_abo_menu_register' );

function wp_abo_menu_register()
{
    add_menu_page(
        'Import abonnés',     // page title
        'Import abonnés',     // menu title
        'manage_options',   // capability
        'import-abonnes',     // menu slug
        'wp_abo_menu_render', // callback function
		 '',
		 3
    );
}
function wp_abo_menu_render()
{
    global $title;
	global $wpdb;

    print '<div class="wrap">';
    print "<h1>$title</h1>";
	// Si fichier csv envoyé
	$upload_dir = wp_upload_dir();
    if (isset($_POST['upload']))
	{
		$tmp_file = $_FILES['fichier']['tmp_name'];

		if (!is_uploaded_file($tmp_file))
		{
			exit("Le fichier est introuvable");
		}

		$upload_dir = wp_upload_dir();
		$name_file = $upload_dir['path']."/info.txt";


		if(!move_uploaded_file($tmp_file, $name_file))
		{
			exit("Impossible de copier le fichier"); // dans ".$content_dir);
		}
		chmod ($upload_dir['path']."/info.txt", 0777);

		$msg = "Le fichier a bien &eacute;t&eacute; upload&eacute;<br /><br />";
	}

	?>

	<form method="post" enctype="multipart/form-data" action="">
	<p>
	<input type="file" name="fichier" size="30">
	<input type="submit" name="upload" value="Uploader">
	</p>
	</form>
	<br><br>
	<br>
	<?php
	if(file_exists($upload_dir['path']."/info.txt")){
		$fichier = file($upload_dir['path']."/info.txt");
		echo $msg.'<br>';
		$total = count($fichier)-1; // Nombre total des lignes du fichier
		print("Il y a ".$total." abonnés dans le fichier.<br /><br />");

		if ($_GET["x"]=="" && $_GET["ok"]=="ok")
		{
			$x = '1';
			$sqlreq = $wpdb->get_results("DELETE FROM abonne_papier");
			$sqlreq = $wpdb->get_results("ALTER TABLE 'abonne_papier' AUTO_INCREMENT = 1");

			echo "<span class='arttitre'>R&eacute;initialisation de la table des abonn&eacute;s effectu&eacute;e</span><br><br>";
		}
		else
		{
			$x=$_GET["x"];
		}

		$fin = $x+3000;

		if ($fin>=$total)
			$fin=$total;

		if ($_GET["x"]=="" && $_GET["ok"]!="ok")
		{
			echo '<form method="get" enctype="multipart/form-data" action="">';
			echo '<input type="hidden" name="ok" value="ok">';
			echo '<input type="hidden" name="page" value="import-abonnes">';
			echo '<input type="submit" value="Lancer la mise &agrave; jour"></form>';
		}
		else
		{
			for($i = $x; $i <= $fin; $i++)
			{ // Départ de la boucle
				$ref = '';
				$mydate = date("Y-m-d",strtotime(date("Y-m-01", strtotime(date("Y-m-d"))) . " +1 month"));

				$reqsql = "SELECT MAX(id) FROM abonne_papier";
				$max = $wpdb->get_results($reqsql,'ARRAY_A');

				$ligne = explode(";", $fichier[$i]);
				$ligne['14']=$ligne['14']+0;

				$nbnum=trim($ligne['15']);

				$thedate = new DateTime ($mydate);

				if($nbnum<0){
					$thedate->sub(new DateInterval('P'.abs($nbnum).'M'));
				}
				else
					$thedate->add(new DateInterval('P'.$nbnum.'M'));

				if ($thedate->format("m")>=7 && $thedate->format("m")<=9){
					$thedate->add(new DateInterval('P1M'));
				}
				if ($thedate->format("m")==9){
					$thedate->add(new DateInterval('P1M'));
				}

				$dateexp = $thedate->format('Y-m-d');

				$id = $max["MAX(id)"]+1;

				$sqlreq = "INSERT INTO 
								abonne_papier (
									nomprenom,
									num_abo,
									cp,
									ville,
									pays,
									num_fin_abo,
									datexp
								) VALUES(
									'".trim(addslashes($ligne['2']).' '.addslashes($ligne['3']).' '.addslashes($ligne['4']))."',
									'".trim($ligne['0'])."',
									'".addslashes($ligne['8'])."',
									'".addslashes($ligne['9'])."',
									'',
									'".$ligne['14']."',
									'".$dateexp."'
								)";
				$myQuery = $wpdb->get_results($sqlreq);

				$querylogin = "SELECT ID AS user_id FROM wp_users WHERE user_login = '".intval(trim($ligne['0']))."'";
				$resultlogin = $wpdb->get_results($querylogin,'ARRAY_A');
				if($resultlogin == NULL){
					$querylogin = "SELECT user_id FROM wp_usermeta WHERE meta_value = '".intval(trim($ligne['0']))."' AND meta_key = 'numero'";
					$resultlogin = $wpdb->get_results($querylogin, 'ARRAY_A');
				}

				if ($dateexp >= date("Y-m-d")){
					$status = "active";
				} else {
					$status = "inactive";
				}
				if($resultlogin != NULL){
					$user_id = $resultlogin[0]['user_id'];
					$query = "SELECT * FROM wp_pmpro_memberships_users WHERE user_id = '".$user_id."'";
					$result = $wpdb->get_results($query,'ARRAY_A');
					if($result != NULL){
						$query = "UPDATE wp_pmpro_memberships_users SET 
										status = '".$status."',
										enddate = '".$dateexp."'
									WHERE user_id = '".$user_id."'";
						$wpdb->get_results($query);
					} else {
						$query = "INSERT INTO wp_pmpro_memberships_users VALUES ('','".$user_id."',1,0,0,0,0,'Month',0,0,0,'".$status."',NOW(),'".$dateexp."',NOW());";
						$wpdb->get_results($query);
					}
				} else {
					$query = "INSERT INTO wp_users (user_login, user_registered, display_name) VALUES ('".intval(trim($ligne['0']))."',NOW(),'".trim(addslashes($ligne['2']).' '.addslashes($ligne['3']).' '.addslashes($ligne['4']))."');\n
					SELECT @id := LAST_INSERT_ID();\n
					INSERT INTO wp_usermeta (user_id, meta_key, meta_value) VALUES (@id,'numero','".intval(trim($ligne['0']))."');\n
					INSERT INTO wp_pmpro_memberships_users VALUES ('',@id,1,0,0,0,0,'Month',0,0,0,'".$status."',NOW(),'".$dateexp."',NOW());";
					$wpdb->get_results(query);
				}
			} // Fin de la boucle

			$reqsql = "SELECT MAX(id) AS max FROM abonne_papier";
			$max = $wpdb->get_results($reqsql,'ARRAY_A');
			$i = $max["max"];
			$tot = $total;
			if ($i>=$tot)
			{
				echo "<span class='arttitre'>Fin de la mise &agrave; jour</span><br>". $id ." enregistrements effectu&eacute;s<br>";
				if ($id!=$total)
				{
					echo "<span class='arttitre'>ERREUR : le nombre de lignes du fichier ne correspond pas avec le nombre d'enregistrement</span><br><br>".$total;
					echo '<form method="get" enctype="multipart/form-data" action="">';
					echo '<input type="hidden" name="ok" value="ok">';
					echo '<input type="hidden" name="page" value="import-abonnes">';
					echo '<input type="submit" value="Recommencer l\'importation des abonnés"></form>';
				}
			}
			else
			{
				echo 'Derni&egrave;re ligne ins&eacute;r&eacute;e :<br>';
				echo $id .' || '.$ligne['0'].' || '.$ligne['1'].' || '.$ligne['2'].' || '.$ligne['3'].' || '.$ligne['4'].' || '.$ligne['5'].' || '.$ligne['6'].' || '.$ligne['7'].' || '.$ligne['8'].'<br>';
			}
			if ($i<$tot)
			{
				echo '<br><br><form method="get" enctype="multipart/form-data" action="">';
				echo '<input type="hidden" name="ok" value="ok">';
				echo '<input type="hidden" name="page" value="import-abonnes">';
				echo '<input type="hidden" name="x" value="'.$i.'"><input type="submit" value="Suivant"></form>';
			}
		}
	} else {
	?><p>Veuillez d'abord envoyer un fichier</p><?php
	}
    print '</div>';
}


/**
 * Registration form
 */

//[registration]
function inscription_form( $atts ){
	global $wpdb;
	$msg = "";
	if (isset($_POST['sinscrire'])) {
		if (!is_email($_POST['email'])) {
			$msg .= "<p><strong>Vous devez introduire une adresse email correcte</strong></p><p>&nbsp;</p>";
		} else if(empty($_POST['lastname']) || empty($_POST['lastname'])){
			$msg .= "<p><strong>Vous devez introduire un nom et pr&eacute;nom</strong></p><p>&nbsp;</p>";
		} else if(empty($_POST['password'])){
			$msg .= "<p><strong>Vous devez introduire un mot de passe</strong></p><p>&nbsp;</p>";
		} else {
			$identifiant = "";
			if (intval($_POST['numero'])>0){
				$queryNum = "SELECT user_id FROM wp_usermeta WHERE meta_key = 'numero' AND meta_value = '".intval($_POST['numero'])."'";
				$data = $wpdb->get_results($queryNum);

				if($data != NULL){

					$userdata = array(
						'ID'				=> $data->user_id,
						'user_pass'		=> $_POST['password'],
						'user_email'		=> $_POST['email'],
						'display_name'		=> $_POST['firstname']." ".$_POST['lastname'],
						'nickname'			=> $_POST['firstname']." ".$_POST['lastname'],
						'first_name'		=> $_POST['firstname'],
						'last_name'		=> $_POST['lastname']
					);
					wp_update_user( $userdata );
					$identifiant = "num&eacute;ro d'abonn&eacute;";
				} else {
					$userdata = array(
						'user_pass'		=> $_POST['password'],
						'user_login'		=> $_POST['email'],
						'user_email'		=> $_POST['email'],
						'display_name'		=> $_POST['firstname']." ".$_POST['lastname'],
						'nickname'			=> $_POST['firstname']." ".$_POST['lastname'],
						'first_name'		=> $_POST['firstname'],
						'last_name'		=> $_POST['lastname']
					);
					wp_insert_user( $userdata );
					$identifiant = "adresse e-mail";
				}
			} else {
				$userdata = array(
					'user_pass'		=> $_POST['password'],
					'user_login'		=> $_POST['email'],
					'user_email'		=> $_POST['email'],
					'display_name'		=> $_POST['firstname']." ".$_POST['lastname'],
					'nickname'			=> $_POST['firstname']." ".$_POST['lastname'],
					'first_name'		=> $_POST['firstname'],
					'last_name'		=> $_POST['lastname']
				);
				wp_insert_user( $userdata );
				$identifiant = "adresse e-mail";
			}
			$content = "Votre compte a &eacute;t&eacute; cr&eacute;&eacute;. Vous pouvez d&egrave;s &agrave; pr&eacute;sent vous connecter gr&acirc;ce &agrave; votre ".$identifiant." et votre mot de passe.";
		}
	}
	if (!isset($_POST['sinscrire']) || $msg != ""){
		$content = "";
		$content .= $msg."
	<form method='post' enctype='multipart/form-data' action=''>
	<table class='form-table'>

		<tr>
			<th><label for='lastname'>Nom*</label></th>

			<td>
				<input type='text' name='lastname' id='name' value='' class='regular-text' />
			</td>
			<td></td>
		</tr>

		<tr>
			<th><label for='firstname'>Pr&eacute;nom*</label></th>

			<td>
				<input type='text' name='firstname' id='firstname' value='' class='regular-text' />
			</td>
			<td></td>
		</tr>

		<tr>
			<th><label for='email'>E-mail*</label></th>

			<td>
				<input type='text' name='email' id='email' value='' class='regular-text' />
			</td>
			<td></td>
		</tr>

		<tr>
			<th><label for='email'>Mot de passe*</label></th>

			<td>
				<input type='password' name='password' id='password' value='' class='regular-text' />
			</td>
			<td></td>
		</tr>

		<tr>
			<th><label for='numero'>Num&eacute;ro d'abonn&eacute;</label></th>

			<td>
				<input type='text' name='numero' id='numero' value='' class='regular-text' />
			</td>
			<td>
				<span class='description'>Si vous poss&eacute;dez un num&eacute;ro d'abonn&eacute;, veuillez l'indiquer ici.</span>
			</td>
		</tr>

	</table>
	<br><br>
	<center><input type='submit' name='sinscrire' value='Cr&eacute;er mon compte' /></center>
	</form>";
	}
		return $content;

}
add_shortcode( 'inscription', 'inscription_form' );

add_filter( 'register_url', 'my_register_page' );
function my_register_page( $register_url ) {
    return home_url( '/inscription/' );
}



/*-----------------------------------------------------------------------------------
 * PJD: BEGIN - Trigger pour rendre public les articles de plus d'une annee reserves aux abonnes
 *-----------------------------------------------------------------------------------
 */


function pjd_log( $msg ) {
    error_log( print_r( $msg . "\n", true ), 3, "/home/clients/84bfc280d4f0c3dc1dfa728febba7dbe/debug.log" );
}

function do_remove_abonnement_category() {

    // pjd_log( "Dans trigger" );

    // Calcule le jour de l'annee precedente
    $lastYearTime = strtotime( date( "Y-m-d", time() ) . " - 1 year" );
    $lastYear = date( 'Y-m-d 23:59:59', $lastYearTime );

    /* Critere de recherche des articles:
        - Uniquement des 'post' (articles)
        - Present dans la categorie "abonnement"
        - Absent de la categorie "abonnement-permanent"
        - Ayant le status "publie"
        - Datant d'avant l'annee derniere
    */
    $criteria = array (
        "post_type" => "post",
        "tax_query" => array (
            array (
                "taxonomy" => "category",
                "field"    => "slug",
                "terms"    => array( "abonnement" ) // Cette categorie contient les articles reserves aux abonnes
            ),
            "relation" => "AND",
            array (
                "operator" => "NOT IN",
                "taxonomy" => "category",
                "field"    => "slug",
                "terms"    => array( "abonnement-permanent" ) // Cette categorie sert a filtrer les articles dont l'acces doit toujours etre reserve aux abonnes
            )
        ),
        "post_status" => "publish",
        "date_query"  => array (
            array (
                "before"    => $lastYear,
                "inclusive" => true // inclus le jour meme de l'annee derniere
            )
        ),
        'posts_per_page' => -1
    );

    // Lancement de la recherche
    $query = new WP_Query( $criteria );

    // pjd_log( "Nombre d'article: " . $query->post_count );

    // Parcours des articles trouves
    foreach ( $query->posts as $post ) {
        // Pour chaque article trouve, suppression de la categorie "abonnement". --> L'article est alors disponible pour tout a chacun.
        wp_remove_object_terms( $post->ID, "abonnement", "category" );
    }

    // On reinitialise a la requete principale (important)
    wp_reset_postdata();

    // pjd_log( "Sortie trigger" );
}
add_action( 'remove_abonnement_event', 'do_remove_abonnement_category' );

function set_remove_abonnement_event_trigger() {
    $timestamp = wp_next_scheduled( 'remove_abonnement_event' );
    if ( ! $timestamp ) {
        $timestamp = wp_schedule_event( strtotime( '00:00:00' ), 'daily', 'remove_abonnement_event' );
    } else {
        // unset_remove_abonnement_event_trigger(); echo " | desactive";
    }

    // pjd_log( "Installation: " . $timestamp );
}

function unset_remove_abonnement_event_trigger() {
    wp_clear_scheduled_hook( 'remove_abonnement_event' );
}

set_remove_abonnement_event_trigger();

/*-----------------------------------------------------------------------------------
 * PJD: END - Trigger pour rendre public les articles de plus d'une annee reserves aux abonnes
 *-----------------------------------------------------------------------------------
 */

?>
