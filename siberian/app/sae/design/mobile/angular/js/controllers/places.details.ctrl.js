App.config(function ($routeProvider) {

    $routeProvider.when(BASE_URL + "/places/mobile_details/index/value_id/:value_id/place_id/:place_id", {
        controller: 'PlacesDetailsController',
        templateUrl: BASE_URL + "/cms/mobile_page_view/template",
        code: "places-details cms"
    });

}).controller('PlacesDetailsController', function ($scope, $routeParams, $location, Cms, Message, ImageGallery, Url, Pictos, Application) {

    $scope.$on("connectionStateChange", function(event, args) {
        if(args.isOnline == true) {
            $scope.loadContent();
        }
    });

    $scope.gallery = ImageGallery;
    $scope.is_loading = true;

    $scope.value_id = Cms.value_id = $routeParams.value_id;

    $scope.loadContent = function () {
        Cms.find($routeParams.place_id).success(function(data) {
            $scope.blocks = data.blocks;

            $scope.page_title = data.page_title;
            $scope.page_picture = data.picture;
            $scope.social_sharing_active = data.social_sharing_active;

            if($scope.social_sharing_active==1 && Application.handle_social_sharing) {
                $scope.header_right_button = {
                    picto_url: Pictos.get("share", "header"),
                    hide_arrow: true,
                    action: function () {
                        $scope.sharing_data = {
                            "page_name": $scope.page_title,
                            "picture": $scope.page_picture ? $scope.page_picture : null,
                            "content_url": null
                        }
                        Application.socialShareData($scope.sharing_data);
                    },
                    height: 25,
                    click_width: 30
                };
            }
        }).error(function() {

        }).finally(function() {
            $scope.is_loading = false;
        });
    };

    $scope.onShowMap = function (address) {
        $location.path(Url.get("places/mobile_detailsmap/index", {
            value_id: $routeParams.value_id,
            place_id: $routeParams.place_id
        }));
    };

    //$scope.header_right_button = {
    //    action: $scope.goToMap,
    //    title: "Map"
    //};

    $scope.loadContent();

});