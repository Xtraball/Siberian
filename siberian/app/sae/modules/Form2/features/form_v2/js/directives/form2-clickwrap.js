/**
 * @directive form2-clickwrap
 * @author Xtraball SAS
 * @version 4.18.5
 */
angular
    .module('starter')
    .directive('form2Clickwrap', function (Application) {
        return {
            restrict: 'E',
            replace: false,
            scope: {
                field: '=',
                cardDesign: '=?'
            },
            templateUrl: './features/form_v2/assets/templates/l1/directive/clickwrap.html',
            link: function (scope) {
                if (scope.cardDesign === undefined) {
                    scope.cardDesign = false;
                }
                scope.field.value = false;
                scope.modal = null;

                scope.privacyPolicy = '';
                if (scope.field.clickwrap === 'privacy-policy') {
                    scope.privacyPolicy = Application.privacyPolicy.text;

                    if (Application.gdpr.isEnabled) {
                        scope.privacyPolicy += '<br /><br />' + Application.privacyPolicy.gdpr;
                    }
                    scope.defaultTitle = 'Privacy policy';
                } else {
                    scope.privacyPolicy = scope.field.clickwrap_richtext;
                    scope.defaultTitle = scope.field.label;
                }

                // Default modal title or custom!
                scope.modalTitle = scope.defaultTitle;
                if (scope.field.clickwrap_modaltitle.length > 0) {
                    scope.modalTitle = scope.field.clickwrap_modaltitle;
                }
            },
            controller: function($scope, Modal) {
                $scope.onClick = function () {
                    if (!$scope.field.value) {
                        return;
                    }
                    Modal
                        .fromTemplateUrl('./features/form_v2/assets/templates/l1/directive/clickwrap-modal.html', {
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
            }
        };
    });