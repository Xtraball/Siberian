App.config(function($routeProvider) {

    $routeProvider.when(BASE_URL+"/catalog/mobile_setmeal_list/index/value_id/:value_id", {
        controller: 'SetMealListController',
        templateUrl: function(params) {
            return BASE_URL+"/catalog/mobile_setmeal_list/template/value_id/"+params.value_id;
        },
        code: "set_meal"
    }).when(BASE_URL+"/catalog/mobile_setmeal_view/index/value_id/:value_id/set_meal_id/:set_meal_id", {
        controller: 'SetMealViewController',
        templateUrl: function(params) {
            return BASE_URL+"/catalog/mobile_setmeal_view/template/value_id/"+params.value_id;
        },
        code: "set_meal"
    });

}).controller('SetMealListController', function($scope, $http, $routeParams, $location, SetMeal) {

    $scope.$on("connectionStateChange", function(event, args) {
        if(args.isOnline == true) {
            $scope.loadContent();
        }
    });

    $scope.is_loading = true;
    $scope.value_id = SetMeal.value_id = $routeParams.value_id;

    $scope.factory = SetMeal;
    $scope.collection = new Array();

    $scope.loadContent = function() {
        SetMeal.findAll().success(function(data) {
            $scope.collection = data.collection;
            $scope.page_title = data.page_title;
        }).error(function() {

        }).finally(function() {
            $scope.is_loading = false;
        });

    };

    $scope.showItem = function(item) {
        $location.path(item.url);
    };

    $scope.loadContent();

}).controller('SetMealViewController', function($scope, $http, $routeParams, SetMeal, Pictos, Application) {

    $scope.$on("connectionStateChange", function(event, args) {
        if(args.isOnline == true) {
            $scope.loadContent();
        }
    });

    $scope.is_loading = false;
    $scope.value_id = SetMeal.value_id = $routeParams.value_id;
    SetMeal.set_meal_id = $routeParams.set_meal_id;

    $scope.loadContent = function() {

        $scope.is_loading = true;

        SetMeal.find($routeParams.set_meal_id).success(function(set_meal) {
            $scope.set_meal = set_meal;

            if($scope.set_meal.social_sharing_active==1 && Application.handle_social_sharing) {
                $scope.header_right_button = {
                    picto_url: Pictos.get("share", "header"),
                    hide_arrow: true,
                    action: function () {
                        $scope.sharing_data = {
                            "page_name": $scope.set_meal.name,
                            "picture": $scope.set_meal.picture ? $scope.set_meal.picture : null,
                            "content_url": null
                        }
                        Application.socialShareData($scope.sharing_data);
                    },
                    height: 25
                };
            }

        }).error($scope.showError).finally(function() {
            $scope.is_loading = false;
        });

    }

    $scope.loadContent();

});