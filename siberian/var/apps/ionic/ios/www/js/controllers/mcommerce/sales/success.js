/*global
    App, BASE_PATH
 */

angular.module("starter").controller('MCommerceSalesSuccessViewController', function ($scope, $state, $stateParams,
                                                                                      $timeout, Customer) {

    $scope.value_id = $stateParams.value_id;

    if(Customer.guest_mode) {
        Customer.guest_mode = false;
        Customer.logout();
    }

    $timeout(function() {
        $state.go("mcommerce-redirect", {
            value_id: $scope.value_id
        });
    }, 3000);

});