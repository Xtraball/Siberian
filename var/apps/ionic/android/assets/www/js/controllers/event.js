App.config(function($stateProvider) {

    $stateProvider.state('event-list', {
        url: BASE_PATH+"/event/mobile_list/index/value_id/:value_id",
        controller: 'EventListController',
        templateUrl: 'templates/event/l1/list.html'
    }).state('event-view', {
        url: BASE_PATH+"/event/mobile_view/index/value_id/:value_id/event_id/:event_id",
        controller: 'EventViewController',
        templateUrl: 'templates/event/l1/view.html'
    }).state("event-map", {
        url: BASE_PATH+'/event/mobile_map/index/value_id/:value_id/event_id/:event_id',
        templateUrl: 'templates/html/l1/maps.html',
        controller: 'EventMapController'
    });

}).controller('EventListController', function($scope, $state, $stateParams, Event) {

    $scope.$on("connectionStateChange", function(event, args) {
        if(args.isOnline == true) {
            $scope.loadContent();
        }
    });

    $scope.is_loading = true;
    $scope.value_id = Event.value_id = $stateParams.value_id;
    $scope.collection = new Array();
    $scope.groups = new Array();
    $scope.can_load_older_posts = false;

    $scope.loadContent = function() {

        var offset = $scope.collection.length;

        Event.findAll(offset).success(function(data) {

            if(data.page_title) {
                $scope.page_title = data.page_title;
            }

            if(data.collection) {
                $scope.collection = $scope.collection.concat(data.collection);
            }

            if(data.groups) {
                angular.forEach(data.groups, function(group) {
                    if($scope.groups.indexOf(group) < 0) {
                        $scope.groups.push(group);
                    }
                });
            }

            $scope.can_load_older_posts = data.collection.length > 0;

        }).finally(function() {
            $scope.is_loading = false;
            $scope.$broadcast('scroll.infiniteScrollComplete');
        });
    };

    $scope.showItem = function(item) {
        $state.go("event-view", { value_id: $scope.value_id, event_id: item.id });
    };

    $scope.loadMore = function() {
        $scope.loadContent();
    };

    $scope.loadContent();

}).controller('EventViewController', function($rootScope, $translate, $scope, $state, $stateParams, $window, Dialog, Event, Url/*, Message, Pictos, Application*/) {

    $scope.$on("connectionStateChange", function(event, args) {
        if(args.isOnline == true) {
            $scope.loadContent();
        }
    });

    $scope.is_loading = true;
    $scope.value_id = Event.value_id = $stateParams.value_id;

    $scope.loadContent = function() {

        Event.findById($stateParams.event_id).success(function(data) {

            $scope.item = data.event;
            $scope.cover = data.cover;
            $scope.page_title = data.page_title;

            /*if($scope.event.social_sharing_active==1 && Application.handle_social_sharing) {
                $scope.header_right_button = {
                    picto_url: Pictos.get("share", "header"),
                    hide_arrow: true,
                    action: function () {
                        $scope.sharing_data = {
                            "page_name": $scope.cover.title,
                            "picture": $scope.cover.picture ? $scope.cover.picture : null,
                            "content_url": null
                        };
                        Application.socialShareData($scope.sharing_data);
                    },
                    height: 25
                };
            }*/

        }).error(function(data) {

            if(data && angular.isDefined(data.message)) {
                Dialog.alert($translate.instant("Error"), data.message, $translate.instant("OK"));
            }

        }).finally(function() {
            $scope.is_loading = false;
        });
    };

    $scope.openLink = function(url) {
        $window.open(url, $rootScope.getTargetForLink(), "location=no");
    };

    $scope.openMaps = function() {
        $state.go("event-map", { value_id: $scope.value_id, event_id: $stateParams.event_id });
    };

    $scope.loadContent();

}).controller('EventMapController', function($scope, $stateParams, Event, GoogleMaps) {

    //$scope.is_loading = true;
    $scope.value_id = Event.value_id = $stateParams.value_id;

    Event.findById($stateParams.event_id).success(function(data) {

        $scope.page_title = data.page_title;

        if(data.event.address) {

            GoogleMaps.geocode(data.event.address).then(function(position) {

                if(position.latitude && position.longitude) {

                    var marker = {
                        title: data.event.title + "<br />" + data.event.address,
                        is_centered: true,
                        latitude: position.latitude,
                        longitude: position.longitude
                    };

                    if(data.cover.picture) {
                        marker.icon = {
                            url: data.cover.picture,
                            width: 49,
                            height: 49
                        }
                    }

                    $scope.map_config = {
                        markers: [marker]
                    };

                }
            });

        }


    }).finally(function() {
        $scope.is_loading = false;
    });

});