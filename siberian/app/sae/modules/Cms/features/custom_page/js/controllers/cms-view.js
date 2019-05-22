/**
 * @author Xtraball SAS <dev@xtraball.com>
 * @version 4.17.0
 */
angular
.module("starter")
.controller("CmsViewController", function ($location, $log, $rootScope, $scope, $stateParams, Cms, SocialSharing, Url,
                                           Places) {
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
                Cms.findAll($stateParams.page_id, true)
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
});