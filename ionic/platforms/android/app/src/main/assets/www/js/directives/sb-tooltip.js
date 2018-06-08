/**
 * sb-tooltip directive!
 */
angular.module('starter').directive('sbTooltip', function () {
    return {
        restrict: 'A',
        scope: {},
        replace: false,
        bindToController: {
            show_tooltip: '=showTooltip',
            collection: '=',
            current_item: '=currentItem',
            button_label: '=buttonLabel',
            button_icon: '=buttonIcon',
            onItemClicked: '&'
        },
        controllerAs: 'tooltip',
        template:
            '<button class="button button-clear" ' +
            '        ng-click="toggleTooltip()">' +
            '{{ tooltip.button_label | translate }}' +
            '   <i ng-if="tooltip.button_icon"' +
            '      ng-class="tooltip.button_icon"></i>'+
            '</button>' +
            '<div class="tooltip tooltip-custom" ' +
            '     ng-show="tooltip.collection.length && tooltip.show_tooltip">' +
            '    <i class="icon ion-arrow-up-b dark"></i>' +
            '    <div style="overflow-y: scroll; overflow-x: hidden; max-height: 250px;">' +
            '        <ul>' +
            '            <li ng-repeat="item in tooltip.collection">' +
            '                <span class="block" ' +
            '                      ng-click="itemClicked(item);" ' +
            '                      ng-class="{ \'active\': tooltip.current_item.id == item.id }">' +
            '{{ item.name | translate }}</span>' +
            '                <ul ng-show="item.show_children">' +
            '                    <li ng-repeat="child in item.children">' +
            '                        <span class="block" ' +
            '                              ng-click="itemClicked(child)" ' +
            '                              ng-class="{ \'active\': tooltip.current_item.id == child.id }">' +
            '{{ child.name | translate }}</span>' +
            '                    </li>' +
            '                </ul>' +
            '            </li>' +
            '        </ul>' +
            '    </div>' +
            '</div>',
        controller: function ($scope) {
            var tooltip = this;
            $scope.toggleTooltip = function () {
                tooltip.show_tooltip = !tooltip.show_tooltip;
            };

            $scope.itemClicked = function (item) {
                if (!item.children) {
                    tooltip.show_tooltip = false;
                }
                tooltip.onItemClicked({
                    object: item
                });
            };
        }
    };
});
