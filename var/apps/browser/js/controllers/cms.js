App.config(function($stateProvider) {

    $stateProvider.state('cms-view', {
        url: BASE_PATH + "/cms/mobile_page_view/index/value_id/:value_id",
        controller: 'CmsViewController',
        templateUrl: "templates/cms/page/l1/view.html"
    }).state('cms-view-map', {
        url: BASE_PATH + "/cms/mobile_page_view_map/index/value_id/:value_id/page_id/:page_id/block_id/:block_id",
        params: {
            page_id: 0
        },
        controller: 'CmsViewMapController',
        templateUrl: "templates/html/l1/maps.html"
    });

}).controller('CmsViewController', function($cordovaSocialSharing, $sbhttp, $location, $scope, $stateParams, $timeout, $translate, Application, Cms, Url/*, Pictos*/) {

    $scope.$on("connectionStateChange", function(event, args) {
        if(args.isOnline == true) {
            $scope.loadContent();
        }
    });

    $scope.social_sharing_active = false;
    $scope.is_loading = true;
    $scope.value_id = Cms.value_id = $stateParams.value_id;

    $scope.loadContent = function() {
        Cms.findAll($stateParams.page_id).success(function(data) {
            $scope.social_sharing_active = !!(data.social_sharing_active == 1 && !Application.is_webview);

            $scope.blocks = data.blocks;
            $scope.page = data.page;

            if($scope.page) {
                $scope.template_header = "templates/cms/page/l1/view/subheader.html";
            }

            $scope.page_title = data.page_title;

        }).error(function() {

        }).finally(function() {
            $scope.is_loading = false;
        });

    };

    $scope.share = function () {

        // Fix for $cordovaSocialSharing issue that opens dialog twice
        if($scope.is_sharing) return;

        $scope.is_sharing = true;

        var app_name = Application.app_name;
        var message = "";
        var link = DOMAIN + "/application/device/downloadapp/app_id/" + Application.app_id;
        var subject = "";
        var file = "";

        angular.forEach($scope.blocks, function (block) {
            if (block.gallery) {
                if (block.gallery.length > 0 && file === null) {
                    file = block.gallery[0].url;
                }
            }
        });

        message = $translate.instant("Hi. I just found: $1 in the $2 app.").replace("$1", $scope.page_title).replace("$2", app_name);

        $cordovaSocialSharing
            .share(message, subject, file, link) // Share via native share sheet
            .then(function (result) {
                console.log("success");
                $scope.is_sharing = false;
            }, function (err) {
                console.log(err);
                $scope.is_sharing = false;
            });
    };

    $scope.onShowMap = function (block) {
        if($rootScope.isOffline) {
            $rootScope.onlineOnly();
            return;
        }

        params = {};

        if(block.latitude && block.longitude) {
            params.latitude = block.latitude;
            params.longitude = block.longitude;
        } else if(block.address) {
            params.address = encodeURI(block.address);
        }

        params.title = block.label;
        params.value_id = $scope.value_id;

        $location.path(Url.get("map/mobile_view/index", params));
    };

    $scope.addToContact = function(contact) {

        var contact = { firstname: $scope.place.title };

        if($scope.place.phone) contact.phone = $scope.place.phone;
        if($scope.place.picture) contact.image_url = $scope.place.picture;
        if($scope.place.address.street) contact.street = $scope.place.address.street;
        if($scope.place.address.postcode) contact.postcode = $scope.place.address.postcode;
        if($scope.place.address.city) contact.city = $scope.place.address.city;
        if($scope.place.address.state) contact.state = $scope.place.address.state;
        if($scope.place.address.country) contact.country = $scope.place.address.country;

        $scope.message = new Message();
    };

    $scope.loadContent();

}).controller('CmsViewMapController', function($scope, $stateParams, $cordovaGeolocation, Cms) {

    $scope.$on("connectionStateChange", function(event, args) {
        if(args.isOnline == true) {
            $scope.loadContent();
        }
    });

    $scope.is_loading = true;
    $scope.value_id = Cms.value_id = $stateParams.value_id;

    $scope.loadContent = function() {

        Cms.findBlock($stateParams.block_id, $stateParams.page_id).success(function(data) {

            $scope.block = data.block;
            $scope.page_title = data.page_title;

            var title = data.block.title ? data.block.title : data.block.label;

            var marker = {
                title: title + "<br />" + data.block.address,
                is_centered: true
            };

            if(data.block.latitude && data.block.longitude) {
                marker.latitude = data.block.latitude;
                marker.longitude = data.block.longitude;
            } else {
                marker.address = data.block.address;
            }

            if(data.block.picture_url) {
                marker.icon = {
                    url: data.block.picture_url,
                    width: 70,
                    height: 44
                }
            }

            $cordovaGeolocation.getCurrentPosition({ enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }).then(function(position) {
                $scope.createMap(position.coords, marker);
            }, function() {
                $scope.createMap(null, marker);
            });


        }).error(function() {
            $scope.is_loading = false;
        });

    };

    $scope.createMap = function(origin, destination) {

        $scope.is_loading = false;

        if(origin) {

            $scope.map_config = {
                coordinates: {
                    origin: origin,
                    destination: destination
                }
            };

        } else {
            $scope.map_config = {
                markers: [destination]
            };
        }

    };

    $scope.loadContent();
});
