angular.module("starter").directive("datetime", function($ionicPlatform) {
    return {
        restrict: 'A',
        scope: {
            date: "="
        },
        link: function (scope, element) {
            if($ionicPlatform.is("android")) {
                element.bind('blur', function () {
                    scope.date = this.value;
                    scope.$parent.$apply();
                });
            }
        }
    };
});;/*global
    angular
 */
angular.module('starter')
    .directive('repeatDone', function () {
        return function (scope, element, attrs) {
            if (scope.$last) { // all are rendered
                scope.$eval(attrs.repeatDone);
            }
        };
    });;/*global
    App, ionic, angular
 */

angular.module("starter").directive("sbAClick", function($filter, $rootScope, $timeout, $window, $state, LinkService) {
    return {
        restrict: 'A',
        scope: {
        },
        link: function (scope, element) {
            $timeout(function () {
                // A links
                var collection = angular.element(element).find("a");
                angular.forEach(collection, function (elem) {
                    if(typeof elem.attributes["data-state"] !== "undefined") {

                        var params = elem.attributes["data-params"].value;
                        params = params.replace(/(^\?)/,'').split(",").map(function(n){return n = n.split(":"),this[n[0].trim()] = n[1],this}.bind({}))[0];

                        var state = elem.attributes["data-state"].value;
                        var offline = (typeof elem.attributes["data-offline"] !== "undefined") ? (elem.attributes["data-offline"].value === "true") : false;

                        angular.element(elem).bind("click", function (e) {
                            e.preventDefault();
                            if(!offline && $rootScope.isOffline) {
                                $rootScope.onlineOnly();
                            } else {
                                $state.go(state, params);
                            }
                        });

                    } else {
                        angular.element(elem).bind("click", function (e) {
                            e.preventDefault();
                            var options = {
                                "hide_navbar" : false,
                                "use_external_app" : false
                            }
                            LinkService.openLink(elem.href,options);
                        });
                    }
                });
            });
        }
    };
});
;angular.module("starter").directive('sbAlbumsBoxes', function () {
    return {
        restrict: 'A',
        templateUrl: 'templates/media/music/l1/album/boxes.html',
        scope: {
            paged_albums: '=pagedAlbums',
            onAlbumSelected: '&'
        }
    };
});;angular.module("starter").directive('sbAppLocked', function () {
    return {
        restrict: 'E',
        replace: true,
        scope: {},
        templateUrl: "templates/html/l1/app_locked.html",
        controller: function($ionicHistory) {

            $ionicHistory.clearHistory();

        }
    };
});;/**
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
;function EventToDirective(app, directive_name, eventName) {
  return app.directive(directive_name, ['$parse', function($parse) {
    return {
        restrict: 'A',
        compile: function($element, attr) {
            return function(scope, element, attr) {
                element.on(eventName, function(event) {
                    scope.$apply(($parse(attr[directive_name])).bind(null, scope, {$event:event}));
                });
            };
        }
    };
  }]);
}

EventToDirective(angular.module("starter"), 'sbError', 'error');
EventToDirective(angular.module("starter"), 'sbLoad', 'load');
;angular.module("starter").directive('sbGoogleAutocomplete', function(GoogleMaps, $timeout) {
    return {
        scope: {
            location: '=?',
            address:'=?',
            place: '=?',
            onAddressChange:'&?'
        },
        require: '?ngModel', // get a hold of NgModelController
        link: function(scope, element, attrs, ngModel) {

            var options = {
                types: []
            };

            element.on("keydown", function(e) {
                if(
                    e.which == 13 &&
                        _.get(
                            document.getElementsByClassName("pac-container"),
                            "[0].style.display"
                        ) === ""
                ) e.preventDefault();
            });

            GoogleMaps.addCallback(function() {
                scope.googleAutocomplete = new google.maps.places.Autocomplete(element[0], options);

                google.maps.event.addListener(scope.googleAutocomplete, 'place_changed', function(data) {

                    var place = scope.googleAutocomplete.getPlace();
                    scope.place = place;

                    if(place.geometry) {
                        if(!angular.isObject(scope.location))
                            scope.location = {};

                        scope.location.latitude = place.geometry.location.lat();
                        scope.location.longitude = place.geometry.location.lng();
                    }

                    var val = element.val();

                    if(angular.isObject(ngModel) && angular.isFunction(ngModel.$setViewValue)) {
                        ngModel.$setViewValue(val, "keyup");
                    }

                    $timeout(function() {
                        scope.location.address = val;
                        scope.onAddressChange(scope);
                    });

                });
            });

        }
    };
});
;"use strict";

angular.module("starter").directive('sbImage', function($timeout) {
    return {
        restrict: 'A',
        scope: {
            image_src: "=imageSrc"
        },
        template: '<div class="image_loader relative scale-fade" ng-hide="is_hidden"><span class="loader block"></span></div>',
        link: function(scope, element) {

            scope.setBackgroundImageStyle = function() {
                $timeout(function() {
                    element.css('background-image', 'url('+img.src+')');
                    scope.is_hidden = true;
                });
            };

            var img = new Image();
            img.src = scope.image_src;
            img.onload = function () {
                scope.setBackgroundImageStyle();
            };
            img.onerror = function () {
                scope.setBackgroundImageStyle();
            };
            if(img.complete) {
                scope.setBackgroundImageStyle();
            }

        },
        controller: function($scope) {
            $scope.is_hidden = false;
        }
    };
});;/*global
    angular
*/

