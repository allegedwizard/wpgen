<?php
namespace {{ plugin_namespace }}\GravityForms;

/**
 * Forms > Settings > {{ gf_addon_name }} tab. Rendered with Gravity Forms'
 * own settings markup so it matches the surrounding tabs.
 */
class {{ gf_addon_class }}SettingsPage
{
    const PAGE_NAME = '{{ gf_addon_slug }}';

    const NONCE_ACTION = '{{ gf_addon_slug }}_settings';

    /**
     * @var bool
     */
    protected $settings_saved = false;

    /**
     * @var {{ gf_addon_class }}Settings|null
     */
    protected $settings = null;

    public function __construct() {
        add_action( 'admin_init', [$this, 'register_settings_page'] );
        add_action( 'admin_init', [$this, 'save_settings'] );
    }

    public function register_settings_page() {
        if ( ! class_exists( 'GFSettings' ) ) {
            return;
        }
        \GFSettings::add_settings_page([
            'name' => static::PAGE_NAME,
            'tab_label' => '{{ gf_addon_name }}',
            'title' => '{{ gf_addon_name }}',
            'handler' => [$this, 'render_settings_page'],
            'icon' => 'dashicons-admin-generic',
        ], null );
    }

    /**
     * @return {{ gf_addon_class }}Settings
     */
    protected function settings() {
        if ( empty( $this->settings ) ) {
            $this->settings = {{ gf_addon_class }}Settings::get();
        }
        return $this->settings;
    }

    public function render_settings_page() {
        if ( $this->settings_saved ) {
            printf( '<div class="alert gforms_note_success" role="alert">%s</div>', esc_html__( 'Settings updated.', '{{ plugin_text_domain }}' ) );
        }
        ?>
        <form method="post">
            <fieldset class="gform-settings-panel gform-settings-panel--full gform-settings-panel--with-title">
                <legend class="gform-settings-panel__title gform-settings-panel__title--header">
                    <?php esc_html_e( '{{ gf_addon_name }} Connection', '{{ plugin_text_domain }}' ); ?>
                </legend>
                <div class="gform-settings-panel__content">
                    <?php
                    $this->field_api_url();
                    $this->field_api_key();
                    ?>
                </div>
                <?php wp_nonce_field( static::NONCE_ACTION, '_{{ gf_addon_slug }}_nonce' ); ?>
            </fieldset>
            <div class="gform-settings-save-container">
                <button type="submit" id="gform-settings-save" name="gform-settings-save" class="primary button large">
                    <?php esc_html_e( 'Save', '{{ plugin_text_domain }}' ); ?>
                </button>
            </div>
        </form>
        <?php
    }

    protected function field_api_url() {
        ?>
        <div class="gform-settings-field gform-settings-field__text">
            <div class="gform-settings-field__header">
                <label class="gform-settings-label" for="api_url"><?php esc_html_e( 'API URL', '{{ plugin_text_domain }}' ); ?></label>
            </div>
            <span>
                <input type="url" id="api_url" name="api_url" class="gform-admin-input" required="required"
                       value="<?php echo esc_attr( $this->settings()->api_url() ); ?>">
            </span>
        </div>
        <?php
    }

    protected function field_api_key() {
        ?>
        <div class="gform-settings-field gform-settings-field__text">
            <div class="gform-settings-field__header">
                <label class="gform-settings-label" for="api_key"><?php esc_html_e( 'API Key', '{{ plugin_text_domain }}' ); ?></label>
            </div>
            <?php if ( $this->settings()->api_key_constant() ) : ?>
                <span>
                    <input type="password" class="gform-admin-input" readonly="readonly" value="********">
                </span>
                <p>
                    <?php
                    printf(
                        esc_html__( 'Defined via the PHP constant %s and cannot be edited here.', '{{ plugin_text_domain }}' ),
                        '<code>' . esc_html( {{ gf_addon_class }}Settings::API_KEY_CONSTANT ) . '</code>'
                    );
                    ?>
                </p>
            <?php else : ?>
                <span>
                    <input type="password" id="api_key" name="api_key" class="gform-admin-input" autocomplete="off"
                           value="<?php echo esc_attr( $this->settings()->api_key() ); ?>">
                </span>
                <p>
                    <?php
                    printf(
                        esc_html__( 'For security, this value can instead be defined as the PHP constant %s so it is not stored in the database.', '{{ plugin_text_domain }}' ),
                        '<code>' . esc_html( {{ gf_addon_class }}Settings::API_KEY_CONSTANT ) . '</code>'
                    );
                    ?>
                </p>
            <?php endif; ?>
        </div>
        <?php
    }

    public function save_settings() {
        if ( ! isset( $_POST['_{{ gf_addon_slug }}_nonce'] ) ) {
            return;
        }
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_{{ gf_addon_slug }}_nonce'] ) ), static::NONCE_ACTION ) ) {
            return;
        }

        $values = [];
        if ( isset( $_POST['api_url'] ) ) {
            $values['api_url'] = esc_url_raw( wp_unslash( $_POST['api_url'] ) );
        }
        if ( isset( $_POST['api_key'] ) && ! $this->settings()->api_key_constant() ) {
            $values['api_key'] = sanitize_text_field( wp_unslash( $_POST['api_key'] ) );
        }

        $this->settings()->set( $values );
        $this->settings_saved = true;
    }
}
