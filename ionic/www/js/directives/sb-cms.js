/**
 * CMS Directives
 * @version 4.20.11
 */
angular.module('starter').directive('sbCmsText', function () {
    return {
        restrict: 'A',
        scope: {
            block: '='
        },
        template:
        '<div class="item item-text-wrap item-custom sb-cms-text">' +
        '   <img alt="cms-image" ' +
        '        width="{{block.size}}%" ' +
        '        ng-src="{{ block.image_url }}" ' +
        '        ng-if="block.image.length && block.position != \'after\'" ' +
        '        class="{{ block.alignment }}" />' +
        '   <div class="content" ' +
        '        ng-bind-html="block.content | trusted_html" ' +
        '        sb-a-click></div>' +
        '   <div class="cb"></div>' +
        '   <img alt="cms-image" ' +
        '        width="{{block.size}}%" ' +
        '        ng-src="{{ block.image_url }}" ' +
        '        ng-if="block.image.length && block.position == \'after\'" ' +
        '        class="{{ block.alignment }}" />' +
        '</div>'
    };
}).directive('sbCmsImage', function ($timeout, Lightbox) {
    return {
        restrict: 'A',
        scope: {
            block: '=',
            gallery: '='
        },
        templateUrl: 'templates/cms/directives/image.html',
        controller: function ($scope) {
            $scope.listDidRender = function () {
                $timeout(function () {
                    Lightbox.run('.sb-cms-block-image');
                }, 200);
            };

            $scope.imagePath = function (image) {
                if (image.src.indexOf('http') === 0) {
                    return image.src;
                }
                return IMAGE_URL + 'images/application' + image.src;
            };
        }
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
        template:
            '<img width="100%" ' +
            '     ng-src="{{ block.gallery[0].src }}" ' +
            '     alt="{{block.name}}" />'
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
        '        ng-click="telIntent()"' +
        '        ng-if="block.phone.length && block.show_phone">' +
        '       <i class="icon ion-android-call"></i>' +
        '       <p>{{ block.phone }}</p>' +
        '   </div>' +
        '   <div class="item item-text-wrap item-icon-left item-custom sb-cms-block-address-website" ' +
        '        ng-click="openWebsite()"' +
        '        ng-if="block.website.length && block.show_website">' +
        '       <i class="icon ion-earth"></i>' +
        '       <p>{{ block._label }}</p>' +
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
            $scope.telIntent = function () {
                var intent = $scope.block.phone.startsWith('tel:') ? $scope.block.phone:
                    'tel:' + $scope.block.phone;
                LinkService.openLink(intent);
            };

            $scope.showMap = function () {
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
                LinkService.openLink($scope.itinerary_link, {}, true);
            };

            $scope.openWebsite = function () {
                LinkService.openLink($scope.block.website, {}, true);
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
        '<div ng-click="openLink()" ' +
        '     ng-class="{\'cms_custom_icon\': !show_icon}" ' +
        '     class="item item-text-wrap item-icon-left item-custom">' +
        '   <i class="icon" ' +
        '      ng-class="icon" ' +
        '      ng-if="show_icon"></i>' +
        '   <i class="icon flex-button-icon" ' +
        '      ng-if="!show_icon">' +
        '       <img ng-src="{{ icon_src }}" ' +
        '            alt="button" ' +
        '            style="width: 32px; height: 32px;" />' +
        '   </i>' +
        '   {{ label | translate }}' +
        '</div>',
        link: function (scope) {
            switch (scope.block.type_id) {
                case 'phone':
                    scope.icon = 'ion-ios-telephone-outline';
                    scope.label = ((scope.block.label !== null) && (scope.block.label.length > 0)) ?
                        scope.block.label : 'Phone';

                    if (!scope.block.content.startsWith('tel:')) {
                        scope.block.content = 'tel:' + scope.block.content;
                    }

                    break;

                case 'link':
                    scope.icon = 'ion-ios-world-outline';
                    scope.label = ((scope.block.label !== null) && (scope.block.label.length > 0)) ?
                        scope.block.label : 'Website';
                    break;

                case 'email':
                    if (!scope.block.content.startsWith('mailto:')) {
                        scope.block.content = 'mailto:' + scope.block.content;
                    }
                    scope.icon = 'ion-ios-email';
                    scope.label = ((scope.block.label !== null) && (scope.block.label.length > 0)) ?
                        scope.block.label : 'Email';
                    break;
            }

            // Icon image!
            scope.show_icon = true;
            if (scope.block.icon.length) {
                scope.show_icon = false;
                scope.icon_src = scope.block.icon;
            }
        },
        controller: function ($scope, LinkService) {
            $scope.openLink = function () {
                var externalBrowser = $scope.block.external_browser === undefined ?
                    true : $scope.block.external_browser;

                LinkService.openLink(
                    $scope.block.content,
                    $scope.block.options || {},
                    externalBrowser);
            };
        }
    };
}).directive('sbCmsFile', function () {
    return {
        restrict: 'A',
        scope: {
            block: '='
        },
        template:
        '<div class="item item-text-wrap item-icon-left item-custom" ' +
            ' ng-click="openFile()">' +
        '   <i class="icon ion-paperclip"></i>' +
        '   {{ block.display_name }}' +
        '</div>',
        controller: function ($scope, LinkService) {
            $scope.openFile = function () {
                LinkService.openLink($scope.block.file_url, {}, true);
            };
        }
    };
}).directive('sbCmsSource', function () {
    return {
        restrict: 'A',
        scope: {
            block: '='
        },
        template:
            '<div class="item item-custom">' +
            '   <iframe class="sb-cms-source" ' +
            '           style="height: {{ frameHeight }};"></iframe>' +
            '</div>',
        link: function (scope, element) {
            var iframe = angular.element(element).find('iframe')[0];
            iframe = iframe.contentWindow || iframe.contentDocument.document || iframe.contentDocument;
            iframe.document.open();
            iframe.document.write(scope.block.source);
            iframe.document.close();

            scope.frameHeight = scope.block.height;
        }
    };
});
