/**
 *
 */
angular
.module('starter')
.controller('CategoryProductViewController', function ($scope, $stateParams, Catalog) {
    angular.extend($scope, {
        is_loading: false,
        value_id: $stateParams.value_id,
        product_id: $stateParams.product_id,
        card_design: true
    });

    Catalog.setValueId($stateParams.value_id);

    $scope.loadContent = function () {
        $scope.is_loading = true;

        Catalog.getProduct($scope.product_id)
            .then(function (product) {
                $scope.product = product;
                $scope.page_title = product.name;
            }).then(function () {
                $scope.is_loading = false;
            });
    };

    $scope.loadContent();
});
