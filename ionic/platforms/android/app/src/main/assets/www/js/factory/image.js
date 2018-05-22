/* global
 App, angular
 */

/**
 * Image
 *
 * @author Xtraball SAS
 */
angular.module('starter').factory('Image', function ($pwaRequest) {
    var factory = {
        value_id: null,
        displayed_per_page: 0,
        extendedOptions: {}
    };

    /**
     *
     * @param value_id
     */
    factory.setValueId = function (value_id) {
        factory.value_id = value_id;
    };

    /**
     *
     * @param options
     */
    factory.setExtendedOptions = function (options) {
        factory.extendedOptions = options;
    };

    factory.findAll = function (refresh) {
        if (!this.value_id) {
            return $pwaRequest.reject('[Factory::Image.findAll] missing value_id');
        }

        var localRefresh = (refresh !== undefined) ? refresh : false;

        if (!localRefresh) {
            var payload = $pwaRequest.getPayloadForValueId(factory.value_id);
            if (payload !== false) {
                return $pwaRequest.resolve(payload);
            }
        }

        /** Otherwise fallback on PWA */
        return $pwaRequest.get('media/mobile_gallery_image_list/findall', angular.extend({
            urlParams: {
                value_id: this.value_id
            }
        }, factory.extendedOptions));
    };

    factory.find = function (item, offset) {
        if (!this.value_id) {
            return $pwaRequest.reject('[Factory::Image.find] missing value_id');
        }

        return $pwaRequest.get('media/mobile_gallery_image_view/find', {
            urlParams: {
                value_id: this.value_id,
                gallery_id: item.id,
                offset: offset

            }
        });
    };

    factory.findFacebook = function (item, albumUrl) {
        var localAlbumUrl = albumUrl;
        if (typeof localAlbumUrl === 'string') {
            localAlbumUrl = encodeURIComponent(btoa(localAlbumUrl));
        }
        return $pwaRequest.get('media/mobile_gallery_image_view/findfacebook', {
            urlParams: {
                value_id: this.value_id,
                album_url: localAlbumUrl,
                gallery_id: item.id
            }
        });
    };

    return factory;
});
