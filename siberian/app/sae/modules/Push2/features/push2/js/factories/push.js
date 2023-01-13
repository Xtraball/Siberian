/**
 * Push
 *
 * @author Xtraball SAS
 * @version 4.19.1
 */
angular
.module('starter')
.factory('Push', function ($pwaRequest, $session, SB) {
    var factory = {
        value_id: null,
        device_type: DEVICE_TYPE,
        device_token: null,
        lastError: null,
        lastErrorMessage: null,
        unread_count: 0,
        extendedOptions: {}
    };

    /**
     *
     * @param valueId
     */
    factory.setValueId = function (valueId) {
        factory.value_id = valueId;
    };

    /**
     *
     * @param options
     */
    factory.setExtendedOptions = function (options) {
        factory.extendedOptions = options;
    };

    /**
     * Pre-Fetch feature.
     *
     * @param page
     */
    factory.preFetch = function (page) {
        factory.findAll();
    };

    // @deprecated
    factory.deletePush = function (deliverId) {
        return $pwaRequest.reject({deprecated: true});
    };

    // @deprecated
    factory.findAll = function (offset, refresh) {
        return $pwaRequest.reject({deprecated: true});
    };

    // @overview, important!
    factory.getSample = function () {
        if (!this.value_id) {
            $pwaRequest.reject('[Factory::Push.getSample] missing value_id');
        }

        return $pwaRequest.get('push/mobile_list/get-sample', {
            urlParams: {
                value_id: this.value_id,
            }
        });
    };

    // @deprecated
    factory.updateUnreadCount = function () {
        return $pwaRequest.resolve({deprecated: true});
    };

    // @deprecated
    factory.getInAppMessages = function () {
        return $pwaRequest.resolve({deprecated: true});
    };

    // @deprecated
    factory.getLastMessages = function (cache) {
        return $pwaRequest.resolve({deprecated: true});
    };

    // @deprecated
    factory.markInAppAsRead = function () {
        return $pwaRequest.resolve({deprecated: true});
    };

    // @deprecated
    factory.markAsDisplayed = function (messageId) {
        return $pwaRequest.resolve({deprecated: true});
    };

    factory.registerPlayer = function (player) {
        return $pwaRequest.post('push/mobile/register-player', {
            data: {
                device_uid: $session.getDeviceUid(),
                player_id: player
            },
            cache: false
        });
    };


    return factory;
});
