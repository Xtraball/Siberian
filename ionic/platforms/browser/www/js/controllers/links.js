/*global
 App, angular, BASE_PATH, IMAGE_URL
 */

angular.module("starter").controller("LinksViewController", function($scope, $stateParams, $rootScope, $timeout, $window, Links, LinkService) {

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

    $scope.loadContent = function() {

        Links
            .find()
            .then(function(data) {

                $scope.showSearch = data.settings.showSearch;
                $scope.cardDesign = data.settings.cardDesign;
                $scope.weblink = data.weblink;

                if (!angular.isArray($scope.weblink.links)) {
                    $scope.weblink.links = [];
                }
                $scope.page_title = data.page_title;

            }).then(function() {
                $scope.is_loading = false;
            });
    };

    /**
     *
     * @param url
     * @param hide_navbar
     * @param use_external_app
     */
    $scope.openLink = function(url, hide_navbar, use_external_app) {
        LinkService.openLink(url, {
            "hide_navbar"       : hide_navbar,
            "use_external_app"  : use_external_app
        });
    };

    /**
     * @todo check behavior ???
     */
    if($rootScope.isOverview) {

        $window.prepareDummy = function() {
            $timeout(function() {
                $scope.dummy = {id: "new"};
                $scope.weblink.links.push($scope.dummy);
            });
        };

        $window.setAttributeTo = function(id, attribute, value) {

            $timeout(function() {
                for (var i in $scope.weblink.links) {
                    if ($scope.weblink.links[i].id == id) {
                        $scope.weblink.links[i][attribute] = value;
                    }
                }
            });
        };

        $window.setCoverUrl = function(url) {
            $timeout(function() {
                $scope.weblink.cover_url = url;
            });
        };

    }

    $scope.loadContent();

});
