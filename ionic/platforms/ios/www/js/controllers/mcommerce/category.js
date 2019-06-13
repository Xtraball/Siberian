/**
 * M-Commerce
 *
 * @author Xtraball SAS <dev@xtraball.com>
 * @version 4.16.11
 */
angular
.module("starter")
.controller("MCommerceListController", function (Loader, $location, $scope, $state, $stateParams, McommerceCategory,
                                                 McommerceCart, Customer) {
    $scope.is_loading = true;
    Loader.show();

    $scope.factory = McommerceCategory;
    $scope.collection = [];
    $scope.collection_is_empty = true;

    McommerceCategory.value_id = $stateParams.value_id;
    McommerceCategory.category_id = $stateParams.category_id;
    $scope.value_id = $stateParams.value_id;

    $scope.use_button_header = false;
    if (Customer.isLoggedIn() && !$stateParams.category_id) {
        $scope.use_button_header = true;
    }

    $scope.loadContent = function () {
        McommerceCategory
        .findAll()
        .then(function (data) {
            $scope.show_search = data.show_search;
            $scope.collection = data.collection;
            $scope.collection_is_empty = $scope.collection.length > 0;

            $scope.cover = data.cover;
            $scope.page_title = data.page_title;
        }).then(function () {
            $scope.is_loading = false;
            Loader.hide();
        });

        McommerceCart.value_id = $stateParams.value_id;
        McommerceCart
        .find()
        .then(function (data) {
            try {
                $scope.cartItems = data.cart.lines.length;
            } catch (e) {
                $scope.cartItems = 0;
            }
        });
    };

    $scope.openCart = function () {
        if (!$scope.is_loading) {
            $state.go('mcommerce-cart-view', {
                value_id: $scope.value_id
            });
        }
    };

    $scope.openHistory = function () {
        if (!$scope.is_loading) {
            $state.go('mcommerce-sales-history', {
                value_id: $scope.value_id
            });
        }
    };

    if (!$scope.use_button_header) {
        $scope.right_button = {
            action: $scope.openCart,
            icon: 'ion-ios-cart'
        };
    }

    $scope.showItem = function (item) {
        $location.path(item.url);
    };

    $scope.loadContent();
}).controller('MCommerceRedirectController', function ($ionicHistory, $scope, $state, $stateParams, HomepageLayout) {
    angular.extend($scope, {
        value_id: $stateParams.value_id,
        layout: HomepageLayout
    });

    $state
    .go('home')
    .then(function () {
        if ($scope.layout.properties.options.autoSelectFirst) {
            $ionicHistory.nextViewOptions({
                historyRoot: true,
                disableAnimate: false
            });
        }
        $state.go('mcommerce-category-list', {
            value_id: $scope.value_id
        });
    });
});
