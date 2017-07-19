/*global
 App, angular
 */

/**
 * Topic
 *
 * @author Xtraball SAS
 */
angular.module("starter").factory("Topic", function($pwaRequest, $session) {

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

    factory.findAll = function() {

        if(!this.value_id) {
            return $pwaRequest.reject("[Factory::Topic.findAll] missing value_id");
        }

        return $pwaRequest.get("topic/mobile_list/findall", angular.extend({
            urlParams: {
                value_id    : this.value_id,
                device_uid  : $session.getDeviceUid()
            }
        }, factory.extendedOptions));
    };

    factory.subscribe = function(topic_id, is_subscribed) {

        return $pwaRequest.post("topic/mobile_list/subscribe", {
            data: {
                category_id : topic_id,
                device_uid  : $session.getDeviceUid(),
                subscribe   : is_subscribed
            }
        });
    };

    return factory;
});
