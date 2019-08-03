<?php 

function makeImagesRelative($json) {
    $url = preg_quote(get_site_url(), "/");

    return preg_replace(
        "/$url\/wp-content\//", '../../static/wordsby/', $json
    );
}


function makeInlineImagesRelative($post_content) {
    $url = preg_quote(get_site_url(), "/");

    return str_replace(
        array(
            "https://www.vrp.be/wp-content/",
            "https://vrp.be/wp-content/",
            "http://www.vrp.be/wp-content/",
            "http://vrp.be/wp-content/"
        ),
        "/wordsby/",
        preg_replace("/$url\/wp-content\//", '/wordsby/', $post_content)
    );

}

?>