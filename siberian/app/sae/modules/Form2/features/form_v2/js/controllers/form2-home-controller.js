/**
 * Form version 2 controllers
 */
angular
.module('starter')
.controller('Form2HomeController', function ($scope, Form2) {
    angular.extend($scope, {
        isLoading: true,
        pageTitle: 'Form 2',
        originalFields: {},
        fields: {},
        valueId: 0
    });

    $scope.pristineFields = function () {
        $scope.fields = angular.copy($scope.originalFields);
    };

    $scope.formIsValid = function () {
        var required = ['number', 'password', 'text', 'textarea', 'date', 'datetime'];
        var isValid = true;
        var invalidFields = [];
        $scope.fields.forEach(function (field) {
            if (required.indexOf(field.type) >= 0) {
                if (field.value === undefined ||
                    (field.value + '').trim().length === 0) {
                    invalidFields.push('&nbsp;-&nbsp;' + field.label);
                    isValid = false;
                }
            }
        });

        if (!isValid) {
            Dialog.alert('Required fields', invalidFields.join('<br />'), 'OK', -1);
        }

        return isValid;
    };

    $scope.loadContent = function () {
        $scope.isLoading = true;
        Form2
            .find()
            .then(function (payload) {
                $scope.originalFields = payload.formFields;
                $scope.pristineFields();
            }, function (error) {
                Dialog.alert('Error', error.message, 'OK');
            }).then(function () {
                $scope.isLoading = false;
            });
    };

    $scope.loadContent();
});

