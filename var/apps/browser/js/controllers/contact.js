App.config(function($stateProvider) {

    $stateProvider.state('contact-view', {
        url: BASE_PATH+'/contact/mobile_view/index/value_id/:value_id',
        templateUrl: 'templates/contact/l1/view.html',
        controller: 'ContactViewController'
    }).state("contact-form", {
        url: BASE_PATH+'/contact/mobile_form/index/value_id/:value_id',
        templateUrl: 'templates/contact/l1/form.html',
        controller: 'ContactFormController'
    }).state("contact-map", {
        url: BASE_PATH+'/contact/mobile_map/index/value_id/:value_id',
        templateUrl: 'templates/html/l1/maps.html',
        controller: 'ContactMapController'
    });

}).controller('ContactViewController', function($rootScope, $scope, $state, $stateParams, $window, Contact) {

    $scope.is_loading = true;
    $scope.value_id = Contact.value_id = $stateParams.value_id;

    Contact.find().success(function(data) {
        $scope.contact = data.contact;
        //$scope.contact.handle_geo_protocol = Application.handle_geo_protocol;
        $scope.page_title = data.page_title;
    }).finally(function() {
        $scope.is_loading = false;
    });

    $scope.call = function() {
        $window.location = "tel:"+$scope.contact.phone;
    };

    $scope.openForm = function() {
        if($rootScope.isOffline) {
            $rootScope.onlineOnly();
            return;
        }
        $state.go("contact-form", {value_id: $stateParams.value_id});
    };

    $scope.openLink = function(link) {
        if($rootScope.isOverview) {
            $rootScope.showMobileFeatureOnlyError();
            return;
        }
        $window.open(link, $rootScope.getTargetForLink(), "location=no");
    };

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
        if($rootScope.isOffline) {
            $rootScope.onlineOnly();
            return;
        }
        $state.go("contact-map", { value_id: $scope.value_id });
    };

    if($rootScope.isOverview) {

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

}).controller('ContactFormController', function($translate, $scope, $state, $stateParams, $timeout, $window, Contact, Dialog) {

    $scope.form = {};
    $scope.is_loading = false;
    $scope.value_id = Contact.value_id = $stateParams.value_id;
    $scope.form = {};

    $scope.postForm = function() {
        Contact.post($scope.form).success(function(data) {

            $scope.form = {};

            Dialog.alert("", data.message, $translate.instant("OK"));

            $timeout(function() {
                $state.go("contact-view", {value_id: $stateParams.value_id});
            }, 2000);

        }).error(function(data) {

            if(data && angular.isDefined(data.message)) {
                Dialog.alert($translate.instant("Error"), data.message, $translate.instant("OK"));
            }

        }).finally();
    }

}).controller('ContactMapController', function($scope, $stateParams, Contact, GoogleMaps) {

    //$scope.is_loading = true;
    $scope.value_id = Contact.value_id = $stateParams.value_id;

    Contact.find().success(function(data) {

        $scope.contact = data.contact;
        $scope.page_title = data.page_title;
        var address = $scope.contact.street + ", " + $scope.contact.postcode + ", " + $scope.contact.city;

        var marker = {
            title: data.contact.name + "<br />" + address,
            is_centered: true
        };

        if(data.contact.coordinates) {
            marker.latitude = data.contact.coordinates.latitude;
            marker.longitude = data.contact.coordinates.longitude;
        } else {
            marker.address = address;
        }

        if(data.contact.cover_url) {
            marker.icon = {
                url: data.contact.cover_url,
                width: 49,
                height: 49
            }
        }

        $scope.map_config = {
            markers: [marker]
        };

    }).finally(function() {
        $scope.is_loading = false;
    });

});
