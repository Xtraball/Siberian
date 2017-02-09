App.config(function($stateProvider) {

    $stateProvider.state('topic-list', {
        url: BASE_PATH+"/topic/mobile_list/index/value_id/:value_id",
        controller: 'TopicController',
        templateUrl: "templates/topic/l1/list.html"
    });

}).controller('TopicController', function($cordovaDevice, $rootScope, $scope, $stateParams, $timeout, $translate, $window, Application, Push, Topic) {

    $scope.value_id = Topic.value_id = $stateParams.value_id;

    try {
        if(!Application.is_webview) {
            $scope.device_uid = Topic.device_uid = Push.device_uid;
        }
    } catch(e) {
        $scope.device_uid = null;
    }
    $scope.is_loading = true;
    $scope.no_items = false;

    $scope.loadContent = function() {

        Topic.findAll().success(function(data) {
            if(data.collection && data.collection.length) {
                $scope.items = data.collection;
            } else {
                $scope.no_items = true;
            }

            if(data.description) {
                $scope.description = data.description;
            } else {
                $scope.description = null;
            }

            $scope.page_title = data.page_title;

        }).finally(function() {
            $scope.is_loading = false;
        });
    };

    $scope.toggle = function(item) {

        if(Application.is_webview) {
            $timeout(function() {
                item.is_subscribed = !item.is_subscribed;
                $rootScope.showMobileFeatureOnlyError();
            }, 500);
            return;
        }

        if($rootScope.isOffline) {
            $timeout(function() {
                item.is_subscribed = !item.is_subscribed;
                $rootScope.onlineOnly();
            }, 500);
            return;
        }

        $scope.is_loading = true;
        Topic.subscribe(item.id, item.is_subscribed).finally(function () {
            $scope.is_loading = false;
        });
    };

    $scope.loadContent();

});
