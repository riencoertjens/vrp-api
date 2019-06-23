<?php
//Child Theme Functions File
add_action( 'wp_enqueue_scripts', 'enqueue_child_theme_styles', PHP_INT_MAX);
function enqueue_child_theme_styles() {
	wp_enqueue_style( 'parent-style', get_template_directory_uri().'/style.css' );
}


function my_acf_google_map_api( $api ){
	$api['key'] = 'AIzaSyDujkg0Ss-J1rQFNy-J1B2S7sgcpdbjXek';
	return $api;
} add_filter('acf/fields/google_map/api', 'my_acf_google_map_api');

// add_image_size( 'hero-image', 1200, 630, true );
// add_image_size( 'post-list-image', 300, 200, true );

// refresh site on post update
function call_hook($url){
	// Generated by curl-to-PHP: http://incarnate.github.io/curl-to-php/
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_POST, 1);
	$headers = array();
	$headers[] = "Content-Type: application/x-www-form-urlencoded";
	$headers[] = "Content-length: 0";
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	$result = curl_exec($ch);
	if (curl_errno($ch)) {
			error_log('Error:' . curl_error($ch));
	} else {
			error_log($result);
	}
	curl_close ($ch);
	
}

if ( $_SERVER["SERVER_ADDR"] !== '127.0.0.1' ) {//don't update on local env
	function refreshSite($post_id){
		$post = get_post($post_id);
		if($post->post_type != 'registratie'){ // don't refresh site on form submission
			call_hook('https://api.netlify.com/build_hooks/5cbf26858b44e1d95d6e5624');
			call_hook('https://api.netlify.com/build_hooks/5d03b9e137d23eafc06bd90a');
		}
	} add_action( 'save_post', 'refreshSite', 11, 1 );

}

//Auto add and update Title field:
function update_ruimte_post_title( $post_id ) {

	$my_post = array();
	$my_post['ID'] = $post_id;

	if ( get_post_type() == 'ruimte' ) {
		$my_post['post_title'] = 'Ruimte ' . get_field('nummer');
		$my_post['post_name'] = 'ruimte-' . get_field('nummer');
	} 
	// Update the post into the database
	wp_update_post( $my_post );

} add_action('acf/save_post', 'update_ruimte_post_title', 10, 1);

//Auto update Prijs category:
function update_prijs_post_category( $post_id ) {

	if ( get_post_type() == 'prijs' ) {
		$parent_cat_id = get_category_by_slug('prijzen')->cat_ID;
		$prijs_cat_id = get_category_by_slug(get_field('prijs'))->cat_ID;
		
		$categories = array();
		foreach (get_the_category() as $category) {
			if ($category->parent != $parent_cat_id) array_push($categories, $category->cat_ID);
		}
		array_push($categories, $prijs_cat_id);
		wp_set_post_categories($post_id, $categories);
	}

} add_action('acf/save_post', 'update_prijs_post_category', 15, 1);


// Add Ruimte column to artikels post list
function add_ruimte_column ( $columns ) {
	return array_merge ( $columns, array ( 
		'ruimte_magazine' => __ ( 'Ruimte' ),
	));
} add_filter ( 'manage_ruimte_artikel_posts_columns', 'add_ruimte_column' );

// Add value to artikels Ruimte column
function ruimte_custom_column ( $column, $post_id ) {
	switch ( $column ) {
		case 'ruimte_magazine':
			echo get_post(get_post_meta($post_id, 'ruimte', true))->post_title;
			break;
	}
} add_action ( 'manage_ruimte_artikel_posts_custom_column', 'ruimte_custom_column', 35, 2 );

// Add sort to artikels Ruimte column
function sort_ruimte_column( $columns ) {
	$columns['ruimte_magazine'] = 'ruimte_magazine';
	return $columns;
} add_filter( 'manage_edit-ruimte_artikel_sortable_columns', 'sort_ruimte_column' );

// Add filter select to artikels posts list
function filter_ruimte_select($post_type) {
	if ( 'ruimte_artikel' !== $post_type ) return; //check to make sure this is your cpt
	$arg=array(
		'show_option_none' => 'Alle uitgaves',
		'orderby' => 'title',
		'hide_empty' => false,
		'post_type'=>'ruimte',
		'name' => 'ruimte_nr',
		'selected' => $_GET['ruimte_nr'],
	);
	wp_dropdown_pages($arg);
} add_action( 'restrict_manage_posts', 'filter_ruimte_select' );

// Add filter functionality to artikels posts list
function filter_ruimte_artikels( $query ){
    global $pagenow;
    $type = 'post';
    if (isset($_GET['post_type'])) {
        $type = $_GET['post_type'];
    }
    if ( 'ruimte_artikel' == $type && is_admin() && $pagenow=='edit.php' && isset($_GET['ruimte_nr']) && $_GET['ruimte_nr'] != '') {
        $query->query_vars['meta_key'] = 'ruimte';
        $query->query_vars['meta_value'] = $_GET['ruimte_nr'];
    }
} add_filter( 'parse_query', 'filter_ruimte_artikels' );


