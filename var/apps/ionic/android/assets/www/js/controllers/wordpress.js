App.config(function($stateProvider, HomepageLayoutProvider) {

    $stateProvider.state('wordpress-list', {
        url: BASE_PATH+'/wordpress/mobile_list/index/value_id/:value_id',
        templateUrl: function(param) {
            var layout_id = HomepageLayoutProvider.getLayoutIdForValueId(param.value_id);
            switch(layout_id) {
                case "2": layout_id = "l5"; break;
                case "3": layout_id = "l6"; break;
                case "1":
                default: layout_id = "l3";
            }
            return 'templates/html/'+layout_id+'/list.html';
        },
        controller: 'WordpressListController'
    }).state("wordpress-view", {
        url: BASE_PATH+'/wordpress/mobile_view/index/value_id/:value_id/post_id/:post_id/offset/:offset',
        templateUrl: 'templates/wordpress/l1/view.html',
        controller: 'WordpressViewController'
    });

}).controller('WordpressListController', function($filter, $window, $scope, $state, $stateParams, Wordpress) {

    $scope.$on("connectionStateChange", function(event, args) {
        if(args.isOnline == true) {
            $scope.loadContent();
        }
    });

    $scope.collection = new Array();
    $scope.cover = {};
    $scope.is_loading = true;
    $scope.can_load_older_posts = true;
    $scope.value_id = Wordpress.value_id = $stateParams.value_id;
    $scope.offset = null;

    $scope.loadContent = function() {

        var offset = $scope.offset = $scope.collection.length;

        Wordpress.findAll(offset).success(function(data) {

            $scope.collection = $scope.collection.concat(data.collection);

            Wordpress.collection = $scope.collection;

            if(!data.cover && !$scope.cover.id) {
                if ($scope.collection.length) {
                    for (var i in $scope.collection) {

                        if ($scope.collection[i].is_hidden) continue;

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

        }).finally(function() {
            $scope.is_loading = false;
            $scope.$broadcast('scroll.infiniteScrollComplete');
        });

    };

    $scope.showItem = function(item) {
        $state.go("wordpress-view", {value_id: $scope.value_id, post_id: item.id, offset: $scope.offset});
    };

    $scope.loadMore = function() {
        $scope.loadContent();
    };

    $scope.loadContent();

}).controller('WordpressViewController', function($filter, $scope, $stateParams, $window, Wordpress) {

    $scope.$on("connectionStateChange", function(event, args) {
        if(args.isOnline == true) {
            $scope.loadContent();
        }
    });

    $scope.is_loading = true;
    $scope.value_id = Wordpress.value_id = $stateParams.value_id;

    $scope.loadContent = function() {

        $scope.item = $filter('getById')(Wordpress.collection, $stateParams.post_id);
        $scope.page_title = $scope.item.title;
        $scope.is_loading = false;
    };

    $scope.loadContent();

});

App.filter('getById', function() {
    return function(input, id) {
        var i = 0, len = input.length;
        for (; i < len; i++) {
            if (+input[i].id == +id) {
                return input[i];
            }
        }
        return null;
    }
});