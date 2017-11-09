/**
 * CMS Directives
 */
angular.module('starter').directive('sbCmsText', function () {
    return {
        restrict: 'A',
        scope: {
            block: '='
        },
        template:
        '<div class="item item-text-wrap item-custom sb-cms-text">' +
        '   <img width="{{block.size}}%" ng-src="{{ block.image_url }}" ng-if="block.image.length" class="{{ block.alignment }}" />' +
        '   <div class="content" ng-bind-html="block.content | trusted_html" sb-a-click></div>' +
        '   <div class="cb"></div>' +
        '</div>'
    };
}).directive('sbCmsImage', function () {
    return {
        restrict: 'A',
        scope: {
            block: '=',
            gallery: '='
        },
        template:
        '<div class="item item-image-gallery item-custom">' +
        '   <ion-scroll direction="y">' +
        '       <ion-gallery ion-gallery-items="block.gallery" ng-if="!is_loading"></ion-gallery>' +
        '   </ion-scroll>' +
        '</div>' +
        '<div ng-if="block.description" class="item item-custom padding description">{{ block.description }}</div>'
    };
}).directive('sbCmsCover', function () {
    return {
        restrict: 'A',
        scope: {
            block: '='
        },
        template: '<img width="100%" ng-src="{{ block.gallery[0].src }}" alt="{{block.name}}">'
    };
}).directive('sbCmsSlider', function () {
    return {
        restrict: 'A',
        scope: {
            block: '=',
            gallery: '='
        },
        template:
        '<div class="item item-image-gallery item-custom sb-cms-image">' +
        '   <ul rn-carousel rn-carousel-index="carouselIndex" class="image">' +
        '       <li ng-repeat="image in block.gallery">' +
        '           <div sb-image image-src="image.src" ng-click="showFullscreen($index)"></div>' +
        '       </li>' +
        '   </ul>' +
        '   <div rn-carousel-indicators ng-if="block.gallery.length > 1" slides="block.gallery" rn-carousel-index="carouselIndex"></div>' +
        '</div>' +
        '<div ng-if="block.description" class="item item-custom padding description">{{ block.description }}</div>' +
        '<script id="zoom-modal.html" type="text/ng-template">'+
        '   <div class="sb-cms-image modal fullscreen">'+
        '       <ion-header-bar class="bar-dark"><button class="button button-clear pull-right" ng-click="hideFullscreen()">{{ "Done" | translate }}</button></ion-header-bar>' +
        '       <ion-content>' +
        '           <ul rn-carousel rn-carousel-index="carouselIndexModal" class="image" id="zoomedImage">' +
        '               <li ng-repeat="image in block.gallery">' +
        '                   <ion-scroll delegate-handle="slide-{{ $index }}" direction="xy" zooming="true" ng-swipe-left="setCarouselIndex($index+1)" ng-swipe-right="setCarouselIndex($index-1)">' +
        '                       <div class="image" sb-image image-src="image.src"></div>' +
        '                   </ion-scroll>' +
        '               </li>' +
        '           </ul>' +
        '           <div rn-carousel-indicators ng-if="block.gallery.length > 1" slides="block.gallery" rn-carousel-index="carouselIndexModal"></div>' +
        '       </ion-content>' +
        '   </div>'+
        '</script>',
        controller: function ($ionicGesture, Modal, $ionicScrollDelegate, $scope) {
            $scope.carouselIndex = 0;

            $scope.is_fullscreen = false;

            $scope.setCarouselIndex = function (index) {
                var localIndex = index;
                if (localIndex < 0) {
                    localIndex = 0;
                } else if (localIndex >= $scope.block.gallery.length) {
                    localIndex = localIndex - 1;
                }
                $scope.carouselIndexModal = localIndex;
            };

            $scope.showFullscreen = function (index) {
                Modal
                    .fromTemplateUrl('zoom-modal.html', {
                        scope: $scope,
                        animation: 'block'
                    }).then(function (modal) {
                    $scope.modal = modal;
                    $scope.carouselIndexModal = index;
                    $scope.modal.show();

                    $scope.modal.is_zoomed = false;
                    $scope.modal.scale_to_original_size = false;

                    var element = angular.element(document.getElementById('zoomedImage'));

                    $scope.modal.release = $ionicGesture.on('release', function () {
                        if ($scope.modal.scale_to_original_size) {
                            $ionicScrollDelegate.$getByHandle('slide-' + $scope.carouselIndexModal).zoomTo(1, true);
                            $scope.modal.scale_to_original_size = false;
                        }
                    }, element);

                    $scope.modal.pinch = $ionicGesture.on('pinch', function (event, o, t, l) {
                        if (event.gesture.scale < 1) {
                            $scope.modal.scale_to_original_size = true;
                        }
                    }, element);

                    $scope.modal.doubleTap = $ionicGesture.on('doubletap', function (event) {
                        if ($scope.modal.is_zoomed) {
                            $ionicScrollDelegate.$getByHandle('slide-' + $scope.carouselIndexModal).zoomTo(1, true);
                        } else {
                            $ionicScrollDelegate.$getByHandle('slide-' + $scope.carouselIndexModal).zoomTo(3, true, event.gesture.touches[0].pageX, event.gesture.touches[0].pageY);
                        }

                        $scope.modal.is_zoomed = !$scope.modal.is_zoomed;
                    }, element);
                });
            };

            $scope.hideFullscreen = function () {
                $scope.carouselIndex = $scope.carouselIndexModal;
                $scope.modal.remove();
            };
        }
    };
}).directive('sbCmsVideo', function () {
    return {
        restrict: 'A',
        scope: {
            block: '='
        },
        template:
        '<div class="cms_block">' +
        '   <div sb-video video="block"></div>' +
        '</div>'
    };
}).directive('sbCmsAddress', function () {
    return {
        restrict: 'A',
        scope: {
            block: '=',
            onShowMap: '&',
            onAddToContact: '&'
        },
        template:
        '<div>' +
        '    <div class="item item-text-wrap item-custom" ng-if="block.show_address">' +
        '       <h2 ng-if="block.label">{{ block.label}}</h2>' +
        '       <p ng-if="block.address">{{ block.address }}</p>' +
        '   </div>' +
        '   <div class="item item-text-wrap item-icon-left item-custom" ng-if="handle_address_book" ng-click="addToContact()">' +
        '       <i class="icon ion-ios-cloud-download"></i>' +
        '       {{ "Add to address book" | translate }}' +
        '   </div>' +
        '   <div class="item item-text-wrap item-icon-left item-custom" ng-if="(block.latitude && block.longitude || block.address) && block.show_geolocation_button && !itinerary_link" ng-click="showMap()">' +
        '       <i class="icon ion-ios-location-outline"></i>' +
        '       {{ "Locate" | translate }}' +
        '   </div>' +
        '   <div class="item item-text-wrap item-icon-left item-custom" ng-if="itinerary_link && block.show_geolocation_button" href="{{ itinerary_link }}" target="{{ itinerary_link_target }}">' +
        '       <i class="icon ion-ios-location-outline"></i>' +
        '       {{ "Locate" | translate }}' +
        '   </div>' +
        '</div>',
        controller: function ($cordovaGeolocation, Loader, $rootScope, $scope, $window) {
            $scope.handle_address_book = false; // Application.handle_address_book;

            $scope.showMap = function () {
                if ($rootScope.isNotAvailableInOverview()) {
                    return;
                }

                if ($rootScope.isNotAvailableOffline()) {
                    return;
                }

                Loader.show();

                $cordovaGeolocation.getCurrentPosition({ enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }).then(function (position) {
                    $scope.getItineraryLink(position.coords, $scope.block);

                    Loader.hide();
                }, function () {
                    var null_point = { 'latitude': null, 'longitude': null };
                    $scope.getItineraryLink(null_point, $scope.block);

                    Loader.hide();
                });
            };

            $scope.addToContact = function () {
                if ($scope.onAddToContact && angular.isFunction($scope.onAddToContact)) {
                    $scope.onAddToContact($scope.block);
                }
            };

            $scope.getItineraryLink = function (point1, point2) {
                var link = 'https://www.google.com/maps/dir/';

                if (point1.latitude) {
                    link = link + (point1.latitude + ',' + point1.longitude);
                }

                if (point2.latitude) {
                    link = link + ('/' + point2.latitude + ',' + point2.longitude);
                }

                $window.open(link, ($rootScope.isNativeApp ? '_system' : '_blank'), 'location=no');
            };
        } // !controller
    };
}).directive('sbCmsButton', function () {
    return {
        restrict: 'A',
        scope: {
            block: '='
        },
        template:
        '<a href="{{ url }}" target="{{ target }}" class="item item-text-wrap item-icon-left item-custom">' +
        '   <i class="icon" ng-class="icon" ng-if="show_icon"></i>' +
        '   <i class="icon flex-button-icon" ng-if="!show_icon">' +
        '       <img ng-src="{{ icon_src }}" style="width: 32px; height: 32px;" />' +
        '   </i>' +
        '   {{ label | translate }}' +
        '</a>',
        controller: function ($scope, LinkService) {
            $scope.openLink = function () {
                LinkService.openLink($scope.block.content, {
                    'hide_navbar': ($scope.block.hide_navbar === '1'),
                    'use_external_app': ($scope.block.use_external_app === '1')
                });
            };
        },
        link: function (scope, element) {
            var a = angular.element(element).find('a');
            switch (scope.block.type_id) {
                case 'phone':
                    scope.icon = 'ion-ios-telephone-outline';
                    scope.label = ((scope.block.label !== null) && (scope.block.label.length > 0)) ?
                        scope.block.label : 'Phone';

                    if (!scope.block.content.startsWith('tel:')) {
                        scope.block.content = 'tel:' + scope.block.content;
                    }

                    scope.url = scope.block.content;
                    scope.target = '_self';
                    break;

                case 'link':
                    scope.icon = 'ion-ios-world-outline';
                    scope.label = ((scope.block.label !== null) && (scope.block.label.length > 0)) ?
                        scope.block.label : 'Website';
                    a.on('click', function (e) {
                        e.preventDefault();
                        scope.openLink();
                        return false;
                    });
                    scope.$on('$destroy', function () {
                        a.off('click');
                    });
                    break;

                case 'email':
                    if (!scope.block.content.startsWith('mailto:')) {
                        scope.block.content = 'mailto:' + scope.block.content;
                    }
                    scope.icon = 'ion-ios-email';
                    scope.label = ((scope.block.label !== null) && (scope.block.label.length > 0)) ?
                        scope.block.label : 'Email';
                    scope.url = scope.block.content;
                    scope.target = '_self';

                    scope.$on('$destroy', function () {
                        a.off('click');
                    });
                    break;
            }

            /** Icon image */
            scope.show_icon = true;
            if (scope.block.icon.length) {
                scope.show_icon = false;
                scope.icon_src = scope.block.icon;
            }
        }
    };
}).directive('sbCmsFile', function () {
    return {
        restrict: 'A',
        scope: {
            block: '='
        },
        template:
        '<div class="item item-text-wrap item-icon-left item-custom" ng-click="openFile()">' +
        '   <i class="icon ion-paperclip"></i>' +
        '   {{ block.display_name }}' +
        '</div>',
        controller: function ($scope, LinkService) {
            $scope.openFile = function () {
                LinkService.openLink($scope.block.file_url, { 'use_external_app': 'true' });
            };
        }
    };
});
