/* global
    angular, BASE_PATH
 */

angular.module('starter').controller('PadlockController', function ($scope, $stateParams, $q, Customer, Dialog,
                                                                    Padlock, Pages) {
    angular.extend($scope, {
        is_loading: true,
        is_logged_in: Customer.isLoggedIn()
    });

    Padlock.value_id = $stateParams.value_id;

    var padlocks = _.filter(Pages.data.pages, function (feature) {
        return (feature.code === 'padlock');
    });

    if (padlocks.length >= 1) {
        var padlock = padlocks[0];
        if (padlock.value_id !== Padlock.value_id) {
            $stateParams.value_id = padlock.value_id;
            Padlock.value_id = $stateParams.value_id;
        }
    }

    if (Padlock.value_id !== 0) {
        var promise1 = Padlock.find();
        promise1
            .then(function (data) {
                $scope.page_title = data.page_title;
                $scope.description = data.description;

                return data;
            });

        var promise2 = Padlock.findUnlockTypes();
        promise2
            .then(function (data) {
                $scope.unlock_by_account_type = !!data.unlock_by_account;
                $scope.unlock_by_qrcode_type = !!data.unlock_by_qrcode;

                return data;
            });

        $q.all([promise1, promise2])
            .then(function () {
                $scope.is_loading = false;
            });
    } else {
        Dialog.alert('Error', 'Unable to find any Padlock feature!', 'OK', -1);
        $scope.is_loading = false;
    }
});
