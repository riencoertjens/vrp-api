<?php 

function makeImagesRelative($json) {
    $url = preg_quote("https://vrp-final.netlify.com", "/");

    return preg_replace(
        "/$url\/wp-content\//", '../../static/wordsby/', $json
    );
}


function makeInlineImagesRelative($post_content) {
    $url = preg_quote(get_site_url(), "/");

    return preg_replace(
        "/$url\/wp-content\//", '/wordsby/', $post_content
    );
}

?>