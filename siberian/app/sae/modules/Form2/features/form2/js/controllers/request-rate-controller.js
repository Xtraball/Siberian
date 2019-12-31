angular.module('starter')
.controller('RequestRateController', function ($scope, $rootScope, $translate, Cabride, Dialog) {

    angular.extend($scope, {
        rating: {
            course: -1,
            driver: -1,
            comment: ""
        }
    });

    $scope.submit = function () {
        if ($scope.rating.course < 1) {
            Dialog.alert("Error", "You must rate the course before submitting!", "OK", 2350, "cabride");
            return;
        }
        Cabride
        .rateCourse($scope.request.request_id, $scope.rating)
        .then(function (success) {
            $rootScope.$broadcast("cabride.updateRequest");
            $scope.close();
        }, function (error) {
            Dialog.alert("Error", error.message, "OK", 2350, "cabride");
        });
    };

    $scope.setRating = function (target, value) {
        if (target === "course") {
            $scope.rating.course = value;
        } else {
            $scope.rating.driver = value;
        }
    };

    $scope.getIcon = function(target, value) {
        if (target === "course") {
            return ($scope.rating.course >= value) ? 'ion-android-star' : 'ion-android-star-outline';
        } else {
            return ($scope.rating.driver >= value) ? 'ion-android-star' : 'ion-android-star-outline';
        }
    };
});