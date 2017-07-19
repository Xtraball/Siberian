/*global
 App, angular, BASE_PATH
 */

angular.module("starter").controller("TopicController", function($rootScope, $scope, $stateParams, $timeout, Topic) {

    angular.extend($scope, {
        is_loading: true,
        value_id: $stateParams.value_id,
        no_items: false
    });

    Topic.setValueId($stateParams.value_id);

    $scope.loadContent = function() {

        Topic.findAll()
            .then(function(data) {
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

                $scope.is_loading = false;

            });
    };

    $scope.toggle = function(item) {

        if($rootScope.isNotAvailableOffline()) {
            $timeout(function() {
                item.is_subscribed = !item.is_subscribed;
            }, 200);
            return;
        }

        $scope.is_loading = true;

        Topic.subscribe(item.id, item.is_subscribed)
            .then(function() {
                Topic.findAll();
                $scope.is_loading = false;
            });
    };

    $scope.loadContent();

});
