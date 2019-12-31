angular.module('starter')
.controller('CabrideCancelled', function ($scope, $translate, $state, Cabride, CabrideUtils, Dialog, Loader) {
    angular.extend($scope, {
        isLoading: false,
        pageTitle: $translate.instant('Declined requests', 'cabride'),
        valueId: Cabride.getValueId(),
        collection: []
    });

    $scope.cs = function () {
        return Cabride.currencySymbol();
    };

    $scope.loadPage = function () {
        $scope.isLoading = true;
        Cabride
        .getCancelledRides()
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
        return CabrideUtils.toHHMM(request.duration);
    };

    $scope.calendar = function (timestampSeconds) {
        return moment(timestampSeconds * 1000).calendar();
    };

    $scope.expiration = function (request) {
        return moment().add(parseInt(request.expires_in, 10), "seconds").fromNow();
    };

    $scope.details = function (request) {
        Cabride.requestDetailsModal($scope.$new(true), request.request_id, "driver");
    };

    $scope.accept = function (request) {
        Loader.show();
        CabrideUtils
        .getDirectionWaypoints(
            Cabride.lastPosition,
            {
                latitude: request.from_lat,
                longitude: request.from_lng,
            },
            {
                latitude: request.to_lat,
                longitude: request.to_lng,
            },
            true
        ).then(function (route) {
            Cabride
            .acceptRide(request.request_id, route)
            .then(function (payload) {
                Cabride.updateRequest(request);
                Dialog
                .alert("", payload.message, "OK", 2350)
                .then(function () {
                    Loader.hide();
                    $state.go("cabride-accepted-requests");
                });
            }, function (error) {
                Dialog.alert("Error", error.message, "OK", -1, "cabride");
            }).then(function () {
                Loader.hide();
                $scope.refresh();
            });
        }, function (error) {
            Dialog.alert("Error", error[1], "OK", -1, "cabride");
            Loader.hide();
            $scope.refresh();
        });
    };

    $scope.refresh = function () {
        $scope.loadPage();
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