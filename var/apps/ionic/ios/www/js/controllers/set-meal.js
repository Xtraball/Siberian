/*global
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

});