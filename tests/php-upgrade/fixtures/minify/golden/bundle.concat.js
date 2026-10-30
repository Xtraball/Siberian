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
;/*global
 angular, IS_NATIVE_APP
 */

/**
 * Dialog
 *
 * @author Xtraball SAS
 *
 * @note $cordovaDialogs has been removed in favor of $ionicPopup which is consistent over all devices,
 * and can be automatically dismissed
 */
angular.module('starter').service('Dialog', function ($ionicPopup, $timeout, $translate, $q) {
    var service = {
        is_open: false,
        stack: []
    };

    /**
     * Un stack popups on event
     */
    service.unStack = function () {

        service.is_open = false;

        if (service.stack.length >= 1) {
            $timeout(function () {
                var dialog = service.stack.shift();

                switch (dialog.type) {
                    case 'alert':
                        service.renderAlert(dialog.data);
                        break;
                    case 'prompt':
                        service.renderPrompt(dialog.data);
                        break;
                    case 'confirm':
                        service.renderConfirm(dialog.data);
                        break;
                    case 'ionicPopup':
                        service.renderIonicPopup(dialog.data);
                        break;
                }
            }, 250);
        }
    };

    /**
     *
     * @param title
     * @param message
     * @param button
     * @param dismiss if -1 dismiss duration will be automatically calculated.
     * @param context
     * @returns {*}
     */
    service.alert = function (title, message, button, dismiss, context) {
        var deferred = $q.defer();

        // Stack alert!
        service.stack.push({
            type: 'alert',
            data: {
                title: title,
                message: message,
                button: button,
                dismiss: dismiss,
                context: context,
                promise: deferred
            }
        });

        if ((service.stack.length === 1) && !service.is_open) {
            service.unStack();
        }

        return deferred.promise;

    };

    /**
     * @param data
     */
    service.renderAlert = function (data) {
        service.is_open = true;

        var alertPromise = null;

        var message = $translate.instant(data.title);
        var cssClass = (data.title === '') ? 'popup-no-title' : '';

        alertPromise = $ionicPopup
            .alert({
                title: $translate.instant(data.title, data.context),
                template: $translate.instant(data.message, data.context),
                cssClass: cssClass,
                okText: $translate.instant(data.button, data.context)
            });

        data.promise.resolve(alertPromise);

        alertPromise.then(function () {
            service.unStack();
        });

        if (typeof data.dismiss === 'number') {
            /**
             * -1 means automatic calculation
             */
            var duration = data.dismiss;
            if (data.dismiss === -1) {
                duration = Math.min(Math.max((message.length * 50), 2000), 7000) + 400;
            }

            $timeout(function () {
                alertPromise.close();
            }, duration);
        }
    };

    /**
     *
     * @param title
     * @param message
     * @param type
     * @param value
     * @param buttonsArray
     * @param cssClass
     * @param context
     * @returns {*}
     */
    service.prompt = function (title, message, type, value, buttonsArray, cssClass, context) {
        var deferred = $q.defer();

        var localType = (type === undefined) ? 'text' : type;
        var localValue = (value === undefined) ? '' : value;
        var localButtonsArray = (buttonsArray === undefined) ? ["OK", "CANCEL"] : buttonsArray;

        /** Stack alert */
        service.stack.push({
            type: 'prompt',
            data: {
                title: title,
                message: message,
                type: localType,
                value: localValue,
                buttons_array: localButtonsArray,
                css_class: cssClass,
                context: context,
                promise: deferred
            }
        });

        if ((service.stack.length === 1) && !service.is_open) {
            service.unStack();
        }

        return deferred.promise;
    };

    /**
     * @param data
     */
    service.renderPrompt = function (data) {
        service.is_open = true;

        var cssClass = (data.title === '') ? 'popup-no-title' : '';

        return $ionicPopup
            .prompt({
                title: $translate.instant(data.title, data.context),
                template: $translate.instant(data.message, data.context),
                okText: $translate.instant(data.buttons_array[0], data.context),
                cancelText: $translate.instant(data.buttons_array[1], data.context),
                cssClass: cssClass,
                inputType: data.type,
                inputPlaceholder: $translate.instant(data.value, data.context)
            }).then(function (result) {
                if (result === undefined) {
                    data.promise.reject(result);
                } else {
                    data.promise.resolve(result);
                }

                service.unStack();
            });
    };

    /**
     * @param message
     * @param title
     * @param buttonsArray - ex: ['Ok', 'Cancel']
     * @param cssClass
     * @param context
     *
     * @returns Integer: 0 - no button, 1 - button 1, 2 - button 2
     */
    service.confirm = function (title, message, buttonsArray, cssClass, context) {
        var deferred = $q.defer();

        /** Stack alert */
        service.stack.push({
            type: 'confirm',
            data: {
                title: title,
                message: message,
                buttons_array: buttonsArray,
                css_class: cssClass,
                context: context,
                promise: deferred
            }
        });

        if ((service.stack.length === 1) && !service.is_open) {
            service.unStack();
        }

        return deferred.promise;
    };

    /**
     * @param data
     *
     * @return Promise
     */
    service.renderConfirm = function (data) {
        service.is_open = true;

        var cssClass = (data.title === '') ? 'popup-no-title' : '';

        return $ionicPopup
            .confirm({
                title: $translate.instant(data.title, data.context),
                cssClass: data.css_class + ' ' + cssClass,
                template: $translate.instant(data.message, data.context),
                okText: $translate.instant(data.buttons_array[0], data.context),
                cancelText: $translate.instant(data.buttons_array[1], data.context)
            }).then(function (result) {
                data.promise.resolve(result);
                service.unStack();
            });
    };

    /**
     * @param config
     *
     * @return Promise
     */
    service.ionicPopup = function (config) {
        var deferred = $q.defer();

        /** Stack alert */
        service.stack.push({
            type: 'ionicPopup',
            data: {
                config: config,
                promise: deferred
            }
        });

        if ((service.stack.length === 1) && !service.is_open) {
            service.unStack();
        }

        return deferred.promise;
    };

    /**
     * @param data
     *
     * @return Promise
     */
    service.renderIonicPopup = function (data) {
        service.is_open = true;

        return $ionicPopup
            .show(data.config)
            .then(function (result) {
                data.promise.resolve(result);
                service.unStack();
            });
    };

    return service;
});

