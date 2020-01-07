/**
 * Links
 *
 * @author Xtraball SAS
 * @version 4.18.5
 */
angular
.module('starter')
.controller('LinksViewController', function($scope, $stateParams, $rootScope, $timeout, $window, Links, LinkService) {

    angular.extend($scope, {
        is_loading: true,
        value_id: $stateParams.value_id,
        weblink: {},
        showSearch: false,
        cardDesign: false,
        search: {
            searchValue: ''
        },
    });

    Links.setValueId($stateParams.value_id);

    /**
     * Reset the search item
     */
    $scope.resetSearch = function () {
        $scope.search = {
            searchValue: ''
        };
    };

    $scope.populate = function (data) {
        $scope.showSearch = data.settings.showSearch;
        $scope.cardDesign = data.settings.cardDesign;
        $scope.weblink = data.weblink;

        if (!angular.isArray($scope.weblink.links)) {
            $scope.weblink.links = [];
        }
        $scope.page_title = data.page_title;
    };

    $scope.loadContent = function() {
        $scope.is_loading = true;
        Links
            .find()
            .then(function(data) {
                $scope.populate(data);
            }).then(function() {
                $scope.is_loading = false;
            });
    };

    /**
     * @param link
     */
    $scope.openLink = function(link) {
        LinkService.openLink(link.url, link.options, link.external_browser);
    };

    $scope.reloadOverview = function () {
        $scope.is_loading = true;
        Links
            .reloadOverview()
            .then(function(data) {
                $scope.populate(data);
            }).then(function() {
                $scope.is_loading = false;
            });
    };

    if ($window.overview) {
        $window.overview['weblink_multi'] = $scope.reloadOverview;
    }

    $scope.loadContent();
});
