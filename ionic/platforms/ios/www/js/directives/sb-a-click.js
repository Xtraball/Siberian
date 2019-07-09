/*global
    App, ionic, angular
 */

angular.module("starter").directive("sbAClick", function($rootScope, $timeout, $window, $state, Pages, Dialog, LinkService, Customer) {
    return {
        restrict: 'A',
        scope: {},
        priority: -10,
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

                            // Special in-app link for my account!
                            if (state === "my-account") {
                                Customer.loginModal();
                            } else if (!offline && $rootScope.isOffline) {
                                $rootScope.onlineOnly();
                            } else {
                                if (params.hasOwnProperty("value_id")) {
                                    var feature = Pages.getValueId(params.value_id);
                                    if (feature && !feature.is_active) {
                                        Dialog.alert("Error", "This feature is no longer available.", "OK", 2350);
                                        return;
                                    }
                                }

                                $state.go(state, params);
                            }
                        });

                    } else {
                        angular.element(elem).bind("click", function (e) {
                            e.preventDefault();
                            var options = {
                                "hide_navbar" : false,
                                "use_external_app" : false
                            };
                            LinkService.openLink(elem.href,options);
                        });
                    }
                });
            });
        }
    };
});
