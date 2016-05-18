App.config(function($routeProvider) {

    $routeProvider.when(BASE_URL+"/booking/mobile_view/index/value_id/:value_id", {
        controller: 'BookingController',
        templateUrl: BASE_URL+"/booking/mobile_view/template",
        code: "booking"
    });

}).controller('BookingController', function($rootScope, $scope, $routeParams, Message, Booking) {

    $scope.$on("connectionStateChange", function(event, args) {
        if(args.isOnline == true) {
            $scope.loadContent();
        }
    });

    $scope.is_loading = true;
    $scope.value_id = Booking.value_id = $routeParams.value_id;

    $scope.people = new Array(); var length = 20;
    while (length > 0) $scope.people[length] = length--;

    $scope.loadContent = function() {
        Booking.findStores().success(function(data) {
            $scope.stores = data.stores;
            $scope.page_title = data.page_title;

        }).finally(function() {
            $scope.is_loading = false;
        });
    };

    $scope.postForm = function() {

        $scope.bookingForm.submitted = true;

        if ($scope.bookingForm.$valid) {

            $rootScope.app_loader_is_visible = true;

            Booking.post($scope.form).success(function(data) {

                $scope.message = new Message();
                $scope.message.setText(data.message)
                    .show()
                ;

                $scope.bookingForm.submitted = false;
                $scope.form = {};

            }).error(function(data) {
                if(data && angular.isDefined(data.message)) {
                    $scope.message = new Message();
                    $scope.message.isError(true)
                        .setText(data.message)
                        .show()
                    ;
                }

            }).finally(function() {
                $rootScope.app_loader_is_visible = false;
            });
        }
    }

    $scope.loadContent();
});