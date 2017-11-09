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
;/*global
 App, angular
 */

/**
 * Topic
 *
 * @author Xtraball SAS
 */
angular.module("starter").factory("Topic", function($pwaRequest, $session) {

    var factory = {
        value_id        : null,
        extendedOptions : {}
    };

    /**
     *
     * @param value_id
     */
    factory.setValueId = function(value_id) {
        factory.value_id = value_id;
    };

    /**
     *
     * @param options
     */
    factory.setExtendedOptions = function(options) {
        factory.extendedOptions = options;
    };

    factory.findAll = function() {

        if(!this.value_id) {
            return $pwaRequest.reject("[Factory::Topic.findAll] missing value_id");
        }

        return $pwaRequest.get("topic/mobile_list/findall", angular.extend({
            urlParams: {
                value_id    : this.value_id,
                device_uid  : $session.getDeviceUid()
            }
        }, factory.extendedOptions));
    };

    factory.subscribe = function(topic_id, is_subscribed) {

        return $pwaRequest.post("topic/mobile_list/subscribe", {
            data: {
                category_id : topic_id,
                device_uid  : $session.getDeviceUid(),
                subscribe   : is_subscribed
            }
        });
    };

    return factory;
});
