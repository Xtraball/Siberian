angular.module('starter')
.controller('CabridePaymentHistory', function ($scope, $translate, $ionicScrollDelegate, Cabride,
                                               CabrideUtils, Dialog) {

    angular.extend($scope, {
        isLoading: false,
        pageTitle: $translate.instant("Payment history", "cabride"),
        valueId: Cabride.getValueId(),
        filtered: null,
        filterName: "credit-card",
        pendingPayouts: [],
        cashReturns: [],
        collections: [],
        allPayments: [],
    });

    $scope.cs = function () {
        return Cabride.currencySymbol();
    };

    $scope.loadPage = function () {
        $scope.isLoading = true;
        Cabride
        .getPaymentHistory()
        .then(function (payload) {
            $scope.collections = payload.collections;
            $scope.allPayments = payload.collections.allPayments;
            $scope.cashReturns = payload.cashReturns;
            $scope.pendingPayouts = payload.pendingPayouts;
            $scope.filtered = $scope.allPayments[$scope.filterName];
        }, function (error) {
            Dialog.alert("Error", error.message, "OK", -1, "cabride");
        }).then(function () {
            $scope.isLoading = false;
        });
    };

    $scope.dateFormat = function (timestampSeconds) {
        return moment(timestampSeconds * 1000).calendar();
    };

    $scope.isTaxiLayout = function () {
        return Cabride.isTaxiLayout;
    };

    $scope.openMenu = function () {
        CabrideUtils.openMenu();
    };

    $scope.calendar = function (timestampSeconds) {
        return moment(timestampSeconds * 1000).calendar();
    };

    $scope.refresh = function () {
        $scope.loadPage();
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

    $scope.creditCardBrand = function (brand) {
        var _brand = (brand === undefined) ? "" : brand.toLowerCase();
        switch (_brand) {
            case "visa":
                return "./features/cabride/assets/templates/images/011-cc-visa.svg";
            case "mastercard":
                return "./features/cabride/assets/templates/images/012-cc-mastercard.svg";
            case "american express":
                return "./features/cabride/assets/templates/images/013-cc-amex.png";
        }
        return "./features/cabride/assets/templates/images/014-cc.svg";
    };

    $scope.statusFilter = function (filter) {
        switch (filter) {
            case "credit-card":
                $scope.filterName = "credit-card";
                break;
            case "cash":
                $scope.filterName = "cash";
                break;
        }
        $scope.filtered = $scope.allPayments[$scope.filterName];
        $ionicScrollDelegate.scrollTop();
    };

    $scope.loadPage();
});
