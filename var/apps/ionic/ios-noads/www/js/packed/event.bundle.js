/*global
 App, BASE_PATH, angular
 */

angular.module('starter').controller('EventListController', function ($scope, $state, $stateParams, $timeout, Event) {
    angular.extend($scope, {
        is_loading              : true,
        value_id                : $stateParams.value_id,
        collection              : [],
        groups                  : [],
        load_more               : false,
        use_pull_refresh        : true,
        pull_to_refresh         : false,
        card_design             : false,
        module_code             : 'event'
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
}).controller('EventViewController', function ($rootScope, $scope, $state, $stateParams, $window, Dialog, Event) {
    angular.extend($scope, {
        is_loading      : true,
        value_id        : $stateParams.value_id,
        card_design     : false
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

    $scope.openLink = function (url) {
        /** @todo Handle links with LinkService */
        $window.open(url, $rootScope.getTargetForLink(), 'location=no');
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

    Event.getEvent($stateParams.event_id)
        .then(function (data) {
            $scope.page_title = data.page_title;

            if (data.event.address) {
                GoogleMaps.geocode(data.event.address)
                    .then(function (position) {
                        if (position.latitude && position.longitude) {
                            var marker = {
                                title: data.event.title + "<br />" + data.event.address,
                                is_centered: true,
                                latitude: position.latitude,
                                longitude: position.longitude
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
                    });
            }
        }).then(function () {
            $scope.is_loading = false;
        });
});
;/* global
    App, angular
 */

/**
 * Event
 *
 * @author Xtraball SAS
 */
angular.module('starter').factory('Event', function ($pwaRequest) {
    var factory = {
        value_id: null,
        collection: [],
        extendedOptions: {}
    };

    /**
     *
     * @param value_id
     */
    factory.setValueId = function (value_id) {
        factory.value_id = value_id;
    };

    /**
     *
     * @param options
     */
    factory.setExtendedOptions = function (options) {
        factory.extendedOptions = options;
    };

    /**
     * Custom Page
     *
     * @todo preload only the second page, next pages are preloaded only once furthermore, to reduce data usage.
     *
     * @param page
     */
    factory.preFetch = function () {
        factory.findAll(0);
    };

    factory.findAll = function (offset, refresh) {
        if (!this.value_id) {
            $pwaRequest.reject('[Factory::Event.findAll] missing value_id');
        }

        return $pwaRequest.get('event/mobile_list/findall',
            angular.extend({
                urlParams: {
                    value_id: this.value_id,
                    offset: offset
                },
                refresh: refresh,
                timeout: 30000
            }, factory.extendedOptions)
        );
    };

    factory.findById = function (event_id, refresh) {
        if (!this.value_id) {
            $pwaRequest.reject('[Factory::Event.findById] missing value_id');
        }

        return $pwaRequest.get('event/mobile_view/find', {
            urlParams: {
                value_id: this.value_id,
                event_id: event_id
            },
            refresh: refresh,
            timeout: 30000
        });
    };

    /**
     * Search for event payload inside cached collection
     *
     * @param event_id
     * @returns {*}
     */
    factory.getEvent = function (event_id) {
        if (!this.value_id) {
            return $pwaRequest.reject('[Factory::Event.getEvent] missing value_id');
        }

        var event = _.get(_.filter(factory.collection, function (item) {
            return (item.id == event_id);
        })[0], 'embed_payload', false);

        if (!event) {
            /** Well then fetch it. */
            return factory.findById(event_id);
        }

        return $pwaRequest.resolve(event);
    };

    return factory;
});
