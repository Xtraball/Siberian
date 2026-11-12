<?php
/**
 * #994: Backoffice's "Cache (var/cache_images, images cache) -> Clear" only emptied var/cache_images. App
 * answers cached in var/cache (front_mobile_home_findall, homepage findall, etc.) carry URLs to the resized/
 * colorized copies in var/cache_images, so once those files are deleted the cached answers keep pointing at
 * files that no longer exist - icons 404 until the app answer cache expires on its own (up to 4 days per
 * app/Bootstrap.php's _initCache()). Clearing cache_images must also clear var/cache.
 */
$siberian = dirname(__DIR__, 3) . '/siberian';
$controller = (string) file_get_contents(
    $siberian . '/app/sae/modules/Backoffice/Controller/Index/Abstract.php'
);

return [
    "994 the cache_images case also clears var/cache, not only the images" => function () use ($controller) {
        $pos = strpos($controller, "case 'cache_images':");
        t_true($pos !== false, "the cache_images case exists");

        $next = strpos($controller, 'break;', $pos);
        $body = substr($controller, $pos, $next - $pos);

        t_true(
            strpos($body, '__clearCacheImages()') !== false,
            'it still clears the image cache itself'
        );
        t_true(
            strpos($body, '__clearCache()') !== false,
            'it now also clears var/cache, so app answers pointing at the deleted images are cleared too'
        );
    },
];
