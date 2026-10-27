/*global
    App
 */

/**
 * A password field with an eye button that shows or hides what is typed.
 *
 * <sb-password ng-model="configs.smtp_credentials.password" toggle-title="Show / hide"></sb-password>
 *
 * The field is not filled by the browser password manager (autocomplete off), the value is bound to ng-model.
 */
App.directive('sbPassword', function () {
    return {
        restrict: 'E',
        scope: {
            ngModel: '=',
            toggleTitle: '@'
        },
        template:
            '<div class="sb-password">' +
                '<input class="form-control" autocomplete="new-password"' +
                    ' ng-attr-type="{{ revealed ? \'text\' : \'password\' }}" ng-model="ngModel" />' +
                '<button type="button" class="sb-password-toggle" tabindex="-1"' +
                    ' ng-click="revealed = !revealed" title="{{ toggleTitle }}" aria-label="{{ toggleTitle }}">' +
                    '<i class="fa" ng-class="revealed ? \'fa-eye-slash\' : \'fa-eye\'"></i>' +
                '</button>' +
            '</div>',
        link: function (scope) {
            scope.revealed = false;
        }
    };
});
