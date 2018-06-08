/*global
 App, angular, BASE_PATH
 */


angular.module("starter").controller("WordpressListController", function($filter, $window, $scope, $state, $stateParams,
                                                                         Wordpress) {

    angular.extend($scope, {
        is_loading: true,
        value_id: $stateParams.value_id,
        cover: {},
        collection: [],
        can_load_older_posts: true,
        offset: null
    });

    Wordpress.setValueId($stateParams.value_id);

    $scope.loadContent = function() {

        var offset = $scope.offset = $scope.collection.length;

        Wordpress.findAll(offset)
            .then(function(data) {

                $scope.collection = $scope.collection.concat(data.collection);

                Wordpress.collection = $scope.collection;

                if(!data.cover && !$scope.cover.id) {
                    if ($scope.collection.length) {
                        for (var i in $scope.collection) {

                            if ($scope.collection[i].is_hidden) {
                                continue;
                            }

                            if ($scope.collection[i].picture) {
                                $scope.collection[i].is_hidden = true;
                                $scope.cover = $scope.collection[i];
                            }

                            break;

                        }
                    }
                } else if(data.cover && data.cover.id) {
                    $scope.cover = data.cover;
                }

                $scope.collection_chunks = $filter("chunk")($scope.collection, 2);
                $scope.can_load_older_posts = !!data.collection.length;
                $scope.page_title = data.page_title;

            }).then(function() {
                $scope.is_loading = false;
                $scope.$broadcast('scroll.infiniteScrollComplete');
            });

    };

    $scope.showItem = function(item) {
        $state.go("wordpress-view", {
            value_id: $scope.value_id,
            post_id: item.id,
            offset: $scope.offset
        });
    };

    $scope.loadMore = function() {
        $scope.loadContent();
    };

    $scope.loadContent();

}).controller("WordpressViewController", function($scope, $stateParams, $window, Wordpress) {

    angular.extend($scope, {
        is_loading: false,
        value_id: $stateParams.value_id
    });

    $scope.loadContent = function() {
        $scope.item         = _.filter(Wordpress.collection, {
            id: ($stateParams.post_id * 1)
        })[0];
        $scope.page_title   = $scope.item.title;
    };

    Wordpress.setValueId($stateParams.value_id);

    $scope.loadContent();

});