// Add Prijs column to prijs post list
function add_prijs_column ( $columns ) {
	return array_merge ( $columns, array ( 
		'prijs' => __ ( 'Prijs' ),
	));
} add_filter ( 'manage_prijs_posts_columns', 'add_prijs_column' );

// Add value to prijs column
function prijs_custom_column ( $column, $post_id ) {
	switch ( $column ) {
		case 'prijs':
			echo (get_post_meta($post_id, 'prijs', true));
			break;
	}
} add_action ( 'manage_prijs_posts_custom_column', 'prijs_custom_column', 35, 2 );

// Add sort to prijs column
function sort_prijs_column( $columns ) {
	$columns['prijs'] = 'prijs';
	return $columns;
} add_filter( 'manage_edit-prijs_sortable_columns', 'sort_prijs_column' );

// Add filter select to prijs posts list
function filter_prijs_select($post_type) {
	if ( 'prijs' !== $post_type ) return; //check to make sure this is your cpt
	$prijzen = array(
		"openruimtebeker" => "Openruimtebeker",
		"vrp-afstudeerprijs" => "VRP Afstudeerprijs",
		"vrp-planningsprijs" => "VRP Planningsprijs"
	);

	?>
	<label for="prijs_type" class="screen-reader-text">Filter per prijs</label>
	<select name="prijs_type" id="prijs" >
		<option value="0">Alle prijzen</option>
		<?php
			$i = 0;
			foreach ($prijzen as $key => $value) {
				?><option class="level-0" value="<?= $key ?>" <?php if ($key == $_GET['prijs_type']) echo 'selected'; ?>><?= $value ?></option><?php
				$i++;
			}
		?>
	</select>
	<?php
} add_action( 'restrict_manage_posts', 'filter_prijs_select' );

// Add filter functionality to prijs post list
function filter_prijzen_list( $query ){
    global $pagenow;
    $type = 'post';
    if (isset($_GET['post_type'])) {
        $type = $_GET['post_type'];
    }
    if ( 'prijs' == $type && is_admin() && $pagenow=='edit.php' && isset($_GET['prijs_type']) && $_GET['prijs_type'] != '') {
        $query->query_vars['meta_key'] = 'prijs';
        $query->query_vars['meta_value'] = $_GET['prijs_type'];
    }
} add_filter( 'parse_query', 'filter_prijzen_list' );









// give form edit permission to editor
function wpforms_custom_capability( $cap ) {
	// unfiltered_html by default means Editors and up.
	// See more about WordPress roles and capabilities
	// https://codex.wordpress.org/Roles_and_Capabilities
	return 'unfiltered_html';
} add_filter( 'wpforms_manage_cap', 'wpforms_custom_capability' );

// create custom post type for 'registratie'
function registratie_cpt() {
	$args = array(
			'labels'  => array(
				'name' => 'registraties',
				'singular_name' => 'registratie',
			),
			'public' => true,
			// 'show_in_menu' => false
	);
	register_post_type( 'registratie', $args );

} add_action( 'init', 'registratie_cpt' );


// create sub menu page for registraties in activity tab
function my_menu() {
	add_submenu_page('edit.php?post_type=activity', 'Registraties', 'registraties', 'unfiltered_html', 'registraties', 'submissions_page_display' );
} add_action('admin_menu', 'my_menu');

// form submissions page
function submissions_page_display() {
	// if no activity is set, show all activities
	if (isset($_REQUEST['activity_id'])){

		$activity = get_post($_REQUEST['activity_id']);

		if( ! class_exists( 'Submissions_List_Table' ) ) {
			require_once( 'includes/submissions-list-table-class.php' );
		}

		$submissionsListTable = new Submissions_List_Table($activity->ID);
		$submissionsListTable->prepare_items();

		?>
			<div class="wrap">
				<h1>registraties: <a href="post.php?action=edit&post=<?=$activity->ID;?>"><?=$activity->post_title;?></a></h1>
				<a href="edit.php?post_type=activity&page=registraties">terug naar overzicht</a><br/>
				<!-- <a href="">exporteer csv</a> -->
				<?php $submissionsListTable->display(); ?>
				

			</div>
		<?php

	// if activity is set, show submissions
	} else {

		if( ! class_exists( 'Submissions_Overview_List_Table' ) ) {
			require_once( 'includes/submissions-overview-list-table-class.php' );
		}

		$submissionsOverviewListTable = new Submissions_Overview_List_Table();
		$submissionsOverviewListTable->prepare_items();

		?>
			<div class="wrap">
				<h1>registraties</h1>
				<?php $submissionsOverviewListTable->display(); ?>
			</div>
		<?php
	}

	
}

