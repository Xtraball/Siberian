/**
 * Directive sbHtml
 */
angular
.module("starter")
.directive("sbHtml", function ($sce, $filter, $state, $parse, $compile, $timeout, Customer, Pages, Dialog, LinkService) {
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
                angular.forEach(linksCollection, function (elem) {
                    if (typeof elem.attributes["data-state"] !== "undefined") {

                        var params = elem.attributes["data-params"].value;
                        params = params.replace(/(^\?)/,'').split(",").map(function(n){return n = n.split(":"),this[n[0].trim()] = n[1],this}.bind({}))[0];
                        var state = elem.attributes["data-state"].value;
                        angular.element(elem).bind("click", function (e) {
                            e.preventDefault();

                            // Special in-app link for my account!
                            if (state === "my-account") {
                                Customer.loginModal();
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
                                "hide_navbar": false,
                                "use_external_app": false
                            };
                            LinkService.openLink(elem.href, options);
                        });
                    }
                });
            };

            $compile.$$addBindingClass(tElement);

            return function sbHtmlLink(scope, element, attr) {
                $compile.$$addBindingInfo(element, attr.sbHtml);

                scope.$watch(sbHtmlWatch, function sbHtmlWatchAction() {
                    console.log("sbHtml, first watch");
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
