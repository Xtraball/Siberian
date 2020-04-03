/**
 * @directive sb-clickwrap
 * @author Xtraball SAS <dev@xtraball.com>
 * @version 4.18.12
 */
angular
    .module('starter')
    .directive('sbClickwrap', function (Application) {
        return {
            restrict: 'E',
            replace: false,
            scope: {
                field: '=',
                model: '=',
                cardDesign: '=?'
            },
            templateUrl: 'templates/directives/clickwrap/clickwrap.html',
            link: function (scope) {
                if (scope.cardDesign === undefined) {
                    scope.cardDesign = false;
                }
                scope.modal = null;

                scope.htmlContent = scope.field.htmlContent;

                // Default modal title or custom!
                if (scope.field.modaltitle.length > 0) {
                    scope.modalTitle = (scope.field.modaltitle.length > 0) ?
                        scope.field.modaltitle : scope.field.label;
                }
            },
            controller: function($scope, Modal) {
                $scope.openModal = function () {
                    Modal
                        .fromTemplateUrl('templates/directives/clickwrap/clickwrap-modal.html', {
                            scope: angular.extend($scope, {
                                close: function () {
                                    $scope.modal.remove();
                                }
                            }),
                            animation: 'slide-in-right-left'
                        }).then(function (modal) {
                        $scope.modal = modal;
                        $scope.modal.show();

                        return modal;
                    });
                };

                $scope.onClick = function () {
                    if (!$scope.model) {
                        return;
                    }
                    $scope.openModal();
                };
            }
        };
    });