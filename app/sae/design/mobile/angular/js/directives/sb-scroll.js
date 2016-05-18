App.directive('sbScroll', function($window) {
    return {
        restrict: 'A',
        link: function(scope, element, attrs) {

            element = element[0];
            var height = $window.innerHeight;
            var offsetTop = element.offsetTop;
            var tolerance = 0;
            var paddingBottom = window.getComputedStyle(element, null).getPropertyValue('padding-bottom').replace("px", "");

            if(angular.isDefined(attrs['rnCarousel'])) {
                tolerance += 23;
            }

            tolerance += parseInt(paddingBottom);

            element.style.height = (height - offsetTop - tolerance) + "px";

        }
    };
});