angular.module('starter').controller('PlacesListController', function (Location, $q, $scope, $rootScope, $state,
                                                                       $stateParams, $translate, $timeout, Places,
                                                                       Search) {
    angular.extend($scope, {
        is_loading: true,
        value_id: $stateParams.value_id,
        position: {
            longitude: 0,
            latitude: 0
        },
        settings: null,
        show_search_bar: false,
        search_part_name: 'SEARCH',
        tag: null,
        filter_search: '',
        parameters: {
            'value_id': $stateParams.value_id
        },
        collection: [],
        load_more: false,
        use_pull_refresh: true,
        pull_to_refresh: false,
        card_design: false,
        module_code: 'places'
    });

    Places.setValueId($stateParams.value_id);

    // @var $scope.filter_search
    // IMPORTANT! MCommerce and Places use same list template
    // This settings is here to make search in mcommerce available
    // important OK ... but named is better > mcommerce_search_filter ....

    $scope.setSearchPartName = function (partName) {
        /* SEARCH, SEARCH_TEXT, SEARCH_TYPE, SEARCH_ADDRESS, SEARCH_AROUND_YOU */
        $scope.search_part_name = partName;
    };

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
    };

    /**
     * If the actual tag is selected then deselect tag, otherwise select the tag
     *
     * @param tag
     */
    $scope.findByTag = function (tag) {
        $scope.search.type = ($scope.search.type === tag) ? '' : tag;
        $scope.loadPlaces();
    };

    /** What the fuck ? */
    $scope.getState = function () {
        if ($scope.is_loading) {
            return 'LOADING';
        } else if (Array.isArray($scope.collection) && $scope.collection.length > 0) {
            return 'RESULTS';
        }
        return 'NO_RESULTS';
    };

    /* Store search params */
    $scope.initSearch = function () {
        $scope.search = {
            'text': '',
            'type': '',
            'address': '',
            'aroundyou': false
        };
    };

    /*
     * Returns true if there are no search terms specified by the user
     * If true then load all places, otherwise search for terms
     */
    $scope.searchIsEmpty = function () {
        return (($scope.search.text === '') &&
                ($scope.search.type === '') &&
                ($scope.search.address === '') &&
                (!$scope.search.aroundyou));
    };

    $scope.clear = function () {
        $scope.loadPlaces();
        $scope.setSearchPartName('SEARCH');
    };


    /**
     * Configuring the Search service
     */
    Search.setAgent(Places, $scope.value_id);
    Search.url = 'places/mobile_list/searchv2';

    $scope.right_button = {
        icon: 'ion-ios-location-outline',
        action: function () {
            $scope.goToMap();
        }
    };

    /** is geolocation available ? */
    var search_ayou = true;

    Places.settings()
        .then(function (settings) {
            Location.getLocation()
                .then(function (position) {
                    $scope.position.latitude = position.coords.latitude;
                    $scope.position.longitude = position.coords.longitude;
                }, function (error) {
                    $scope.position.latitude = 0;
                    $scope.position.longitude = 0;
                }).then(function () {
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
                });
        });


    $scope.loadContent = function (loadMore) {
        /* Initialize search terms */
        $scope.initSearch();

        var noGeoLoc = function () {
            $scope.position = {
                latitude: 0, longitude: 0
            };
            $scope.parameters.latitude = 0;
            $scope.parameters.longitude = 0;

            $scope.loadPlaces(loadMore, false);
        };

        if ($rootScope.isOffline) {
            noGeoLoc();
        } else {
            Location.getLocation()
                .then(function (position) {
                $scope.position = {
                    latitude: position.coords.latitude,
                    longitude: position.coords.longitude
                };
                $scope.parameters.latitude = position.coords.latitude;
                $scope.parameters.longitude = position.coords.longitude;

                $scope.loadPlaces(loadMore, true);
            }, noGeoLoc);
        }
    };

    $scope.loadMore = function () {
        $scope.loadPlaces(true);
    };

    $scope.loadPlaces = function (loadMore) {
        $scope.is_loading = true;

        $scope.parameters.search = $scope.search;

        var offset = $scope.collection.length;

        $scope.parameters.offset = offset;

        // Clear collection.
        if (offset <= 0) {
            $scope.collection = [];
            Places.collection = [];
        }

        var resolver = null;
        if (($scope.searchIsEmpty() || $rootScope.isOffline)) {
            resolver = Places.findAll($scope.position, offset);
        } else {
            // Clear collection on search!
            $scope.collection = [];
            Places.collection = [];
            resolver = Search.findAll($scope.parameters);
        }

        resolver
            .then(function (data) {
                $scope.page_title = data.page_title;
                Places.collection = Places.collection.concat(angular.copy(data.places));
                $scope.reduce_collection = data.places.reduce(function (collection, place) {
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

                $scope.collection = $scope.collection.concat($scope.reduce_collection);

                $scope.load_more = (data.places.length > 0);
            }).then(function () {
                if (loadMore) {
                    $scope.$broadcast('scroll.infiniteScrollComplete');
                }

                $scope.is_loading = false;
            });
    };

    $scope.pullToRefresh = function () {
        $scope.pull_to_refresh = true;
        $scope.load_more = false;

        Places.findAll($scope.position, 0, true)
            .then(function (data) {
                if (data.collection) {
                    $scope.page_title = data.page_title;
                    Places.collection = angular.copy(data.places);
                    $scope.reduce_collection = data.places.reduce(function (collection, place) {
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

                    $scope.collection = $scope.collection.concat($scope.reduce_collection);
                }

                $scope.load_more = (data.places.length >= data.displayed_per_page);
            }).then(function () {
                $scope.$broadcast('scroll.refreshComplete');
                $scope.pull_to_refresh = false;

                $timeout(function () {
                    $scope.can_load_older_posts = !!$scope.collection.length;
                }, 500);
            });
    };

    $scope.goToMap = function () {
        if ($rootScope.isNotAvailableOffline()) {
            return;
        }

        $state.go('places-list-map', {
            value_id: $scope.value_id,
            page_id: $stateParams.page_id
        });
    };

    $scope.showItem = function (item) {
        $state.go('places-view', {
            value_id: $scope.value_id,
            page_id: item.id,
            type: 'places'
        });
    };

    $scope.loadContent(false);
}).controller('CmsListMapController', function ($scope, $state, $stateParams, $translate, Places) {
    angular.extend($scope, {
        is_loading: true,
        value_id: $stateParams.value_id
    });

    Places.setValueId($stateParams.value_id);

    $scope.loadContent = function () {
        Places.findAllMaps()
            .then(function (data) {
                $scope.page_title = data.page_title;
                $scope.collection = data.places;

                var markers = [];

                for (var i = 0; i < $scope.collection.length; i = i + 1) {
                    var place = $scope.collection[i];

                    var marker = {
                        config: {
                            id: angular.copy(place.id)
                        },
                        title:
                            place.title + '<br />' +
                            place.address.address + '<br />' +
                            '<i class="ion-android-open"></i>&nbsp;' + $translate.instant('See details') + '</span>',
                        onClick: (function (config) {
                            $scope.goToPlace(config.id);
                        })
                    };

                    if (place.address.latitude && place.address.longitude) {
                        marker.latitude = place.address.latitude;
                        marker.longitude = place.address.longitude;
                    } else {
                        marker.address = place.address.address;
                    }

                    if (place.picture) {
                        marker.icon = {
                            url: place.picture,
                            width: 70,
                            height: 44
                        };
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

    $scope.goToPlace = function (placeId) {
        $state.go('places-view', {
            value_id: $scope.value_id,
            page_id: placeId,
            type: 'places'
        });
    };
});
