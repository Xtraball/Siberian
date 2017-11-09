App.config(function ($routeProvider) {

    $routeProvider.when(BASE_URL + "/map/mobile_view/index/address/:address/title/:title/value_id/:value_id", {
        controller: 'MapController',
        templateUrl: BASE_URL + "/map/mobile_view/template",
        code: "map"
    }).when(BASE_URL + "/map/mobile_view/index/latitude/:latitude/longitude/:longitude/value_id/:value_id", {
        controller: 'MapController',
        templateUrl: BASE_URL + "/map/mobile_view/template",
        code: "map"
    }).when(BASE_URL + "/map/mobile_view/index/latitude/:latitude/longitude/:longitude/title/:title/value_id/:value_id", {
        controller: 'MapController',
        templateUrl: BASE_URL + "/map/mobile_view/template",
        code: "map"
    });

}).controller('MapController', function ($window, $scope, $routeParams, Message, GoogleMapService) {

    $scope.$on("connectionStateChange", function(event, args) {
        if(args.isOnline == true) {
            $scope.loadContent();
        }
    });

    if ($routeParams.title) {
        $scope.page_title = $routeParams.title;
    }

    $scope.message = new Message();

    $scope.loadContent = function () {

        if ($routeParams.address) {

            var address = decodeURI($routeParams.address);

            GoogleMapService.geocode(address).then(function (coordinates) {
                $scope.showMap(coordinates.latitude, coordinates.longitude);
            }, function (error) {
                $scope.message.setText(error)
                    .isError(true)
                    .show();
            });

        } else if ($routeParams.latitude && $routeParams.longitude) {
            $scope.showMap($routeParams.latitude, $routeParams.longitude);
        }
    }

    $scope.showMap = function (latitude, longitude) {

        $scope.mapConfig = {
            center: {
                latitude: latitude,
                longitude: longitude
            },
            markers: [{
                title: $scope.page_title,
                latitude: latitude,
                longitude: longitude
            }]
        };

    }

    $scope.loadContent();

});