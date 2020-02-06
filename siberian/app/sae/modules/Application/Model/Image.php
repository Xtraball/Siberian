<?php

use Siberian\Image;

/**
 * Class Application_Model_Image
 */
class Application_Model_Image
{
    /**
     *
     */
    public static function pushPreviewImage()
    {
        $language = Core_Model_Language::getCurrentLanguage();
        $cachedPath = path("/var/cache/images/android-notification-base-{$language}.png");
        if (!is_file($cachedPath)) {
            $baseImage = path("/app/sae/design/desktop/flat/css/fonts/android-notification-base.png");
            $font = path("/app/sae/design/desktop/flat/css/fonts/play-regular.ttf");

            $image = new Image($baseImage);
            $image->write($font, p__('application', 'New push message'), 130, 55, 24, 0, "#202020", "left");
            $image->write($font, p__('application', 'You have a new push message'), 130, 94, 20, 0, "#787878", "left");
            $image->write($font, date("g:i a"), 670, 55, 20, 0, "#787878", "right");

            $image->save($cachedPath, "png", 100);
        }

        return "/var/cache/images/android-notification-base-{$language}.png";
    }
}