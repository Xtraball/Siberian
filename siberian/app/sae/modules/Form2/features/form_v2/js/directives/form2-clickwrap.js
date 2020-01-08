/**
 * @directive form2-clickwrap
 * @author Xtraball SAS
 * @version 4.18.5
 */
angular
    .module('starter')
    .directive('form2Clickwrap', function () {
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
            },
            controller: function($scope, Application, Modal) {
                $scope.getPrivacyPolicy = function () {
                    var html = '';
                    if ($scope.field.clickwrap === 'privacy-policy') {
                        html = Application.privacyPolicy.text;

                        if (Application.gdpr.isEnabled) {
                            html += '<br /><br />' + Application.privacyPolicy.gdpr;
                        }
                    } else {
                        html = $scope.field.clickwrap_richtext;
                    }

                    return html;
                };

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