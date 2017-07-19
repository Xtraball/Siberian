/*global
 App, device, angular
 */

/**
 * Video
 *
 * @author Xtraball SAS
 */
angular.module("starter").factory("Video", function($pwaRequest, Youtube) {

    var factory = {
        value_id        : null,
        extendedOptions : {}
    };

    /**
     *
     * @param value_id
     */
    factory.setValueId = function(value_id) {
        factory.value_id = value_id;
    };

    /**
     *
     * @param options
     */
    factory.setExtendedOptions = function(options) {
        factory.extendedOptions = options;
    };

    /**
     * Pre-Fetch feature.
     */
    factory.preFetch = function() {
        factory.findAll();
    };

    factory.findAll = function() {

        if(!this.value_id) {
            return $pwaRequest.reject("[Factory::Video.findAll] missing value_id");
        }

        var payload = $pwaRequest.getPayloadForValueId(factory.value_id);
        if(payload !== false) {

            return $pwaRequest.resolve(payload);

        } else {

            /** Otherwise fallback on PWA */
            return $pwaRequest.get("media/mobile_gallery_video_list/findall", angular.extend({
                urlParams: {
                    value_id: this.value_id
                }
            }, factory.extendedOptions));

        }


    };

    factory.find = function(item) {

        if(!this.value_id) {
            return $pwaRequest.reject("[Factory::Video.find] missing value_id");
        }

        return $pwaRequest.get("media/mobile_gallery_video_view/find", {
            urlParams: {
                value_id    : this.value_id,
                gallery_id  : item.id,
                offset      : item.current_offset
            }
        });
    };

    factory.findInYouTube = function(search_by, keyword, offset) {

        if(search_by === "user") {
            return Youtube.findByUser(keyword, offset);
        } else if(search_by === "channel") {
            return Youtube.findByChannel(keyword, offset);
        } else {
            return Youtube.findBySearch(keyword, offset);
        }

    };

    return factory;
});
