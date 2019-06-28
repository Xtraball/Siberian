angular
.module("starter")
.directive("sbCustomTab", function($state, $rootScope, $timeout, $window, Customer, CustomTab) {
    return {
        restrict: "A",
        scope: {},
        link: function (scope, element) {
            $timeout(function () {
                // A links
                var collection = angular.element(element).find("a");
                angular.forEach(collection, function (elem) {
                    if (typeof elem.attributes["data-state"] !== "undefined") {

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
                                $state.go(state, params);
                            }
                        });

                    } else {
                        angular.element(elem).bind("click", function (e) {
                            e.preventDefault();

                            var options = {
                                tabColor: $window.colors.header.statusBarColor
                            };

                            CustomTab.openLink(elem.href, options);
                        });
                    }
                });
            });
        }
    };
});
