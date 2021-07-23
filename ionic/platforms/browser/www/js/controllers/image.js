/**
 * ImageListController
 *
 * @author Xtraball SAS <dev@xtraball.com>
 * @version 4.20.12
 */
angular
    .module('starter')
    .controller('ImageListController', function ($scope, $stateParams, $timeout, $translate, $window, $q,
                                                 Url, Image, Lightbox, Loader) {
    angular.extend($scope, {
        is_loading: false,
        can_load_more: false,
        images: [],
        collection: [],
        show_galleries: false,
        value_id: $stateParams.value_id,
        card_design: false
    });

    Image.setValueId($stateParams.value_id);

    $scope.loadContent = function (refresh) {
        $scope.is_loading = true;
        Loader.show($translate.instant('Loading...', 'media'));

        Image
            .findAll(refresh)
            .then(function (data) {
                $scope.galleries = data.galleries;
                $scope.page_title = data.page_title;
                if ($scope.galleries.length) {
                    $scope.showGallery($scope.galleries[0]);
                } else {
                    $scope.is_loading = true;
                    Loader.hide();
                }
            });
    };

    $scope.listDidRender = function () {
        $timeout(function () {
            Lightbox.run('.media-images-gallery');
        }, 200);
    };

    $scope.imagePath = function (src) {
        return src;
    };

    $scope.showGallery = function (gallery) {
        $scope.show_galleries = false;
        $scope.button_label = gallery.name;

        if ($scope.current_gallery && ($scope.current_gallery.id === gallery.id)) {
            return;
        }

        $scope.is_loading = true;

        $scope.collection = [];
        $scope.current_gallery = gallery;

        $scope
            .loadGallery()
            .then(function () {
                $scope.is_loading = false;
                Loader.hide();
            });
    };

    $scope.loadGallery = function () {
        var defer = $q.defer();

        var offset = 0;
        if ($scope.collection.length) {
            offset = $scope.collection.length;
        }
        if (offset === 0) {
            Loader.show($translate.instant('Loading...', 'media'));
        }

        $scope.loadPage = ($scope.current_gallery.nextPage !== false) ? $scope.current_gallery.nextPage : $scope.current_gallery.currentPage;

        switch ($scope.current_gallery.type) {
            case "facebook":
                Image
                    .findFacebook($scope.current_gallery, $scope.loadPage)
                    .then(function (data) {
                        for (var i = 0; i < data.collection.length; i = i + 1) {
                            $scope.collection.push(data.collection[i]);
                        }
                        $scope.can_load_more = !!data.nextPage;

                        $scope.current_gallery.currentPage = data.currentPage;
                        $scope.current_gallery.nextPage = data.nextPage;
                    }).then(function () {
                        $scope.$broadcast('scroll.infiniteScrollComplete');
                        defer.resolve();
                    });
                break;
            default:
                Image
                    .find($scope.current_gallery, offset)
                    .then(function (data) {
                        for (var i = 0; i < data.collection.length; i = i + 1) {
                            $scope.collection.push(data.collection[i]);
                        }
                        $scope.can_load_more = data.collection.length > 0 && data.show_load_more;
                    }).then(function () {
                        $scope.$broadcast('scroll.infiniteScrollComplete');
                        defer.resolve();
                    });
        }

        return defer.promise;
    };

    $scope.toggleGalleries = function () {
        $scope.show_galleries = !$scope.show_galleries;
    };

    // Overview fresh features
    if (isOverview) {
        $window.overview.features.image = {
            loadContent: function () {
                $scope.loadContent(true);
            }
        };
    }

    $scope.loadContent();
});
