<?php
// Admin polish for Flexible Modules: hide the ACF layout status toggle and badge
add_action('admin_head', function () {
		// Polyfill tippy if missing (some admin UIs remove it); prevents ACF tooltip errors
		echo '<script>(function(){
			if(typeof window.tippy !== "function"){
				window.tippy = function(){ return { hide:function(){}, destroy:function(){}, setProps:function(){}, setContent:function(){} }; };
			}
		})();</script>';
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
			echo '<script>document.addEventListener("DOMContentLoaded",function(){
			var root = document.querySelectorAll(".acf-field[data-key=\"field_hj_modules_fc\"] .acf-flexible-content .layout");
			root.forEach(function(l){
				l.classList.remove("-disabled");
				l.removeAttribute("aria-disabled");
			});

			// Guard against ACF tooltip errors from conflicting tooltip libraries
			try{
				if(window.acf && acf.Model && acf.Model.prototype){
					var proto = acf.Model.prototype;
					var _show = proto.showTitle || function(){};
					var _hide = proto.hideTitle || function(){};
					proto.showTitle = function(){
						// If tippy is missing, skip custom tooltips to avoid breaking interactions
						if(typeof window.tippy !== "function"){ return; }
						return _show.apply(this, arguments);
					};
					proto.hideTitle = function(){
						try{
							if(this.tooltip && typeof this.tooltip.hide === "function"){
								this.tooltip.hide();
								return;
							}
							// Try a safe destroy if available
							if(this.tooltip && typeof this.tooltip.destroy === "function"){
								this.tooltip.destroy();
							}
						}catch(e){}
					};
				}
			}catch(e){}
		});</script>';
});

// Admin: ensure ACF flexible icons display correctly even if fonts/styles clash
add_action('admin_head', function(){
	echo '<style>
		.acf-field[data-key="field_hj_modules_fc"] .acf-icon:before{ font-family: dashicons !important; speak: never; }
		.acf-field[data-key="field_hj_modules_fc"] .acf-icon{ color:#555; }
	</style>';
});

// Ensure dashicons are enqueued in admin if any plugin dequeued them
add_action('admin_enqueue_scripts', function(){
	wp_enqueue_style('dashicons');
	// Try removing potential conflicting core tooltip script
	wp_dequeue_script('tooltip-wizard');
	wp_deregister_script('tooltip-wizard');
}, 100);

// Last-resort patch: after all scripts are printed, hard-override ACF tooltip methods
add_action('admin_print_footer_scripts', function(){
	echo '<script>(function(){
		console.log("HJ Admin Patch: start");
		function safePatch(){
			if(!window.acf || !acf.Model || !acf.Model.prototype){
				console.log("HJ Admin Patch: ACF not ready", window.acf);
				return;
			}
			try{
				var P = acf.Model.prototype;
				if(P.__hj_patched){ return; }
				var originalHide = P.hideTitle;
				P.hideTitle = function(){
					try{
						var tt = this && this.tooltip ? this.tooltip : null;
						console.log("HJ Admin Patch: hideTitle called. tooltip=", tt);
						if(tt && typeof tt.hide === "function"){ tt.hide(); }
						else { console.warn("HJ Admin Patch: tooltip.hide missing, suppressing error"); }
					}catch(e){ console.warn("HJ Admin Patch: hideTitle error suppressed", e); }
				};
				P.showTitle = function(){
					if(typeof window.tippy !== "function"){ console.log("HJ Admin Patch: tippy missing, skip showTitle"); return; }
					if(originalHide && originalHide.apply){ /* leave default if needed */ }
				};
				P.__hj_patched = true;
				console.log("HJ Admin Patch: ACF Model patched");
			}catch(e){ console.warn("HJ Admin Patch: exception while patching", e); }
		}
		// Attempt multiple times as other scripts may load later
		safePatch();
		var tries = 0; var iv = setInterval(function(){ tries++; safePatch(); if(tries>10) clearInterval(iv); }, 300);
		if(window.acf && acf.addAction){
			acf.addAction("ready", safePatch);
			acf.addAction("append", safePatch);
		}
	})();</script>';
}, 999);

// Disable Gutenberg block editor for pages using our custom Pricelist template
add_filter('use_block_editor_for_post', function ($use_block_editor, $post) {
	if (!$post) { return $use_block_editor; }
	$template = get_page_template_slug($post);
	if (in_array($template, ['page-pricelist.php', 'page-pricelist-dental.php'], true)) {
		return false; // use Classic editor (or ACF only)
	}
	return $use_block_editor;
}, 10, 2);

// Back-compat: older Gutenberg filter name
add_filter('gutenberg_can_edit_post', function ($can_edit, $post) {
	if (!$post) { return $can_edit; }
	$template = get_page_template_slug($post);
	if (in_array($template, ['page-pricelist.php', 'page-pricelist-dental.php'], true)) {
		return false;
	}
	return $can_edit;
}, 10, 2);
