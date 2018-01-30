/**
 * Folder v2 feature
 *
 * @version 4.12.24
 */
angular.module('starter').controller('Folder2ListController', function ($scope, $stateParams, $ionicNavBarDelegate,
                                                                      $timeout, SB, Customer, Folder2, Padlock) {
    angular.extend($scope, {
        is_loading: true,
        value_id: $stateParams.value_id,
        search: {},
        card_design: false
    });

    Folder2.setValueId($stateParams.value_id);

    /**$scope.computeCollections = function () {
        var unlocked = Customer.can_access_locked_features || Padlock.unlocked_by_qrcode;

        var compute = function (collection) {
            var destination = [];
            angular.forEach(collection, function (folder_item) {
                if (unlocked || !folder_item.is_locked || (folder_item.code === 'padlock')) {
                    if (unlocked && (folder_item.code === 'padlock')) {
                        return;
                    }

                    this.push(folder_item);
                }
            }, destination);
            return destination;
        };

        $scope.collection = compute($scope.collection_data);
        $scope.search_list = compute($scope.search_list_data);
    };*/

    $scope.loadContent = function () {
        Folder2.findAll()
            .then(function () {
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

                $scope.current.picture = IMAGE_URL + $scope.current.picture;
                $scope.current.thumbnail = IMAGE_URL + $scope.current.thumbnail;

                $scope.showSearch = Folder2.showSearch;
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
