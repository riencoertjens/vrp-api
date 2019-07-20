<?php 

function getTermsJSON() {
    return makeImagesRelative(
        json_encode(
            custom_api_get_all_taxonomies_terms_callback('terms'), 
            JSON_UNESCAPED_SLASHES
        )
    );
}

?>