angular.module('starter').directive('sbInputNumber', function ($timeout) {
    return {
        restrict: 'E',
        scope: {
            changeQty: '&',
            step: '=?',
            min: '=?',
            max: '=?',
            value: '=?',
            params: '=?'
        },
        template:
        '<div class="item item-input item-custom input-number-sb">' +
        '   <div class="input-label"><i class="ion-plus"></i> {{ label }}</div>' +
        '   <div class="input-container text-right">' +
        '       <button class="button button-small button-custom button-left" ng-click="down()">-</button>' +
        '       <div class="item-input-wrapper">' +
        '           <input type="text" value="{{ dirValue }}" class="text-center input" readonly />' +
        '       </div>' +
        '       <button class="button button-small button-custom button-right" ng-click="up()">+</button>' +
        '   </div>' +
        '</div>',
        replace: true,
        link: function (scope, element, attrs) {
            scope.step = scope.step ? scope.step : 1;
            scope.min = scope.min ? scope.min : 0;
            scope.max = scope.max ? scope.max : 999;
            scope.value = scope.value ? angular.copy(scope.value) : 0;
            scope.dirValue = scope.value;
            scope.params = scope.params ? scope.params : {};
            scope.label = attrs.label ? attrs.label + ':' : '';

            scope.up = function () {
                if (scope.dirValue < scope.max) {
                    scope.dirValue = scope.dirValue + 1;
                    scope.callBack(scope.dirValue);
                }
            };

            scope.down = function () {
                if (scope.dirValue > scope.min) {
                    scope.dirValue = scope.dirValue - 1;
                    scope.callBack(scope.dirValue);
                }
            };

            scope.callBack = function (value) {
                if (typeof scope.changeQty === 'function') {
                    $timeout(function () {
                        scope.changeQty({
                            qty: value,
                            params: scope.params
                        });
                    }, 500);
                }
            };
        }
    };
});
;angular.module("starter").directive('sbMaps', function() {
    return {
        restrict: 'A',
        scope: {
            map: "=",
            config: "="
        },
        controller: function($scope, GoogleMaps) {

            GoogleMaps.addCallback(function() {

                $scope.map = GoogleMaps.createMap("google-maps");

                if($scope.config.coordinates) {

                    if(!$scope.config.coordinates.origin || ((!$scope.config.coordinates.origin.latitude || !$scope.config.coordinates.origin.longitude) && !$scope.config.coordinates.origin.address)) {
                        console.error("An origin (latitude / longitude or address) is required to use Maps");
                        return;
                    }
                    if(!$scope.config.coordinates.destination || ((!$scope.config.coordinates.destination.latitude || !$scope.config.coordinates.destination.longitude) && !$scope.config.coordinates.destination.address)) {
                        console.error("A destination (latitude / longitude or address) is required to use Maps");
                        return;
                    }

                    GoogleMaps.calculateRoute($scope.config.coordinates.origin, $scope.config.coordinates.destination).then(function(route) {
                        GoogleMaps.addRoute(route);
                    });

                }

                if($scope.config.markers && $scope.config.markers.constructor === Array) {

                    for(var i = 0; i < $scope.config.markers.length; i++) {
                        var marker = $scope.config.markers[i];
                        GoogleMaps.addMarker(marker);
                    }

                    if($scope.config.bounds_to_marker) {
                        var bounds = GoogleMaps.getBoundsFromPoints($scope.config.markers);
                        GoogleMaps.fitToBounds(bounds);
                    }

                }
            });

        }
    }
});
;angular.module("starter").directive('sbMediaPlayerControls', function () {
    return {
        restrict: 'A',
        controller: function($scope, MediaPlayer) {
            $scope.player = MediaPlayer;

            $scope.openPlayer = function() {
                MediaPlayer.openPlayer();
            };

            $scope.playPause = function() {
                MediaPlayer.playPause();
            };

            $scope.prev = function() {
                if(!MediaPlayer.is_minimized) {
                    MediaPlayer.loading();
                }
                MediaPlayer.prev();
            };

            $scope.next = function() {
                if(!MediaPlayer.is_minimized) {
                    MediaPlayer.loading();
                }
                MediaPlayer.next();
            };

            $scope.willSeek = function() {
                MediaPlayer.willSeek();
            };

            $scope.seekTo = function(position) {
                MediaPlayer.seekTo(position);
            };
        }
    };
});

