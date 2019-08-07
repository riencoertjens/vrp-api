<?php 
function make_url_path($path) {
    return str_replace(home_url(), "", $path);
}

add_action( 'rest_api_init', 'custom_api_get_all_posts' );   

function custom_api_get_all_posts() {
    register_rest_route( 'wp/v1', '/collections', array(
        'methods' => 'GET',
        'callback' => 'custom_api_get_all_posts_callback'
    ));
}

function custom_api_get_all_posts_callback( $data ) {
    $id_param = $data->get_param('id');

    return posts_formatted_for_gatsby($id_param);    
} 


add_action( 'rest_api_init', 'custom_api_get_instant_publish_post_by_id' );   

function custom_api_get_instant_publish_post_by_id() {
    register_rest_route( 
        'wordsby/v1', 
        '/instant_publish_collections/(?P<id>\\d+)', 
        array(
            'methods' => 'GET',
            'callback' => 'custom_api_get_all_posts_instant_publish_callback',
            'args' => array(
                'id' => array(
                'validate_callback' => 
                    function($param, $request, $key) {
                        return is_numeric( $param );
                    }
                ),
        ),
    ));
}

function custom_api_get_all_posts_instant_publish_callback( $data ) {
    $id_param = $data->get_param('id');

    return posts_formatted_for_gatsby($id_param, "", true);    
} 


function posts_formatted_for_gatsby($id_param, $revision = "", $liveData = "") {
    // Initialize the array that will receive the posts' data. 
    $posts_data = array();

    if ($revision === "") {
        $posts = get_posts( array(
                'post_type' => 'any',
                'posts_per_page' => -1, 
                'p' => $id_param,
                'orderby' => 'post_type menu_order date',
                'order' => 'ASC'       
            )
        ); 
    } else {
        $posts = get_posts( array(
            'post_type' => 'revision',
            'posts_per_page' => 1, 
            'post_parent' => $id_param,
            'post_status' => 'any'     
        )
    ); 
    }

    $Yoast_To_Wordsby = Wordsby_Yoast_init();

    // Loop through the posts and push the desired data to the array we've initialized earlier in the form of an object
    foreach( $posts as $post ) {
        $id = $post->ID; 

        $post_thumbnail = null;

        if ($post->post_type === "job_listing"){
            // get_post_meta($post_id, $key, $single)
            $post->job_info = array(
                "filled" => get_post_meta($post->ID, "_filled", true),
                "featured" => get_post_meta($post->ID, "_featured", true),
                "job_location" => get_post_meta($post->ID, "_job_location", true),
                "application" => get_post_meta($post->ID, "_application", true),
                "company_name" => get_post_meta($post->ID, "_company_name", true),
                "company_website" => get_post_meta($post->ID, "_company_website", true),
                "company_tagline" => get_post_meta($post->ID, "_company_tagline", true),
                "company_video" => get_post_meta($post->ID, "_company_video", true),
                "company_twitter" => get_post_meta($post->ID, "_company_twitter, true")
            );
        }

        $post_thumbnail = get_post_thumbnail_object($id);
        
        if (!$post_thumbnail) {
            $post_thumbnail = get_post_thumbnail_object($id_param);
        }

        $permalink = get_permalink($id);
        $post_type = $post->post_type;
        $template_slug = get_page_template_slug($id);

        $template = $template_slug ? $template_slug : "single/$post_type";

        $post_taxonomies = get_post_taxonomies($id);

        $post_taxonomy_terms = array();
        $post_terms = array();

        foreach($post_taxonomies as $taxonomy) {
            $terms = get_the_terms($id, $taxonomy);
            
            if (!$terms) continue;

            foreach($terms as $term) {

                $term->parent = ($term->parent !== 0) ? get_term($term->parent)->slug : null;

                $term->pathname = make_url_path(
                    get_term_link($term)
                );

                array_push($post_terms, $term->slug);
            }
            
            $firstTermPathname = $terms[0]->pathname;
            $firstTermSlug = $terms[0]->slug . "/";
            
            $taxonomy_pathname = str_replace($firstTermSlug, "", $firstTermPathname);

            $taxonomy_object = get_taxonomy($taxonomy);
            
            $post_taxonomy_terms[$taxonomy] = array(
                'labels' => array(
                    'plural' => $taxonomy_object->label,
                    'single' => $taxonomy_object->labels->singular_name
                ),
                'pathname' => $taxonomy_pathname,
                'terms' => $terms
            );
        } 

        $all_acf = get_fields($id);

        if ($all_acf) {
            // removing site urls from links to create pathnames in gatsby
            array_walk_recursive($all_acf, 'remove_urls');

            array_walk_recursive($all_acf, 'set_acf_fields');


            if ($revision !== "" || $liveData !== "") {
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

        
        
        
        if ($Yoast_To_Wordsby) {
            $yoast_meta = $Yoast_To_Wordsby->json_encode_yoast($id);
        } else {
            $yoast_meta = null;
        }
        
        // write_log($yoast_meta);
        // $post->yoast = $yoast_meta;

        if ( empty( $post->post_excerpt ) ) {
            $post->post_excerpt = substr(strip_tags( $post->post_content ), 0, 160);
        }

        $post->post_parent = $post->post_parent ? get_post($post->post_parent) : [];
        $post->type = "collection";
        $post->taxonomies = $post_taxonomy_terms;
        $post->term_slugs = $post_terms;
        $post->taxonomy_slugs = $post_taxonomies;
        $post->pathname = str_replace(home_url(), '', $permalink); 
        $post->permalink = $permalink;
        $post->featured_img = $post_thumbnail;
        $post->template_slug = $template;
        $post->acf = $all_acf ? $all_acf : [];
        $post->post_content = replace_urls_with_pathnames(
            makeInlineImagesRelative(
                apply_filters('the_content', $post->post_content)
            )
        );

        // remove unneeded data
        unset($post->ping_status);
        unset($post->post_password);
        unset($post->to_ping);
        unset($post->pinged);
        unset($post->post_modified_gmt);
        unset($post->post_date_gmt);
        unset($post->post_content_filtered);
        unset($post->guid);
        unset($post->post_mime_type);
        unset($post->filter);
        unset($post->permalink);

        $posts_data[] = $post;
    }                  
    return $posts_data;
}


function set_acf_fields(&$item, $key) {
    if (is_object($item) && get_class($item) === "WP_Post") {
        $acf_fields = get_fields($item->ID);
        if ($acf_fields){
            $item->acf = $acf_fields;
        }

        $item->featured_img = get_post_thumbnail_object($item->ID);
    }
}

function get_post_thumbnail_object($post_id){
    $post_thumbnail = null;
    if ( has_post_thumbnail( $post_id ) ) {
        $post_thumbnail['file'] = get_the_post_thumbnail_url( $post_id );
        $smartcrop_image_focus = get_post_meta(get_post_thumbnail_id( $post_id ), "_wpsmartcrop_image_focus");
        $post_thumbnail['smartcrop_image_focus'] = 
            $smartcrop_image_focus ? 
                $smartcrop_image_focus[0] : 
                array(
                    "left"=> "50",
                    "top"=> "50"
                );
    }
    return $post_thumbnail;
}
?>