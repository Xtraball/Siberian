angular.module('starter')
.controller('CabrideAcceptedRequests', function ($scope, $translate, $state, Cabride, CabrideUtils, Dialog, Loader,
                                                 $window) {
    angular.extend($scope, {
        isLoading: false,
        pageTitle: $translate.instant('Accepted requests', 'cabride'),
        valueId: Cabride.getValueId(),
        showPassengerPhone: Cabride.settings.showPassengerPhone,
        collection: []
    });

    $scope.cs = function () {
        return Cabride.currencySymbol();
    };

    $scope.loadPage = function () {
        $scope.isLoading = true;
        Cabride
        .getAcceptedRides()
        .then(function (payload) {
            $scope.collection = payload.collection;
        }, function (error) {
            Dialog.alert("Error", error.message, 'OK', -1, 'cabride');
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
        var unit = Cabride.settings.distance_unit;
        var distance = request.distance / 1000;
        if (unit === 'mi') {
            return Math.ceil(distance) + ' mi';
        }
        return Math.ceil(distance) + ' Km';
    };

    $scope.duration = function (request) {
        return CabrideUtils.toHHMM(request.duration);
    };

    $scope.calendar = function (timestampSeconds) {
        return moment(timestampSeconds * 1000).calendar();
    };

    $scope.refresh = function () {
        $scope.loadPage();
    };

    $scope.driveToPassenger = function (request) {
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
            .driveToPassenger(request.request_id, route)
            .then(function (payload) {
                Cabride.updateRequest(request);
                Dialog
                .alert('', payload.message, 'OK', 2350)
                .then(function () {
                    Loader.hide();
                    Navigator.navigate(payload.driveTo);
                });
            }, function (error) {
                Dialog.alert('Error', error.message, 'OK', -1, 'cabride');
            }).then(function () {
                Loader.hide();
                $scope.refresh();
            });
        }, function (error) {
            Dialog.alert('Error', error[1], 'OK', -1, 'cabride');
            Loader.hide();
            $scope.refresh();
        });
    };

    $scope.driveToDestination = function (request) {
        Loader.show();
        Cabride
        .driveToDestination(request.request_id)
        .then(function (payload) {
            Cabride.updateRequest(request);
            Dialog
            .alert('', payload.message, 'OK', 2350)
            .then(function () {
                Loader.hide();
                Navigator.navigate(payload.driveTo);
            });
        }, function (error) {
            Dialog.alert('Error', error.message, 'OK', -1, 'cabride');
        }).then(function () {
            Loader.hide();
            $scope.refresh();
        });
    };

    $scope.complete = function (request) {
        Loader.show();
        Cabride
        .completeRide(request.request_id)
        .then(function (payload) {
            Cabride.updateRequest(request);
            Dialog
            .alert('', payload.message, 'OK', 2350)
            .then(function () {
                Loader.hide();
                $state.go('cabride-completed-rides');
            });
        }, function (error) {
            Dialog.alert('Error', error.message, 'OK', -1, 'cabride');
        }).then(function () {
            Loader.hide();
            $scope.refresh();
        });
    };

    $scope.callClient = function (request) {
        $window.open('tel:' + request.client_phone, '_system');
    };

    $scope.details = function (request) {
        Cabride.requestDetailsModal($scope.$new(true), request.request_id, 'driver');
    };

    $scope.imagePath = function (image) {
        if (image === '') {
            return IMAGE_URL + 'app/local/modules/Cabride/resources/design/desktop/flat/images/no-route.jpg';
        }
        return IMAGE_URL + 'images/application' + image;
    };

    $scope.$on('cabride.updateRequest', function (event, request) {
        $scope.refresh();
    });

    $scope.loadPage();
});