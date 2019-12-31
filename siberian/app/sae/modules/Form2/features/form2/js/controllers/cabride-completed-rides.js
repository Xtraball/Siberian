angular.module('starter')
.controller('CabrideCompletedRides', function ($scope, $translate, Cabride, CabrideUtils, Dialog) {
    angular.extend($scope, {
        isLoading: false,
        pageTitle: $translate.instant("Completed requests", "cabride"),
        valueId: Cabride.getValueId(),
        collection: []
    });

    $scope.cs = function () {
        return Cabride.currencySymbol();
    };

    $scope.loadPage = function () {
        $scope.isLoading = true;
        Cabride
        .getCompletedRides()
        .then(function (payload) {
            $scope.collection = payload.collection;
        }, function (error) {
            Dialog.alert("Error", error.message, "OK", -1, "cabride");
        }).then(function () {
            $scope.isLoading = false;
        });
    };

    $scope.isTaxiLayout = function () {
        return Cabride.isTaxiLayout;
    };

    $scope.openMenu = function () {
        CabrideUtils.openMenu();
    };

    $scope.distance = function (request) {
        return Math.ceil(request.distance / 1000) + "Km";
    };

    $scope.duration = function (request) {
        return $scope.toHHMM(request.duration);
    };

    $scope.calendar = function (timestampSeconds) {
        return moment(timestampSeconds * 1000).calendar();
    };

    $scope.refresh = function () {
        $scope.loadPage();
    };

    $scope.details = function (request) {
        Cabride.requestDetailsModal($scope.$new(true), request.request_id, "driver");
    };

    $scope.getRatingIcon = function(request, value) {
        return (request.course_rating >= value) ? 'ion-android-star' : 'ion-android-star-outline';
    };

    $scope.imagePath = function (image) {
        if (image === "") {
            return IMAGE_URL + "app/local/modules/Cabride/resources/design/desktop/flat/images/no-route.jpg";
        }
        return IMAGE_URL + "images/application" + image;
    };

    $scope.$on('cabride.updateRequest', function (event, request) {
        $scope.refresh();
    });

    $scope.loadPage();
});