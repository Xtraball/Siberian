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
                .post('media/mobile_gallery_video_list/proxy-youtube', {
                    data: {
                        type: type,
                        keyword: keyword,
                        offset: offset
                    }
                });
        };

        factory.findBySearch = function (keyword, offset) {
            return factory.genericRequest('search', keyword, offset);
        };

        factory.findByChannel= function (keyword, offset) {
            return factory.genericRequest('channel', keyword, offset);
        };

        factory.findByUser = function (keyword, offset) {
            return factory.genericRequest('user', keyword, offset);
        };

        return factory;
    });
