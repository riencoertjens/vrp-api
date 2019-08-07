<?php 

function makeImagesRelative($json) {
    $url = preg_quote(get_site_url(), "/");
    $content = str_replace(array('http://www.vrp.be', 'http://.vrp.be', 'https://vrp.be'), 'https://www.vrp.be', $json);
    return preg_replace(
        "/$url\/wp-content\//", '../../static/wordsby/', $content
    );
}


function makeInlineImagesRelative($post_content) {
    $url = preg_quote(get_site_url(), "/");

    return preg_replace(
        "/$url\/wp-content\//", '/wordsby/', $post_content
    );
}

?>