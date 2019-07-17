<?php

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