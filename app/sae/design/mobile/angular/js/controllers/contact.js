App.config(function($routeProvider) {

    $routeProvider.when(BASE_URL+"/contact/mobile_view/index/value_id/:value_id", {
        controller: 'ContactViewController',
        templateUrl: BASE_URL+"/contact/mobile_view/template",
        code: "contact"
    }).when(BASE_URL+"/contact/mobile_form/index/value_id/:value_id", {
        controller: 'ContactFormController',
        templateUrl: BASE_URL+"/contact/mobile_form/template",
        code: "contact"
    });

}).controller('ContactViewController', function($window, $scope, $routeParams, $location, Application, Url, Contact) {

    $scope.is_loading = true;
    $scope.value_id = Contact.value_id = $routeParams.value_id;

    Contact.find().success(function(data) {
        $scope.contact = data.contact;
        $scope.contact.handle_geo_protocol = Application.handle_geo_protocol;
        $scope.page_title = data.page_title;
    }).finally(function() {
        $scope.is_loading = false;
    });

    $scope.getGeoData = function() {

        var params = "";
        if($scope.contact.coordinates) {
            params = $scope.contact.coordinates.latitude + "," + $scope.contact.coordinates.longitude;
            params += "?q="+$scope.contact.coordinates.latitude + "," + $scope.contact.coordinates.longitude;
        } else {
            var address = $scope.contact.street+", "+$scope.contact.postcode+", "+$scope.contact.city;
            params = "0,0?q="+encodeURI(address);
        }

        return params;
    };
    $scope.showMap = function() {

        var params = {};
        if($scope.contact.coordinates) {
            params.latitude = $scope.contact.coordinates.latitude;
            params.longitude = $scope.contact.coordinates.longitude;
        } else {
            var address = $scope.contact.street+", "+$scope.contact.postcode+", "+$scope.contact.city;
            params.address = encodeURI(address);
        }

        if($scope.contact.name) params.title = $scope.contact.name;
        params.value_id = $scope.value_id;

        $location.path(Url.get("map/mobile_view/index", params));

    };

    if($scope.isOverview) {

        $window.setCoverUrl = function(cover_url) {
            $scope.contact.cover_url = cover_url;
            $scope.$apply();
        };

        $window.setAttribute = function(attribute, value) {
            $scope.contact[attribute] = value;
            $scope.$apply();
        };

        $scope.$on("$destroy", function() {
            $window.setCoverUrl = null;
            $window.setAttribute = null;
        });
    }

}).controller('ContactFormController', function($window, $scope, $routeParams, Contact, Message) {

    $scope.form = {};
    $scope.is_loading = false;
    $scope.value_id = Contact.value_id = $routeParams.value_id;

    $scope.postForm = function() {
        $scope.contactForm.submitted = true;
        if ($scope.contactForm.$valid) {
            Contact.post($scope.form).success(function(data) {

                $scope.message = new Message();
                $scope.message.setText(data.message)
                    .show()
                ;

            }).error(function(data) {
                if(data && angular.isDefined(data.message)) {
                    $scope.message = new Message();
                    $scope.message.isError(true)
                        .setText(data.message)
                        .show()
                    ;
                }

            }).finally();
        }
    }

});