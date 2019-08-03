<?php
//Auto add and update Title field for post_type ruimte:
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

//Auto update Prijs category for post_type prijs:
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