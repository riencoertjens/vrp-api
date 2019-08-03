<?php 
add_action( 'rest_api_init', 'custom_api_get_all_taxonomies_terms' );   

function custom_api_get_all_taxonomies_terms() {
    register_rest_route( 'wp/v1', '/tax-terms', array(
        'methods' => 'GET',
        'callback' => 'custom_api_get_all_taxonomies_terms_callback'
    ));
}

function custom_api_get_all_taxonomies_terms_callback($type = null) {
    $taxonomies = get_taxonomies([
        'show_ui' => true,
        'show_in_rest' => true,
        'public' => true
    ]);

    $taxonomy_types = [];
    $taxonomy_terms = [];

    $site_url = get_site_url();

    foreach ($taxonomies as $taxonomy) {
        $taxonomy_details = get_taxonomy($taxonomy);

        $tax_term = [
            'name' => $taxonomy_details->name,
            'id' => $taxonomy_details->name,
            'label' => $taxonomy_details->label,
            'pathname' => get_taxonomy_archive_link($taxonomy),
        ];

        $taxonomy_types[] = $tax_term;

        $terms = get_terms(array(
            'taxonomy' => $taxonomy
        ));

        // update term count
        // this fixes incorrect terms. Probably this could be made into a wordsby option to fix term counts
        // $terms_update = get_terms($taxonomy, array('hide_empty' => 0, 'fields' => 'ids'));
        // wp_update_term_count_now($terms_update, $tax_term['name']);
        // end update term count
        
        foreach ($terms as $term) {
            $term_link = get_term_link($term);
            $pathname = $term_link ? str_replace($site_url, '', $term_link) : null;

            $all_acf = get_fields($term->taxonomy.'_'.$term->term_id);

            $featured_img = [];

            if ($all_acf) {

                if($all_acf['afbeelding']){
                    $image_id = $all_acf['afbeelding'];
                    if (wp_attachment_is_image($image_id)){

                        $featured_img['file'] = wp_get_attachment_url( $image_id );
                        
                        $smartcrop_image_focus= get_post_meta($image_id, "_wpsmartcrop_image_focus");
    
                        $featured_img['smartcrop_image_focus'] = 
                        $smartcrop_image_focus ? 
                            $smartcrop_image_focus[0] : 
                            array(
                                "left"=> "50",
                                "top"=> "50"
                            );
                    }

                }

                // removing site urls from links to create pathnames in gatsby
                array_walk_recursive($all_acf, 'remove_urls');

                if ((isset($revision) && $revision !== "") || (isset($liveData) && $liveData !== "")) {
                    // checking for flexible content and manipulating flexible fields to mimic gatsby's graphql fragment output structure.
                    foreach ($all_acf as $key=>$field) {
                        if (
                            is_array($field) && 
                            isset($field[0]) && 
                            is_array($field[0]) && 
                            array_key_exists('acf_fc_layout', $field[0])
                            // it's a flexible content field if it passes all these checks 
                            ) {
                            if (is_array($field)) {
                                foreach ($field as &$flexlayout) {
                                    $fieldname = $flexlayout['acf_fc_layout'];
                                    $flexlayout['__typename'] = "WordPressAcf_$fieldname";
                                    unset($flexlayout['acf_fc_layout']);
                                }
                            }
                            $all_acf[$key."_collection"] = $field;
                        }
                    }
                }
            }

            $taxonomy_terms[] = [
                'slug' => $term->slug,
                'name' => $term->name,
                'description' => $term->description,
                'wordpress_id' => $term->term_id,
                'taxonomy' => $term->taxonomy,
                'featured_img' => $featured_img,
                'pathname' => $pathname,
                'parent_term' => $term->parent ? get_term($term->parent)->slug : null,
                'acf' => $all_acf ? $all_acf : [],
            ];
        }
    }

    if ($type === 'terms'){
        return $taxonomy_terms;
    } else if ($type === 'tax'){
        return $taxonomy_types;
    } else {
        return array(
            'types' => $taxonomy_types,
            'terms' => $taxonomy_terms
        );
    }
}
?>