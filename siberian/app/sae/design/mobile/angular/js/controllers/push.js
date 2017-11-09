App.config(function($routeProvider) {

    $routeProvider.when(BASE_URL+"/push/mobile_list/index/value_id/:value_id", {
        controller: 'PushController',
        templateUrl: BASE_URL+"/push/mobile_list/template",
        code: "push"
    });

}).controller('PushController', function($rootScope, $scope, $routeParams, $location, PUSH_EVENTS, Push) {

    $scope.$on("connectionStateChange", function(event, args) {
        if(args.isOnline == true) {
            $scope.loadContent();
        }
    });

    $scope.is_loading = true;
    $scope.value_id = Push.value_id = $routeParams.value_id;

    $scope.factory = Push;
    $scope.collection = new Array();

    $scope.loadContent = function() {

        Push.findAll().success(function(data) {
            if(data.collection.length) {
                $scope.collection = data.collection;
                $rootScope.$broadcast(PUSH_EVENTS.readPushs);
                Application.call("markPushAsRead");
            } else {
                $scope.collection_is_empty = true;
            }
            $scope.page_title = data.page_title;
        }).finally(function() {
            $scope.is_loading = false;
        });
    };

    $scope.showItem = function(item) {
        if(item.action_value) {
            $location.path(item.action_value);
        } else if(item.url) {
            $location.path(item.url);
        }
    };

    $scope.loadContent();

});