// add rest route to get registration count and limit
add_action('rest_api_init', function () {

	register_rest_field(
		'attachment',
		'smartcrop_image_focus',
		array(
			'get_callback'  => function($post) {
				return get_post_meta($post['id'], "_wpsmartcrop_image_focus");
			}
		)
	);

	register_rest_field(
		array(
			'post',
			'ruimte',
			'ruimte_artikel',
			'activities',
			'page',
			'job-listings',
			'locations',
			'prijs'
		),
		'content_raw',
		array(
			'get_callback'  => function($post) {
				return wp_strip_all_tags($post['content']['raw'], true);
			}
		)
	);

	register_rest_field(
		'page',
		'featured_posts',
		array(
			'get_callback'  => function($page) {
				if ($page['slug'] === "home"){
					$posts = array();

					foreach (get_field('in_de_kijker',$page['id']) as $post_id) {
						$post = get_post($post_id);
						$media_id = get_post_thumbnail_id($post);
						$post->featured_media = $media_id ?  (int)$media_id : 0;
						$post->content_raw = wp_strip_all_tags($post->post_content, true);
						$posts[] = $post;
					}
					return $posts;
				} else {
					return null;
				}
			}
		)
	);

	register_rest_route( 'vrp-api/v1', 'activity/submission-count/(?P<id>\d+)',array(
		'methods'  => WP_REST_Server::READABLE,
		'callback' => 'activity_submission_count'
	));
});




// check+return registration count and limit
function activity_submission_count( $data ) {
	$activity_id = $data['id'];

	// $hasform = get_field('hasform', $activity_id);

	$args = array(
		'post_type' => 'registratie',
		'meta_query' => array(
			array(
				'key' => 'activity_id',
				'value' => $activity_id,
				)
			)
	);

	$query = new WP_Query($args);
	$count = $query->found_posts;

	$places = get_field('places', $activity_id);

	$response = new WP_REST_Response(array(
		'count' => $count,
		'places' => $places > 0 ? $places : '-1'
	));
	$response->set_status(200);

	return $response;
}

// add rest route to add form submission
add_action('rest_api_init', function () {

	register_rest_route( 'vrp-api/v1', 'form-submission',array(
		'methods'  => WP_REST_Server::CREATABLE,
		'callback' => 'vrp_form_submission'
	));

	register_job_listing_meta(array(
		"_filled" => array( // Validate and sanitize the meta value.
			// Note: currently (4.7) one of 'string', 'boolean', 'integer',
			// 'number' must be used as 'type'. The default is 'string'.
			'type'         => 'boolean',
			// Shown in the schema for the meta key.
			'description'  => 'position filled',
			// Return a single value of the type.
			'single'       => true,
			// Show in the WP REST API response. Default: false.
			'show_in_rest' => true,
		),
		"_featured" => array(
			'type'         => 'boolean',
			'description'  => 'job listing featured',
			'single'       => true,
			'show_in_rest' => true,
		),
		"_job_location" => array(
			'type'         => 'string',
			'description'  => 'job location',
			'single'       => true,
			'show_in_rest' => true,
		),
		"_application" => array(
			'type'         => 'string',
			'description'  => 'application details',
			'single'       => true,
			'show_in_rest' => true,
		),
		"_company_name" => array(
			'type'         => 'string',
			'description'  => 'company name',
			'single'       => true,
			'show_in_rest' => true,
		),
		"_company_website" => array(
			'type'         => 'string',
			'description'  => 'company website',
			'single'       => true,
			'show_in_rest' => true,
		),
		"_company_tagline" => array(
			'type'         => 'string',
			'description'  => 'company tagline',
			'single'       => true,
			'show_in_rest' => true,
		),
		"_company_video" => array(
			'type'         => 'string',
			'description'  => 'company video',
			'single'       => true,
			'show_in_rest' => true,
		),
		"_company_twitter" => array(
			'type'         => 'string',
			'description'  => 'company twitter',
			'single'       => true,
			'show_in_rest' => true,
		),
	));

});

// form submission function
function vrp_form_submission( WP_REST_Request $request ) {

	$json_data = $request->get_json_params();
	$activity = get_post($json_data['data']['activity_id']);
	$data = wp_slash(wp_json_encode($json_data['data']));

	$postarr = array(
		'post_title' => $json_data['email'],
		'post_date' => $json_data['created_at'],
		'post_content' => $data,
		'post_status' => 'publish',
		'post_type' => 'registratie',
		'comment_status' => 'closed'
	);

	$post_id = wp_insert_post($postarr, $wp_error);

	add_post_meta($post_id, 'activity_id', $activity->ID);

	$response = new WP_REST_Response($post_id);
	$response->set_status(200);

	return $response;
}

function register_job_listing_meta($fields){
	foreach ($fields as $meta_key => $args) {
		register_meta( "post", $meta_key, $args );
	}
}

if (file_exists($customfunctions = get_template_directory()."/functions-custom.php")): include($customfunctions); endif;
