App.directive('sbInputNumber', function($window) {
    return {
        restrict: 'E',
        scope: {
            "changeQty": "&",
            "params": "=?"
        },
        template: "<div class='input-number' ng-style='styleAlign'><div class='label'>{{ label }}</div> " + "" +
                        "<div ng-style='btStyle' class='button toggle-left' ng-click='down()'>-</div>" +
                        "<div ng-style='labelStyle' class='number'><div>{{ value }}</div></div>" +
                        "<div ng-style='btStyle' class='button toggle-right' ng-click='up()'>+</div>" +
                    "</div>",
        replace: true,
        link: function(scope, element, attrs) {
            scope.step = attrs.step?attrs.step:1;
            scope.min = attrs.min?attrs.min:0;
            scope.max = attrs.max?attrs.max:999;
            scope.value = attrs.value?attrs.value:0;
            scope.label = attrs.label?attrs.label + ":":"";
            scope.lineHeight = attrs.lineHeight?attrs.lineHeight:"30px";
            scope.params = angular.isDefined(scope.params)?scope.params:{};
            scope.height = angular.isDefined(scope.params.size)?scope.params.size:25;
            scope.btStyle = {'height':scope.height + 'px','width':scope.height + 'px','line-height':scope.height + 'px'};
            scope.labelStyle = {'height':scope.height + 'px','width':scope.height + 'px','line-height':scope.lineHeight};
            scope.styleAlign = angular.isDefined(scope.params.align)?{'text-align':scope.params.align}:{'text-align':'right'};

            scope.up = function() {
                if(scope.value<scope.max) {
                    scope.value++;
                    scope.callBack();
                }
            };
            scope.down = function() {
                if(scope.value>scope.min) {
                    scope.value--;
                    scope.callBack();
                }
            };

            scope.callBack = function() {
                if(scope.changeQty) {
                    scope.changeQty({qty: scope.value,params: scope.params});
                }
            }
        }
    };
});