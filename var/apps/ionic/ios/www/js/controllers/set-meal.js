App.config(function($stateProvider, HomepageLayoutProvider) {

    $stateProvider.state('set-meal-list', {
        url: BASE_PATH+"/catalog/mobile_setmeal_list/index/value_id/:value_id",
        controller: 'SetMealListController',
        templateUrl: function(param) {
            var layout_id = HomepageLayoutProvider.getLayoutIdForValueId(param.value_id);
            switch(layout_id) {
                case "2": layout_id = "l5"; break;
                case "3": layout_id = "l6"; break;
                case "1":
                default: layout_id = "l3";
            }
            return 'templates/html/'+layout_id+'/list.html';
        }
    }).state('set-meal-view', {
        url: BASE_PATH+"/catalog/mobile_setmeal_view/index/value_id/:value_id/set_meal_id/:set_meal_id",
        controller: 'SetMealViewController',
        templateUrl: "templates/catalog/setmeal/l1/view.html"
    });

}).controller('SetMealListController', function($filter, $scope, $state, $stateParams, SetMeal) {

    $scope.$on("connectionStateChange", function(event, args) {
        if(args.isOnline == true) {
            $scope.loadContent();
        }
    });

    $scope.is_loading = true;
    $scope.can_load_older_posts = true;
    $scope.collection = new Array();
    $scope.value_id = SetMeal.value_id = $stateParams.value_id;

    $scope.loadContent = function() {

        SetMeal.findAll($scope.collection.length).success(function(data) {
            $scope.collection = $scope.collection.concat(data.collection);
            $scope.collection_chunks = $filter("chunk")($scope.collection, 2);
            $scope.page_title = data.page_title;
            $scope.can_load_older_posts = data.collection.length > 0;
        }).finally(function() {
            $scope.is_loading = false;
            $scope.$broadcast('scroll.infiniteScrollComplete');
        });

    };

    $scope.loadMore = function() {
        $scope.loadContent();
    };

    $scope.showItem = function(item) {
        $state.go("set-meal-view", {value_id: $scope.value_id, set_meal_id: item.id});
    };

    $scope.loadContent();

}).controller('SetMealViewController', function($scope, $sbhttp, $stateParams, SetMeal/*, Application*/) {

    $scope.$on("connectionStateChange", function(event, args) {
        if(args.isOnline == true) {
            $scope.loadContent();
        }
    });

    $scope.is_loading = false;
    $scope.value_id = SetMeal.value_id = $stateParams.value_id;
    SetMeal.set_meal_id = $stateParams.set_meal_id;

    $scope.loadContent = function() {

        $scope.is_loading = true;

        SetMeal.find($stateParams.set_meal_id).success(function(set_meal) {

            $scope.set_meal = set_meal;
            $scope.page_title = set_meal.name;

            //if($scope.set_meal.social_sharing_active==1 && Application.handle_social_sharing) {
            //    $scope.header_right_button = {
            //        picto_url: Pictos.get("share", "header"),
            //        hide_arrow: true,
            //        action: function () {
            //            $scope.sharing_data = {
            //                "page_name": $scope.set_meal.name,
            //                "picture": $scope.set_meal.picture ? $scope.set_meal.picture : null,
            //                "content_url": null
            //            }
            //            Application.socialShareData($scope.sharing_data);
            //        },
            //        height: 25
            //    };
            //}

        }).finally($scope.showError).finally(function() {
            $scope.is_loading = false;
        });

    };

    $scope.loadContent();

});