<?php 

function makeImagesRelative($json) {

    $url = preg_quote(get_site_url(), "/");

    return preg_replace(
        "/$url\/wp-content\//", '../../static/wordsby/', str_replace('vrp.be', 'webhart.one', $json)
    );
}


function makeInlineImagesRelative($post_content) {
    $url = preg_quote(get_site_url(), "/");

    return preg_replace(
        "/$url\/wp-content\//", '/wordsby/', $post_content
    );
}

?>