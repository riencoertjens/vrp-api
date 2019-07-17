<?php
// Add Prijs column to prijs list
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

// Add filter select to prijs list
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

// Add filter functionality to prijs list
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