/** @deprecated, use Dialog instead, will be removed by mid-2017, SafePopups is a proxy to Dialog. */
angular.module('starter').service('SafePopups', function (Dialog) {
    var service = {};
    service.show = function (type, params) {
        var button = {};
        switch (type) {
            case 'alert':
                if (params.buttons.length === 1) {
                    button = params.buttons[0];
                }
                return Dialog.alert(params.title, params.template, button);
            case 'confirm':
                return Dialog.confirm(params.title, params.template, params.buttons, '');
            default:
                return Dialog.ionicPopup(params);
        }
    };
    return service;
});
;/**
 * Location, location and coordinates should be acquired fast,
 * we are using timeouts and promise to send answer as fast as possible.
 *
 * @author Xtraball SAS
 *
 */
angular.module('starter').service('Location', function ($rootScope, $cordovaGeolocation, $q, Dialog, Loader) {
    var service = {
        PERMISSION_DENIED: 1,
        POSITION_UNAVAILABLE: 2,
        TIMEOUT: 3,
        debug: false,
        lastFetch: null,
        position: null,
        isEnabled: true,
    };

    /**
     * Default timeout is 10 seconds
     *
     * @param config
     * @param force
     * @returns {*|promise}
     */
    service.getLocation = function (config, force) {
        var deferred = $q.defer();
        var isResolved = false;
        var localForce = (force !== undefined);
        var localConfig = angular.extend({
            enableHighAccuracy: true,
            timeout: 10000,
            maximumAge: 0
        }, config);

        var localRequestLocation = function (deferred) {
            if (!localForce && (service.lastFetch !== null) && ((service.lastFetch + 42000) > Date.now())) {
                // fresh poll, send direct
                deferred.resolve(service.position);
                isResolved = true;
            }

            $cordovaGeolocation
            .getCurrentPosition(localConfig)
            .then(function (position) {
                service.lastFetch = Date.now();
                service.position = position;
                if (service.debug) {
                    console.log("position ok", position);
                }
                if (!isResolved) {
                    deferred.resolve(service.position);
                }
            }, function (error) {
                if (service.debug) {
                    console.log("position ko");
                }
                if (error.code === service.TIMEOUT ||
                    error.code === service.PERMISSION_DENIED ||
                    error.code === service.POSITION_UNAVAILABLE) {
                    localReject(deferred);
                }
                if (!isResolved) {
                    deferred.reject();
                }
            });
        };

        var localReject = function (deferred) {
            // Disable for all next requests!
            service.isEnabled = false;
            deferred.reject();
        };

        if (service.isEnabled === false) {
            if (service.debug) {
                console.log("service disabled");
            }
            deferred.reject();
        } else {
            if (cordova.plugins.permissions !== undefined) {
                var permissions = cordova.plugins.permissions;
                permissions.checkPermission(
                    permissions.ACCESS_FINE_LOCATION,
                    function(status) {
                        if (status.hasPermission) {
                            localRequestLocation(deferred);
                        } else {
                            permissions.requestPermission(
                                permissions.ACCESS_FINE_LOCATION,
                                function (success) {
                                    localRequestLocation(deferred);
                                }, function (error) {
                                    localReject(deferred);
                                });
                        }
                    }, function (error) {
                        permissions.requestPermission(
                            permissions.ACCESS_FINE_LOCATION,
                            function (success) {
                                localRequestLocation(deferred);
                            }, function (error) {
                                localReject(deferred);
                            });
                    });
            } else {
                localRequestLocation(deferred);
            }
        }

        return deferred.promise;
    };

    service.requestPermission = function () {
        var deferred = $q.defer();

        if (cordova.plugins.permissions !== undefined) {
            var permissions = cordova.plugins.permissions;
            permissions.checkPermission(
                permissions.ACCESS_FINE_LOCATION,
                function(status) {
                    if (!status.hasPermission) {
                        permissions.requestPermission(
                            permissions.ACCESS_FINE_LOCATION,
                            function (success) {
                                service.isEnabled = true;
                                deferred.resolve(service.position);
                            }, function (error) {
                                service.isEnabled = false;
                                deferred.reject();
                            });
                    }
                }, function (error) {
                    permissions.requestPermission(
                        permissions.ACCESS_FINE_LOCATION,
                        function (success) {
                            service.isEnabled = true;
                            deferred.resolve(service.position);
                        }, function (error) {
                            service.isEnabled = false;
                            deferred.reject();
                        });
                });
        } else {
            deferred.reject();
        }

        return deferred.promise;
    };

    service.requestLocation = function (success, error) {
        Dialog
            .confirm(
                'Error',
                'We were unable to request your location.<br />Please check that the application is allowed to use the GPS and that your device GPS is on.',
                ['TRY AGAIN', 'DISMISS'],
                -1,
                'location')
            .then(function (success) {
                if (success) {
                    service.isEnabled = true;
                    Loader.show();
                    service
                        .getLocation({timeout: 30000, enableHighAccuracy: false}, true)
                        .then(function (payload) {
                            // GPS is OK!!
                            $rootScope.$broadcast('location.request.success');
                            Loader.hide();
                            Dialog.alert('Success', 'We finally got you location', 'OK', 2350, 'location');

                            if (success &&
                                typeof success === 'function') {
                                try {
                                    success();
                                } catch (e) {
                                    // Silent!
                                }
                            }
                        }, function () {
                            $rootScope.$broadcast('location.request.error');
                            Loader.hide();
                            Dialog
                                .alert(
                                    'Error',
                                    'We were unable to request your location.<br />Please check that the application is allowed to use the GPS and that your device GPS is on.',
                                    'OK',
                                    3700,
                                    'location'
                                );

                            if (error &&
                                typeof error === 'function') {
                                try {
                                    error();
                                } catch (e) {
                                    // Silent!
                                }
                            }
                        });
                }
            });
    };

    /**
     * Returns the latest fetch position, if there is one, or false
     *
     * @returns {null}
     */
    service.getLatest = function () {
        var deferred = $q.defer();

        if (service.lastFetch === null) {
            // Try to fetch it!
            service.getLocation()
                .then(function (position) {
                    deferred.resolve(position);
                }, function () {
                    deferred.reject(false);
                });
        } else {
            deferred.resolve(service.position);
        }

        return deferred.promise;
    };

    return service;
});
;/**
 * sbSideMenu
 *
 * @author Xtraball SAS <dev@xtraball.com>
 * @version 4.17.3
 */
