/* global
    App, angular, BASE_PATH, IS_NATIVE_APP
 */
angular.module('starter').controller('BookingController', function ($scope, $stateParams, Booking, Customer,
                                                                    Dialog, Loader) {
    angular.extend($scope, {
        is_loading: false,
        value_id: $stateParams.value_id,
        use_pull_refresh: false,
        formData: {},
        people: [],
        card_design: false
    });

    Booking.setValueId($stateParams.value_id);

    var length = 1;
    while (length <= 20) {
        $scope.people.push(length);
        length = length + 1;
    }

    $scope.loadContent = function () {
        $scope.is_loading = true;

        Booking.findStores()
            .then(function (data) {
                $scope.populate(data);

                if (Customer.isLoggedIn()) {
                    $scope.formData.name = Customer.customer.firstname + ' ' + Customer.customer.lastname;
                    $scope.formData.email = Customer.customer.email;
                }
            }).then(function () {
                $scope.is_loading = false;
            });
    };

    $scope.populate = function (data) {
        $scope.stores = data.stores;
        $scope.page_title = data.page_title;
    };

    $scope.clearForm = function () {
        $scope.formData = {};
    };

    $scope.submitForm = function () {
        Loader.show();

        Booking
            .submitForm($scope.formData)
            .then(function (data) {
                Dialog.alert('Success', data.message, 'OK');
                // Reset form on success!
                $scope.formData = {};
            }, function (data) {
                Dialog.alert('Error', data.message, 'OK');
            }).then(function () {
                Loader.hide();
            });
    };

    $scope.loadContent();
});
;/* global
    App, angular
 */

/**
 * Booking
 *
 * @author Xtraball SAS
 */
angular.module('starter').factory('Booking', function ($pwaRequest) {
    var factory = {
        value_id: null,
        cache_key: null,
        cache_key_prefix: 'feature_booking_',
        extendedOptions: {}
    };

    /**
     *
     * @param value_id
     */
    factory.setValueId = function (value_id) {
        factory.value_id = value_id;
        factory.cache_key = factory.cache_key_prefix + value_id;
    };

    /**
     *
     * @param options
     */
    factory.setExtendedOptions = function (options) {
        factory.extendedOptions = options;
    };

    factory.findStores = function () {
        if (!this.value_id) {
            return $pwaRequest.reject('[Factory::Booking.findStores] missing value_id');
        }

        var payload = $pwaRequest.getPayloadForValueId(factory.value_id);
        if (payload !== false) {
            return $pwaRequest.resolve(payload);
        }

            /** Otherwise fallback on PWA */
            return $pwaRequest.get('booking/mobile_view/find',
                angular.extend({
                    urlParams: {
                        value_id: this.value_id
                    }
                }, factory.extendedOptions)
            );
    };

    factory.submitForm = function (form) {
        if (!this.value_id) {
            return $pwaRequest.reject('[Factory::Booking.submitForm] missing value_id');
        }

        var data = {};
        for (var prop in form) {
            data[prop] = form[prop];
        }

        data.value_id = this.value_id;

        if (data.date) {
            var date = new Date(data.date);
            var zeroPad = function (e) {
                return ('00' + e).slice(-2);
            };
            // Send date with unknown timezone (timezone will be replaced server side)!
            data.date = date.getFullYear()+ '-' +
                zeroPad(date.getMonth()+1) + '-' +
                zeroPad(date.getDate()) + 'T' +
                zeroPad(date.getHours()) + ':' +
                zeroPad(date.getMinutes()) + ':' +
                zeroPad(date.getSeconds()) + '-00:00';
        }

        return $pwaRequest.post('booking/mobile_view/post', {
            urlParams: {
                value_id: this.value_id
            },
            data: data,
            cache: false
        });
    };

    return factory;
});
