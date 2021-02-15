/**
 * @version 4.19.9
 */
angular
.module('starter')
.controller("PlacesListController", function (Location, $q, $scope, $rootScope, $session, $state, $pwaRequest,
                                              $stateParams, $translate, $timeout, Places, Dialog, Modal) {
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
        total: 0,
        filters: {
            fulltext: '',
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
        if ($scope.categories) {
            $scope.categories.forEach(function (category) {
                category.isSelected = false;
            });
        }

        $scope.filters.categories = null;
        $scope.filters.fulltext = "";

        $scope.closeFilterModal();

        $scope.collection = [];
        if (skipSearch === undefined) {
            $scope.searchPlaces();
        }
    };

    $scope.filterModal = function() {
        Modal.fromTemplateUrl("features/places/assets/templates/l1/filter.html", {
            scope: $scope
        }).then(function(modal) {
            $scope.modal = modal;
            $scope.modal.show();
        });
    };

    $scope.imageSrc = function (picture) {
        if (!picture.length) {
            return "./features/places/assets/templates/l1/img/no-category.png";
        }

        return IMAGE_URL + "images/application" + picture;
    };

    /**
     *
     * @param item
     * @returns {*}
     */
    $scope.placeThumbnailSrc = function (item) {
        var url = null;
        try {
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
        } catch (e) {
            // Continue to fallback!
        }
        return "./features/places/assets/templates/l1/img/no-place.png";
    };

    // Search places
    $scope.searchPlaces = function (loadMore) {
        $scope.is_loading = true;
        if (Location.isEnabled) {
            Location
                .getLocation({timeout: 10000}, true)
                .then(function (position) {
                    $scope.filters.latitude = position.coords.latitude;
                    $scope.filters.longitude = position.coords.longitude;
                }, function () {
                    $scope.filters.latitude = 0;
                    $scope.filters.longitude = 0;
                }).then(function () {
                    $scope.loadPlaces(loadMore, true);
                });
        } else {
            $scope.filters.latitude = 0;
            $scope.filters.longitude = 0;
            $scope.loadPlaces(loadMore, true);
        }
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
        if ($scope.categories) {
            $scope.filters.categories = $scope.categories
            .filter(function (category) {
                return category.isSelected;
            }).map(function (category) {
                return category.id;
            }).join(",");
        } else {
            $scope.filters.categories = "";
        }

        Places.findAll($scope.filters, false)
            .then(function (data) {
                Places.collection = Places.collection.concat(angular.copy(data.places));
                $scope.collection = Places.collection;

                $scope.load_more = (data.total > $scope.collection.length);

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
            value_id: $scope.value_id
        });
    };

    $scope.showItem = function (item) {
        $state.go('places-view', {
            value_id: $scope.value_id,
            page_id: item.id,
            type: 'places'
        });
    };

    $rootScope.$on('location.request.success', function () {
        $scope.validateFilters();
    });

    // Loading places feature settings
    $pwaRequest.get("places/mobile_list/fetch-settings", {
        urlParams: {
            value_id: $scope.value_id,
            t: Date.now()
        },
        cache: false
    }).then(function (payload) {
            $scope.settings = payload.settings;
            Places.settings = payload.settings;
            $session
                .getItem("places_place_format_" + $stateParams.value_id)
                .then(function (value) {
                    if (value) {
                        $scope.setFormat(value);
                    } else {
                        $scope.setFormat($scope.settings.default_layout);
                    }
                }).catch(function () {
                    $scope.setFormat($scope.settings.default_layout);
                });

            $scope.categories = $scope.settings.categories;

            // Select the category if needed
            if ($stateParams.category_id !== undefined) {
                $scope.clearFilters(true);
                if ($scope.categories) {
                    $scope.categories.forEach(function (category) {
                        if (category.id == $stateParams.category_id) {
                            category.isSelected = true;
                        }
                    });
                }
            }

            // To ensure a fast loading even when GPS is off, we need to decrease the GPS timeout!
            if (Location.isEnabled) {
                Location.getLocation({timeout: 10000}, true)
                    .then(function (position) {
                        $scope.filters.latitude = position.coords.latitude;
                        $scope.filters.longitude = position.coords.longitude;
                    }, function (error) {
                        $scope.filters.latitude = 0;
                        $scope.filters.longitude = 0;
                    }).then(function () {
                        // Initiate the first loading!
                        $scope.searchPlaces(false);
                    });
            } else {
                $scope.filters.latitude = 0;
                $scope.filters.longitude = 0;
                $scope.searchPlaces(false);
            }
    });

});
