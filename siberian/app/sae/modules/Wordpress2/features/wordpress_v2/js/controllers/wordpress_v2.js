/**
 * Folder v2 feature
 *
 * @version 0.0.1
 */
angular.module('starter').controller('Wordpress2ListController', function ($scope, $stateParams, $state, $timeout,
                                                                           Wordpress2) {
    angular.extend($scope, {
        is_loading: true,
        value_id: $stateParams.value_id,
        cardDesign: false,
        currentPage: 1,
        previousPage: 1,
        collection: [],
        queries: [],
        load_more: true,
        isLoadingMore: false,
        use_pull_to_refresh: true,
        pull_to_refresh: false,
        isPullingToRefresh: false,
        queryView: true,
        imagePath: function (path) {
            return IMAGE_URL + 'images/application' + path;
        }
    });

    Wordpress2.setValueId($stateParams.value_id);
    Wordpress2.collection = [];
    Wordpress2.cardDesign = false;

    /**
     * Load page payload
     */
    $scope.loadContent = function () {
        if ($stateParams.query_id !== '') {
            Wordpress2.loadposts($stateParams.query_id, $scope.currentPage, $scope.isPullingToRefresh)
                .then(function (data) {
                    $scope.queryView = false;
                    if ($scope.isPullingToRefresh) {
                        // Clear collection!
                        $scope.collection = [];
                    }

                    $scope.page_title = data.page_title;
                    $scope.wordpress = data.wordpress;
                    $scope.cardDesign = $scope.wordpress.cardDesign;
                    Wordpress2.cardDesign = $scope.cardDesign;

                    // Enforce query group (it's the list)
                    $scope.wordpress.groupQueries = true;

                    $scope.collection = $scope.collection.concat(angular.copy(data.posts));
                    Wordpress2.collection = $scope.collection;

                    $scope.query = angular.copy(data.query);

                    //$scope.chunks2 = $filter('chunk')(angular.copy(current.subfolders), 2);
                    //$scope.chunks3 = $filter('chunk')(angular.copy(current.subfolders), 3);

                }).then(function (data) {
                    if ($scope.isPullingToRefresh) {
                        $scope.currentPage = $scope.previousPage;
                        $scope.$broadcast('scroll.refreshComplete');
                        $timeout(function () {
                            $scope.isPullingToRefresh = false;
                        }, 100);
                    }

                    if ($scope.isLoadingMore) {
                        $scope.$broadcast('scroll.infiniteScrollComplete');
                        $timeout(function () {
                            $scope.isLoadingMore = false;
                        }, 100);
                    }

                    $scope.is_loading = false;
                });
        } else {
            Wordpress2.find($scope.currentPage, $scope.isPullingToRefresh)
                .then(function (data) {
                    if ($scope.isPullingToRefresh) {
                        // Clear collection!
                        $scope.collection = [];
                    }

                    $scope.page_title = data.page_title;
                    $scope.wordpress = data.wordpress;
                    $scope.cardDesign = $scope.wordpress.cardDesign;
                    Wordpress2.cardDesign = $scope.cardDesign;

                    $scope.queryView = true && !$scope.wordpress.groupQueries;

                    $scope.collection = $scope.collection.concat(angular.copy(data.posts));
                    Wordpress2.collection = $scope.collection;

                    $scope.queries = angular.copy(data.queries);
                    $scope.query = $scope.wordpress;

                    //$scope.chunks2 = $filter('chunk')(angular.copy(current.subfolders), 2);
                    //$scope.chunks3 = $filter('chunk')(angular.copy(current.subfolders), 3);

                }).then(function (data) {
                    if ($scope.isPullingToRefresh) {
                        $scope.currentPage = $scope.previousPage;
                        $scope.$broadcast('scroll.refreshComplete');
                        $timeout(function () {
                            $scope.isPullingToRefresh = false;
                        }, 100);
                    }

                    if ($scope.isLoadingMore) {
                        $scope.$broadcast('scroll.infiniteScrollComplete');
                        $timeout(function () {
                            $scope.isLoadingMore = false;
                        }, 100);
                    }

                    $scope.is_loading = false;
                });
        }
    };

    /**
     *
     * @param queryId
     */
    $scope.loadPosts = function (queryId) {
        $scope.currentPage = 1;
        $state.go(
            'wordpress2-list',
            {
                value_id: $stateParams.value_id,
                query_id: queryId
            },
            {
                reload: true
            }
        );
    };

    /**
     *
     * @param postId
     */
    $scope.viewPost = function (postId) {
        $state.go(
            'wordpress2-view',
            {
                value_id: $stateParams.value_id,
                post_id: postId
            }
        );
    };

    /**
     *
     */
    $scope.loadMore = function () {
        if (!$scope.isLoadingMore) {
            $scope.isLoadingMore = true;
            $scope.currentPage = $scope.currentPage + 1;
            $scope.loadContent(false);
        }
    };

    /**
     *
     */
    $scope.pullToRefresh = function () {
        if (!$scope.isPullingToRefresh) {
            $scope.isPullingToRefresh = true;
            $scope.currentPage = 1;
            $scope.previousPage = $scope.currentPage;
            $scope.loadContent(true);
        }
    };

    /**
     *
     */
    $scope.canPullToRefresh = function () {
        return !$scope.isLoadingMore &&
            !$scope.is_loading &&
            $scope.use_pull_to_refresh;
    };

    /**
     *
     */
    $scope.canLoadMore = function () {
        return !$scope.queryView &&
            !$scope.isPullingToRefresh &&
            !$scope.is_loading &&
            $scope.load_more;
    };

    $scope.loadContent(false);

}).controller('Wordpress2ViewController', function ($filter, $scope, $stateParams, Wordpress2) {
    angular.extend($scope, {
        is_loading: true,
        value_id: $stateParams.value_id,
        search: {},
        cardDesign: false,
        post: null,
        imagePath: function (path) {
            return IMAGE_URL + 'images/application' + path;
        }
    });

    /**
     * Load page payload
     */
    $scope.loadContent = function () {
        $scope.cardDesign = Wordpress2.cardDesign;
        $scope.post = _.find(Wordpress2.collection, function (post) {
            return post.id == $stateParams.post_id;
        });

        $scope.post.mtDate = $filter('moment')($scope.post.date).calendar();
        $scope.page_title = $scope.post.title;
    };

    $scope.loadContent();
});
