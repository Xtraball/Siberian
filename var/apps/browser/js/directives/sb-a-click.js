App.directive("sbAClick", function($rootScope, $timeout, $window) {
    return {
        restrict: 'A',
        scope: {
        },
        link: function (scope, element) {
            $timeout(function() {
                var collection = angular.element(element).find("a");
                angular.forEach(collection, function(elem) {
                    var old_href = elem.href;
                    elem.href = "javascript:void(0)";
                    angular.element(elem).bind("click", function() {
                        if($rootScope.isOverview) {
                            $rootScope.showMobileFeatureOnlyError();
                            return false;
                        }
                        $window.open(old_href, $rootScope.getTargetForLink(), "location=no"); return false;
                    });
                });
            });
        }
    };
});