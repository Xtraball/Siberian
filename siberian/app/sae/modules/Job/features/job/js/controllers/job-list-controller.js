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
        admin_modal: null,
        can_load_older_places: false,
        social_sharing_active: false,
        collection: [],
        categories: [],
        distance_range: [1, 5, 10, 20, 50, 75, 100, 150, 200, 500, 1000],
        distance_unit: "km",
        filters: {
            time: 0,
            pull_to_refresh: false,
            count: 0,
            fulltext: "",
            locality: null,
            position: null,
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

    $scope.filterPlaces = function (place) {
        var concat = place.title + place.subtitle + place.location + place.contract_type + place.company_name;
        var result = true;

        var parts = $scope.filters.fulltext.split(" ");
        for (var i = 0; i < parts.length; i++) {
            var filtered = parts[i].replace(/[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g, "\\$&");
            var regexp = new RegExp(parts[i], "gi");

            result = result && concat.match(regexp);
        }

        return result;
    };

    /** Re-run findAll with new options */
    $scope.validateFilters = function () {
        $scope.filters.position = false;
        $scope.filters.more_search = true;

        $scope.closeFilterModal();

        $scope.collection = [];
        $scope.loadContent();
    };

    /** Reset filters */
    $scope.clearFilters = function () {
        angular.forEach($scope.categories, function (value, key) {
            $scope.filter.categories[key].is_checked = false;
        });

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

    $scope.loadContent = function (type) {

        $scope.filters.time = 0;
        $scope.filters.pull_to_refresh = false;
        $scope.filters.count = 0;

        Job.findAll($scope.filters)
        .then(function (data) {

            $scope.options = Job.options = data.options;
            $scope.collection = $scope.collection.concat(data.collection);
            if ($scope.filters.categories == null) {
                $scope.filters.categories = data.categories;
            }
            $scope.filters.locality = data.locality;
            $scope.page_title = data.page_title;
            $scope.can_load_older_places = data.more;
            Job.admin_companies = $scope.admin_companies = data.admin_companies;

            $scope.distance_unit = $scope.options.distance_unit;
            $scope.filters.radius = $scope.options.default_radius;

            $scope.social_sharing_active = !!(data.social_sharing_is_active == 1 && $scope.collection.length > 0 && !Application.is_webview);

            $scope.page_title = data.page_title;
        }).then(function () {
            $scope.is_loading = false;
        });
    };

    $scope.loadMore = function () {
        if ($scope.filters.more_search) {
            return;
        }

        var time = 0;
        var distance = 0;
        if ($scope.collection.length > 0) {
            time = $scope.collection[$scope.collection.length - 1].time;
            distance = $scope.collection[$scope.collection.length - 1].distance;
        }

        $scope.filters.time = time;
        $scope.filters.distance = distance;
        $scope.filters.pull_to_refresh = false;
        $scope.filters.count = $scope.collection.length;

        Job.findAll($scope.filters)
        .then(function (data) {

            $scope.collection = $scope.collection.concat(data.collection);
            $scope.page_title = data.page_title;
            $scope.can_load_older_places = data.more;

            $scope.social_sharing_active = !!(data.social_sharing_is_active == 1 && $scope.collection.length > 0 && !Application.is_webview);

            $scope.page_title = data.page_title;
        }).then(function () {
            $scope.is_loading = false;
            $scope.$broadcast('scroll.infiniteScrollComplete');
        });
    };

    $scope.pullToRefresh = function () {
        if ($scope.filters.more_search) {
            return;
        }

        var time = 0;
        var distance = 0;
        if ($scope.collection.length > 0) {
            time = $scope.collection[0].time;
            distance = $scope.collection[0].distance;
        }

        $scope.filters.time = time;
        $scope.filters.distance = distance;
        $scope.filters.pull_to_refresh = true;
        $scope.filters.count = $scope.collection.length;

        Job.findAll($scope.filters, true)
        .then(function (data) {

            $scope.collection = $scope.collection;

            $scope.page_title = data.page_title;

            $scope.social_sharing_active = !!(data.social_sharing_is_active == 1 && $scope.collection.length > 0 && !Application.is_webview);

            $scope.page_title = data.page_title;
        }).then(function () {
            $scope.is_loading = false;
            $scope.$broadcast('scroll.refreshComplete');
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

    Location.getLocation()
    .then(function (position) {
        $scope.filters.position = position.coords;
        $scope.loadContent();
    }, function () {
        $scope.filters.position = false;
        $scope.loadContent();
    });

});