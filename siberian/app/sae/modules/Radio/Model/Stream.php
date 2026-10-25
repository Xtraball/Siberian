<?php

/**
 * Class Radio_Model_Stream
 *
 * Pure helper for the stream link saved with a radio, kept apart from the model so it can be tested alone.
 */
class Radio_Model_Stream
{
    /**
     * Shoutcast streams need a trailing "/;" to serve the audio instead of the status page.
     *
     * @param string $link the link as typed
     * @param string|null $contentType what the server answered for that link, null when it was not tested
     * @param bool $keepUrl the "use the link as typed" option, off by default
     * @return string
     */
    public static function link($link, $contentType, $keepUrl = false)
    {
        if ($keepUrl) {
            return $link;
        }

        $contentType = (string) $contentType;
        if (explode('/', $contentType)[0] !== 'audio' &&
            $contentType !== 'application/ogg' &&
            strrpos($link, ';') === false) {
            return $link . '/;';
        }

        return $link;
    }
}
