/**
 * Form version 2 factory
 */
angular
    .module('starter')
    .factory('Form2', function ($pwaRequest) {
        var factory = {
            value_id: null
        };

        factory.setValueId = function (valueId) {
            factory.value_id = valueId;

            return factory;
        };

        factory.getValueId = function () {
            return factory.value_id;
        };

        factory.validateRequest = function (form) {
            return $pwaRequest.post('form2/mobile/submit', {
                urlParams: {
                    value_id: factory.value_id
                },
                data: {
                    form: form,
                },
                cache: false
            });
        };

        factory.find = function () {
            if (!this.value_id) {
                return $pwaRequest.reject('[Factory::Form2.find] missing value_id');
            }

            var payload = $pwaRequest.getPayloadForValueId(factory.value_id);
            if (payload !== false) {
                return $pwaRequest.resolve(payload);
            }

            // Otherwise fallback on PWA!
            return $pwaRequest.get('form2/mobile/find', angular.extend({
                urlParams: {
                    value_id: this.value_id
                }
            }, factory.extendedOptions));
        };

        factory.reloadOverview = function () {
            // enforce fresh content
            return $pwaRequest.post('form2/mobile/find', angular.extend({
                urlParams: {
                    value_id: this.value_id
                }
            }, factory.extendedOptions));
        };

        factory.submit = function (form) {
            if (!this.value_id) {
                return $pwaRequest.reject('[Factory::Form2.post] missing value_id');
            }

            return $pwaRequest.post('form2/mobile/submit', {
                urlParams: {
                    value_id: this.value_id
                },
                data: {
                    timestamp: Math.round(Date.now() / 1000),
                    form: form
                },
                timeout: 300000,
                cache: false
            });
        };

        return factory;
    });
