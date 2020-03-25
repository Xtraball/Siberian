/**
 * Youtube
 *
 * @author Xtraball SAS
 * @version 4.18.13
 */
angular
    .module('starter')
    .factory('Youtube', function ($q, $pwaRequest) {

        var factory = {
            extendedOptions: {}
        };

        factory.genericRequest = function (type, keyword, offset) {
            return $pwaRequest
                .get('media/mobile_gallery_video_list/proxy-youtube', {
                    data: {
                        type: type,
                        keyword: keyword,
                        offset: offset
                    }
                });
        };

        factory.findBySearch = function (keyword, offset) {
            factory.genericRequest('search', keyword, offset);
        };

        factory.findByChannel= function (keyword, offset) {
            factory.genericRequest('channel', keyword, offset);
        };

        factory.findByUser = function (keyword, offset) {
            factory.genericRequest('user', keyword, offset);
        };

        return factory;
    });
