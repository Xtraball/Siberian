/*global
    angular, BASE_PATH
 */
angular.module('starter').controller('RssListController', function ($filter, $scope, $state, $stateParams,
                                                                    Rss, Pages) {
    angular.extend($scope, {
        is_loading: true,
        value_id: $stateParams.value_id
    });

    Rss.setValueId($stateParams.value_id);

    Rss.findAll()
        .then(function (data) {
            $scope.collection = data.collection;
            Rss.collection = angular.copy($scope.collection);
            if (data.cover) {
                Rss.collection.unshift(angular.copy(data.cover));
            }

            if (Pages.getLayoutIdForValueId(Rss.value_id) === 1) {
                $scope.cover = angular.copy(data.cover);
                $scope.page_title = angular.copy(data.page_title);
            } else {
                // Unshift before chunking!
                $scope.collection.unshift(angular.copy(data.cover));
                $scope.collection_chunks = $filter('chunk')($scope.collection, 2);
            }
        }).then(function () {
            $scope.is_loading = false;
        });

    $scope.showItem = function (item) {
        $state.go('rss-view', {
            value_id: $scope.value_id,
            feed_id: item.id
        });
    };
}).controller('RssViewController', function ($rootScope, $scope, $stateParams, LinkService, Rss) {
    angular.extend($scope, {
        is_loading: false,
        value_id: $stateParams.value_id
    });

    Rss.setValueId($stateParams.value_id);
    Rss.feed_id = $stateParams.feed_id;

    $scope.loadContent = function () {
        $scope.is_loading = true;

        Rss.getFeed($stateParams.feed_id)
            .then(function (feed) {
                $scope.item = feed;
            }).then(function () {
                $scope.is_loading = false;
            });
    };

    $scope.showItem = function () {
        if ($rootScope.isNotAvailableInOverview()) {
            return;
        }

        LinkService.openLink($scope.item.url);
    };

    $scope.loadContent();
});
;/* global
 App, device, angular
 */

/**
 * Rss
 *
 * @author Xtraball SAS
 */
angular.module('starter').factory('Rss', function ($pwaRequest) {
    var factory = {
        value_id: null,
        extendedOptions: {},
        collection: []
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
     * @param options
     */
    factory.setExtendedOptions = function (options) {
        factory.extendedOptions = options;
    };

    /**
     * Pre-Fetch feature.
     *
     * @param page
     */
    factory.preFetch = function () {
        factory.findAll();
    };

    factory.findAll = function () {
        if (!this.value_id) {
            return $pwaRequest.reject('[Factory::Rss.findAll] missing value_id');
        }

        return $pwaRequest.get('rss/mobile_feed_list/findall', angular.extend({
            urlParams: {
                value_id: this.value_id
            }
        }, factory.extendedOptions));
    };

    factory.find = function (feed_id) {
        if (!this.value_id) {
            return $pwaRequest.reject('[Factory::Rss.find] missing value_id');
        }

        return $pwaRequest.get('rss/mobile_feed_view/find', {
            urlParams: {
                value_id: this.value_id,
                feed_id: feed_id
            }
        });
    };

    /**
     * Search for feed payload inside cached collection
     *
     * @param feed_id
     * @returns {*}
     */
    factory.getFeed = function (feed_id) {
        if (!this.value_id) {
            return $pwaRequest.reject('[Factory::Rss.getFeed] missing value_id');
        }

        var feed = _.get(_.filter(factory.collection, function (item) {
            return (item.id == feed_id);
        })[0], 'embed_payload', false);

        if (!feed) {
            return factory.find(feed_id);
        }
        return $pwaRequest.resolve(feed);
    };


    return factory;
});
