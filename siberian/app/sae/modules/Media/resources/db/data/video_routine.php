<?php

// Remove YouTube API keys from the backoffice
try {
    $provider = (new Api_Model_Provider())->find('youtube', 'code');
    if ($provider && $provider->getId()) {
        $providerId = $provider->getId();
        $provider->delete();
        // Keys
        $keys = (new Api_Model_Key())->findAll(['provider_id = ?' => $providerId]);
        foreach ($keys as $key) {
            $key->delete();
        }
    }
} catch (\Exception $e) {
    // Silent!
}
