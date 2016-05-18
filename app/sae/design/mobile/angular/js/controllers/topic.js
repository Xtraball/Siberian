App.config(function($routeProvider) {

    $routeProvider.when(BASE_URL+"/topic/mobile_list/index/value_id/:value_id", {
        controller: 'TopicController',
        templateUrl: BASE_URL+"/topic/mobile_list/template",
        code: "topic"
    });

}).controller('TopicController', function($window, $scope, $routeParams, Topic, Application) {

    $scope.value_id = Topic.value_id = $routeParams.value_id;
    $scope.device_uid = Topic.device_uid = Application.device_uid;
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

    $scope.toggleSwitch = function(is_checked,params) {
        if(params.topic_id) {
            $scope.is_loading = true;
            Topic.subscribe(params.topic_id, Application.device_uid, is_checked).success(function (data) {
            }).finally(function () {
                $scope.is_loading = false;
            });
        }
    };

    $scope.loadContent();
});