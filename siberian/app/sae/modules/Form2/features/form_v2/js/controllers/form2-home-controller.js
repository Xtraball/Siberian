/**
 * Form version 2 controllers
 */
angular
.module('starter')
.controller('Form2HomeController', function ($scope, $stateParams, Form2, Dialog) {
    angular.extend($scope, {
        isLoading: true,
        value_id: $stateParams.value_id,
        page_title: 'Form 2',
        originalFields: {},
        fields: {},
        valueId: 0
    });

    Form2.setValueId($stateParams.value_id);

    $scope.pristineFields = function () {
        $scope.fields = angular.copy($scope.originalFields);
    };

    $scope.getImageSrc = function (image) {
        if (!image.length) {
            return "./features/form_v2/assets/templates/l1/img/no-image.png";
        }

        return IMAGE_URL + "images/application" + image;
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
                $scope.page_title = payload.page_title;
                $scope.pristineFields();
            }, function (error) {
                Dialog.alert('Error', error.message, 'OK');
            }).then(function () {
                $scope.isLoading = false;
            });
    };

    $scope.loadContent();
});

