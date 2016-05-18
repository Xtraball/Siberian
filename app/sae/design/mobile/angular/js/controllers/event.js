App.config(function($routeProvider) {

    $routeProvider.when(BASE_URL+"/event/mobile_list/index/value_id/:value_id", {
        controller: 'EventListController',
        templateUrl: BASE_URL+"/event/mobile_list/template",
        code: "event"
    }).when(BASE_URL+"/event/mobile_view/index/value_id/:value_id/event_id/:event_id", {
        controller: 'EventViewController',
        templateUrl: BASE_URL+"/event/mobile_view/template",
        code: "event"
    });

}).controller('EventListController', function($scope, $routeParams, $location, Event) {

    $scope.$on("connectionStateChange", function(event, args) {
        if(args.isOnline == true) {
            $scope.loadContent();
        }
    });

    $scope.is_loading = true;
    $scope.value_id = Event.value_id = $routeParams.value_id;

    $scope.factory = Event;
    $scope.collection = new Array();

    $scope.loadContent = function() {
        Event.findAll().success(function(data) {
            $scope.collection = data.collection;
            $scope.page_title = data.page_title;
        }).finally(function() {
            $scope.is_loading = false;
        });
    }

    $scope.showItem = function(item) {
        $location.path(item.url);
    }

    $scope.loadContent();

}).controller('EventViewController', function($window, $scope, $routeParams, $location, Message, Event, Url, Pictos, Application) {

    $scope.$on("connectionStateChange", function(event, args) {
        if(args.isOnline == true) {
            $scope.loadContent();
        }
    });

    $scope.is_loading = true;
    $scope.value_id = Event.value_id = $routeParams.value_id;

    $scope.loadContent = function() {
        Event.findById($routeParams.event_id).success(function(data) {
            $scope.event = data.event;
            $scope.cover = data.cover;
            $scope.page_title = data.page_title;

            if($scope.event.social_sharing_active==1 && Application.handle_social_sharing) {
                $scope.header_right_button = {
                    picto_url: Pictos.get("share", "header"),
                    hide_arrow: true,
                    action: function () {
                        $scope.sharing_data = {
                            "page_name": $scope.cover.title,
                            "picture": $scope.cover.picture ? $scope.cover.picture : null,
                            "content_url": null
                        }
                        Application.socialShareData($scope.sharing_data);
                    },
                    height: 25
                };
            }
        }).error(function(data) {
            if(data && angular.isDefined(data.message)) {
                $scope.message = new Message();
                $scope.message.isError(true)
                    .setText(data.message)
                    .show()
                ;
            }
        }).finally(function() {
            $scope.is_loading = false;
        });
    }

    $scope.openMaps = function() {

        var address = $scope.event.address;
        address = encodeURI(address);
        $location.path(Url.get("map/mobile_view/index", {
            address: address,
            title: $scope.event.title,
            value_id: $scope.value_id
        }));

    };

    $scope.openRsvp = function() {
        $window.open($scope.event.rsvp);
    };

    $scope.loadContent();

});