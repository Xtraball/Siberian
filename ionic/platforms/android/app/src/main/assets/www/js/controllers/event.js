/*global
 App, BASE_PATH, angular
 */

angular.module('starter').controller('EventListController', function ($scope, $state, $stateParams, $timeout, Event) {
    angular.extend($scope, {
        is_loading: true,
        value_id: $stateParams.value_id,
        collection: [],
        groups: [],
        load_more: false,
        use_pull_refresh: true,
        pull_to_refresh: false,
        card_design: false,
        module_code: 'event'
    });

    Event.setValueId($stateParams.value_id);

    $scope.loadContent = function (loadMore) {
        var offset = $scope.collection.length;

        Event.findAll(offset)
            .then(function (data) {

                if (data.page_title) {
                    $scope.page_title = data.page_title;
                }

                if (data.collection) {
                    $scope.collection = $scope.collection.concat(data.collection);
                    Event.collection = $scope.collection;
                }

                if (data.groups) {
                    angular.forEach(data.groups, function (group) {
                        if ($scope.groups.indexOf(group) < 0) {
                            $scope.groups.push(group);
                        }
                    });
                }

                $scope.load_more = (data.collection.length >= data.displayed_per_page);
            }).then(function () {
            if (loadMore) {
                $scope.$broadcast('scroll.infiniteScrollComplete');
            }
            $scope.is_loading = false;
        });
    };

    $scope.pullToRefresh = function () {
        $scope.pull_to_refresh = true;
        $scope.load_more = false;

        Event.findAll(0, true)
            .then(function (data) {
                if (data.collection) {
                    $scope.collection = data.collection;
                    Event.collection = $scope.collection;

                    $scope.groups = [];
                }

                if (data.groups) {
                    angular.forEach(data.groups, function (group) {
                        if ($scope.groups.indexOf(group) < 0) {
                            $scope.groups.push(group);
                        }
                    });
                }

                $scope.load_more = (data.collection.length === data.displayed_per_page);
            }).then(function () {
            $scope.$broadcast('scroll.refreshComplete');
            $scope.pull_to_refresh = false;

            $timeout(function () {
                $scope.can_load_older_posts = !!$scope.collection.length;
            }, 500);
        });
    };

    $scope.showItem = function (item) {
        $state.go('event-view', {
            value_id: $scope.value_id,
            event_id: item.id
        });
    };

    $scope.loadContent(false);
}).controller('EventViewController', function ($rootScope, $scope, $state, $stateParams, $window,
                                               Dialog, Event, LinkService) {
    angular.extend($scope, {
        is_loading: true,
        value_id: $stateParams.value_id,
        card_design: false
    });

    Event.setValueId($stateParams.value_id);

    $scope.loadContent = function () {
        Event.getEvent($stateParams.event_id)
            .then(function (data) {
                $scope.item = data.event;
                $scope.cover = data.cover;
                $scope.page_title = data.page_title;
            }, function (data) {
                if (data && angular.isDefined(data.message)) {
                    Dialog.alert('Error', data.message, 'OK', -1);
                }
            }).then(function () {
            $scope.is_loading = false;
        });
    };

    // Open navigation intent!
    $scope.navigateTo = function (event) {
        Navigator.navigate({
            'lat': event.geo[0],
            'lng': event.geo[1]
        });
    };

    $scope.openLink = function (url) {
        LinkService.openLink(url, {}, true);
    };

    $scope.openMaps = function () {
        $state.go('event-map', {
            value_id: $scope.value_id,
            event_id: $stateParams.event_id
        });
    };

    $scope.loadContent();

}).controller('EventMapController', function ($scope, $stateParams, Event, GoogleMaps) {
    angular.extend($scope, {
        value_id: $stateParams.value_id
    });

    Event.setValueId($stateParams.value_id);

    $scope.loadEvent = function () {
        Event
            .getEvent($stateParams.event_id)
            .then(function (data) {
                $scope.page_title = data.page_title;

                if (data.event.geo !== false &&
                    data.event.geo[0] &&
                    data.event.geo[1]) {
                    var marker = {
                        title: data.event.title + "<br />" + data.event.address,
                        is_centered: true,
                        latitude: data.event.geo[0],
                        longitude: data.event.geo[1]
                    };

                    if (data.cover.picture) {
                        marker.icon = {
                            url: data.cover.picture,
                            width: 49,
                            height: 49
                        };
                    }

                    $scope.map_config = {
                        markers: [marker]
                    };
                }
            }).then(function () {
            $scope.is_loading = false;
        });
    };

    // Init and callback, to be sure gmaps is loaded!
    GoogleMaps.addCallback($scope.loadEvent);
});
