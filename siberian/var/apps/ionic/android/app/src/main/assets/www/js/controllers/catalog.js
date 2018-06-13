/* global
    App, angular, BASE_PATH
*/
angular.module('starter').controller('CategoryListController', function ($filter, $scope, $state, $stateParams, Catalog) {
    angular.extend($scope, {
        is_loading: false,
        value_id: $stateParams.value_id,
        collection: [],
        use_pull_refresh: true,
        pull_to_refresh: false,
        card_design: false
    });

    Catalog.setValueId($stateParams.value_id);

    $scope.loadContent = function (pullToRefresh) {
        $scope.is_loading = true;

        Catalog.findAll(pullToRefresh)
            .then(function (data) {
                if (data.page_title) {
                    $scope.page_title = data.page_title;
                }

                $scope.categories = data.categories;

                $scope.tooltip = {
                    collection: $scope.categories,
                    current_item: $scope.categories[0],
                    button_label: $scope.categories[0] ? $scope.categories[0].name : null,
                    onItemClicked: function (category) {
                        $scope.showTooltip(category);
                    }
                };

                if ($scope.categories.length) {
                    var last_category = _.filter($scope.categories,
                        _.matches({
                            name: _.get(Catalog.getLastCategory(), 'name', null)
                        })
                    );

                    if (last_category.length === 1) {
                        $scope.showTooltip(last_category[0]);
                    } else {
                        var category = $scope.categories[0];
                        if (category.children) {
                            category = category.children[0];
                        }
                        $scope.current_category = category;
                        $scope.showTooltip($scope.current_category);
                    }
                }
            }).then(function () {
                if ($scope.pull_to_refresh) {
                    $scope.$broadcast('scroll.refreshComplete');
                    $scope.pull_to_refresh = false;
                }

                $scope.is_loading = false;
            });
    };

    $scope.pullToRefresh = function () {
        $scope.pull_to_refresh = true;
        $scope.loadContent(true);
    };

    $scope.showTooltip = function (category) {
        if (category.children) {
            category.show_children = !category.show_children;
        } else {
            $scope.collection = category.collection;
            $scope.collection_chunks = $filter('chunk')($scope.collection, 2);
            $scope.current_category = category;
            $scope.tooltip.current_item = $scope.current_category;
            $scope.tooltip.button_label = $scope.current_category.name;

            Catalog.collection = category.collection;
            Catalog.setLastCategory(category);
        }
    };

    $scope.showItem = function (item) {
        $state.go('catalog-product-view', {
            value_id: $scope.value_id,
            product_id: item.id
        });
    };

    $scope.loadContent(false);
}).controller('CategoryProductViewController', function ($scope, $stateParams, Catalog) {
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
