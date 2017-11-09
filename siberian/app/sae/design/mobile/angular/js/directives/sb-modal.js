"use strict";

App.directive('sbModal', function($window, $rootScope, $timeout, $q, modalManager, Translator) {
    return {
        restrict: 'A',
        replace: true,
        template:
        '<div class="modal" ng-show="showModal">' +
            '<div class="overlay toggle" ng-show="showModal"></div>' +
            '<div class="dialog scale-fade" ng-style="dialogStyle" ng-show="showModal">' +
                '<div class="close-only background dialog-content"><p class="title">{{ title | translate }}</p><p class="modal-content" ng-bind-html="content"></p></div>' +
                '<div class="bt-div">' +
                    '<div ng-class="{\'width-100\': !show_cancel,\'width-50\': show_cancel,\'bt-div-double\': show_cancel}" class="left">' +
                        '<button ng-class="{\'bt-ok-double\': show_cancel}"  ng-if="!show_link" class="button bt-ok" ng-click="confirm()">{{ ok_label | translate }}</button>' +
                        '<a href="{{ url }}" target="{{ link_target }}" ng-class="{\'bt-ok-double\': show_cancel}" class="button bt-ok" ng-if="show_link">{{ ok_label | translate }}</a>' +
                    '</div>' +
                    '<div ng-class="{\'width-50\': show_cancel}" class="right" ng-if="show_cancel">' +
                        '<button class="button bt-ko" ng-click="cancel();">{{ cancel_label | translate }}</button>' +
                    '</div>' +
                '</div>' +
            '</div>' +
        '</div>',
        scope: true,
        link: function(scope, element) {

            scope.hideModal = function() {
                $timeout(function() {
                    scope.showModal = false;
                    $timeout(function() {
                        modalManager.next();
                    }, 100);

                });
            };

            scope.$on('show_modal', function(e, modal) {

                scope.dialogStyle = {};

                if(modal.width)
                    scope.dialogStyle.width = modal.width;
                if(modal.height)
                    scope.dialogStyle.height = modal.height;

                scope.dialogStyle.height = modal.height;

                scope.link_target = $rootScope.isOverview?"_blank":"";
                scope.url = modal.url ? modal.url : null;
                scope.show_link = !!scope.url;
                scope.show_cancel = scope.show_link || modal.show_cancel;
                scope.title = modal.title;
                scope.cancel_label = modal.cancel_label ? modal.cancel_label : "Cancel";
                scope.ok_label = modal.ok_label ? modal.ok_label : "OK";
                scope.text = modal.content ? Translator.get(modal.content) : "";
                scope.cover = modal.cover;
                scope.content = modal.cover ? "<p class='a-center'><img src='" + modal.cover + "' width='150px'></p><p>" + scope.text + "</p>" : "<p>" + scope.text + "</p>";

                scope.confirm = function() {
                    if(angular.isFunction(modal.confirmAction)) {
                        $q.when(modal.confirmAction(),function() {
                            scope.hideModal();
                        });
                    } else {
                        scope.hideModal();
                    }
                };

                scope.cancel = function() {
                    if(angular.isFunction(modal.cancelAction)) {
                        $q.when(modal.cancelAction()).then(function() {
                            scope.hideModal();
                        });
                    } else {
                        scope.hideModal();
                    }
                };

                scope.showModal = true;
                var modal_dialog = element.children().next();
                var element_height = modal_dialog[0].offsetHeight;
                var window_height = $window.innerHeight;
                var element_top = (window_height - element_height) / 2;

                $timeout(function() {
                    modal_dialog.css("top", element_top+"px");
                });

            });
        }

    };
});

App.directive("sbProgressBar", function($timeout, $window) {
    return {
        restrict: 'A',
        replace: true,
        template:
        '<div class="modal">' +
            '<div class="overlay" ng-click="OverlayClick()"></div>' +
                '<div class="dialog" ng-style="dialogStyle">' +
                    '<div ng-class="{\'close-only\': show_close}" class="background dialog-content">' +
                        '<div class="a-center">' +
                            '<div class="progress">' +
                                '<div class="progress-bar button" style="width:{{ percent }}%;"><span style="float:right;margin-top:3px;margin-right:10px;" class="a-center download-content-percent">{{ percent }}%</span></div>' +
                            '</div>' +
                        '</div>' +
                    '</div>' +
                    '<div class="bt-div">' +
                        '<div class="width-100">' +
                            '<button class="progress-bar-info button bt-ok" style="height:auto;padding:5px;">{{ "Don\'t close the app while downloading. This may take a while." | translate }}</button>' +
                        '</div>' +
                    '</div>' +
                '</div>' +
            '</div>' +
        '</div>',
        scope: {
            percent: "="
        },
        link: function(scope, element, attrs) {
            $timeout(function() {
                var modal_dialog = element.children().next();
                var element_height = modal_dialog[0].offsetHeight;
                var window_height = $window.innerHeight;
                var element_top = (window_height - element_height) / 2;
                modal_dialog.css("top", element_top+"px");
            });

        }
    };
});