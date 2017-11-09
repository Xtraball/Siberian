/*global
 App, BASE_PATH
 */
angular.module("starter").controller("MCommerceSalesErrorViewController", function ($scope, $state, $stateParams, $timeout) {

    $scope.value_id = $stateParams.value_id;

    $timeout(function() {
        $state.go("mcommerce-redirect", {value_id: $scope.value_id});
    }, 4000);

});