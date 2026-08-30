<?php
namespace {{ plugin_namespace }}\GravityForms;

/**
 * Gravity Forms integration for {{ plugin_name }}.
 *
 * Everything Gravity Forms related hangs off this component so the plugin
 * degrades gracefully when Gravity Forms is not active: the feed add-on is
 * only registered once GF has loaded its add-on framework.
 */
class GravityFormsComponent
{
    public function __construct() {
        add_action( 'gform_loaded', [$this, 'register_feed_addon'], 5 );
        add_action( 'admin_notices', [$this, 'maybe_missing_gravity_forms_notice'] );
        new FilterEntryNotes;
{{ gf_settings_page_line }}    }

    /**
     * Register the feed add-on with Gravity Forms.
     */
    public function register_feed_addon() {
        if ( ! method_exists( 'GFForms', 'include_feed_addon_framework' ) ) {
            return;
        }
        \GFForms::include_feed_addon_framework();
        \GFAddOn::register( {{ gf_addon_class }}FeedAddOn::class );
    }

    /**
     * Let admins know the integration is idle without Gravity Forms.
     */
    public function maybe_missing_gravity_forms_notice() {
        if ( class_exists( 'GFForms' ) || ! current_user_can( 'activate_plugins' ) ) {
            return;
        }
        printf(
            '<div class="notice notice-warning"><p>%s</p></div>',
            esc_html__( '{{ plugin_name }}: Gravity Forms is not active, so form feeds are unavailable.', '{{ plugin_text_domain }}' )
        );
    }
}
