angular.module('starter')
.controller('CabrideVehicleInformation', function ($scope, $translate, Cabride, CabrideUtils, Dialog, Loader) {
    angular.extend($scope, {
        isLoading: false,
        pageTitle: $translate.instant("Vehicle information", "cabride"),
        valueId: Cabride.getValueId(),
        pricingMode: Cabride.settings.pricingMode,
        settings: Cabride.settings,
        changingType: false,
        collection: []
    });

    $scope.cs = function () {
        return Cabride.currencySymbol();
    };

    $scope.loadPage = function () {
        $scope.isLoading = true;
        Cabride
        .getVehicleInformation()
        .then(function (payload) {
            $scope.vehicleTypes = payload.vehicleTypes;
            $scope.driver = payload.driver;
            $scope.currentType = payload.currentType;
        }, function (error) {
            Dialog.alert("Error", error.message, "OK", -1, "cabride");
        }).then(function () {
            $scope.isLoading = false;
        });
    };

    $scope.selectType = function (type) {
        Loader.show();
        Cabride
        .selectVehicleType(type.id)
        .then(function (payload) {
            $scope.driver = payload.driver;
            $scope.driver.vehicle_id = payload.currentType.vehicle_id;
            $scope.currentType = payload.currentType;
            $scope.changingType = false;
        }, function (error) {
            Dialog.alert("Error", error.message, "OK", -1, "cabride");
        }).then(function () {
            Loader.hide();
        });
    };

    $scope.isTaxiLayout = function () {
        return Cabride.isTaxiLayout;
    };

    $scope.openMenu = function () {
        CabrideUtils.openMenu();
    };

    $scope.changeType = function () {
        $scope.changingType = true;
    };

    $scope.cancelType = function () {
        $scope.changingType = false;
    };

    $scope.save = function () {
        Loader.show();
        Cabride
        .saveDriver($scope.driver)
        .then(function (payload) {
            $scope.driver = payload.driver;
            Dialog.alert("Saved!", payload.message, "OK", -1, "cabride");
        }, function (error) {
            Dialog.alert("Error", error.message, "OK", -1, "cabride");
        }).then(function () {
            Loader.hide();
        });
    };

    $scope.distanceUnit = function () {
        return Cabride.settings.distanceUnit;
    };

    $scope.pricingDriver = function () {
        return Cabride.settings.pricingMode === "driver";
    };

    $scope.imagePath = function (image) {
        if (image === "") {
            return IMAGE_URL + "app/local/modules/Cabride/resources/design/desktop/flat/images/car-icon.png";
        }
        return IMAGE_URL + "images/application" + image;
    };

    $scope.showFixedPricing = function () {
        return $scope.pricingMode === "fixed";
    };

    $scope.refresh = function () {
        $scope.loadPage();
    };

    $scope.loadPage();
});
