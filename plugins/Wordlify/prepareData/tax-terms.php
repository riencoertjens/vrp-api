<?php 

function getTaxTermsJSON() {
    return makeImagesRelative(
        json_encode(
            custom_api_get_all_taxonomies_terms_callback(), 
            JSON_UNESCAPED_SLASHES
        )
    );
}

?>