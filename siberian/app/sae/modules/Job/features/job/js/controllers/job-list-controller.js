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
        isLoading: true,
        value_id: $stateParams.value_id,
        offset: null,
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
        }
    });

    $scope.Math = window.Math;

    Job.setValueId($stateParams.value_id);

    $scope.locationIsDisabled = function () {
        return !Location.isEnabled;
    };

    $scope.requestLocation = function () {
        Dialog.alert(
            "Error",
            "We were unable to request your location.<br />Please check that the application is allowed to use the GPS and that your device GPS is on.",
            "OK",
            3700,
            "job");
    };

    $scope.validateFilters = function () {
        $scope.closeFilterModal();

        Job.collection = [];
        $scope.collection = [];
        $scope.loadContent(true);
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

        Job.collection = [];
        $scope.collection = [];
        $scope.loadContent();
    };

    $scope.refresh = function () {
        Job.collection = [];
        $scope.collection = [];
        $scope.loadContent(true);
    };

    $scope.filterModal = function () {
        Modal.fromTemplateUrl("features/job/assets/templates/l1/more.html", {
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

    $scope.loadMore = function () {
        $scope.loadContent(false, true);
    };

    $scope.loadContent = function (refresh, loadMore) {
        $scope.isLoading = true;
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
            Job
            .findAll($scope.filters, refresh)
            .then(function (data) {
                Job.collection = Job.collection.concat(angular.copy(data.places));
                $scope.collection = Job.collection;

                $scope.load_more = (data.total > $scope.collection.length);
            }).then(function () {
                if (loadMore) {
                    $scope.$broadcast("scroll.infiniteScrollComplete");
                }

                $scope.isLoading = false;
            });
        });
    };

    $scope.imageSrc = function (picture) {
        if (!picture.length) {
            return "./features/job/assets/templates/l1/img/no-category.png";
        }

        return IMAGE_URL + "images/application" + picture;
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
    Job
    .fetchSettings()
    .then(function (payload) {
        // Settings!
        Job.settings = $scope.settings = payload.settings;
        Job.admin_companies = $scope.admin_companies = $scope.settings.admin_companies;
        Job.categories = $scope.categories = $scope.settings.categories;
        $scope.cardDesign = $scope.settings.cardDesign;

        Job.collection = [];
        $scope.collection = [];
        $scope.loadContent(true);
    });

});