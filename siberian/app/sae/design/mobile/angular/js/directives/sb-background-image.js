App.directive('sbBackgroundImage', function($window, $http, $timeout, LayoutService) {
    return {
        restrict: 'A',
        scope: {
            valueId: "=",
            closeOnClick: "="
        },
        link: function (scope, element, attrs) {

            //scope.$parent.background_is_loading = true;
            scope.background_images = {};
            var isOverview = false;
            if(scope.$parent.isOverview) {
                isOverview = true;
            }
            if(angular.isDefined(scope.valueId)) {
                $http({
                    method: 'GET',
                    url: BASE_URL+'/front/mobile/backgroundimage/value_id/'+scope.valueId,
                    cache: !isOverview
                }).success(function(urls) {
                    if(urls) {
                        scope.background_images = urls;
                        scope.setBackgroundImage();
                    }
                }).error(function() {
                    //scope.$parent.background_is_loading = false;
                });
            } else {
                //scope.$parent.background_is_loading = false;
            }

            scope.onResizeFunction = function() {
                var height = $window.innerHeight;
                var width = $window.innerWidth;

                angular.forEach(element.children(), function(div, key) {
                    if(angular.element(div).hasClass("scrollable_content")) {
                        try {
                            if(!isNaN(div.offsetTop)) {

                                $timeout(function() {
                                    div.style.height = "calc(100% - " + div.offsetTop + "px)"; //height - div.offsetTop +"px";
                                    if(!div.style.length) {
                                        div.style.height = height - div.offsetTop + "px";
                                    }
                                }, 10);
                            }
                        } catch(e) {

                        }
                    }
                });

//                element[0].style.height = height + "px";
                element.css({height: "100%", minWidth: width - LayoutService.leftAreaSize + "px"});

                scope.setBackgroundImage();
            };

            scope.setBackgroundImage = function() {
                var src = scope.background_images.tablet;
                if(window.innerWidth > 350) {
                    src = scope.background_images.hd;
                } else if(scope.background_images.standard) {
                    src = scope.background_images.standard;
                }

                if(src) {
                    var img = new Image();
                    img.src = src;
                    img.onload = function () {
                        scope.setBackgroundImageStyle(src);
                    };
                    if(img.complete) {
                        scope.setBackgroundImageStyle(src);
                    } else if(Application.is_ios) {
                        scope.setBackgroundImageStyle(src);
                    }
                }
            };

            scope.setBackgroundImageStyle = function(src) {
                $timeout(function() {
                    scope.$parent.style_background_image = {"background-image": "url('" + src + "')"};
                });
            };

            scope.onResizeFunction();

            angular.element($window).bind('resize', function() {
                $timeout(function() {
                    scope.onResizeFunction();
                });
            });

            scope.$on("connectionStateChange", function() {
                scope.onResizeFunction();
            });

            scope.$on("refreshPageSize", function() {
                scope.onResizeFunction();
            });

            scope.$on("$destroy", function() {
                angular.element($window).unbind('resize');
            });

            if(scope.valueId == "homepage") {
                element.on("click", function (e) {
                    if(angular.element(e.target).hasClass("close_on_click")) {
                        $window.location = "app:closeApplication";
                    }
                });

                scope.$on("$destroy", function () {
                    element.off("click");
                });
            }
        }
    };
});