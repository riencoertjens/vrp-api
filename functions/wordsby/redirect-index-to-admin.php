<?php 
add_action( 'template_redirect', function() {
    if ( is_page( 1140 ) || is_page( 1139 ) ) { //job dashboard + post job pages
        return;
    }

    if( is_user_logged_in() ) {
        $user = wp_get_current_user();

        if ( in_array( 'administrator', (array) $user->roles ) ) {
            return;
        }

        if ( in_array( 'employer', (array) $user->roles ) ) {
            wp_redirect( get_permalink( 1140 ) );
            die;
        }
    }

    wp_redirect(get_admin_url());
    exit;
} );

// add_filter( 'template_include', 'redirect_index_to_admin', 99 );

// function redirect_index_to_admin( $template ) {
//     if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['gatsbypress_previews']) || isset($_POST['gatsbypress_preview_keycheck']))) {
//         return $template;
//     }

//     if (isset($_GET['rest_base']) || isset($_GET['nonce']) || isset($_GET['_wpnonce'])) return $template;

//     $template_filename = str_replace(get_template_directory(). "/", '', $template);

//     if ($template_filename === 'index.php') {
//         wp_redirect(get_admin_url());
//         return $template;
//     } else {
//         return $template;
//     }
// }
?>