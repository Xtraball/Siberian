<?php

namespace Siberian;

/**
 * Class TranslateResponse
 *
 * Reads the answer of the machine translation services used by the translation editor. Anything that is not a
 * translation (an error body, the HTML page Google serves to a blocked address, an empty answer) is null.
 */
class TranslateResponse
{
    /**
     * Yandex v1.5: {"code":200,"lang":"en-fr","text":["Bonjour"]}
     *
     * @param string|null $body
     * @return string|null
     */
    public static function fromYandex(?string $body): ?string
    {
        $response = self::decode($body);
        if (!is_array($response) ||
            (int) ($response['code'] ?? 0) !== 200 ||
            !isset($response['text'][0]) ||
            !is_string($response['text'][0]) ||
            $response['text'][0] === '') {
            return null;
        }

        return $response['text'][0];
    }

    /**
     * Google "gtx": [[["Bonjour. ","Hello. ",...],["Monde","World",...]],null,"en",...], one entry per sentence
     *
     * @param string|null $body
     * @return string|null
     */
    public static function fromGoogle(?string $body): ?string
    {
        $response = self::decode($body);
        if (!is_array($response) || !isset($response[0]) || !is_array($response[0])) {
            return null;
        }

        $text = '';
        foreach ($response[0] as $segment) {
            if (is_array($segment) && isset($segment[0]) && is_string($segment[0])) {
                $text .= $segment[0];
            }
        }

        return $text === '' ? null : $text;
    }

    /**
     * @param string|null $body
     * @return mixed
     */
    private static function decode(?string $body)
    {
        if ($body === null || $body === '') {
            return null;
        }

        return json_decode($body, true);
    }
}
