/**
 * Set Meal
 *
 * @author Xtraball SAS
 * @version 4.17.0
 */
angular
.module('starter')
.controller('SetMealViewController', function ($ionicHistory, $scope, $stateParams, Loader, SetMeal) {
    angular.extend($scope, {
        is_loading: false,
        value_id: $stateParams.value_id
    });

    SetMeal.setValueId($stateParams.value_id);
    SetMeal.setSetMealId($stateParams.set_meal_id);

    $scope.loadContent = function () {
        Loader.show();

        SetMeal.getSetMeal()
        .then(function (set_meal) {
            $scope.set_meal = set_meal;
            $scope.page_title = set_meal.name;
        }, function () {
            $ionicHistory.goBack();
        }).then(function () {
            Loader.hide();
        });
    };

    $scope.loadContent();
});