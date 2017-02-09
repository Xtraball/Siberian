/*global
    App, ionic, angular
 */

App.directive("sbAClick", function($filter, $rootScope, $timeout, $window, $state) {
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

                        elem.href = "javascript:void(0);";
                        angular.element(elem).bind("click", function () {
                            if(!offline && $rootScope.isOffline) {
                                $rootScope.onlineOnly();
                            } else {
                                $state.go(state, params);
                            }
                        });

                    } else {

                        var old_href = elem.href;
                        elem.href = "javascript:void(0)";
                        angular.element(elem).bind("click", function () {
                            if ($rootScope.isOverview) {
                                $rootScope.showMobileFeatureOnlyError();
                                return false;
                            }

                            if (/^(tel:).*/.test(old_href) && ionic.Platform.isAndroid()) {
                                $window.open(old_href, '_self', "location=no");
                            } else if (ionic.Platform.isIOS() && old_href.indexOf("pdf") >= 0) {
                                $window.open(old_href, $rootScope.getTargetForLink(), "EnableViewPortScale=yes");
                            } else {
                                $window.open(old_href, $rootScope.getTargetForLink(), "location=no");
                            }
                            return false;
                        });
                    }
                });
            });
        }
    };
});