angular.module("starter").directive('sbMediaMiniPlayer', function () {
    return {
        restrict: 'E',
        templateUrl: 'templates/media/music/l1/player/mini.html'
    };
});;angular.module("starter").directive('sbNavView', function ($rootScope, SB) {
    return {
        restrict: 'E',
        replace: true,
        template: "<ion-nav-view></ion-nav-view>",
        link: function(scope, element) {
            $rootScope.$on(SB.EVENTS.MEDIA_PLAYER.SHOW, function() {
                element.addClass("has-mini-player-controls");
            });
            $rootScope.$on(SB.EVENTS.MEDIA_PLAYER.HIDE, function() {
                element.removeClass("has-mini-player-controls");
            });
        }
    };
});;angular.module("starter").directive('sbPad', function() {
    return {
        restrict: 'E',
        scope: {
            card: "="
        },
        controller: function($scope, Modal) {

            Modal
                .fromTemplateUrl("templates/loyaltycard/l1/pad.html", {
                    scope: $scope
                }).then(function(modal) {
                    $scope.modal = modal;
                });

            $scope.openPad = function() {
                $scope.modal.show();
            };
            $scope.closeModal = function() {
                $scope.modal.hide();
            };

        }
    }
});
;/*global
 angular
 */

angular.module("starter").directive("sbPadlock", function(Application) {
    return {
        restrict: "A",
        controller: function($cordovaBarcodeScanner, $ionicHistory, Modal, $rootScope, $scope, $state, $stateParams,
                             $timeout, $translate, $window, Application, Customer, Dialog, Padlock, SB) {

            $scope.is_webview = Application.is_webview;

            if(Application.is_locked) {
                $ionicHistory.clearHistory();
            }

            $rootScope.$on(SB.EVENTS.AUTH.loginSuccess, function() {
                $scope.is_logged_in = true;
            });

            $rootScope.$on(SB.EVENTS.AUTH.logoutSuccess, function() {
                $scope.is_logged_in = false;
            });

            $scope.is_logged_in = Customer.isLoggedIn();
            $scope.value_id = Padlock.value_id = $stateParams.value_id;

            Padlock.findUnlockTypes()
                .then(function(data) {
                    $scope.unlock_by_account_type = data.unlock_by_account;
                    $scope.unlock_by_qrcode_type = data.unlock_by_qrcode;
                });

            $scope.padlock_login = function () {
                Customer.display_account_form = false;
                Customer.loginModal($scope, function() {
                    $rootScope.$broadcast(SB.EVENTS.PADLOCK.unlockFeatures);

                    if(Application.is_locked) {
                        $ionicHistory.clearHistory();
                        $state.go("home");
                    } else {
                        $ionicHistory.goBack();
                    }
                });
            };

            $scope.padlock_signup = function () {
                Customer.display_account_form = true;
                Customer.loginModal($scope);
            };

            $scope.padlock_logout = function() {
                $scope.is_loading = true;
                Customer.logout()
                    .then(function() {
                        $scope.is_loading = false;
                    });
            };

            $scope.openScanCamera = function() {
                $scope.scan_protocols = ["sendback:"];

                $cordovaBarcodeScanner.scan().then(function(barcodeData) {

                    if(!barcodeData.cancelled && (barcodeData.text !== "")) {

                        $timeout(function () {
                            for (var i = 0; i < $scope.scan_protocols.length; i++) {
                                if (barcodeData.text.toLowerCase().indexOf($scope.scan_protocols[i]) == 0) {
                                    $scope.is_loading = true;

                                    var qrcode = barcodeData.text.replace($scope.scan_protocols[i], "");

                                    Padlock.unlockByQRCode(qrcode)
                                        .then(function() {

                                            Padlock.unlocked_by_qrcode = true;

                                            $scope.is_loading = false;

                                            $window.localStorage.setItem('sb-uc', qrcode);

                                            $rootScope.$broadcast(SB.EVENTS.PADLOCK.unlockFeatures);

                                            if(Application.is_locked) {
                                                $ionicHistory.clearHistory();
                                                $state.go("home");
                                            } else {
                                                $ionicHistory.goBack();
                                            }

                                        }, function (data) {

                                            var message_text = $translate.instant('An error occurred while reading the code.');
                                            if(angular.isObject(data)) {
                                                message_text = data.message;
                                            }

                                            Dialog.alert("Error", message_text, "OK", -1);

                                        }).then(function () {
                                            $scope.is_loading = false;
                                        });

                                    break;
                                }
                            }

                        });

                    }

                }, function(error) {
                    Dialog.alert("Error", "An error occurred while reading the code.", "OK", -1);
                });
            };

        }
    };
});
;/* global
    App, angular, window
 */

