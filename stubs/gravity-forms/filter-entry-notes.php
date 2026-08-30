<?php
namespace {{ plugin_namespace }}\GravityForms;

/**
 * Brands the entry notes written by the feed add-on (note_type = the add-on
 * slug) with an icon instead of the default user avatar.
 */
class FilterEntryNotes
{
    public function __construct() {
        add_filter( 'gform_notes_avatar', [$this, 'maybe_filter_note_avatar'], 10, 2 );
    }

    /**
     * @param string $avatar
     * @param object $note
     * @return string
     */
    public function maybe_filter_note_avatar( $avatar, $note ) {
        if ( '{{ gf_addon_slug }}' !== $note->note_type ) {
            return $avatar;
        }
        return '<span class="dashicons dashicons-admin-generic" style="font-size: 40px; width: 40px; height: 40px;" aria-hidden="true"></span>';
    }
}
