App.config(function($stateProvider) {

    $stateProvider.state('places-list', {
        url: BASE_PATH+"/places/mobile_list/index/value_id/:value_id",
        controller: 'PlacesListController',
        templateUrl: "templates/html/l3/list.html"
    }).state('places-list-map', {
        url: BASE_PATH+"/cms/mobile_list_map/index/value_id/:value_id",
        controller: 'CmsListMapController',
        templateUrl: "templates/html/l1/maps.html"
    }).state('places-view', {
        url: BASE_PATH+"/cms/mobile_page_view/index/value_id/:value_id/page_id/:page_id",
        controller: 'CmsViewController',
        templateUrl: "templates/cms/page/l1/view.html"
    });

}).controller('PlacesListController', function ($cordovaGeolocation, $q, $scope, $state, $stateParams, $translate, Places) {

    $scope.$on("connectionStateChange", function(event, args) {
        if(args.isOnline == true) {
            $scope.loadContent();
        }
    });

    $scope.is_loading = true;
    $scope.value_id = Places.value_id = $stateParams.value_id;
    $scope.right_button = {
        icon: "ion-ios-location-outline",
        action: function() {
            $scope.goToMap();
        }
    };

    $scope.loadContent = function () {

        $cordovaGeolocation.getCurrentPosition({ enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }).then(function(position) {
            $scope.position = position.coords;
            $scope.loadPlaces();
        }, function() {
            $scope.loadPlaces();
        });

    };

    $scope.loadPlaces = function() {

        Places.findAll($scope.position).success(function (data) {
            $scope.page_title = data.page_title;
            $scope.collection = data.places.reduce(function (collection, place) {
                var item = {
                    id: place.id,
                    title: place.title,
                    subtitle: place.subtitle,
                    picture: place.picture,
                    url: place.url
                };
                collection.push(item);
                return collection;
            }, []);

        }).finally(function () {
            $scope.is_loading = false;
        });

    };

    $scope.goToMap = function () {
        $state.go("places-list-map", {value_id: $scope.value_id, page_id: $stateParams.page_id});
    };

    $scope.showItem = function(item) {
        $state.go("places-view", {value_id: $scope.value_id, page_id: item.id});
    };

    $scope.loadContent();

}).controller('CmsListMapController', function ($scope, $state, $stateParams, Places, GoogleMaps) {

    $scope.$on("connectionStateChange", function(event, args) {
        if(args.isOnline == true) {
            $scope.loadContent();
        }
    });

    $scope.is_loading = true;
    $scope.value_id = Places.value_id = $stateParams.value_id;

    $scope.loadContent = function () {

        Places.findAll().success(function(data) {

            $scope.page_title = data.page_title;
            $scope.collection = data.places;

            var markers = new Array();

            for(var i = 0; i < $scope.collection.length; i++) {

                var place = $scope.collection[i];

                var marker = {
                    title: place.title + "<br />" + place.address.address,
                    link: "#"+place.url
                };

                if(place.address.latitude && place.address.longitude) {
                    marker.latitude = place.address.latitude;
                    marker.longitude = place.address.longitude;
                } else {
                    marker.address = place.address.address;
                }

                if(place.picture) {
                    marker.icon = {
                        url: place.picture,
                        width: 70,
                        height: 44
                    }
                }

                markers.push(marker);
            }

            $scope.map_config = {
                markers: markers,
                bounds_to_marker: true
            };

        }).finally(function () {
            $scope.is_loading = false;
        });

    };

    $scope.loadContent();

    $scope.showItem = function(item) {
        $state.go("facebook-view", {value_id: $scope.value_id, post_id: item.id});
    };
});