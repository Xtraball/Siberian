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
