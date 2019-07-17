<?php

// Add Ruimte column to artikels list
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

// Add filter select field to artikels list
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

// Add filter functionality to artikels list
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