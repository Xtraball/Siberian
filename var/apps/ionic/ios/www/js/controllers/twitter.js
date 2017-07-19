/*global
 App, angular, BASE_PATH
 */

angular.module("starter").controller("TwitterListController", function ($scope, $stateParams, $window, $translate,
                                                                        Twitter, Dialog) {

    angular.extend($scope, {
        is_loading: true,
        value_id: $stateParams.value_id,
        done: false,
        collection: [],
        card_design: false
    });

    Twitter.setValueId($stateParams.value_id);
    Twitter.last_id = null;

    Twitter.getInfo()
        .then(function(response) {
            $scope.name             = response[0].name;
            $scope.description      = response[0].description;
            $scope.banner_url       = response[0].profile_banner_url;
            $scope.nb_followers     = response[0].followers_count;
            $scope.nb_friends       = response[0].friends_count;
        });

    $scope.getTweets = function () {
        Twitter
            .loadData()
            .then(function (response) {
                $scope.collection = $scope.collection.concat(response);
                if (!response.length) {
                    $scope.done = true;
                }
                // set the last tweet id
                Twitter.last_id = $scope.collection[$scope.collection.length - 1].id;
            }, function (data) {
                if (data && angular.isDefined(data.message)) {
                    Dialog.alert($translate.instant("Error"), data.message, $translate.instant("OK"), -1);
                }

                $scope.done = true;
            }).then(function () {
                $scope.$broadcast("scroll.infiniteScrollComplete");
                $scope.is_loading = false;
            });
    };

    $scope.loadMore = function () {
        // if there are no more tweets to show return
        if ($scope.done) {
            return;
        }
        // load tweets
        $scope.getTweets();
    };

    /**
     * @todo what's the purpose of this ?
     */
    $scope.removeScrollEvent = function () {
        angular.element($window).unbind("scroll");
    };

    // load tweets
    $scope.getTweets();
});