angular.module('starter').directive('sbPageBackground', function ($rootScope, $state, $stateParams, $pwaRequest,
                                                                  $session, $timeout, $window, Application) {
    var init = false,
        isUpdating = false,
        deviceScreen = $session.getDeviceScreen(),
        deffered = $pwaRequest.defer(),
        backgroundImages = deffered.promise,
        catched = false,
        orientationChange = $window.matchMedia('(orientation: portrait)');

    $session.loaded
        .then(function () {
            var loadBackgrounds = function (refresh) {
                $pwaRequest.get('front/mobile/backgroundimages', {
                    urlParams: {
                        device_width: deviceScreen.width,
                        device_height: deviceScreen.height
                    },
                    refresh: refresh
                }).then(function (data) {
                    deffered.resolve(data);
                }).catch(function () {
                    if (!catched) {
                        catched = true;
                        $timeout(loadBackgrounds(false), 1);
                    }
                }); // Main load, then
            };
            loadBackgrounds(true);
        });

    return {
        restrict: 'A',
        controller: function ($scope) {
            $scope.valueId = ($state.current.name === 'home') ? 'home' : $stateParams.value_id;
        },
        link: function (scope, element) {
            var networkDone = false;

            scope.setBackgroundImageStyle = function (src, color) {
                if (!isUpdating) {
                    isUpdating = true;
                    var el = angular.element(element);

                    el.addClass('has-background-image');
                    if (color !== undefined) {
                        el.css({
                            'background-image': 'linear-gradient(' + color + ', ' + color + '), url(\'' + src + '\')'
                        });
                    } else {
                        el.css({
                            'background-image': 'url(\'' + src + '\')'
                        });
                    }

                    $timeout(function () {
                        if (navigator.splashscreen !== undefined) {
                            navigator.splashscreen.hide();
                        }
                    }, 20);
                }
                isUpdating = false;
            };

            // Default base64 fast image!
            Application.ready
                .then(function () {
                    if (!networkDone) {
                        scope.setBackgroundImageStyle(Application.default_background);
                    }
                });

            var updateBackground = function () {
                backgroundImages
                    .then(function (data) {
                        Application.ready
                            .then(function () {
                                networkDone = true;

                                var valueId = angular.copy(scope.valueId);

                                if (deviceScreen.orientation === 'landscape') {
                                    valueId = 'landscape_' + valueId;
                                }

                                var exists = (angular.isDefined(valueId) &&
                                                angular.isDefined(data.backgrounds) &&
                                                 angular.isDefined(data.backgrounds[valueId]));
                                var fallback = ((Application.homepage_background || (valueId === 'home' || valueId === 'landscape_home')) &&
                                                angular.isDefined(data.backgrounds) &&
                                                angular.isDefined(data.backgrounds.home));

                                if (exists || fallback) {
                                    var backgroundImage = data.backgrounds[valueId];
                                    window.tmpImg = new Image();
                                    window.tmpImg.src = backgroundImage;
                                    window.tmpImg.onload = function () {
                                        scope.setBackgroundImageStyle(backgroundImage);
                                    };

                                    if (window.tmpImg.complete || $rootScope.isOffline) {
                                        scope.setBackgroundImageStyle(backgroundImage);
                                    }
                                    delete window.tmpImg;
                                } else {
                                    scope.setBackgroundImageStyle(
                                        './img/placeholder/white-1.png',
                                        Application.colors.background.rgba
                                    );
                                }

                                $timeout(function () {
                                    if (navigator.splashscreen !== undefined) {
                                        navigator.splashscreen.hide();
                                    }
                                }, 20);
                            });
                    });
            };

            if (!init) {
                $rootScope.$on('$stateChangeStart', function (evt, toState, toParams) {
                    scope.valueId = (toState.name === 'home') ? 'home' : toParams.value_id;
                    updateBackground();
                });

                orientationChange.addListener(function () {
                    // Refresh device screen!
                    deviceScreen = $session.setDeviceScreen();
                    // Then trigger background update!
                    updateBackground();
                });
                init = true;
            }

            scope.valueId = ($state.current.name === 'home') ? 'home' : $stateParams.value_id;
            updateBackground();
        }
    };
});
;/* global
 angular
 */

