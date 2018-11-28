/**
 * Folder v2 feature
 *
 * @version 4.14.0
 */
angular.module('starter').controller('Folder2ListController', function ($scope, $stateParams, $ionicNavBarDelegate,
                                                                        $timeout, SB, Customer, Folder2, Padlock,
                                                                        $filter) {
    angular.extend($scope, {
        is_loading: true,
        value_id: $stateParams.value_id,
        search: {
            searchValue: ''
        },
        showSearch: false,
        searchIndex: [],
        cardDesign: false,
        allowLineReturn: false,
        imagePath: function (path) {
            return IMAGE_URL + path;
        }
    });

    Folder2.setValueId($stateParams.value_id);

    /**
     * Reset the search item
     */
    $scope.resetSearch = function () {
        $scope.search = {
            searchValue: ''
        };
    };

    /**
     * Load page payload
     */
    $scope.loadContent = function () {
        Folder2.findAll()
            .then(function (result) {
                if (result && result.collection) {
                    Folder2.populate(result);
                }

                $scope.cardDesign = Folder2.cardDesign;
                $scope.allowLineReturn = Folder2.allowLineReturn;
                $scope.showSearch = Folder2.showSearch;
                $scope.searchIndex = Folder2.searchIndex;

                var categoryId = _.get($stateParams, 'category_id', null);
                if (_.isEmpty(categoryId)) {
                    categoryId = null;
                }
                var current = Folder2.fetchForParentId(categoryId);

                // Page title!
                $ionicNavBarDelegate.title(current.folder.title);
                $timeout(function () {
                    $scope.page_title = current.folder.title;
                });

                // Folders
                $scope.current = angular.copy(current.folder);
                $scope.collection = angular.copy(current.subfolders);
                $scope.chunks2 = $filter('chunk')(angular.copy(current.subfolders), 2);
                $scope.chunks3 = $filter('chunk')(angular.copy(current.subfolders), 3);
            }).then(function (data) {
                $scope.is_loading = false;
            });
    };

    $scope.$on(SB.EVENTS.AUTH.loginSuccess, function () {
        $scope.loadContent();
    });

    $scope.$on(SB.EVENTS.AUTH.logoutSuccess, function () {
        $scope.loadContent();
    });

    $scope.$on(SB.EVENTS.PADLOCK.unlockFeatures, function () {
        $scope.loadContent();
    });

    $scope.$on(SB.EVENTS.PADLOCK.lockFeatures, function () {
        $scope.loadContent();
    });

    $scope.loadContent();
});
