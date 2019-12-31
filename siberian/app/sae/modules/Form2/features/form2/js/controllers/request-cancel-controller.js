angular.module('starter')
.controller('RequestCancelController', function ($scope, $rootScope, $translate, Cabride, Dialog, Loader) {

    angular.extend($scope, {
        cancel: {
            reason: null,
            message: ""
        }
    });

    $scope.submit = function () {
        if ($scope.cancel.reason === null) {
            Dialog.alert("Error", "You must select a reason!", "OK", 2350, "cabride");
            return;
        }

        if ($scope.cancel.reason === "other" &&
            $scope.cancel.message.length < 15) {
            Dialog.alert("Error", "You must leave a message, at least 15 characters long!", "OK", 2350, "cabride");
            return;
        }

        Loader.show();

        if ($scope.userType === "driver") {
            Cabride
            .cancelRideDriver($scope.request.request_id, $scope.cancel)
            .then(function (payload) {
                Loader.hide();
                Cabride.updateRequest($scope.request);
                Dialog
                .alert("Thanks", payload.message, "OK", 3500, "cabride")
                .then(function () {
                    $scope.close();
                });
            }, function (error) {
                Loader.hide();
                Dialog
                .alert("Sorry", error.message, "OK", 3500, "cabride");
            });
        } else {
            Cabride
            .cancelRide($scope.request.request_id, $scope.cancel)
            .then(function (payload) {
                Loader.hide();
                Cabride.updateRequest($scope.request);
                Dialog
                .alert("Thanks", payload.message, "OK", 3500, "cabride")
                .then(function () {
                    $scope.close();
                });
            }, function (error) {
                Loader.hide();
                Dialog
                .alert("Sorry", error.message, "OK", 3500, "cabride");
            });
        }

    };
});