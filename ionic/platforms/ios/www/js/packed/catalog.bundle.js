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
;/* global
    App, angular
 */

/**
 * Catalog
 *
 * @author Xtraball SAS
 */
angular.module('starter').factory('Catalog', function ($pwaRequest) {
    var factory = {
        value_id: null,
        last_category: null,
        collection: [],
        extendedOptions: {}
    };

    /**
     *
     * @param value_id
     */
    factory.setValueId = function (value_id) {
        factory.value_id = value_id;
    };

    /**
     *
     * @param category
     */
    factory.setLastCategory = function (category) {
        factory.last_category = category;
    };

    /**
     */
    factory.getLastCategory = function () {
        return factory.last_category;
    };

    /**
     *
     * @param options
     */
    factory.setExtendedOptions = function (options) {
        factory.extendedOptions = options;
    };

    /**
     * Pre-Fetch feature.
     */
    factory.preFetch = function () {
        factory.findAll();
    };

    factory.findAll = function (refresh) {
        if (!this.value_id) {
            $pwaRequest.reject('[Factory::Catalog.findAll] missing value_id');
        }

        return $pwaRequest.get('catalog/mobile_category_list/findall',
            angular.extend({
                urlParams: {
                    value_id: this.value_id
                },
                refresh: refresh
            }, factory.extendedOptions)
        );
    };

    factory.find = function (product_id) {
        if (!this.value_id) {
            $pwaRequest.reject('[Factory::Catalog.find] missing value_id');
        }

        return $pwaRequest.get('catalog/mobile_category_product_view/find', {
            urlParams: {
                value_id: this.value_id,
                product_id: product_id
            }
        });
    };

    /**
     * Search for product payload inside cached collection
     *
     * @param product_id
     * @returns {*}
     */
    factory.getProduct = function (product_id) {
        if (!this.value_id) {
            return $pwaRequest.reject('[Factory::Catalog.getProduct] missing value_id');
        }

        var product = _.get(_.filter(factory.collection, function (product) {
            return (product.id == product_id);
        })[0], 'embed_payload', false);

        if (!product) {
            /** Well then fetch it. */
            return factory.find(product_id);
        }

        return $pwaRequest.resolve(product);
    };

    return factory;
});
;/*global
 App, angular, BASE_PATH
 */

angular.module("starter").controller("SetMealListController", function($filter, $scope, $state, $stateParams, $timeout,
                                                                       SetMeal) {

    angular.extend($scope, {
        is_loading          : true,
        value_id            : $stateParams.value_id,
        displayed_per_page  : 10,
        load_more           : false,
        use_pull_refresh    : true,
        pull_to_refresh     : false,
        card_design         : false
    });

    SetMeal.setValueId($stateParams.value_id);

    $scope.loadContent = function(loadMore) {

        $scope.is_loading = true;

        SetMeal.findAll(SetMeal.collection.length, false)
            .then(function(data) {
                if(data.collection.length) {
                    SetMeal.collection              = SetMeal.collection.concat(data.collection);
                    $scope.collection               = SetMeal.collection;
                    $scope.collection_chunks        = $filter("chunk")($scope.collection, 2);
                } else {
                    $scope.collection               = SetMeal.collection;
                    $scope.collection_chunks        = $filter("chunk")($scope.collection, 2);
                }

                $scope.displayed_per_page = data.displayed_per_page;
                $scope.page_title         = data.page_title;

                return data;

            }).then(function(data) {
                if(loadMore) {
                    $scope.$broadcast('scroll.infiniteScrollComplete');
                }

                $scope.is_loading = false;

                $timeout(function() {
                    $scope.load_more = (data.collection.length === $scope.displayed_per_page);

                }, 250);
            });

    };

    $scope.loadMore = function() {
        $scope.loadContent(true);
    };

    $scope.pullToRefresh = function() {
        $scope.pull_to_refresh  = true;
        $scope.load_more        = false;

        SetMeal.findAll(0, true)
            .then(function(data) {

                if(data.collection) {
                    SetMeal.collection = data.collection;
                    $scope.collection  = SetMeal.collection;
                }

                $scope.load_more = (data.collection.length === data.displayed_per_page);

            }).then(function() {
                $scope.$broadcast('scroll.refreshComplete');
                $scope.pull_to_refresh = false;
            });
    };

    $scope.showItem = function(item) {
        $state.go("set-meal-view", {
            value_id: $scope.value_id,
            set_meal_id: item.id
        });
    };

    $scope.loadContent(false);

}).controller('SetMealViewController', function($ionicHistory, $scope, $stateParams, Loader, SetMeal) {

    angular.extend($scope, {
        is_loading: false,
        value_id: $stateParams.value_id
    });

    SetMeal.setValueId($stateParams.value_id);
    SetMeal.setSetMealId($stateParams.set_meal_id);

    $scope.loadContent = function() {

        Loader.show();

        SetMeal.getSetMeal()
            .then(function(set_meal) {
                $scope.set_meal     = set_meal;
                $scope.page_title   = set_meal.name;

            }, function() {
                $ionicHistory.goBack();

            }).then(function() {

                Loader.hide();
            });

    };

    $scope.loadContent();

});;/*global
 App, device, angular
 */

/**
 * SetMeal
 *
 * @author Xtraball SAS
 */
angular.module("starter").factory("SetMeal", function($pwaRequest) {

    var factory = {
        value_id            : null,
        set_meal_id         : null,
        displayed_per_page  : null,
        extendedOptions     : {},
        collection          :[]
    };

    /**
     *
     * @param value_id
     */
    factory.setValueId = function(value_id) {
        factory.value_id = value_id;
    };

    /**
     *
     * @param value_id
     */
    factory.setSetMealId = function(set_meal_id) {
        factory.set_meal_id = set_meal_id;
    };

    /**
     *
     * @param options
     */
    factory.setExtendedOptions = function(options) {
        factory.extendedOptions = options;
    };

    /**
     * Pre-Fetch feature.
     *
     * @param page
     */
    factory.preFetch = function(page) {
        factory.findAll();
    };

    factory.findAll = function(offset, refresh) {

        if(!this.value_id) {
            return $pwaRequest.reject("[Factory::SetMeal.findAll] missing value_id");
        }

        return $pwaRequest.get("catalog/mobile_setmeal_list/findall", angular.extend({
            urlParams: {
                value_id    : this.value_id,
                offset      : offset
            },
            refresh: refresh
        }, factory.extendedOptions));
    };

    /**
     * Fallback for direct call.
     *
     * @param set_meal_id
     */
    factory.find = function(set_meal_id) {

        if(set_meal_id === undefined) {
            set_meal_id = factory.set_meal_id;
        }

        if(!this.value_id && !set_meal_id) {
            return $pwaRequest.reject("[Factory::SetMeal.find] missing value_id or set_meal_id");
        }

        return $pwaRequest.get("catalog/mobile_setmeal_view/find", {
            urlParams: {
                value_id        : this.value_id,
                set_meal_id     : set_meal_id
            }
        });
    };

    /**
     * Search for set meal payload inside cached collection
     *
     * @param set_meal_id
     * @returns {*}
     */
    factory.getSetMeal = function() {

        if(!this.value_id) {
            return $pwaRequest.reject("[Factory::SetMeal.getSetMeal] missing value_id");
        }

        var set_meal = _.get(_.filter(factory.collection, function(set_meal) {
            return (set_meal.id == factory.set_meal_id);
        })[0], "embed_payload", false);

        if(!set_meal) {
            /** Well then fetch it. */
            return factory.find();

        } else {

            return $pwaRequest.resolve(set_meal);
        }
    };

    return factory;
});
