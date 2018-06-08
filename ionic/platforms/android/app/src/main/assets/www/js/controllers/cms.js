angular.module('starter').controller('CmsViewController', function ($location, $log, $rootScope, $scope, $stateParams,
                                                                   Cms, SocialSharing, Url, Places) {
    angular.extend($scope, {
        is_loading: true,
        value_id: $stateParams.value_id,
        social_sharing_active: false,
        use_pull_to_refresh: true,
        pull_to_refresh: false,
        card_design: false
    });

    if ($stateParams.type === 'places') {
        $scope.use_pull_to_refresh = false;
    }

    Cms.setValueId($stateParams.value_id);

    $scope.loadContent = function (pullToRefresh) {
        switch ($stateParams.type) {
            case 'places':
                Places.getPlace($stateParams.page_id)
                    .then(function (data) {
                        $scope.social_sharing_active = (data.social_sharing_active && $rootScope.isNativeApp);

                        $scope.blocks = data.blocks;
                        $scope.page = data.page;

                        if ($scope.page) {
                            $scope.template_header = 'templates/cms/page/l1/view/subheader.html';
                        }

                        $scope.page_title = data.page_title;
                    }).then(function () {
                        $scope.is_loading = false;
                    });

                break;
            default:
                Cms.findAll($stateParams.page_id, pullToRefresh)
                    .then(function (data) {
                        $scope.social_sharing_active = (data.social_sharing_active && $rootScope.isNativeApp);

                        $scope.blocks = data.blocks;
                        $scope.page = data.page;

                        if ($scope.page) {
                            $scope.template_header = 'templates/cms/page/l1/view/subheader.html';
                        }

                        $scope.page_title = data.page_title;
                    }, function (error) {
                        $log.error('[CmsViewController] an error occurred while loading CMS', error);
                    }).then(function () {
                        if ($scope.pull_to_refresh) {
                            $scope.$broadcast('scroll.refreshComplete');
                            $scope.pull_to_refresh = false;
                        }
                    }).then(function () {
                        $scope.is_loading = false;
                    });
        }
    };

    $scope.pullToRefresh = function () {
        $scope.pull_to_refresh = true;
        $scope.loadContent(true);
    };

    $scope.share = function () {
        var file;
        angular.forEach($scope.blocks, function (block) {
            if (block.gallery) {
                if (block.gallery.length > 0 && file === null) {
                    file = block.gallery[0].url;
                }
            }
        });

        SocialSharing.share(undefined, undefined, undefined, file);
    };

    $scope.onShowMap = function (block) {
        if ($rootScope.isNotAvailableOffline()) {
            return;
        }

        var params = {};

        if (block.latitude && block.longitude) {
            params.latitude = block.latitude;
            params.longitude = block.longitude;
        } else if (block.address) {
            params.address = encodeURI(block.address);
        }

        params.title = block.label;
        params.value_id = $scope.value_id;

        $location.path(Url.get('map/mobile_view/index', params));
    };

    $scope.addToContact = function (contact) {
        contact = {
            firstname: $scope.place.title
        };

        if ($scope.place.phone) {
            contact.phone = $scope.place.phone;
        }
        if ($scope.place.picture) {
            contact.image_url = $scope.place.picture;
        }
        if ($scope.place.address.street) {
            contact.street = $scope.place.address.street;
        }
        if ($scope.place.address.postcode) {
            contact.postcode = $scope.place.address.postcode;
        }
        if ($scope.place.address.city) {
            contact.city = $scope.place.address.city;
        }
        if ($scope.place.address.state) {
            contact.state = $scope.place.address.state;
        }
        if ($scope.place.address.country) {
            contact.country = $scope.place.address.country;
        }

        $scope.message = new Message();
    };

    $scope.loadContent(false);
}).controller('CmsViewMapController', function ($log, $scope, $stateParams, Location, Cms) {
    angular.extend($scope, {
        is_loading: true,
        value_id: $stateParams.value_id
    });

    Cms.setValueId($stateParams.value_id);

    $scope.loadContent = function () {
        Cms.findBlock($stateParams.block_id, $stateParams.page_id)
            .then(function (data) {
                $scope.block = data.block;
                $scope.page_title = data.page_title;

                var title = (data.block.title) ? data.block.title : data.block.label;

                var marker = {
                    title: title + '<br />' + data.block.address,
                    is_centered: true
                };

                if (data.block.latitude && data.block.longitude) {
                    marker.latitude = data.block.latitude;
                    marker.longitude = data.block.longitude;
                } else {
                    marker.address = data.block.address;
                }

                if (data.block.picture_url) {
                    marker.icon = {
                        url: data.block.picture_url,
                        width: 70,
                        height: 44
                    };
                }

                Location.getLocation()
                    .then(function (position) {
                        $scope.createMap(position.coords, marker);
                    }, function () {
                        $scope.createMap(null, marker);
                    });
            }, function (error) {
                $log.error('[CmsViewMapController] an error occurred while loading CMS', error);
            }).then(function () {
                $scope.is_loading = false;
            });
    };

    $scope.createMap = function (origin, destination) {
        $scope.is_loading = false;

        if (origin) {
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
