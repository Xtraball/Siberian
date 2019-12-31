angular.module('starter')
.controller('CabrideVehicleType', function ($scope, $translate, Cabride, Dialog) {
    angular.extend($scope, {
        isLoading: false,
        enableCustomForm: Cabride.settings.enableCustomForm,
        customFormFields: Cabride.settings.customFormFields,
        currentVehicleId: null,
        currentVehicleType: null
    });

    $scope.select = function (vehicleId, vehicleType) {
        $scope.currentVehicleId = vehicleId;
        $scope.currentVehicleType = vehicleType;

        // Directly go to the next page!
        if (!$scope.enableCustomForm) {
            $scope.selectVehicle($scope.currentVehicleType);
        }
    };

    $scope.imagePath = function (image) {
        if (image === "") {
            return IMAGE_URL + "app/local/modules/Cabride/resources/design/desktop/flat/images/car-icon.png";
        }
        return IMAGE_URL + "images/application" + image;
    };

    $scope.validate = function () {
        if ($scope.customFormIsValid()) {
            $scope.selectVehicle($scope.currentVehicleType);
        }
    };

    $scope.customFormIsValid = function () {
        var required = ["number", "password", "text", "textarea", "date", "datetime"];
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
