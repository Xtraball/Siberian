/*global
 App, angular, BASE_PATH
*/

angular.module('starter').controller('FolderListController', function ($scope, $stateParams, $ionicNavBarDelegate,
                                                                      $timeout, SB, Customer, Folder, Padlock) {
    angular.extend($scope, {
        is_loading: true,
        value_id: $stateParams.value_id,
        search: {},
        card_design: false
    });

    Folder.setValueId($stateParams.value_id);
    Folder.setCategoryId(_.get($stateParams, 'category_id', null));

    $scope.computeCollections = function () {
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
    };

    $scope.loadContent = function () {
        Folder.findAll()
            .then(function (data) {
                var values = angular.copy(data);

                $scope.cover = values.cover;

                $ionicNavBarDelegate.title(values.page_title);
                $timeout(function () {
                    $scope.page_title = values.page_title;
                });

                $scope.collection_data = values.folders;
                $scope.search_list_data = values.search_list;

                $scope.computeCollections();

                $scope.show_search = values.show_search;

                return values;
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
