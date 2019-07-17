<?php

// give form edit permission to editor
function wpforms_custom_capability( $cap ) {
	// unfiltered_html by default means Editors and up.
	// See more about WordPress roles and capabilities
	// https://codex.wordpress.org/Roles_and_Capabilities
	return 'unfiltered_html';
} add_filter( 'wpforms_manage_cap', 'wpforms_custom_capability' );