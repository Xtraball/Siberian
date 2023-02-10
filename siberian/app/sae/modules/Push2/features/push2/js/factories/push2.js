/**
 * Push v2
 *
 * @author Xtraball SAS <dev@xtraball.com>
 * @version 1.0.0
 */
angular
    .module('starter')
    .factory('Push2', function (Application, Pages, $timeout, $stateParams, $pwaRequest, $session) {

        var factory = {
            storage: [],
            value_id: null
        };

        /**
         * Set value_id
         * @param value_id
         * @returns {{storage: *[], value_id: null}}
         */
        factory.setValueId = function (value_id) {
            factory.value_id = value_id;
            return factory;
        };

        factory.registerPlayer = function (deviceState) {
            if (!this.value_id) {
                $pwaRequest.reject('[Factory::Push2.registerPlayer] missing value_id');
            }

            return $pwaRequest.post('push2/mobile_player/register', {
                urlParams: {
                    value_id: this.value_id,
                },
                data: {
                    device_uid: $session.getDeviceUid(),
                    player_id: deviceState.userId,
                    external_user_id: $session.getExternalUserId(),
                    push_token: deviceState.pushToken
                },
                cache: false
            });
        };

        // @overview, important!
        factory.getSample = function () {
            if (!this.value_id) {
                $pwaRequest.reject('[Factory::Push2.getSample] missing value_id');
            }

            return $pwaRequest.get('push2/mobile_list/get-sample', {
                urlParams: {
                    value_id: this.value_id,
                }
            });
        };

        factory.findAll = function () {
            if (!this.value_id) {
                $pwaRequest.reject('[Factory::Push2.findAll] missing value_id');
            }

            return $pwaRequest.get('push2/mobile_list/find-all', {
                urlParams: {
                    value_id: this.value_id,
                }
            });
        };

        return factory;
    });