angular.module('starter').directive('sbSideMenu', function ($rootElement, $rootScope, $ionicHistory, $translate,
                                                            $timeout, HomepageLayout, ContextualMenu) {
    return {
        restrict: 'E',
        replace: true,
        scope: {},
        templateUrl: 'templates/page/side-menu.html',
        link: function (scope, element) {
            /** Defining the global functionalities of the page */
            HomepageLayout.getFeatures()
                .then(function (features) {
                    scope.layout = HomepageLayout.properties;
                    scope.layout_id = HomepageLayout.properties.layoutId;
                    angular.element($rootElement)
                        .addClass(('layout-'+scope.layout_id)
                        .replace(/[^a-zA-Z0-9_\-]+/, '-')
                        .replace('.', '-')
                        .replace(/\-\-*/, '-'));
                });

            scope.backButton = 'Back';

            scope.$on('$stateChangeSuccess', function (event, toState, toStateParams, fromState, fromStateParams) {
                scope.backButton = $translate.instant('Back');
            });

            /** Custom go back, works with/without side-menus */
            scope.goBack = function () {
                $ionicHistory.goBack();
            };

            /** Special trick to handle manual updates. */
            scope.checkForUpdate = function () {
                $rootScope.checkForUpdate();
            };

            scope.showLeft = function () {
                return (scope.layout_id && (scope.layout.menu.position === 'left'));
            };

            scope.showRight = function () {
                return (scope.layout_id && (scope.layout.menu.position === 'right'));
            };

            scope.showBottom = function () {
                return (scope.layout_id && (scope.layout.menu.position === 'bottom') &&
                    (scope.layout.menu.visibility === 'homepage'));
            };

            scope.showAlways = function () {
                return (scope.layout_id && (scope.layout.menu.position === 'bottom') &&
                    (scope.layout.menu.visibility === 'always'));
            };

            scope.contextualMenuSideWidth = function () {
                return ContextualMenu.width;
            };

            scope.contextualMenuIsEnabled = function () {
                return ContextualMenu.isEnabled;
            };

            scope.contextualMenuExists = function () {
                return ContextualMenu.exists;
            };

            scope.contextualMenu = function () {
                return ContextualMenu.templateURL;
            };
        }
    };
});
;/* global
 App, angular
 */

