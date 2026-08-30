<?php
namespace {{ plugin_namespace }}\GravityForms;

use {{ plugin_namespace }}\Abstract\AbstractSettings;

/**
 * Connection settings for the {{ gf_addon_name }} add-on, saved from the
 * Forms > Settings > {{ gf_addon_name }} tab.
 *
 * The API key may be defined as the PHP constant
 * {{ plugin_constants_prefix }}API_KEY (e.g. in wp-config.php) so that it never
 * lives in the database; when defined it wins over the saved value.
 */
class {{ gf_addon_class }}Settings extends AbstractSettings
{
    const OPTION_KEY = '{{ plugin_filter_prefix }}gf_settings';

    const PREFIX = '{{ plugin_filter_prefix }}gf_';

    const API_KEY_CONSTANT = '{{ plugin_constants_prefix }}API_KEY';

    /**
     * @return array The plugin defaults.
     */
    protected function get_defaults() {
        $settings = [
            'api_url' => '',
            'api_key' => '',
        ];
        return apply_filters( static::PREFIX . 'default_settings', $settings );
    }

    /**
     * @return string
     */
    public function api_url() {
        return untrailingslashit( (string) $this->get_setting( 'api_url' ) );
    }

    /**
     * @return string|false The API key constant value, if defined and non-empty.
     */
    public function api_key_constant() {
        if ( defined( static::API_KEY_CONSTANT ) && ! empty( constant( static::API_KEY_CONSTANT ) ) ) {
            return constant( static::API_KEY_CONSTANT );
        }
        return false;
    }

    /**
     * @return string The constant (when defined), otherwise the saved value.
     */
    public function api_key() {
        $constant = $this->api_key_constant();
        if ( ! empty( $constant ) ) {
            return $constant;
        }
        return (string) $this->get_setting( 'api_key' );
    }

    /**
     * @return bool
     */
    public function is_configured() {
        return ! empty( $this->api_url() ) && ! empty( $this->api_key() );
    }
}
