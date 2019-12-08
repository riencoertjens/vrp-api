<?php
add_action('rest_api_init', function () {

	// register_rest_field(
	// 	'attachment',
	// 	'smartcrop_image_focus',
	// 	array(
	// 		'get_callback'  => function($post) {
	// 			return get_post_meta($post['id'], "_wpsmartcrop_image_focus");
	// 		}
	// 	)
	// );

	// register_rest_field(
	// 	array(
	// 		'post',
	// 		'ruimte',
	// 		'ruimte_artikel',
	// 		'activities',
	// 		'page',
	// 		'job-listings',
	// 		'locations',
	// 		'prijs'
	// 	),
	// 	'content_raw',
	// 	array(
	// 		'get_callback'  => function($post) {
	// 			return wp_strip_all_tags($post['content']['raw'], true);
	// 		}
	// 	)
	// );

	// register_rest_field(
	// 	'page',
	// 	'featured_posts',
	// 	array(
	// 		'get_callback'  => function($page) {
	// 			if ($page['slug'] === "home"){
	// 				$posts = array();

	// 				foreach (get_field('slider_posts',$page['id']) as $post_id) {
	// 					$post = get_post($post_id);
	// 					$media_id = get_post_thumbnail_id($post);
	// 					$post->featured_media = $media_id ?  (int)$media_id : 0;
	// 					$post->content_raw = wp_strip_all_tags($post->post_content, true);
	// 					$posts[] = $post;
	// 				}
	// 				return $posts;
	// 			} else {
	// 				return null;
	// 			}
	// 		}
	// 	)
	// );

	// add rest route to add form submission
	register_rest_route('vrp-api/v1', 'form-submission', array(
		'methods'  => WP_REST_Server::CREATABLE,
		'callback' => 'vrp_form_submission'
	));

	// register job listing meta keys
	// register_job_listing_meta(array(
	// 	"_filled" => array( // Validate and sanitize the meta value.
	// 		// Note: currently (4.7) one of 'string', 'boolean', 'integer',
	// 		// 'number' must be used as 'type'. The default is 'string'.
	// 		'type'         => 'boolean',
	// 		// Shown in the schema for the meta key.
	// 		'description'  => 'position filled',
	// 		// Return a single value of the type.
	// 		'single'       => true,
	// 		// Show in the WP REST API response. Default: false.
	// 		'show_in_rest' => true,
	// 	),
	// 	"_featured" => array(
	// 		'type'         => 'boolean',
	// 		'description'  => 'job listing featured',
	// 		'single'       => true,
	// 		'show_in_rest' => true,
	// 	),
	// 	"_job_location" => array(
	// 		'type'         => 'string',
	// 		'description'  => 'job location',
	// 		'single'       => true,
	// 		'show_in_rest' => true,
	// 	),
	// 	"_application" => array(
	// 		'type'         => 'string',
	// 		'description'  => 'application details',
	// 		'single'       => true,
	// 		'show_in_rest' => true,
	// 	),
	// 	"_company_name" => array(
	// 		'type'         => 'string',
	// 		'description'  => 'company name',
	// 		'single'       => true,
	// 		'show_in_rest' => true,
	// 	),
	// 	"_company_website" => array(
	// 		'type'         => 'string',
	// 		'description'  => 'company website',
	// 		'single'       => true,
	// 		'show_in_rest' => true,
	// 	),
	// 	"_company_tagline" => array(
	// 		'type'         => 'string',
	// 		'description'  => 'company tagline',
	// 		'single'       => true,
	// 		'show_in_rest' => true,
	// 	),
	// 	"_company_video" => array(
	// 		'type'         => 'string',
	// 		'description'  => 'company video',
	// 		'single'       => true,
	// 		'show_in_rest' => true,
	// 	),
	// 	"_company_twitter" => array(
	// 		'type'         => 'string',
	// 		'description'  => 'company twitter',
	// 		'single'       => true,
	// 		'show_in_rest' => true,
	// 	),
	// ));
});

function register_job_listing_meta($fields)
{
	foreach ($fields as $meta_key => $args) {
		register_meta("post", $meta_key, $args);
	}
}

// form submission function
function vrp_form_submission(WP_REST_Request $request)
{
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

	$fields = get_fields($activity->ID);

	$confirm_subject = "inschrijving " . $activity->post_title;
	$confirm_message = $fields['confirmation_mail'];
	$confirm_message = str_replace('#_EVENTNAME', "'" . $activity->post_title . "'", $confirm_message);

	$confirm_mail = $json_data['email'];

	if ($fields['admin_confirmation'] === true) { //send confirmation to admin
		$admin_message = $fields['admin_email_message'];
		$admin_message = str_replace('#_EVENTNAME', "'" . $activity->post_title . "'", $admin_message);

		mail(
			'rien@lucifer.be',
			'nieuwe ' . $confirm_subject,
			$admin_message + json_encode($fields, JSON_PRETTY_PRINT),
			"From: no-reply@webhart.one\r\n",
			"-F no-reply@webhart.one"
		);
	}

	$success = mail(
		$confirm_mail,
		$confirm_subject,
		$confirm_message,
		"From: no-reply@webhart.one\r\n" . "Reply-To: info@vrp.be\r\n",
		"-F no-reply@webhart.one"
	);

	// error_log(json_encode(array($confirm_mail, $confirm_subject, $confirm_message, "From: no-reply@vrp.be\r\n"), JSON_PRETTY_PRINT));
	// error_log($success ? 'yay' : 'nay');
	// error_log('donezeeeees');

	return $response;
}