angular.module('starter').directive('sbTabbar', function ($pwaRequest, $ionicHistory, Modal, $ionicSlideBoxDelegate,
                                    $ionicSideMenuDelegate, $location, $rootScope, $session, $timeout,
                                    $translate, $window, $ionicPlatform, Analytics, Application,
                                    Customer, Dialog, HomepageLayout, LinkService, Pages, Url, SB) {
    return {
        restrict: 'A',
        templateUrl: function () {
            return HomepageLayout.getTemplate();
        },
        scope: {},
        link: function ($scope, element, attrs) {
            $scope.tabbar_is_visible = Pages.is_loaded;
            $scope.tabbar_is_transparent = HomepageLayout.properties.tabbar_is_transparent;
            $scope.animate_tabbar = !$scope.tabbar_is_visible;
            $scope.pages_list_is_visible = false;
            $scope.active_page = 0;
            $scope.card_design = false;

            $scope.layout = HomepageLayout;

            $scope.loadContent = function () {
                HomepageLayout.getOptions()
                    .then(function (options) {
                        $scope.options = options;
                    });

                HomepageLayout.getData()
                    .then(function (data) {
                        $scope.data = data;
                        $scope.push_badge = data.push_badge;
                    });

                HomepageLayout.getFeatures()
                    .then(function (features) {
                        // filtered active options!
                        $scope.features = features;

                        $timeout(function () {
                            if (!Pages.is_loaded) {
                                Pages.is_loaded = true;
                                $scope.tabbar_is_visible = true;
                            }
                        }, 500);

                        // Load first feature is needed!
                        if ($rootScope.loginFeature === true) {
                            if ($rootScope.loginFeatureBack === true) {
                                $ionicHistory.goBack();
                            } else {
                                $rootScope.loginFeatureBack = false;
                            }
                            $rootScope.loginFeature = null;
                        } else if (!Application.is_customizing_colors &&
                            HomepageLayout.properties.options.autoSelectFirst &&
                            (features.first_option !== false)) {
                            var feat_index = 0;
                            for (var fi = 0; fi < features.options.length; fi = fi + 1) {
                                var feat = features.options[fi];
                                // Don't load unwanted features on first page!
                                if ((feat.code !== 'code_scan') &&
                                    (feat.code !== 'radio') &&
                                    (feat.code !== 'padlock')) {
                                    feat_index = fi;
                                    break;
                                }
                            }

                            if (features.options[feat_index].path !== $location.path()) {
                                $ionicHistory.nextViewOptions({
                                    historyRoot: true,
                                    disableAnimate: false
                                });

                                $location.path(features.options[feat_index].path).replace();
                            }
                        }
                    });
            };

            $scope.closeList = function () {
                $scope.tabbar_is_visible = true;
                $scope.pages_list_is_visible = false;
            };

            $scope.gotoPage = function (index) {
                $ionicSlideBoxDelegate.$getByHandle('slideBoxLayout').slide(index);
            };

            $scope.closePreviewer = function () {
                $window.location = 'app:closeApplication';
            };

            $scope.login = function ($scope) {
                $rootScope.loginFeature = null;
                Customer.loginModal($scope);
            };

            $scope.loadContent();

            if ($rootScope.isOverview) {
                $scope.$on('tabbarStatesChanged', function () {
                    $scope.loadContent();
                });
            }

            $rootScope.$on(SB.EVENTS.CACHE.layoutReload, function () {
                $scope.loadContent();
            });

            $rootScope.$on(SB.EVENTS.PUSH.unreadPushs, function (event, args) {
                $timeout(function () {
                    $scope.push_badge = args;
                });
            });

            $rootScope.$on(SB.EVENTS.PUSH.readPushs, function () {
                $scope.push_badge = 0;
            });

            var rebuildOptions = function () {
                $timeout(function () {
                    HomepageLayout.setNeedToBuildTheOptions(true);
                    $scope.loadContent();
                });
            };

            $rootScope.$on(SB.EVENTS.AUTH.logoutSuccess, function () {
                rebuildOptions();
            });
            $rootScope.$on(SB.EVENTS.AUTH.loginSuccess, function () {
                rebuildOptions();
            });
            $rootScope.$on(SB.EVENTS.PADLOCK.unlockFeatures, function () {
                rebuildOptions();
            });
            $rootScope.$on(SB.EVENTS.CACHE.pagesReload, function () {
                rebuildOptions();
            });

            Application.loaded
                .then(function () {
                    rebuildOptions();
                });

            if ($rootScope.isOverview) {
                $window.changeIcon = function (id, url) {
                    angular.forEach($scope.features.options, function (option) {
                        if (option.id == id) {
                            $timeout(function () {
                                option.icon_url = url;
                            });
                        }
                    });
                };
            }
        }
    };
});

