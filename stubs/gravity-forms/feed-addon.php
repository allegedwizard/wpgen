<?php
namespace {{ plugin_namespace }}\GravityForms;

/**
 * {{ gf_addon_name }} feed add-on.
 *
 * A feed is a per-form configuration (Form Settings > {{ gf_addon_name }})
 * describing what to do with each submission. Gravity Forms calls
 * process_feed() for every active feed whose conditional logic matches the
 * entry.
 *
 * @see https://docs.gravityforms.com/gffeedaddon/
 */
class {{ gf_addon_class }}FeedAddOn extends \GFFeedAddOn
{
    protected $_version = {{ plugin_constants_prefix }}VERSION;

    protected $_min_gravityforms_version = '2.5';

    /** Also used as the entry note type (see FilterEntryNotes). */
    protected $_slug = '{{ gf_addon_slug }}';

    protected $_path = '{{ plugin_text_domain }}/{{ plugin_text_domain }}.php';

    protected $_full_path = __FILE__;

    protected $_title = '{{ gf_addon_name }} Add-On';

    protected $_short_title = '{{ gf_addon_name }}';

    /**
     * @var {{ gf_addon_class }}FeedAddOn|null
     */
    private static $_instance = null;

    /**
     * @return {{ gf_addon_class }}FeedAddOn
     */
    public static function get_instance() {
        if ( null === self::$_instance ) {
            self::$_instance = new self;
        }
        return self::$_instance;
    }

    /**
     * Icon for the form settings tab and feed pages. Accepts a dashicon
     * class, a gform-icon class, an SVG string, or an image URL.
     *
     * @return string
     */
    public function get_menu_icon() {
        return 'dashicons-admin-generic';
    }

    /**
     * Columns shown on the feed list page.
     *
     * @return array
     */
    public function feed_list_columns() {
        return [
            'feedName' => esc_html__( 'Name', '{{ plugin_text_domain }}' ),
        ];
    }

    /**
     * Fields rendered on the feed edit page.
     *
     * @return array
     */
    public function feed_settings_fields() {
        return [
            [
                'title' => esc_html__( '{{ gf_addon_name }} Feed Settings', '{{ plugin_text_domain }}' ),
                'fields' => [
                    [
                        'label' => esc_html__( 'Feed Name', '{{ plugin_text_domain }}' ),
                        'type' => 'text',
                        'name' => 'feedName',
                        'required' => true,
                        'class' => 'medium',
                        'tooltip' => esc_html__( 'Enter a name to identify this feed.', '{{ plugin_text_domain }}' ),
                    ],
                ],
            ],
            [
                'title' => esc_html__( 'Field Mapping', '{{ plugin_text_domain }}' ),
                'fields' => [
                    [
                        'type' => 'generic_map',
                        'name' => 'field_map',
                        'label' => esc_html__( 'Map Fields', '{{ plugin_text_domain }}' ),
                        'key_field' => [
                            'title' => esc_html__( '{{ gf_addon_name }} Field', '{{ plugin_text_domain }}' ),
                            'choices' => $this->get_destination_field_choices(),
                            'allow_duplicates' => false,
                            'allow_custom' => false,
                        ],
                        'value_field' => [
                            'title' => esc_html__( 'Form Field', '{{ plugin_text_domain }}' ),
                            'allow_custom' => true,
                        ],
                    ],
                ],
            ],
            [
                'title' => esc_html__( 'Conditional Logic', '{{ plugin_text_domain }}' ),
                'fields' => [
                    [
                        'type' => 'feed_condition',
                        'name' => 'feed_condition',
                        'label' => esc_html__( 'Conditional Logic', '{{ plugin_text_domain }}' ),
                        'checkbox_label' => esc_html__( 'Enable', '{{ plugin_text_domain }}' ),
                        'instructions' => esc_html__( 'Process this feed if', '{{ plugin_text_domain }}' ),
                    ],
                ],
            ],
        ];
    }

    /**
     * The destination keys a form field can be mapped to.
     *
     * @return array List of ['label' => ..., 'value' => ...].
     */
    protected function get_destination_field_choices() {
        $choices = [
            ['label' => esc_html__( 'Email', '{{ plugin_text_domain }}' ), 'value' => 'email'],
            ['label' => esc_html__( 'First Name', '{{ plugin_text_domain }}' ), 'value' => 'first_name'],
            ['label' => esc_html__( 'Last Name', '{{ plugin_text_domain }}' ), 'value' => 'last_name'],
        ];
        return apply_filters( '{{ plugin_filter_prefix }}gf_destination_fields', $choices );
    }

    /**
     * Handle a matching entry.
     *
     * @param array $feed  The feed configuration (mapping lives in $feed['meta']).
     * @param array $entry The submitted entry.
     * @param array $form  The form.
     * @return array|void The (possibly modified) entry.
     */
    public function process_feed( $feed, $entry, $form ) {

        // Resolves the generic map to [destination_key => entry value].
        $mapped = $this->get_generic_map_fields( $feed, 'field_map', $form, $entry );
        $mapped = array_filter( $mapped, function( $value ) {
            return '' !== $value && null !== $value;
        });

        // TODO: send $mapped to your service.

        $this->add_note(
            $entry['id'],
            sprintf( esc_html__( 'Feed "%s" processed.', '{{ plugin_text_domain }}' ), rgars( $feed, 'meta/feedName' ) ),
            'success'
        );

        return $entry;
    }
}
