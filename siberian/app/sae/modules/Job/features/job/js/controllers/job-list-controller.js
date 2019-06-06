/**
 * Job module
 *
 * @author Xtraball SAS <dev@xtraball.com>
 * @version 4.16.11
 */
angular.module("starter").controller("JobListController", function (Location, SocialSharing, Modal, $rootScope,
                                                                    $scope, $state, $stateParams, $timeout, $translate,
                                                                    $window, Application, Dialog, Job) {

    angular.extend($scope, {
        is_loading: true,
        value_id: $stateParams.value_id,
        offset: null,
        time: null,
        modal: null,
        load_more: false,
        card_design: false,
        admin_modal: null,
        can_load_older_places: false,
        social_sharing_active: false,
        collection: [],
        categories: [],
        distance_range: [1, 5, 10, 20, 50, 75, 100, 150, 200, 500, 1000],
        distance_unit: "km",
        filters: {
            fulltext: "",
            locality: null,
            longitude: 0,
            latitude: 0,
            keywords: null,
            radius: 4,
            distance: 0,
            categories: null,
            more_search: false
        },
        card_design: false
    });

    $scope.Math = window.Math;

    Job.setValueId($stateParams.value_id);

    $scope.validateFilters = function () {
        $scope.closeFilterModal();

        $scope.collection = [];
        $scope.loadContent();
    };

    /** Reset filters */
    $scope.clearFilters = function () {
        if ($scope.categories) {
            $scope.categories.forEach(function (category) {
                category.isSelected = false;
            });
        }

        $scope.filters.fulltext = "";
        $scope.filters.locality = null;
        $scope.filters.more_search = false;
        $scope.filters.position = false;
        $scope.filters.keywords = null;
        $scope.filters.radius = 4;
        $scope.filters.distance = 0;

        $scope.closeFilterModal();

        $scope.collection = [];
        $scope.loadContent();
    };

    $scope.refresh = function () {
        $scope.collection = [];
        $scope.loadContent();
    };

    $scope.filterModal = function () {
        Modal.fromTemplateUrl('features/job/assets/templates/l1/more.html', {
            scope: $scope
        }).then(function (modal) {
            $scope.modal = modal;
            $scope.modal.show();
        });
    };

    $scope.closeFilterModal = function () {
        $scope.modal.hide();
    };

    $scope.adminModal = function () {
        if ($rootScope.isNotAvailableOffline()) {
            return;
        }

        Modal.fromTemplateUrl('features/job/assets/templates/l1/admin-modal.html', {
            scope: $scope
        }).then(function (modal) {
            $scope.admin_modal = modal;
            $scope.admin_modal.show();
        });
    };

    $scope.closeAdminModal = function () {
        $scope.admin_modal.hide();
    };

    $scope.loadContent = function (loadMore) {
        $scope.is_loading = true;
        $scope.filters.offset = $scope.collection.length;

        // Clear collection.
        if ($scope.collection.length <= 0) {
            $scope.collection = [];
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

        Job
        .findAll($scope.filters, false)
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

    $scope.showCompany = function (company_id) {
        $scope.closeAdminModal();
        $state.go("company-view", {
            value_id: $scope.value_id,
            company_id: company_id
        });
    };

    $scope.showItem = function (item) {
        $state.go("job-view", {
            value_id: $scope.value_id,
            place_id: item.id
        });
    };

    // Loading places feature settings
    $pwaRequest.get("job/mobile_list/fetch-settings'", {
        urlParams: {
            value_id: $scope.value_id,
            t: Date.now()
        },
        cache: false
    }).then(function (payload) {
        $scope.settings = payload.settings;
        $scope.categories = $scope.settings.categories;

        // To ensure a fast loading even when GPS is off, we need to decrease the GPS timeout!
        Location
        .getLocation({timeout: 10000}, true)
        .then(function (position) {
            $scope.filters.latitude = position.coords.latitude;
            $scope.filters.longitude = position.coords.longitude;
            $scope.geolocationAvailable = true;
        }, function (error) {
            $scope.filters.latitude = 0;
            $scope.filters.longitude = 0;
            $scope.geolocationAvailable = false;
        }).then(function () {
            // Initiate the first loading!
            $scope.loadContent(false);
        });
    });

});