angular.module('starter').directive('tabbarItems', function ($rootScope, $timeout, $log, HomepageLayout) {
    return {
        restrict: 'A',
        scope: {
            option: '='
        },
        link: function (scope, element) {
            element.on('click', function () {
                $log.debug('Clicked Option: ', scope.option);
                $rootScope.$broadcast('OPTION_POSITION', scope.option.position);

                $timeout(function () {
                    HomepageLayout.openFeature(scope.option, scope);
                });
            });
        }
    };
});
;angular.module("starter").directive('sbTooltip', function () {
    return {
        restrict: 'A',
        scope: {},
        replace: false,
        bindToController: {
            show_tooltip: "=showTooltip",
            collection: "=",
            current_item: "=currentItem",
            button_label: "=buttonLabel",
            onItemClicked: "&"
        },
        controllerAs: "tooltip",
        template:
            '<button class="button button-clear" ng-click="toggleTooltip()">' +
            '    {{ tooltip.button_label | translate }}' +
            '</button>' +
            '<div class="tooltip tooltip-custom" ng-show="tooltip.collection.length && tooltip.show_tooltip">' +
            '    <i class="icon ion-arrow-up-b dark"></i>' +
            '    <ion-scroll style="max-height: 250px">' +
            '        <ul>' +
            '            <li ng-repeat="item in tooltip.collection">' +
            '                <span class="block" ng-click="itemClicked(item);" ng-class="{ \'active\': tooltip.current_item.id == item.id }">{{ item.name | translate }}</span>' +
            '                <ul ng-show="item.show_children">' +
            '                    <li ng-repeat="child in item.children">' +
            '                        <span class="block" ng-click="itemClicked(child)" ng-class="{ \'active\': tooltip.current_item.id == child.id }">{{ child.name | translate }}</span>' +
            '                    </li>' +
            '                </ul>' +
            '            </li>' +
            '        </ul>' +
            '    </ion-scroll>' +
            '</div>',
        controller: function($scope, $translate) {

            var tooltip = this;

            if(!tooltip.button_label) {
                tooltip.button_label = $translate.instant("More");
            }

            $scope.toggleTooltip = function() {
                tooltip.show_tooltip = !tooltip.show_tooltip;
            };

            $scope.itemClicked = function(item) {
                if(!item.children) {
                    tooltip.show_tooltip = false;
                }
                tooltip.onItemClicked({object: item});
            };

        }
    };
});;/*global
    App
*/
angular.module("starter").directive("sbVideo", function($timeout, $window, YouTubeAutoPauser) {

    return {
        restrict: "A",
        replace:true,
        scope: {
            video: "="
        },
        template:
            '<div class="card">'
                + '<div class="item item-image" ng-click="play()">'
                    + '<div ng-hide="use_iframe || use_video_element" ng-style="height_style">'
                        + '<img ng-src="{{ video.cover_url }}" />'
                        + '<div class="sprite"></div>'
                    + '</div>'
                    +'<div ng-if="use_iframe">'
                        +'<iframe type="text/html" width="100%" height="200" src="" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>'
                    +'</div>'
                    +'<div ng-if="use_video_element">'
                        +'<div id="video_player_view" class="player">'
                            +'<video src="" type="video/mp4" controls preload="none" width="100%" height="200px"></video>'
                        +'</div>'
                    +'</div>'
                + '</div>'
                + '<div ng-if="video.title" class="item item-text-wrap item-custom">'
                    + '<p>{{ video.title }}</p>'
                + '</div>'
            + '</div>'
        ,
        link: function(scope, element) {
            scope.height_style = null;
            if(!scope.video.cover_url) {
                scope.height_style = {"min-height":"200px","width":"100%"};
            }

            scope.play = function() {
                //Open videos on external apps for android version prior 5
                //Indeed Android < 5 don't stop video played in background
                //And video player is bugged
                if((device.platform == "Android") && parseInt(device.version.substr(0,1)) < 5) {
                    $window.open(scope.video.url, '_system');
                } else { // iOS or Android >= 5
                    //check if it is a youtube/vimeo url
                    if(/^https?:\/\/(www.|player.)?(youtube|vimeo)\./.test(scope.video.url)) {
                        scope.use_iframe = true;
                        $timeout(function() {
                            var iframe = element.find('iframe');
                            iframe.attr('src', scope.video.url_embed + "?autoplay=1&autopause=1&enablejsapi=1");
                            YouTubeAutoPauser.register(iframe);
                        });
                    } else {
                        scope.use_video_element = true;
                        $timeout(function() {
                            element.find('video').attr('src', scope.video.url_embed);
                        });
                    }
                }
            };

        },
        controller: function($scope) {
            $scope.use_iframe = false;
            $scope.use_video_element = false;
        }
    };
});
