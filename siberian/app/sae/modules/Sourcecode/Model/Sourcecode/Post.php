<?php

/**
 * Class Sourcecode_Model_Sourcecode_Post
 *
 * What the editor posts for the Source Code feature, before it reaches the model.
 */
class Sourcecode_Model_Sourcecode_Post
{
    /**
     * The editor form only posts html_code and value_id: allow_offline is not part of it, so it is converted when
     * it is posted and the stored value is left alone otherwise (it used to be read unconditionally, which raised
     * an undefined index and reset the flag to 0 on every save).
     *
     * @param array $post
     * @return array
     */
    public static function normalize(array $post): array
    {
        if (array_key_exists('allow_offline', $post)) {
            $post['allow_offline'] = (bool) $post['allow_offline'];
        }

        return $post;
    }
}
