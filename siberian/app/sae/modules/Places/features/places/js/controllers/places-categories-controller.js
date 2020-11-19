/**
 * @version 4.19.9
 */
angular
.module('starter')
.controller('PlacesCategoriesController', function ($scope, $state, $stateParams, $session, $rootScope, $pwaRequest,
                                                    Places) {
    angular.extend($scope, {
        is_loading: true,
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
        return "./features/places/assets/templates/l1/img/no-category.png";
    };

    $scope.selectCategory = function (category) {
        $state.go("places-list", {
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
            .getItem("places_category_format_" + $stateParams.value_id)
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
        $scope.is_loading = false;
    });

});
