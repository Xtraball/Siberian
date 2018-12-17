/**
 * @version 4.15.9
 */
angular.module('starter').controller('PlacesHomeController', function ($scope, $state, $stateParams, $ionicHistory, Places) {

    angular.extend($scope, {
        value_id: $stateParams.value_id,
        settings: null,
    });

    /** Routing history to be sure we don't enter inside a loop */
    var backView = $ionicHistory.backView();
    var forwardView = $ionicHistory.forwardView();
    if (backView) {
        var backState = backView.stateName;
    }
    if (forwardView) {
        var forwardState = forwardView.stateName;
    }
    var states = [
        "places-categories",
        "places-list-map",
        "places-list"
    ];
    if (states.indexOf(backState) !== -1) {
        return $ionicHistory.goBack();
    }

    Places.setValueId($stateParams.value_id);

    // Router page only!
    Places.settings()
        .then(function (settings) {
            $scope.settings = settings;

            if ($scope.settings.default_page === "categories") {
                $state.go('places-categories', {
                    value_id: $scope.value_id
                });
            } else {
                $state.go('places-list', {
                    value_id: $scope.value_id
                });
            }
        });

}).controller('PlacesCategoriesController', function ($scope, $state, $stateParams, $session, $ionicHistory, $rootScope,
                                                      Places) {

    /** Routing history for forward action */
    if ($ionicHistory.backView().stateName === 'places-home') {
        $ionicHistory.removeBackView();
    }

    angular.extend($scope, {
        value_id: $stateParams.value_id,
        settings: null,
        module_code: 'places',
        currentFormatBtn: 'ion-sb-grid-33',
        currentFormat: 'place-100',
        categories: [],
        filters: {
            fulltext: "",
            categories: null,
            longitude: 0,
            latitude: 0
        },
    });

    Places.setValueId($stateParams.value_id);

    // Version 2
    $scope.nextFormat = function (user) {
        switch ($scope.currentFormat) {
            case "place-33":
                $scope.setFormat("place-50", user);
                break;
            case "place-50":
                $scope.setFormat("place-100", user);
                break;
            case "place-100": default:
                $scope.setFormat("place-33", user);
            break;
        }
    };

    $scope.setFormat = function (format, user) {
        if (user !== undefined) {
            $session.setItem("places_category_format_" + $stateParams.value_id, format);
        }

        switch (format) {
            case "place-33":
                $scope.currentFormat = "place-33";
                $scope.currentFormatBtn = "ion-sb-grid-50";
                break;
            case "place-50":
                $scope.currentFormat = "place-50";
                $scope.currentFormatBtn = "ion-sb-list1";
                break;
            case "place-100": default:
                $scope.currentFormat = "place-100";
                $scope.currentFormatBtn = "ion-sb-grid-33";
                break;
        }
    };

    $scope.categoryThumbnailSrc = function (item) {
        if (item.picture && item.picture.length) {
            return IMAGE_URL + "images/application" + item.picture;
        }
        return './features/places/assets/templates/l1/img/no-category.png';
    };

    $scope.selectCategory = function (category) {
        $state.go('places-list', {
            value_id: $scope.value_id,
            page_id: $stateParams.page_id,
            category_id: category.id
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

    // Loading places feature settings
    Places.settings()
        .then(function (settings) {

            $session
                .getItem("places_category_format_" + $stateParams.value_id)
                .then(function (value) {
                    if (value) {
                        $scope.setFormat(value);
                    } else {
                        $scope.setFormat(settings.default_layout);
                    }
                }).catch(function () {
                    $scope.setFormat(settings.default_layout);
                });

            $scope.settings = settings;
            $scope.categories = settings.categories;
        });

}).controller('PlacesListController', function (Location, $q, $ionicHistory, $scope, $rootScope, $session, $state,
                                                $stateParams, $translate, $timeout, Places, Modal) {

    /** Routing history for forward action */
    if ($ionicHistory.backView().stateName === 'places-home') {
        $ionicHistory.removeBackView();
    }

    angular.extend($scope, {
        is_loading: true,
        value_id: $stateParams.value_id,
        settings: null,
        collection: [],
        load_more: false,
        card_design: false,
        module_code: 'places',
        modal: null,
        // Version 2
        currentFormatBtn: 'ion-sb-grid-33',
        currentFormat: 'place-100',
        categories: [],
        filters: {
            fulltext: "",
            categories: null,
            longitude: 0,
            latitude: 0
        },
        // Version 2
    });

    Places.setValueId($stateParams.value_id);

    // Version 2
    $scope.nextFormat = function (user) {
        switch ($scope.currentFormat) {
            case "place-33":
                $scope.setFormat("place-50", user);
                break;
            case "place-50":
                $scope.setFormat("place-100", user);
                break;
            case "place-100": default:
                $scope.setFormat("place-33", user);
                break;
        }
    };

    $scope.setFormat = function (format, user) {
        if (user !== undefined) {
            $session.setItem("places_place_format_" + $stateParams.value_id, format);
        }

        switch (format) {
            case "place-33":
                $scope.currentFormat = "place-33";
                $scope.currentFormatBtn = "ion-sb-grid-50";
                break;
            case "place-50":
                $scope.currentFormat = "place-50";
                $scope.currentFormatBtn = "ion-sb-list1";
                break;
            case "place-100": default:
                $scope.currentFormat = "place-100";
                $scope.currentFormatBtn = "ion-sb-grid-33";
            break;
        }
    };

    $scope.refreshPlaces = function () {
        $scope.pullToRefresh();
    };

    /** Re-run findAll with new options */
    $scope.validateFilters = function () {
        $scope.closeFilterModal();

        $scope.collection = [];
        $scope.searchPlaces();
    };

    $scope.closeFilterModal = function () {
        if ($scope.modal) {
            $scope.modal.hide();
        }
    };

    /** Reset filters */
    $scope.clearFilters = function(skipSearch) {
        $scope.categories.forEach(function (category) {
            category.isSelected = false;
        });

        $scope.filters.categories = null;
        $scope.filters.fulltext = "";

        $scope.closeFilterModal();

        $scope.collection = [];
        if (skipSearch === undefined) {
            $scope.searchPlaces();
        }
    };

    $scope.filterModal = function() {
        Modal.fromTemplateUrl('features/places/assets/templates/l1/filter.html', {
            scope: $scope
        }).then(function(modal) {
            $scope.modal = modal;
            $scope.modal.show();
        });
    };

    $scope.imageSrc = function (picture) {
        if (!picture.length) {
            return './features/places/assets/templates/l1/img/no-category.png';
        }

        return IMAGE_URL + 'images/application' + picture;
    };

    /**
     *
     * @param item
     * @returns {*}
     */
    $scope.placeThumbnailSrc = function (item) {
        var url = null;
        switch ($scope.settings.listImagePriority) {
            case "thumbnail": default:
                if (item.thumbnail && item.thumbnail.length) {
                    url = item.thumbnail;
                } else if (item.picture && item.picture.length) {
                    url = item.picture;
                }
                break;
            case "image":
                if (item.picture && item.picture.length) {
                    url = item.picture;
                } else if (item.thumbnail && item.thumbnail.length) {
                    url = item.thumbnail;
                }
                break;
        }
        if (url !== null) {
            // Monkey Patch non-well formatted uris
            if (!url.match(/^https?:\/\//)){
                url = IMAGE_URL + url;
            }
            return url;
        }
        return './features/places/assets/templates/l1/img/no-place.png';
    };

    // Version 2

    $scope.geolocationAvailable = true;

    // Loading places feature settings
    Places.settings()
        .then(function (settings) {

            $session
                .getItem("places_place_format_" + $stateParams.value_id)
                .then(function (value) {
                    if (value) {
                        $scope.setFormat(value);
                    } else {
                        $scope.setFormat(settings.default_layout);
                    }
                }).catch(function () {
                    $scope.setFormat(settings.default_layout);
                });

            $scope.settings = settings;
            $scope.categories = settings.categories;

            // Select the category if needed
            if ($stateParams.category_id !== undefined) {
                $scope.clearFilters(true);
                $scope.categories.forEach(function (category) {
                    if (category.id == $stateParams.category_id) {
                        category.isSelected = true;
                    }
                });
            }

            // To ensure a fast loading even when GPS is off, we neeeeeed to decrease the GPS timeout!
            Location.getLocation({timeout: 3200})
                .then(function (position) {
                    $scope.filters.latitude = position.coords.latitude;
                    $scope.filters.longitude = position.coords.longitude;
                    $scope.geolocationAvailable = true;
                }, function (error) {
                    $scope.filters.latitude = 0;
                    $scope.filters.longitude = 0;
                    $scope.geolocationAvailable = false;
                });
        });

    // Search places
    $scope.searchPlaces = function (loadMore) {
        Location
            .getLocation({timeout: 3200})
            .then(function (position) {
                $scope.filters.latitude = position.coords.latitude;
                $scope.filters.longitude = position.coords.longitude;
                $scope.geolocationAvailable = true;
            }, function () {
                $scope.filters.latitude = 0;
                $scope.filters.longitude = 0;
                $scope.geolocationAvailable = false;
            }).then(function () {
                $scope.loadPlaces(loadMore, true);
            });
    };

    $scope.loadPlaces = function (loadMore) {
        $scope.is_loading = true;
        $scope.filters.offset = $scope.collection.length;

        // Clear collection.
        if ($scope.collection.length <= 0) {
            $scope.collection = [];
            Places.collection = [];
        }

        // Group categories
        $scope.filters.categories = $scope.categories
            .filter(function (category) {
                return category.isSelected;
            }).map(function (category) {
                return category.id;
            }).join(",");

        Places.findAll($scope.filters, false)
            .then(function (data) {
                Places.collection = Places.collection.concat(angular.copy(data.places));
                $scope.collection = Places.collection;

                $scope.load_more = (data.places.length > 0);

            }).then(function () {
                if (loadMore) {
                    $scope.$broadcast('scroll.infiniteScrollComplete');
                }

                $scope.is_loading = false;
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

    // Initiate the first loading!
    $scope.searchPlaces(false);

}).controller('PlacesViewController', function ($filter, $scope, $rootScope, $state, $stateParams, $translate,
                                                $location, Places, SocialSharing) {
    angular.extend($scope, {
        is_loading: true,
        value_id: $stateParams.value_id,
        social_sharing_active: false,
        use_pull_to_refresh: true,
        pull_to_refresh: false,
        card_design: false
    });

    $scope.blankImage = "./features/places/assets/templates/l1/img/blank-700-440.png";

    Places.setValueId($stateParams.value_id);

    $scope.loadContent = function () {
        Places.getPlace($stateParams.page_id)
            .then(function (data) {
                $scope.social_sharing_active = (data.social_sharing_active && $rootScope.isNativeApp);
                $scope.blocks = data.blocks;

                $scope.blockChunks = $filter('chunk')(angular.copy($scope.blocks),
                    Math.ceil($scope.blocks.length / 2));

                $scope.place = data.page;
                $scope.page_title = data.page_title;
            }).then(function () {
                $scope.is_loading = false;
            });
    };

    $scope.share = function () {
        var file;
        var address = "";
        var link = undefined;
        angular.forEach($scope.blocks, function (block) {
            if (block.gallery) {
                if (block.gallery.length > 0 && file === null) {
                    file = block.gallery[0].url;
                }
            }
            if (block.type === "address") {
                address = block.address;
                if (block.website !== "" && block.show_website) {
                    link = block.website;
                }
            }
        });

        var message = "Check this place!\n" + $scope.place.title + "\n" + address;

        SocialSharing.share(undefined, message, undefined, link, file);
    };

    $scope.onShowMap = function (block) {
        if ($rootScope.isNotAvailableOffline()) {
            return;
        }

        var params = {};

        if (block.latitude && block.longitude) {
            params.latitude = block.latitude;
            params.longitude = block.longitude;
        } else if (block.address) {
            params.address = encodeURI(block.address);
        }

        params.title = block.label;
        params.value_id = $scope.value_id;

        $location.path(Url.get('map/mobile_view/index', params));
    };

    $scope.loadContent();

}).controller('PlacesMapController', function ($scope, $state, $stateParams, $translate, $timeout, Location, Places,
                                               GoogleMaps) {
    angular.extend($scope, {
        is_loading: true,
        value_id: $stateParams.value_id,
        collection: [],
        showInfoWindow: false,
        currentPlace: null,
        filters: {
            latitude: 0,
            longitude: 0,
        }
    });

    Places.setValueId($stateParams.value_id);

    $scope.hideInfoWindow = function () {
        $scope.showInfoWindow = false;
    };

    $scope.loadContent = function () {
        Location
            .getLocation({timeout: 3200})
            .then(function (position) {
                $scope.filters.latitude = position.coords.latitude;
                $scope.filters.longitude = position.coords.longitude;
            }, function () {
                $scope.filters.latitude = 0;
                $scope.filters.longitude = 0;
            }).then(function () {
                Places
                    .findAllMaps($scope.filters, false)
                    .then(function (data) {
                        $scope.page_title = data.page_title;
                        $scope.collection = data.places;

                        Places.setMapCollection($scope.collection);

                        var markers = [];

                        for (var i = 0; i < $scope.collection.length; i = i + 1) {
                            var place = $scope.collection[i];
                            var marker = {
                                config: {
                                    id: angular.copy(place.id),
                                    place: angular.copy(place)
                                },
                                onClick: (function (marker) {
                                    $timeout(function () {
                                        $scope.showInfoWindow = true;
                                        $scope.currentPlace = marker.config.place;
                                    });
                                })
                            };

                            if (place.address.latitude && place.address.longitude) {
                                marker.latitude = place.address.latitude;
                                marker.longitude = place.address.longitude;
                            } else {
                                marker.address = place.address.address;
                            }

                            switch (place.mapIcon) {
                                case "pin":
                                    if (place.pin) {
                                        marker.icon = {
                                            url: place.pin,
                                            width: 42,
                                            height: 42
                                        };
                                    }
                                    break;
                                case "image":
                                    if (place.picture) {
                                        marker.icon = {
                                            url: place.picture,
                                            width: 70,
                                            height: 44
                                        };
                                    }
                                    break;
                                case "thumbnail":
                                    if (place.thumbnail) {
                                        marker.icon = {
                                            url: place.thumbnail,
                                            width: 42,
                                            height: 42
                                        };
                                    }
                                    break;
                                case "default": default:
                                    // Defaults to google map icons
                                    break;
                            }

                            markers.push(marker);
                        }

                        $scope.map_config = {
                            cluster: true,
                            markers: markers,
                            bounds_to_marker: true
                        };
                    }).finally(function () {
                        $scope.is_loading = false;
                    });
            });


    };

    $scope.loadContent();

    $scope.goToPlace = function (placeId) {
        $state.go('places-view', {
            value_id: $scope.value_id,
            page_id: placeId
        });
    };
});
