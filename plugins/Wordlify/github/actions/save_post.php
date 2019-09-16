<?php
// on post save, trash, or untrash, commit the collections endpoint to the gatsby repo.
add_action('acf/save_post', 'commitJSON');
add_action('edited_terms', 'commitJSON');
add_action('trashed_post', 'commitJSON');
add_action('untrashed_post', 'commitJSON');

function commitJSON($id)
{

    if (get_post($id)->post_type === 'registratie') return;

    // dont create commits when saving menus
    if (isset($_POST['nav-menu-data'])) return;

    // dont create commits when saving preview revisions.
    if (
        isset($_POST['wp-preview']) &&
        $_POST['wp-preview'] === 'dopreview'
    ) return $id;

    commit(
        createCommitMessage($id),
        [
            [
                'path' => 'wordsby/data/collections.json',
                'content' => getCollectionsJSON(),
                'encoding' => 'utf-8'
            ],
            [
                'path' => 'wordsby/data/terms.json',
                'content' => getTermsJSON(),
                'encoding' => 'utf-8'
            ],
            [
                'path' => 'wordsby/data/tax.json',
                'content' => getTaxJSON(),
                'encoding' => 'utf-8'
            ],
            // [
            //     'path' => 'wordsby/data/tax-terms.json',
            //     'content' => getTaxTermsJSON(), 
            //     'encoding' => 'utf-8'
            // ],
            // [
            //     'path' => 'wordsby/data/options.json',
            //     'content' => getOptionsJSON(), 
            //     'encoding' => 'utf-8'
            // ],
            // [
            //     'path' => 'wordsby/data/site-meta.json',
            //     'content' => getSiteMetaJSON(), 
            //     'encoding' => 'utf-8'
            // ],
            // [
            //     'path' => 'wordsby/data/menus.json',
            //     'content' => getMenusJSON(), 
            //     'encoding' => 'utf-8'
            // ],
        ]
    );
}

function save_wpforms_post_content($id)
{ //form

    $post = get_post($id);

    if ($post->post_type === "wpforms") {
        $post_content_data = json_decode($post->post_content);
        // error_log(json_encode($post_content, JSON_PRETTY_PRINT));
        $newFields = array();
        $i = 0;
        foreach ($post_content_data->fields as $stupid_key => $fieldData) {
            error_log($stupid_key);
            $newFields["{$i}"] = $fieldData;

            $i++;
        }

        $post_content_data->fields = $newFields;

        $post_content = wp_slash(wp_json_encode($post_content_data));

        $post_arr = array(
            'ID'           => $post->ID,
            'post_content'   => $post_content,
        );

        // If calling wp_update_post, unhook this function so it doesn't loop infinitely
        remove_action('save_post', 'save_wpforms_post_content');
        // call wp_update_post update
        wp_update_post($post_arr);
        // re-hook this function
        add_action('save_post', 'save_wpforms_post_content');
    }

    commitJSON($id);
}
add_action('save_post', 'save_wpforms_post_content');
