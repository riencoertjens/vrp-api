<?php 

function getTaxJSON() {
    return makeImagesRelative(
        json_encode(
            custom_api_get_all_taxonomies_terms_callback('tax'), 
            JSON_UNESCAPED_SLASHES
        )
    );
}

?>