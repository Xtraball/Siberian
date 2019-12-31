angular.module('starter')
.controller('CabrideMyRides', function ($scope, $filter, $translate, $ionicScrollDelegate,
                                        Cabride, CabrideUtils, Dialog, $window) {
    angular.extend($scope, {
        isLoading: false,
        pageTitle: $translate.instant("My rides", "cabride"),
        valueId: Cabride.getValueId(),
        filtered: null,
        toRate: null,
        filterName: "inprogress",
        collection: []
    });

    $scope.cs = function () {
        return Cabride.currencySymbol();
    };

    $scope.loadPage = function () {
        $scope.isLoading = true;
        Cabride
        .getMyRides()
        .then(function (payload) {
            $scope.collection = payload.collection;
            $scope.filtered = $filter("cabrideStatusFilter")($scope.collection, $scope.filterName);
            $scope.toRate = $filter("cabrideStatusFilter")($scope.collection, "torate");
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
        return CabrideUtils.toHHMM(request.duration);
    };

    $scope.calendar = function (timestampSeconds) {
        return moment(timestampSeconds * 1000).calendar();
    };

    $scope.expiration = function (request) {
        return moment().add(parseInt(request.expires_in, 10), "seconds").fromNow();
    };

    $scope.eta = function (request) {
        // Ensure values are integers
        var duration = parseInt(request.eta_driver, 10) * 1000;
        return moment(duration).fromNow();
    };

    $scope.refresh = function () {
        $scope.loadPage();
    };

    $scope.canCancel = function (request) {
        return ["pending", "accepted"].indexOf(request.status) != -1;
    };

    $scope.callDriver = function (request) {
        $window.open("tel:" + request.driver_phone, "_system");
    };

    $scope.cancel = function (request) {
        Cabride.cancelModal(request, "client");
    };

    $scope.details = function (request) {
        Cabride.requestDetailsModal($scope.$new(true), request.request_id, "client");
    };

    $scope.imagePath = function (image) {
        if (image === "") {
            return IMAGE_URL + "app/local/modules/Cabride/resources/design/desktop/flat/images/no-route.jpg";
        }
        return IMAGE_URL + "images/application" + image;
    };

    $scope.statusFilter = function (filter) {
        // "pending", "accepted", "declined", "done", "aborted", "expired"
        if (filter === "inprogress") {
            $scope.filterName = "inprogress";
        } else if (filter === "torate") {
            $scope.filterName = "torate";
        } else if (filter === "archived") {
            $scope.filterName = "archived";
        }

        $ionicScrollDelegate.scrollTop();
    };

    $scope.rateCourse = function (request) {
        Cabride.rateCourseModal(request);
    };

    $scope.getRatingIcon = function(request, value) {
        return (request.course_rating >= value) ? 'ion-android-star' : 'ion-android-star-outline';
    };

    $scope.$watch("filterName", function () {
        $scope.filtered = $filter("cabrideStatusFilter")($scope.collection, $scope.filterName);
        $scope.toRate = $filter("cabrideStatusFilter")($scope.collection, "torate");
    });

    $scope.$on('cabride.updateRequest', function (event, request) {
        $scope.refresh();
    });

    $scope.loadPage();
});
