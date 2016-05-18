"use strict";

App.directive('sbHeader', function(Pictos, LayoutService) {
    return {
        restrict: 'E',
        template:
            '<header class="page_header">' +
                '<div class="header absolute scale-fade" ng-show="!message.is_visible">' +
                    '<button ng-show="showBackButton()" type="button" class="btn_left header no-background ng-hide" back-button>' +
                        '<div class="back_arrow header"></div>' +
                        '<span>{{ title_back }}</span>' +
                    '</button>' +
                    '<a ng-show="layout.properties.menu.visibility == \'toggle\' && layout.properties.options.isRootPage" ng-click="layout.properties.menu.isVisible = !layout.properties.menu.isVisible" class="toggleLeftSideBarIcon">' +
                        '<img ng-src="{{ pictos.url }}" width="20px" />' +
                    '</a>' +
                    '<p class="title">{{ title | translate }}</p>' +
                    '<button type="button" class="btn_right header no-background" ng-if="right_button" ng-click="right_button.action()" ng-class="{arrow: !right_button.hide_arrow, \'is-picto\': right_button.picto_url}">' +
                        '<div class="next_arrow header" ng-hide="right_button.hide_arrow"></div>' +
                        '<span ng-if="!right_button.picto_url">{{ right_button.title | translate }}</span>' +
                        '<img ng-if="right_button.picto_url" ng-src="{{ right_button.picto_url }}" height="{{right_button.height ? right_button.height : 30}}" />' +
                    '</button>' +
                '</div>' +
                '<div class="message scale-fade" ng-show="message.is_visible">' +
                    '<p ng-class="{error: message.is_error, header: !message.is_error}" ng-bind-html="message.text | translate"></p>' +
                '</div>' +
            '</header>',
        replace: true,
        scope: {
            title_back: '=titleBack',
            title: '=',
            right_button: '=rightButton',
            message: '='
        },
        link: function ($scope) {

            $scope.layout = LayoutService;
            $scope.pictos = {
                url: Pictos.get("menu", "header")
            };
            $scope.showBackButton = function () {

                if(!LayoutService.isInitialized()) return false;

                switch (LayoutService.properties.menu.visibility) {
                    // Type Homepage => The back button is always visible
                    case 'homepage': return true;
                    // Type Toggle, Always or whatsoever => The back button is not visible in the main pages
                    case 'toggle':
                    case 'always':
                    default: return !LayoutService.properties.options.isRootPage;
                }

            };

        }
    }
});