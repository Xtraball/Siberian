angular.module('starter')
.controller('CabrideMyPayments', function ($rootScope, $scope, $filter, $translate, $ionicScrollDelegate,
                                           Cabride, CabrideUtils, Dialog, PaymentMethod) {
    angular.extend($scope, {
        isLoading: false,
        pageTitle: $translate.instant('My payments', 'cabride'),
        valueId: Cabride.getValueId(),
        filtered: null,
        filterName: 'payments',
        payments: [],
        cards: [],
        cardActions: [
            PaymentMethod.ACTION_DELETE
        ]
    });

    $scope.cs = function () {
        return Cabride.currencySymbol();
    };

    $scope.loadPage = function () {
        $scope.isLoading = true;
        Cabride
        .getMyPayments()
        .then(function (payload) {
            $scope.payments = payload.payments;
        }, function (error) {
            Dialog.alert('Error', error.message, 'OK', -1, 'cabride');
        }).then(function () {
            $scope.isLoading = false;
        });
    };

    $scope.creditCardBrand = function (brand) {
        switch (brand.toLowerCase()) {
            case 'visa':
                return './features/cabride/assets/templates/images/011-cc-visa.svg';
            case 'mastercard':
                return './features/cabride/assets/templates/images/012-cc-mastercard.svg';
            case 'american express':
                return './features/cabride/assets/templates/images/013-cc-amex.png';
        }
        return './features/cabride/assets/templates/images/014-cc.svg';
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
        $rootScope.$broadcast("paymentMethod.refresh");
        $scope.loadPage();
    };

    $scope.statusFilter = function (filter) {
        // 'payments', 'cards'
        switch (filter) {
            case 'payments':
            case 'cards':
                $scope.filterName = filter;
                break;
        }
        $ionicScrollDelegate.scrollTop();
    };

    $scope.$on('cabride.updateRequest', function (event, request) {
        $scope.refresh();
    });

    $scope.loadPage();
});