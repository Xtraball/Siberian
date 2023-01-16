/**
 * Push v2
 *
 * @author Xtraball SAS <dev@xtraball.com>
 * @version 1.0.0
 */
angular
    .module('starter')
    .factory('Push2', function (Application, Pages, $timeout, $stateParams, $pwaRequest) {

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

        factory.registerPlayer = function (player) {
            if (!this.value_id) {
                $pwaRequest.reject('[Factory::Push.registerPlayer] missing value_id');
            }

            return $pwaRequest.post('push2/mobile_list/register-player', {
                urlParams: {
                    value_id: this.value_id,
                },
                data: {
                    device_uid: $session.getDeviceUid(),
                    player_id: player
                },
                cache: false
            });
        };

        // @overview, important!
        factory.getSample = function () {
            if (!this.value_id) {
                $pwaRequest.reject('[Factory::Push.getSample] missing value_id');
            }

            return $pwaRequest.get('push2/mobile_list/get-sample', {
                urlParams: {
                    value_id: this.value_id,
                }
            });
        };

        factory.findAll = function () {
            if (!this.value_id) {
                $pwaRequest.reject('[Factory::Push.findAll] missing value_id');
            }

            return $pwaRequest.get('push2/mobile_list/find-all', {
                urlParams: {
                    value_id: this.value_id,
                }
            });
        };

        return factory;
    });
