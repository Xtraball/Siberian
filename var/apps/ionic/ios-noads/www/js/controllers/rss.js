var layout_id = "l3";

App.config(function($stateProvider, HomepageLayoutProvider) {
    $stateProvider.state('rss-list', {
        url: BASE_PATH+"/rss/mobile_feed_list/index/value_id/:value_id",
        templateUrl: function(param) {
            layout_id = HomepageLayoutProvider.getLayoutIdForValueId(param.value_id);
            switch(layout_id) {
                case "2": layout_id = "l5"; break;
                case "3": layout_id = "l6"; break;
                case "1":
                default: layout_id = "l3";
            }

            return 'templates/html/'+layout_id+'/list.html';
        },
        controller: 'RssListController'
    }).state("rss-view", {
        url: BASE_PATH+"/rss/mobile_feed_view/index/value_id/:value_id/feed_id/:feed_id",
        templateUrl: 'templates/rss/l1/view.html',
        controller: 'RssViewController'
    });

}).controller('RssListController', function($filter, $rootScope, $scope, $state, $stateParams, Application, Rss) {

    $scope.is_loading = true;
    $scope.value_id = Rss.value_id = $stateParams.value_id;

    Rss.findAll().success(function(data) {
        $scope.collection = data.collection;

        if(layout_id == "l3") {
            $scope.cover = data.cover;
            $scope.page_title = data.page_title;
        } else {
            $scope.collection_chunks = $filter("chunk")($scope.collection, 2);
            $scope.collection.unshift(data.cover);
        }
    }).error(function() {

    }).finally(function() {
        $scope.is_loading = false;
    });

    $scope.showItem = function(item) {
        $state.go("rss-view", {value_id: $scope.value_id, feed_id: item.id});
    };

}).controller('RssViewController', function($rootScope, $scope, $stateParams, $window, Rss, Application) {

    $scope.is_loading = false;
    $scope.value_id = Rss.value_id = $stateParams.value_id;
    Rss.feed_id = $stateParams.feed_id;

    $scope.loadContent = function() {

        $scope.is_loading = true;

        Rss.find($stateParams.feed_id).success(function(feed) {
            $scope.item = feed;
        }).finally(function() {
            $scope.is_loading = false;
        });

    };

    $scope.showItem = function() {
        if($rootScope.isOverview) {
            $rootScope.showMobileFeatureOnlyError();
            return;
        }
        $window.open($scope.item.url, $rootScope.getTargetForLink(), "location=no");
    };

    $scope.loadContent();

});