angular
.module("starter")
.directive("sbSideMenu", function () {
    return {
        restrict: "E",
        scope: {},
        templateUrl: "templates/page/side-menu.html",
        controller: function ($q, $scope, $rootElement, $rootScope, $injector, $timeout, $ionicHistory,
                              $ionicSideMenuDelegate, ContextualMenu, HomepageLayout, Application) {

            ContextualMenu.settings.isReady = $q.defer();

            $scope = angular.extend($scope, {
                contextualMenu: {
                    isEnabled: false,
                    side: 'right'
                },
                leftMenu: {
                    width: 0,
                    show: false,
                    src: 'blank-menu.html'
                },
                rightMenu: {
                    width: 0,
                    show: false,
                    src: 'blank-menu.html'
                }
            });

            // Custom go back, works with/without side-menus!
            $scope.goBack = function () {
                $ionicHistory.goBack();
            };

            $scope.showBottom = function () {
                return (
                    $scope.layout_id &&
                    ($scope.layout.menu.position === 'bottom') &&
                    ($scope.layout.menu.visibility === 'homepage')
                );
            };

            $scope.showAlways = function () {
                return Application.layoutShowAlways;
            };

            $scope.isMenuOpen = function() {
                return $ionicSideMenuDelegate.isOpen();
            };

            $scope.isMenuLeftOpen = function() {
                return $ionicSideMenuDelegate.isOpenLeft();
            };

            $scope.isMenuRightOpen = function() {
                return $ionicSideMenuDelegate.isOpenRight();
            };

            $scope.layoutIsRight = function () {
                if (!$scope.layout) {
                    return false;
                }
                return ($scope.layout.menu.position === "right");
            };

            $scope.layoutIsLeft = function () {
                if (!$scope.layout) {
                    return false;
                }
                return ($scope.layout.menu.position === "left");
            };

            $scope.layoutHasSideMenu = function () {
                if (!$scope.layout) {
                    return false;
                }
                return ($scope.layoutIsRight() || $scope.layoutIsLeft());
            };

            $scope.showLeftButton = function () {
                return $scope.layoutIsLeft();
            };

            $scope.showRightButton = function () {
                return $scope.layoutIsRight();
            };

            $scope.getLeftMenuWidth = function () {
                return $scope.leftMenu.width;
            };

            $scope.getLeftMenuSrc = function () {
                return $scope.leftMenu.src;
            };

            $scope.getLeftMenuShow = function () {
                return $scope.leftMenu.show;
            };

            $scope.getRightMenuWidth = function () {
                return $scope.rightMenu.width;
            };

            $scope.getRightMenuSrc = function () {
                return $scope.rightMenu.src;
            };

            $scope.getRightMenuShow = function () {
                return $scope.rightMenu.show;
            };

            $scope.contextualMenuIsReady = function () {
                return ContextualMenu.settings.isReady.promise;
            };

            $scope.backButtonIcon = function () {
                return Application.getBackIcon();
            };

            $scope.leftToggleIcon = function () {
                return Application.getLeftToggleIcon();
            };

            $scope.rightToggleIcon = function () {
                return Application.getRightToggleIcon();
            };

            $scope.init = function () {
                // Reset contextual menu ready promise!
                ContextualMenu.settings.isReady = $q.defer();

                $timeout(function () {
                    if ($scope.layoutIsLeft()) {
                        // Build left menu!
                        $scope.leftMenu = {
                            width: $scope.layout.menu.sidebarLeftWidth,
                            show: true,
                            src: "homepage-menu.html"
                        };

                        // Reset right & contextual!
                        $scope.contextualMenu.isEnabled = false;
                        $scope.contextualMenu.side = "right";
                        $scope.rightMenu = {
                            width: 0,
                            show: false,
                            src: "blank-menu.html"
                        };

                        // Enjoy contextual menu only (forced right)!
                        if (ContextualMenu.settings.isEnabled) {
                            $scope.contextualMenu.isEnabled = true;
                            $scope.contextualMenu.side = "right";
                            $scope.rightMenu = {
                                width: ContextualMenu.settings.width,
                                show: true,
                                src: ContextualMenu.settings.templateUrl
                            };
                        }

                    } else if ($scope.layoutIsRight()) {
                        // Reset left & contextual!
                        $scope.contextualMenu.isEnabled = false;
                        $scope.contextualMenu.side = "right";
                        $scope.leftMenu = {
                            width: 0,
                            show: false,
                            src: "blank-menu.html"
                        };

                        // Build right menu!
                        $scope.rightMenu = {
                            width: $scope.layout.menu.sidebarRightWidth,
                            show: true,
                            src: "homepage-menu.html"
                        };

                        // Enjoy contextual menu only (forced left)!
                        if (ContextualMenu.settings.isEnabled) {
                            $scope.contextualMenu.isEnabled = true;
                            $scope.contextualMenu.side = "left";
                            $scope.leftMenu = {
                                width: ContextualMenu.settings.width,
                                show: true,
                                src: ContextualMenu.settings.templateUrl
                            };
                        }
                    } else {
                        //  Reset left, right & contextual!
                        $scope.contextualMenu.isEnabled = false;
                        $scope.contextualMenu.side = "right";
                        $scope.leftMenu = {
                            width: 0,
                            show: false,
                            src: "blank-menu.html"
                        };

                        $scope.rightMenu = {
                            width: 0,
                            show: false,
                            src: "blank-menu.html"
                        };

                        // Enjoy contextual menu only!
                        if (ContextualMenu.settings.isEnabled) {
                            $scope.contextualMenu.isEnabled = true;
                            if (ContextualMenu.settings.preferredSide === "left") {
                                $scope.contextualMenu.side = "left";
                                $scope.leftMenu = {
                                    width: ContextualMenu.settings.width,
                                    show: true,
                                    src: ContextualMenu.settings.templateUrl
                                };
                            } else {
                                $scope.contextualMenu.side = "right";
                                $scope.rightMenu = {
                                    width: ContextualMenu.settings.width,
                                    show: true,
                                    src: ContextualMenu.settings.templateUrl
                                };
                            }
                        }
                    }

                    // It's ready, wait 20ms for the breathing!
                    $timeout(function () {
                        ContextualMenu.settings.isReady.resolve();
                    }, 20);
                }, 1);
            };

            $rootScope.$on("contextualMenu.init", function () {
                $scope.init();
            });

            $rootScope.$on("contextualMenu.toggle", function () {
                if ($scope.contextualMenu.isEnabled) {
                    if ($scope.contextualMenu.side === "left") {
                        $ionicSideMenuDelegate.toggleLeft(!$scope.isMenuLeftOpen());
                    } else {
                        $ionicSideMenuDelegate.toggleRight(!$scope.isMenuRightOpen());
                    }
                }
            });

            $rootScope.$on("contextualMenu.close", function () {
                if ($scope.contextualMenu.isEnabled) {
                    if ($scope.contextualMenu.side === "left") {
                        $ionicSideMenuDelegate.toggleLeft(false);
                    } else {
                        $ionicSideMenuDelegate.toggleRight(false);
                    }
                }
            });

            $rootScope.$on("contextualMenu.open", function () {
                if ($scope.contextualMenu.isEnabled) {
                    if ($scope.contextualMenu.side === "left") {
                        $ionicSideMenuDelegate.toggleLeft(true);
                    } else {
                        $ionicSideMenuDelegate.toggleRight(true);
                    }
                }
            });

            // Close all side menus!
            $rootScope.$on("sideMenu.close", function () {
                if ($ionicSideMenuDelegate.isOpenLeft()) {
                    $ionicSideMenuDelegate.toggleLeft(false);
                }
                if ($ionicSideMenuDelegate.isOpenRight()) {
                    $ionicSideMenuDelegate.toggleRight(false);
                }
            });

            // Init when ready!
            HomepageLayout
                .getFeatures()
                .then(function () {

                    // Default settings, module, layout!
                    $scope.layout = HomepageLayout.properties;
                    $scope.layout_id = HomepageLayout.properties.layoutId;
                    $scope.layout_code = HomepageLayout.properties.layoutCode;

                    // Store the nature of the fixed layout
                    Application.layoutShowAlways = (
                        $scope.layout_id &&
                        ($scope.layout.menu.position === 'bottom') &&
                        ($scope.layout.menu.visibility === 'always')
                    );

                    var layoutId = ('layout-' + $scope.layout_id)
                        .replace(/[^a-zA-Z0-9_\-]+/, '-')
                        .replace('.', '-')
                        .replace(/\-\-*/, '-');

                    var extendedClass = [
                        layoutId,
                        $scope.layout_code
                    ];

                    if (Application.layoutShowAlways) {
                        extendedClass.push('layout-always-visible');
                    }

                    angular
                        .element($rootElement)
                        .addClass(extendedClass.join(' '));

                    $scope.init();
                });
        }
    };
});
;/* hand written edge cases for the minifier, not an app file */
(function (root, factory) {
    // a block comment with a "string" and a /regex/ in it
    var a = 10, b = 2, c = a / b / 5; // division, not a regex
    var re = /ab+c\/d[/]e/gi, re2 = /["']/g;
    var s1 = 'it\'s', s2 = "say \"hi\"", s3 = `tpl ${a + b} and ${`nested ${c}`}`;
    var url = "http://example.com/a//b"; // the // in the string is not a comment
    if (a++ + ++b > 3) { return a - -b; }
    var o = { delete: 1, new: 2, class: 3, 'quoted-key': 4 };
    o.delete = o.new + o["class"];
    var t = a ? /x/.test(s1) : /y/.test(s2);
    var neg = !!a, und = undefined, tr = true, fl = false;
    for (var i = 0; i < 3; i++) { if (i === 1) continue; else break }
    var f = function () { return /re/.test("re") }
    var g = () => ({ x: 1 })
    switch (a) { case 1: b = 2; break; default: b = 3 }
    var x = a
    ++b
    root.lib = factory(re, re2, s3, url, o, t, neg, und, tr, fl, f, g, x);
}(this, function (re) { return re; }));
/*! preserved important comment */
;