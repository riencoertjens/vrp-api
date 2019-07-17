<?php
require_once dirname( __FILE__ ) . "/functions/settings.php";
require_once dirname( __FILE__ ) . "/functions/permissions.php";

require_once dirname( __FILE__ ) . "/functions/vrp_post_types/save_post.php";
require_once dirname( __FILE__ ) . "/functions/vrp_post_types/rest_mods.php";
require_once dirname( __FILE__ ) . "/functions/vrp_post_types/registraties.php";

require_once dirname( __FILE__ ) . "/functions/vrp_admin/artikels_list.php";
require_once dirname( __FILE__ ) . "/functions/vrp_admin/prijs_list.php";

// require_once dirname( __FILE__ ) . "/functions/save_post_webhook.php";

//wordsby functions
require_once dirname( __FILE__ ) . "/functions/wordsby/write_log.php";
require_once dirname( __FILE__ ) . "/functions/wordsby/prevent-init-being-called-twice.php";
require_once dirname( __FILE__ ) . "/functions/wordsby/discourage-search-engines.php";
require_once dirname( __FILE__ ) . "/functions/wordsby/redirect-index-to-admin.php";
require_once dirname( __FILE__ ) . "/functions/wordsby/activate-pretty-permalinks.php";
require_once dirname( __FILE__ ) . "/functions/wordsby/add-theme-support.php";

require_once dirname( __FILE__ ) . "/plugins/WordsbyCore/wordsby-core.php";
require_once dirname( __FILE__ ) . "/plugins/Wordlify/wordlify.php";


add_theme_support('editor-styles');
add_editor_style('style-editor.css');