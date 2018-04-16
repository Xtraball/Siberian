/**
 * Folder v2 feature
 *
 * @version 0.0.1
 */
angular.module('starter').controller('Wordpress2ListController', function ($scope, $stateParams, $state, Wordpress2) {
    angular.extend($scope, {
        is_loading: true,
        value_id: $stateParams.value_id,
        search: {},
        showSearch: false,
        searchIndex: [],
        cardDesign: false,
        currentPage: 1,
        collection: [],
        queries: [],
        load_more: false,
        use_pull_refresh: true,
        pull_to_refresh: false,
        imagePath: function (path) {
            return IMAGE_URL + 'images/application' + path;
        }
    });

    Wordpress2.setValueId($stateParams.value_id);

    /**
     * Load page payload
     */
    $scope.loadContent = function () {
        if ($stateParams.query_id !== '') {
            Wordpress2.loadposts($stateParams.query_id, $scope.currentPage)
                .then(function (data) {
                    $scope.page_title = data.page_title;
                    $scope.wordpress = data.wordpress;
                    $scope.cardDesign = $scope.wordpress.cardDesign;

                    // Enforce query group (it's the list)
                    $scope.wordpress.groupQueries = true;

                    $scope.collection = $scope.collection.push(angular.copy(data.posts));
                    $scope.query = angular.copy(data.query);

                    //$scope.chunks2 = $filter('chunk')(angular.copy(current.subfolders), 2);
                    //$scope.chunks3 = $filter('chunk')(angular.copy(current.subfolders), 3);

                }).then(function (data) {
                    $scope.is_loading = false;
                });
        } else {
            Wordpress2.find($scope.currentPage)
                .then(function (data) {
                    $scope.page_title = data.page_title;
                    $scope.wordpress = data.wordpress;
                    $scope.cardDesign = $scope.wordpress.cardDesign;

                    $scope.collection = $scope.collection.push(angular.copy(data.posts));
                    $scope.queries = angular.copy(data.queries);

                    //$scope.chunks2 = $filter('chunk')(angular.copy(current.subfolders), 2);
                    //$scope.chunks3 = $filter('chunk')(angular.copy(current.subfolders), 3);

                }).then(function (data) {
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

    $scope.loadMore = function () {
        $scope.currentPage = $scope.currentPage + 1;
        $scope.loadContent();
    };

    $scope.pullToRefresh = function () {
        $scope.collection = [];
        $scope.currentPage = 1;
    };

    $scope.loadContent();
});
