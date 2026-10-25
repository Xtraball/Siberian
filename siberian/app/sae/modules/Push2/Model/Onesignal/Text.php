<?php

namespace Push2\Model\Onesignal;

/**
 * Class Text
 *
 * Pure helper for the text shown by the phone, which does not render HTML entities.
 */
class Text
{
    /**
     * Titles and bodies saved from the editor can hold HTML entities ("Caf&eacute; &amp; Co"). Phones show them
     * as typed, so the text sent to OneSignal is decoded. The saved message is not changed.
     *
     * @param string|null $value
     * @return string
     */
    public static function plain($value)
    {
        return html_entity_decode((string) $value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}
