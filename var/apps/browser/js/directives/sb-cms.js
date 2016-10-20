App.directive("sbCmsText", function() {
    return {
        restrict: 'A',
        scope: {
            block: "="
        },
        template:
        '<div class="item item-text-wrap item-custom sb-cms-text">' +
        '   <img width="{{block.size}}%" ng-src="{{ block.image_url }}" ng-if="block.image_url" class="{{ block.alignment }}" />' +
        '   <div class="content" ng-bind-html="block.content | trusted_html" sb-a-click></div>' +
        '   <div class="cb"></div>' +
        '</div>'
    };
}).directive("sbCmsImage", function() {
    return {
        restrict: 'A',
        scope: {
            block: "=",
            gallery: "="
        },
        template:
        '<div class="item item-image-gallery item-custom">' +
        '   <ion-scroll direction="y">' +
        '       <ion-gallery ion-gallery-items="block.gallery" ng-if="!is_loading"></ion-gallery>' +
        '   </ion-scroll>' +
        '</div>' +
        '<div ng-if="block.description" class="item item-custom padding description">{{ block.description }}</div>'
    };
}).directive("sbCmsSlider", function() {
    return {
        restrict: 'A',
        scope: {
            block: "=",
            gallery: "="
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
        controller: function($ionicGesture, $ionicModal, $ionicScrollDelegate, $rootScope, $scope) {
            $scope.carouselIndex = 0;

            $scope.is_fullscreen = false;

            $scope.setCarouselIndex = function(index) {
                if(index < 0) {
                    index = 0;
                } else if(index >= $scope.block.gallery.length) {
                    index--;
                }
                $scope.carouselIndexModal = index;
            };

            $scope.showFullscreen = function(index) {
                $ionicModal.fromTemplateUrl('zoom-modal.html', {
                    scope: $scope,
                    animation: 'block'
                }).then(function(modal) {
                    $scope.modal = modal;
                    $scope.carouselIndexModal = index;
                    $scope.modal.show();

                    $scope.modal.is_zoomed = false;
                    $scope.modal.scale_to_original_size = false;

                    var element = angular.element(document.getElementById('zoomedImage'));

                    $scope.modal.release = $ionicGesture.on("release", function() {
                        if($scope.modal.scale_to_original_size) {
                            $ionicScrollDelegate.$getByHandle('slide-' + $scope.carouselIndexModal).zoomTo(1, true);
                            $scope.modal.scale_to_original_size = false;
                        }
                    }, element);

                    $scope.modal.pinch = $ionicGesture.on("pinch", function (event, o, t, l) {
                        if(event.gesture.scale < 1) {
                            $scope.modal.scale_to_original_size = true;
                        }
                    }, element);

                    $scope.modal.doubleTap = $ionicGesture.on("doubletap", function (event) {
                        if($scope.modal.is_zoomed) {
                            $ionicScrollDelegate.$getByHandle('slide-' + $scope.carouselIndexModal).zoomTo(1, true);
                        } else {
                            $ionicScrollDelegate.$getByHandle('slide-' + $scope.carouselIndexModal).zoomTo(3, true, event.gesture.touches[0].pageX, event.gesture.touches[0].pageY);
                        }

                        $scope.modal.is_zoomed = !$scope.modal.is_zoomed;
                    }, element);
                });

            };

            $scope.hideFullscreen = function() {
                $scope.carouselIndex = $scope.carouselIndexModal;
                $scope.modal.remove();
            };
        }
    };
}).directive("sbCmsVideo", function() {
    return {
        restrict: 'A',
        scope: {
            block: "="
        },
        template:
        '<div class="cms_block">'
        + '<div sb-video video="block"></div>'
        + '</div>'
    };
}).directive("sbCmsAddress", function() {
    return {
        restrict: 'A',
        scope: {
            block: "=",
            onShowMap: "&",
            onAddToContact: "&"
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
        controller: function ($cordovaGeolocation, $ionicLoading, $rootScope, $scope, $state, $stateParams, $window /*$location, $q, Url/*, Application, GoogleMapService*/) {

$scope.handle_address_book = false; // Application.handle_address_book;

$scope.showMap = function () {
    if($rootScope.isOverview) {
        $rootScope.showMobileFeatureOnlyError();
        return;
    }

    $ionicLoading.show();

    $cordovaGeolocation.getCurrentPosition({ enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }).then(function(position) {
        $scope.getItineraryLink(position.coords, $scope.block);

        $ionicLoading.hide();
    }, function() {
        var null_point = {"latitude":null,"longitude":null};
        $scope.getItineraryLink(null_point, $scope.block);

        $ionicLoading.hide();
    });
};

$scope.addToContact = function () {

    if ($scope.onAddToContact && angular.isFunction($scope.onAddToContact)) {
        $scope.onAddToContact($scope.block);
    }
};

$scope.getItineraryLink = function(point1,point2) {
    var link = "https://www.google.com/maps/dir/";

    if(point1.latitude) {
        link += point1.latitude + "," + point1.longitude;
    }

    if(point2.latitude) {
        link += "/" + point2.latitude + "," + point2.longitude;
    }

    $window.open(link, $rootScope.getTargetForLink(), "location=no");
};

}
};
}).directive("sbCmsButton", function() {
    return {
        restrict: 'A',
        scope: {
            block: "="
        },
        template:
        '   <a href="{{ url }}" target="{{ target }}" class="item item-text-wrap item-icon-left item-custom">' +
        '       <i class="icon" ng-class="icon"></i>' +
        '       {{ label | translate }}' +
        '   </a>',
        controller: function ($ionicPopup, $rootScope, $scope, $timeout, $translate, $window, Application) {
            $scope.openLink = function() {

                if($rootScope.isOverview) {
                    $rootScope.showMobileFeatureOnlyError();
                    return;
                }

                if(ionic.Platform.isIOS() && $scope.block.content.indexOf("pdf") >= 0) {
                    $window.open($scope.block.content, $rootScope.getTargetForLink(), "EnableViewPortScale=yes");
                } else {
                    $window.open($scope.block.content, $rootScope.getTargetForLink(), "location=no");
                }

            };
        },
        link: function (scope, element) {
            if (scope.block.type_id == "phone") {

                scope.icon = "ion-ios-telephone-outline";
                scope.label = "Phone";

                if (!scope.block.content.startsWith('tel:')) {
                    scope.block.content = "tel:" + scope.block.content;
                }

                scope.url = scope.block.content;
                scope.target = "_self";

            } else if (scope.block.type_id == "link") {

                scope.icon = "ion-ios-world-outline";
                scope.label = (scope.block.label != null && scope.block.label.length > 0) ? scope.block.label : "Website";
                var a = angular.element(element).find("a");
                a.on("click", function (e) {
                    e.preventDefault();
                    scope.openLink();
                    return false;
                });

                scope.$on("$destroy", function () {
                    a.off("click");
                });
            } else {
                if (!scope.block.content.startsWith('mailto:')) {
                    scope.block.content = "mailto:" + scope.block.content;
                }
                scope.icon = "ion-ios-email";
                scope.label = "Email";
                scope.url = scope.block.content;
                scope.target = "_self";

                scope.$on("$destroy", function () {
                    a.off("click");
                });
            }
        }
    };
}).directive("sbCmsFile", function() {
    return {
        restrict: 'A',
        scope: {
            block: "="
        },
        template:
        '<div class="item item-text-wrap item-icon-left item-custom" ng-click="openFile()">' +
        '   <i class="icon ion-paperclip"></i>' +
        '   {{ block.display_name }}' +
        '</div>',
        controller: function ($rootScope, $scope) {

            $scope.openFile = function() {

                if($rootScope.isOverview) {
                    $rootScope.showMobileFeatureOnlyError();
                    return;
                }

                if(ionic.Platform.isAndroid()) {
                    window.open($scope.block.file_url, "_system", "location=no");
                } else {
                    window.open($scope.block.file_url, $rootScope.getTargetForLink(), "EnableViewPortScale=yes");
                }

            };

        }
    };
});
