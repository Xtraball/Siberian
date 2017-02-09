App.config(function($stateProvider) {

    $stateProvider.state("push-list", {
        url: BASE_PATH + "/push/mobile_list/index/value_id/:value_id",
        controller: 'PushController',
        templateUrl: 'templates/html/l1/list.html',
        cache: false
    });

}).controller('PushController', function($location, $rootScope, $scope, $stateParams, $window, PUSH_EVENTS, Push) {

    $scope.$on("connectionStateChange", function(event, args) {
        if(args.isOnline == true) {
            $scope.loadContent();
        }
    });

    $scope.is_loading = true;
    $scope.value_id = Push.value_id = $stateParams.value_id;
    $scope.toggle_text = false;

    $scope.device_uid = Push.device_uid;
    if(!Push.device_uid) {
        try {
            $scope.device_uid = Push.device_uid = device.uuid;
        } catch (e) {
            $scope.device_uid = null;
        }
    }

    $scope.collection = new Array();

    $scope.loadContent = function() {

        Push.findAll().success(function(data) {
            if(data.collection.length) {
                $scope.collection = data.collection;
                $rootScope.$broadcast(PUSH_EVENTS.readPushs);
                //Application.call("markPushAsRead");
            } else {
                $scope.collection_is_empty = true;
            }
            $scope.page_title = data.page_title;
        }).finally(function() {
            $scope.is_loading = false;
        });
    };

    $scope.showItem = function(item) {
        if($rootScope.isOffline) {
            return $rootScope.onlineOnly();
        }

        if(item.url) {
            $window.open(item.url, $rootScope.getTargetForLink(), "location=no");
        }else if(item.action_value) {
            $location.path(item.action_value);
        }else{
            $scope.toggle_text = !$scope.toggle_text;
        }
    };

    $scope.loadContent();

});
