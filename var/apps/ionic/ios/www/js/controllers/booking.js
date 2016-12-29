App.config(function($stateProvider) {

    $stateProvider.state('booking-view', {
        url: BASE_PATH+"/booking/mobile_view/index/value_id/:value_id",
        controller: 'BookingController',
        templateUrl: "templates/booking/l1/view.html"
    });

}).controller('BookingController', function($rootScope, $scope, $stateParams, $timeout, $translate, Booking, Dialog) {

    $scope.$on("connectionStateChange", function(event, args) {
        if(args.isOnline == true) {
            $scope.loadContent();
        }
    });

    $scope.value_id = Booking.value_id = $stateParams.value_id;
    $scope.formData = {};
    $scope.people = new Array(); var length = 0;
    while (length < 20) $scope.people.push(length++);

    $scope.loadContent = function() {

        $scope.is_loading = true;

        Booking.findStores().success(function(data) {
            $scope.stores = data.stores;
            $scope.page_title = data.page_title;
        }).finally(function() {
            $scope.is_loading = false;
        });

    };

    $scope.postForm = function() {

        $scope.is_loading = true;

        Booking.post($scope.formData).success(function(data) {
            Dialog.alert("", data.message, $translate.instant("OK"));

            $scope.formData = {};

        }).error(function(data) {
            if(data && angular.isDefined(data.message)) {
                Dialog.alert($translate.instant("Error"), data.message, $translate.instant("OK"));
            }

        }).finally(function() {
            $scope.is_loading = false;
        });
    };

    $timeout(function() {
        $scope.loadContent();
    }, 3000);

});