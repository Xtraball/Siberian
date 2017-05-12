App.config(function($stateProvider) {

    $stateProvider.state('places-list', {
        url: BASE_PATH+"/places/mobile_list/index/value_id/:value_id",
        controller: 'PlacesListController',
        cache: false,
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

}).controller('PlacesListController', function ($cordovaGeolocation, $q, $scope, $rootScope, $state, $stateParams, $translate, Places, Search) {
    /* Necessary to assure that  */
    $rootScope.$on('$stateChangeSuccess', function (event, toState, toParams, fromState, fromParams) {
        if (toState.name == "places-list" && fromState.name == "home") {
            $scope.loadContent();
        }
    });

    $scope.$on("connectionStateChange", function(event, args) {
        if(args.isOnline == true) {
            $scope.loadContent();
        }
    });


    $scope.setSearchPartName = function (part_name) {
        /* SEARCH, SEARCH_TEXT, SEARCH_TYPE, SEARCH_ADDRESS, SEARCH_AROUND_YOU */
        $scope.search_part_name = part_name;
    }

    $scope.findByAroundyou = function () {
        if (!$scope.search.aroundyou) {
            $scope.search.aroundyou = {
                latitude: $scope.position.latitude,
                longitude: $scope.position.longitude
            };
        } else {
            $scope.search.aroundyou = false;
        }
        $scope.loadPlaces();
    }

    $scope.findByTag = function (tag) {
        /* If the actual tag is selected then deselect tag, otherwise select the tag */
        $scope.search.type = ($scope.search.type == tag) ? '' : tag;
        $scope.loadPlaces();
    };

    $scope.getState = function () {
        if ($scope.is_loading) {
            return "LOADING";
        } else if (Array.isArray($scope.collection) && $scope.collection.length > 0) {
            return "RESULTS";
        } else {
            return "NO_RESULTS";
        }
    };

    /* Store search params */
    $scope.initSearch = function () {
        $scope.search = {
            'text': "",
            'type': "",
            'address': "",
            'aroundyou': false
        };
    };

    /*
     * Returns true if there are no search terms specified by the user
     * If true then load all places, otherwise search for terms
     */
    $scope.searchIsEmpty = function () {
        return $scope.search.text == "" && $scope.search.type == "" && $scope.search.address == "" && (!$scope.search.aroundyou);
    };

    $scope.clear = function () {
        //$scope.initSearch();
        $scope.loadPlaces();
        $scope.setSearchPartName('SEARCH');
    };

    $scope.is_loading = true;
    $scope.position = null;
    $scope.value_id = $stateParams.value_id;
    $scope.settings = null;

    //IMPORTANT! MCommerce and Places use same list template
    //This settings is here to make search in mcommerce available
    $scope.filter_search = "";

    /* Configuring the Search service */
    Search.setAgent(Places, $scope.value_id);
    Search.url = 'places/mobile_list/search';
    $scope.parameters = {
        'value_id': $stateParams.value_id
    };
    $scope.show_search_bar = false;

    $scope.value_id = Places.value_id = $stateParams.value_id;
    $scope.search_part_name = 'SEARCH';
    $scope.tag = null;

    $scope.right_button = {
        icon: "ion-ios-location-outline",
        action: function() {
            $scope.goToMap();
        }
    };

    $scope.loadContent = function () {
        /* Initialize search terms */
        $scope.initSearch();

        var noGeoLoc = function() {
            $scope.position = {latitude: 0, longitude: 0};
            $scope.parameters.latitude = 0;
            $scope.parameters.longitude = 0;

            $scope.loadSettings(false);
            $scope.loadPlaces();

        };

        if($rootScope.isOffline) {
            noGeoLoc();
        } else {
            $cordovaGeolocation.getCurrentPosition({ enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }).then(function(position) {
                $scope.position = {latitude: position.coords.latitude, longitude: position.coords.longitude};
                $scope.parameters.latitude = position.coords.latitude;
                $scope.parameters.longitude = position.coords.longitude;
                // Loading the settings for the search parameters is independent of places loading

                $scope.loadSettings(true);
                $scope.loadPlaces();

            }, noGeoLoc);
        }


    };

    $scope.loadSettings = function (search_ayou) {
        Places.settings().success(function (settings) {
            $scope.settings = settings;
            /* If the coordinates are not defined, then don't show the search by vicinity */
            if (!($scope.position.longitude && $scope.position.latitude) || $rootScope.isOffline) {
                $scope.settings.search_aroundyou_show = false;
            } else {
                $scope.settings.search_aroundyou_show = search_ayou && $scope.settings.search_aroundyou_show;
            }
            /* Only show search when at least one search method is activated */
            $scope.settings.showSearch = !$rootScope.isOffline &&
                ($scope.settings.search_address_show || $scope.settings.search_text_show ||
                 $scope.settings.search_type_show || $scope.settings.search_aroundyou_show);
        }).error(function (error) {
            $scope.settings = {};
        });
    };

    $scope.loadPlaces = function() {
        $scope.parameters.search = $scope.search;
        ($scope.searchIsEmpty() || $rootScope.isOffline ? Places.findAll($scope.position) : Search.findAll($scope.parameters)).success(function (data) {
            $scope.page_title = data.page_title;
            $scope.collection = data.places.reduce(function (collection, place) {
                var item = {
                    id: place.id,
                    title: place.title,
                    subtitle: place.subtitle,
                    picture: place.picture,
                    thumbnail: place.thumbnail,
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
        if($rootScope.isOffline) {
            $rootScope.onlineOnly();
            return;
        }

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
