<?php
function my_acf_google_map_api( $api ){
	$api['key'] = 'AIzaSyDgFjKWoeM2IUFQcQPUhkdsmZfSgs5v8AQ';
	return $api;
} add_filter('acf/fields/google_map/api', 'my_acf_google_map_api');


/**
 * Exclude current post/page from relationship field results
 */

// 1. Add the name=[NAME_OF_RELATIONSHIP_FIELD].
add_filter('acf/fields/relationship/query', 'exclude_id', 10, 3);

// 2. Add the $field and $post arguments.
function exclude_id ( $args, $field, $post ) {

    //3. $post argument passed in from the query hook is the $post->ID.
    $args['post__not_in'] = array( $post );
    
    return $args;
}