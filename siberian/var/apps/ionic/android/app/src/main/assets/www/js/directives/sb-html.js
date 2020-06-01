/**
 * Directive sbHtml
 */
angular
.module("starter")
.directive("sbHtml", function ($sce, $filter, $parse, $compile, InAppLinks, LinkService) {
    return {
        restrict: 'A',
        compile: function sbHtmlCompile(tElement, tAttrs) {
            var sbHtmlGetter = $parse(tAttrs.sbHtml);
            var sbHtmlWatch = $parse(tAttrs.sbHtml, function sceValueOf(val) {
                // Unwrap the value to compare the actual inner safe value, not the wrapper object.
                return $sce.valueOf(val);
            });
            var bindLinks = function (element) {
                var linksCollection = angular.element(element).find("a");
                angular.forEach(linksCollection, function (_element) {

                    if (_element.attributes.hasOwnProperty('data-state')) {
                        InAppLinks.handlerLink(_element);
                    } else {
                        angular.element(_element).bind('click', function (event) {
                            event.preventDefault();
                            LinkService.openLink(_element.href, {}, true);
                        });
                    }
                });
            };

            $compile.$$addBindingClass(tElement);

            return function sbHtmlLink(scope, element, attr) {
                $compile.$$addBindingInfo(element, attr.sbHtml);

                scope.$watch(sbHtmlWatch, function sbHtmlWatchAction() {
                    // The watched value is the unwrapped value. To avoid re-escaping, use the direct getter.
                    var value = sbHtmlGetter(scope);
                    var html = $filter("parseUrlFilter")(value);
                    html = $sce.trustAsHtml(html);

                    element.html(html);

                    bindLinks(element);
                });
            };
        }
    };
});
