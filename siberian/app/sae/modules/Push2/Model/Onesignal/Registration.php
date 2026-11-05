<?php

namespace Push2\Model\Onesignal;

/**
 * Class Registration
 *
 * Pure helper for the body of Push2.registerPlayer. The app sends player_id, push_token and external_user_id, but a
 * browser (editor preview) or an older shell can leave some of them out: a missing key is an empty value, not a notice.
 */
class Registration
{
    /**
     * @param mixed $body
     * @return array{player_id: string, push_token: string, external_user_id: string}
     */
    public static function fromBody($body)
    {
        $body = is_array($body) ? $body : [];
        $pick = function ($key) use ($body) {
            $value = $body[$key] ?? '';
            return is_scalar($value) ? (string) $value : '';
        };

        return [
            'player_id' => $pick('player_id'),
            'push_token' => $pick('push_token'),
            'external_user_id' => $pick('external_user_id'),
        ];
    }
}
