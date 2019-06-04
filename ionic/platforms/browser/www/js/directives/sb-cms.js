/**
 * CMS Directives
 * @version 4.16.11
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
        '<div class="sb-cms-block-image">' +
        '    <div class="item item-image-gallery item-custom">' +
        '        <ion-scroll direction="y">' +
        '           <ion-gallery ion-gallery-items="block.gallery" ' +
        '                        ng-if="!is_loading"></ion-gallery>' +
        '       </ion-scroll>' +
        '    </div>' +
        '    <div ng-if="block.description" ' +
        '         class="item item-custom padding description">{{ block.description }}</div>' +
        '</div>'
    };
}).directive('sbPlaceImage', function () {
    return {
        restrict: 'A',
        scope: {
            block: '=',
            gallery: '='
        },
        template:
        '<div class="sb-place-block-image">' +
        '    <div ng-if="block.description" ' +
        '         class="item item-custom padding description">{{ block.description }}</div>' +
        '    <div class="item item-image-gallery item-custom">' +
        '        <ion-scroll direction="y">' +
        '           <ion-gallery ion-gallery-items="block.gallery" ' +
        '                        ng-if="!is_loading"></ion-gallery>' +
        '       </ion-scroll>' +
        '    </div>' +
        '</div>'
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
        '   <ul rn-carousel ' +
        '       rn-carousel-index="carouselIndex" ' +
        '       class="image">' +
        '       <li ng-repeat="image in block.gallery">' +
        '           <div sb-image ' +
        '                image-src="image.src" ' +
        '                ng-click="showFullscreen($index)"></div>' +
        '       </li>' +
        '   </ul>' +
        '   <div rn-carousel-indicators ' +
        '        ng-if="block.gallery.length > 1" ' +
        '        slides="block.gallery" ' +
        '        rn-carousel-index="carouselIndex"></div>' +
        '</div>' +
        '<div ng-if="block.description" ' +
        '     ng-style="lineReturn" ' +
        '     class="item item-custom padding description">{{ block.description }}</div>' +
        '<script id="zoom-modal.html" ' +
        '        type="text/ng-template">'+
        '   <div class="sb-cms-image modal fullscreen">'+
        '       <ion-header-bar class="bar-dark">' +
        '           <button class="button button-clear pull-right" ' +
        '                   ng-click="hideFullscreen()">{{ "Done" | translate:"cms" }}' +
        '           </button>' +
        '       </ion-header-bar>' +
        '       <ion-content>' +
        '           <ul rn-carousel ' +
        '               rn-carousel-index="carouselIndexModal" ' +
        '               class="image" ' +
        '               id="zoomedImage">' +
        '               <li ng-repeat="image in block.gallery">' +
        '                   <ion-scroll delegate-handle="slide-{{ $index }}" ' +
        '                               overflow-scroll="false" ' +
        '                               direction="xy" ' +
        '                               zooming="true" ' +
        '                               ng-swipe-left="setCarouselIndex($index+1)" ' +
        '                               ng-swipe-right="setCarouselIndex($index-1)">' +
        '                       <div class="image" ' +
        '                            sb-image ' +
        '                            image-src="image.src"></div>' +
        '                   </ion-scroll>' +
        '               </li>' +
        '           </ul>' +
        '           <div rn-carousel-indicators ' +
        '                ng-if="block.gallery.length > 1" ' +
        '                slides="block.gallery" ' +
        '                rn-carousel-index="carouselIndexModal"></div>' +
        '       </ion-content>' +
        '   </div>'+
        '</script>',
        controller: function ($ionicGesture, Modal, $ionicScrollDelegate, $scope) {
            $scope.carouselIndex = 0;

            $scope.is_fullscreen = false;

            $scope.lineReturn = {};
            if ($scope.block.allow_line_return == true) {
                $scope.lineReturn = {
                    'word-wrap': 'normal',
                    'white-space': 'normal'
                };
            }

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
        '<div class="sb-cms-block-address">' +
        '   <div class="item item-text-wrap item-icon-left item-custom sb-cms-block-address-locate" ' +
        '        ng-if="block.show_address || block.show_geolocation_button" ' +
        '        ng-click="openIntent()">' +
        '       <i class="icon ion-sb-turn-right"></i>' +
        '       <h2 class="sb-cms-address-label" ' +
        '           ng-if="block.label">{{ block.label}}</h2>' +
        '       <p class="sb-cms-address-address" ' +
        '          ng-if="block.address">{{ block.address }}</p>' +
        '   </div>' +
        '   <div class="item item-text-wrap item-icon-left item-custom sb-cms-block-address-phone" ' +
        '        ng-if="block.phone.length && block.show_phone">' +
        '       <i class="icon ion-android-call"></i>' +
        '       <p>' +
        '           <a href="tel:{{ block.phone }}">{{ block.phone }}</a>' +
        '       </p>' +
        '   </div>' +
        '   <div class="item item-text-wrap item-icon-left item-custom sb-cms-block-address-website" ' +
        '        ng-if="block.website.length && block.show_website">' +
        '       <i class="icon ion-earth"></i>' +
        '       <p>' +
        '           <a ng-click="openWebsite(block.website)">{{ block._label }}</a>' +
        '       </p>' +
        '   </div>' +
        '</div>',
        link: function (scope) {
            scope.$watch('block', function() {
                // Clean-up "readable" uri with all fancy things.
                scope.block._label = scope.block.website
                    .replace(/^https?:\/\//i, "")
                    .replace(/\/$/, "")
                    .replace(/(\?.*)$/, "")
                    .replace(/(\#.*)$/, "");
            });
        },
        controller: function (Location, Loader, LinkService, $rootScope, $scope) {
            $scope.showMap = function () {
                if ($rootScope.isNotAvailableInOverview()) {
                    return;
                }

                if ($rootScope.isNotAvailableOffline()) {
                    return;
                }
                var to = {
                    lat: $scope.block.latitude * 1,
                    lng: $scope.block.longitude * 1
                };

                Navigator.navigate(to);
            };

            $scope.openIntent = function () {
                if (!$scope.itinerary_link) {
                    $scope.showMap();
                } else if ($scope.itinerary_link) {
                    $scope.openItinary();
                } else {
                    // Do nothing!
                }
            };

            $scope.openItinary = function () {
                LinkService.openLink($scope.itinerary_link, {use_external_app: true});
            };

            $scope.openWebsite = function (url) {
                LinkService.openLink(url, {use_external_app: true});
            };

            $scope.addToContact = function () {
                if ($scope.onAddToContact && angular.isFunction($scope.onAddToContact)) {
                    $scope.onAddToContact($scope.block);
                }
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
