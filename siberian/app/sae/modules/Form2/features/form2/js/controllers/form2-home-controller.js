/**
 * Form version 2 controllers
 */
angular
.module('starter')
.controller('Form2HomeController', function ($scope, Form2) {
    angular.extend($scope, {
        pageTitle: 'Form 2',
        valueId: 0
    });

    $scope.customFormIsValid = function () {
        var required = ['number', 'password', 'text', 'textarea', 'date', 'datetime'];
        var isValid = true;
        var invalidFields = [];
        Cabride.settings.customFormFields.forEach(function (field) {
            if (required.indexOf(field.type) >= 0) {
                if (field.value === undefined ||
                    (field.value + "").trim().length === 0) {
                    invalidFields.push("&nbsp;-&nbsp;" + field.label);
                    isValid = false;
                }
            }
        });

        if (!$scope.currentVehicleType) {
            invalidFields.push("&nbsp;-&nbsp;" + $translate.instant("Vehicle type", "cabride"));
            isValid = false;
        }

        if (!isValid) {
            Dialog.alert("Required fields", invalidFields.join("<br />"), "OK", -1);

            return isValid;
        }

        return isValid;
    };
});

