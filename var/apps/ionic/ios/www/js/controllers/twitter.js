App.config(function ($stateProvider) {

    $stateProvider.state('twitter-list', {
        url: BASE_PATH + "/twitter/mobile_twitter_list/index/value_id/:value_id",
        controller: 'TwitterListController',
        templateUrl: "templates/twitter/l1/list.html",
        cache: false
    });

}).controller('TwitterListController', function ($filter, $location, $q, $scope, $state, $stateParams, $timeout, $window, $translate, Twitter, Url, Dialog) {

    $scope.is_loading = true;
    $scope.value_id = Twitter.value_id = $stateParams.value_id;
    // used to indicate that there are no more tweets
    $scope.done = false;

    $scope.collection = new Array();

    Twitter.last_id = null;

    Twitter.getInfo().success(function(response) {
        $scope.name = response[0].name;
        $scope.description = response[0].description;
        $scope.banner_url = response[0].profile_banner_url;
        $scope.nb_followers = response[0].followers_count;
        $scope.nb_friends = response[0].friends_count;
    });

    $scope.getTweets = function () {
        Twitter.loadData().success(function (response) {
            $scope.collection = $scope.collection.concat(response);
            if (!response.length) $scope.done = true;
            // set the last tweet id
            Twitter.last_id = $scope.collection[$scope.collection.length - 1].id;
        }).error(function (data) {
            if (data && angular.isDefined(data.message)) {
                Dialog.alert($translate.instant("Error"), data.message, $translate.instant("OK"));
            }

            $scope.done = true;
        }).finally(function () {
            $scope.$broadcast('scroll.infiniteScrollComplete');
            $scope.is_loading = false;
        });
    };

    $scope.loadMore = function () {
        // if there are no more tweets to show return
        if ($scope.done) return;
        // load tweets
        $scope.getTweets();
    };

    $scope.removeScrollEvent = function () {
        angular.element($window).unbind('scroll');
    };

    // load tweets
    $scope.getTweets();
});