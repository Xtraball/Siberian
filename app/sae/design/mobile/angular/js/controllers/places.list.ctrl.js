App.config(function ($routeProvider) {

    $routeProvider.when(BASE_URL + "/places/mobile_list/index/value_id/:value_id", {
        controller: 'PlacesListController',
        templateUrl: BASE_URL + "/places/mobile_list/template",
        code: "places-list"
    });

}).controller('PlacesListController', function ($window, $scope, $routeParams, $location, $q, Places, Message, Url, Application) {

    $scope.$on("connectionStateChange", function(event, args) {
        if(args.isOnline == true) {
            $scope.loadContent();
        }
    });

    $scope.is_loading = true;

    $scope.value_id = Places.value_id = $routeParams.value_id;

    $scope.getCurrentPosition = function () {

        var deferred = $q.defer();

        Application.getLocation(function(position) {
            $scope.app_loader_is_visible = false;
            if(!Connection.isOnline) deferred.reject(position);
            deferred.resolve(position);
        }, function (err) {
            $scope.app_loader_is_visible = false;
            deferred.reject(err);
        });

        return deferred.promise;
    };

    $scope.loadContent = function () {

        $scope.getCurrentPosition().then(function (position) {
            $scope.position = position;
        }).finally(function () {
            Places.findAll($scope.position).success(function (data) {
                $scope.page_title = data.page_title;
                $scope.collection = data.places.reduce(function (collection, place) {
                    var item = {
                        id: place.id,
                        title: place.title,
                        subtitle: place.content,
                        picture: place.picture,
                        url: place.url
                    };
                    collection.push(item);
                    return collection;
                }, []);

            }).finally(function () {
                $scope.is_loading = false;
            });
        });

    };

    $scope.goToMap = function () {
        $location.path(Url.get("places/mobile_map/index", {
            value_id: $routeParams.value_id
        }));
    };

    $scope.loadContent();

    $scope.header_right_button = {
        action: $scope.goToMap,
        title: "Map"
    };

});