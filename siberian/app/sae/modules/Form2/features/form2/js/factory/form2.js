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
            return $pwaRequest.post('/form2/mobile/submit', {
                urlParams: {
                    value_id: factory.value_id
                },
                data: {
                    form: form,
                },
                cache: false
            });
        };

        return factory;
    });
