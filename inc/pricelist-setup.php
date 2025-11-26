<?php
// Admin polish for Flexible Modules: hide the ACF layout status toggle and badge
add_action('admin_head', function () {
		$screen = function_exists('get_current_screen') ? get_current_screen() : null;
		if (!$screen || $screen->base !== 'post') { return; }
		echo '<style>
			/* Limit to our flexible field */
			.acf-field[data-key="field_hj_modules_fc"] .acf-fc-layout-status,
			.acf-field[data-key="field_hj_modules_fc"] [aria-label="Disable layout"],
			.acf-field[data-key="field_hj_modules_fc"] [aria-label="Enable layout"],
			.acf-field[data-key="field_hj_modules_fc"] .acf-fc-layout-handle .-badge { display:none !important; }
		</style>';
});

// Optional: ensure disabled layouts still render by forcing enable at load time in admin UI
add_action('admin_footer', function () {
		$screen = function_exists('get_current_screen') ? get_current_screen() : null;
		if (!$screen || $screen->base !== 'post') { return; }
		echo '<script>document.addEventListener("DOMContentLoaded",function(){
			var root = document.querySelectorAll(".acf-field[data-key=\"field_hj_modules_fc\"] .acf-flexible-content .layout");
			root.forEach(function(l){
				l.classList.remove("-disabled");
				l.removeAttribute("aria-disabled");
			});
		});</script>';
});

// Disable Gutenberg block editor for pages using our custom Pricelist template
add_filter('use_block_editor_for_post', function ($use_block_editor, $post) {
	if (!$post) { return $use_block_editor; }
	$template = get_page_template_slug($post);
	if ($template === 'page-pricelist-dental.php') {
		return false; // use Classic editor (or ACF only)
	}
	return $use_block_editor;
}, 10, 2);

// Back-compat: older Gutenberg filter name
add_filter('gutenberg_can_edit_post', function ($can_edit, $post) {
	if (!$post) { return $can_edit; }
	$template = get_page_template_slug($post);
	if ($template === 'page-pricelist-dental.php') {
		return false;
	}
	return $can_edit;
}, 10, 2);
