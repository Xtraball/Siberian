<?php

namespace rock\helpers;

/**
 * Link represents a link object as defined in [JSON Hypermedia API Language](https://tools.ietf.org/html/draft-kelly-json-hal-03).
 */
class Link
{
    /**
     * The self link.
     */
    const REL_SELF = 'self';

    /**
     * Serializes a list of links into proper array format.
     * @param array $links the links to be serialized
     * @return array the proper array representation of the links.
     */
    public static function serialize(array $links)
    {
        foreach ($links as $rel => $link) {
            if (is_array($link)) {
                foreach ($link as $i => $l) {
                    $link[$i] = $l instanceof self ? array_filter((array)$l) : ['href' => $l];
                }
                $links[$rel] = $link;
            } elseif (!$link instanceof self) {
                $links[$rel] = ['href' => $link];
            }
        }

        return $links;
    }
}