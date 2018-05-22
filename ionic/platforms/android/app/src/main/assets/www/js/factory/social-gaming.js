/*global
    App, device, angular
 */

/**
 * SocialGaming
 *
 * @author Xtraball SAS
 */
angular.module("starter").factory("SocialGaming", function($rootScope, $pwaRequest, SB) {

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
     *
     * @param page
     */
    factory.preFetch = function(page) {
        factory.findAll();
    };


    factory.findAll = function(offset) {

        if(!this.value_id) {
            return $pwaRequest.reject("[Factory::SocialGaming.findAll] missing value_id");
        }

        return $pwaRequest.get("socialgaming/mobile_view/findall", angular.extend({
            urlParams: {
                value_id    : this.value_id,
                offset      : offset
            }
        }, factory.extendedOptions));
    };

    $rootScope.$on(SB.EVENTS.CACHE.clearSocialGaming, function() {

        $pwaRequest.cache("socialgaming/mobile_view/findall", {
            urlParams: {
                value_id : factory.value_id
            }
        });
    });

    return factory;
});
