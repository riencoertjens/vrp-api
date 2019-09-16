<?php
//Auto add and update Title field for post_type ruimte:
function update_ruimte_post_title($post_id)
{

	$my_post = array();
	$my_post['ID'] = $post_id;

	if (get_post_type() == 'ruimte') {
		$my_post['post_title'] = 'Ruimte ' . get_field('nummer');
		$my_post['post_name'] = 'ruimte-' . get_field('nummer');
	}
	// Update the post into the database
	wp_update_post($my_post);
}
add_action('acf/save_post', 'update_ruimte_post_title', 10, 1);

//Auto update Prijs category for post_type prijs:
function update_prijs_post_category($post_id)
{

	if (get_post_type() == 'prijs') {
		$parent_cat_id = get_category_by_slug('prijzen')->cat_ID;
		$prijs_cat_id = get_category_by_slug(get_field('prijs'))->cat_ID;

		$categories = array();
		foreach (get_the_category() as $category) {
			if ($category->parent != $parent_cat_id) array_push($categories, $category->cat_ID);
		}
		array_push($categories, $prijs_cat_id);
		wp_set_post_categories($post_id, $categories);
	}
}
add_action('acf/save_post', 'update_prijs_post_category', 15, 1);

//update post excerpt
function update_post_excerpt($post_id)
{
	$post = get_post($post_id);
	$excerpt = wp_strip_all_tags($post->post_content, true);
	$excerpt = preg_replace('/\s+/', ' ', $excerpt);
	if (strlen($excerpt) > 160) {
		$excerpt = substr($excerpt, 0, 160);
		$excerpt = substr($excerpt, 0, strrpos($excerpt, ' '));
		$excerpt .= " [...]";
	}
	$post_arr = array(
		'ID'           => $post->ID,
		'post_excerpt'   => $excerpt,
	);
	wp_update_post($post_arr);
}
add_action('acf/save_post', 'update_post_excerpt', 20, 1);



// function save_wpforms_post_content($id)
// { //form

// 	$post = get_post($id);

// 	if ($post->post_type === "wpforms") {
// 		$post_content_data = json_decode($post->post_content);
// 		// error_log(json_encode($post_content, JSON_PRETTY_PRINT));
// 		$newFields = array();
// 		$i = 0;
// 		foreach ($post_content_data->fields as $stupid_key => $fieldData) {
// 			error_log($stupid_key);
// 			$newFields["{$i}"] = $fieldData;

// 			$i++;
// 		}

// 		$post_content_data->fields = $newFields;

// 		$post_content = wp_slash(wp_json_encode($post_content_data));

// 		$post_arr = array(
// 			'ID'           => $post->ID,
// 			'post_content'   => $post_content,
// 		);

// 		// If calling wp_update_post, unhook this function so it doesn't loop infinitely
// 		remove_action('save_post', 'save_wpforms_post_content');
// 		// call wp_update_post update
// 		wp_update_post($post_arr);
// 		// re-hook this function
// 		add_action('save_post', 'save_wpforms_post_content');
// 	}
// }
// add_action('save_post', 'save_wpforms_post_content');
