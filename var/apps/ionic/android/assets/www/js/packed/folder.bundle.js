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
;/* global
    App, angular, _
 */

/**
 * Folder
 *
 * @author Xtraball SAS
 */
angular.module('starter').factory('Folder', function ($pwaRequest) {
    var factory = {
        value_id: null,
        folder_id: null,
        category_id: null,
        collection: [],
        extendedOptions: {}
    };

    /**
     *
     * @param value_id
     */
    factory.setValueId = function (value_id) {
        factory.value_id = value_id;
    };

    /**
     *
     * @param category_id
     */
    factory.setCategoryId = function (category_id) {
        factory.category_id = category_id;
    };

    /**
     *
     * @param options
     */
    factory.setExtendedOptions = function (options) {
        factory.extendedOptions = options;
    };

    factory.findAll = function (value_id, category_id, options) {
        var localValueId = (value_id === undefined) ? this.value_id : value_id;
        var localCategoryId = (category_id === undefined) ? this.category_id : category_id;

        if (!localValueId) {
            return $pwaRequest.reject('[Factory::Facebook.findAll] missing value_id');
        }

        var payload = $pwaRequest.getPayloadForValueId(factory.value_id);
        if ((payload !== false) && (localCategoryId === null)) {
            return $pwaRequest.resolve(payload);
        } else if ((localCategoryId !== null) &&
            (_.find(factory.collection, { category_id: localCategoryId }) !== undefined)) {
            return _.find(factory.collection, { category_id: localCategoryId });
        }

        // Otherwise fallback on PWA!
        return $pwaRequest.get('folder/mobile_list/findallv2', angular.extend({
            urlParams: {
                value_id: localValueId,
                category_id: localCategoryId
            }
        }, factory.extendedOptions, options));
    };

    return factory;
});
