/**
 * Form version 2 controllers
 */
angular
.module('starter')
.controller('Form2HomeController', function ($window, $scope, $stateParams, Form2, Loader, Dialog) {
    angular.extend($scope, {
        isLoading: true,
        value_id: $stateParams.value_id,
        pageTitle: 'Form 2',
        originalFields: {},
        cardDesign: false,
        fields: {},
        valueId: 0
    });

    Form2.setValueId($stateParams.value_id);

    $scope.pristineFields = function () {
        $scope.fields = angular.copy($scope.originalFields);
        $scope.sections = $scope.buildSections();
    };

    // Check if section has at least a real field (not spacer)!
    $scope.validateSection = function (sections) {
        let hasOneValidField = false;
        sections.forEach(function (checkField) {
            if (checkField.type !== 'spacer') {
                hasOneValidField = true;
            }
        });

        return hasOneValidField;
    };

    $scope.buildSections = function () {
        var sections = [];
        var _tmpSection = [];
        $scope.fields.forEach(function (field) {
            if (field.type === 'divider') {
                if (_tmpSection.length > 0 &&
                    $scope.validateSection(_tmpSection)) {
                    sections.push(_tmpSection);
                }
                _tmpSection = [];
            }
            _tmpSection.push(field);
        });
        // last section!
        if (_tmpSection.length > 0 &&
            $scope.validateSection(_tmpSection)) {
            sections.push(_tmpSection);
        }

        return sections;
    };

    $scope.getImageSrc = function (image) {
        if (!image.length) {
            return "./features/form_v2/assets/templates/l1/img/no-image.png";
        }

        return IMAGE_URL + 'images/application' + image;
    };

    $scope.formIsValid = function () {
        var required = ['number', 'password', 'text', 'textarea', 'date', 'datetime'];
        var isValid = true;
        var invalidFields = [];
        $scope.fields.forEach(function (field) {
            if (required.indexOf(field.type) >= 0 && field.is_required) {
                if (field.value === undefined ||
                    (field.value + '').trim().length === 0) {
                    invalidFields.push('&nbsp;-&nbsp;' + field.label);
                    isValid = false;
                }
            }
        });

        if (!isValid) {
            Dialog.alert('Required fields', invalidFields.join('<br />'), 'OK', -1, 'form2');
        }

        return isValid;
    };

    $scope.submit = function () {
        Loader.show();
        var isValid = $scope.formIsValid();
        if (!isValid) {
            Loader.hide();
            return;
        }

        Form2
            .submit($scope.fields)
            .then(function (data) {
                $scope.pristineFields();
                Dialog.alert('Success', data.message, 'OK', 3200, 'form2');
            }, function (data) {
                Dialog.alert('Error', data.message, 'OK', -1, 'form2');
            }).then(function () {
                Loader.hide();
            });
    };

    $scope.loadContent = function () {
        $scope.isLoading = true;

        Form2
            .find()
            .then(function (payload) {
                $scope.originalFields = payload.formFields;
                $scope.pageTitle = payload.pageTitle;
                $scope.cardDesign = payload.cardDesign;
                $scope.pristineFields();
            }, function (error) {
                Dialog.alert('Error', error.message, 'OK');
            }).then(function () {
                $scope.isLoading = false;
            });
    };

    $scope.loadContent();

    /** Overview specs */
    if ($window.overview) {
        $scope.reloadOverview = function () {
            $scope.isLoading = true;

            Form2
                .reloadOverview()
                .then(function (payload) {
                    $scope.originalFields = payload.formFields;
                    $scope.pageTitle = payload.pageTitle;
                    $scope.cardDesign = payload.cardDesign;
                    $scope.pristineFields();
                }, function (error) {
                    Dialog.alert('Error', error.message, 'OK');
                }).then(function () {
                $scope.isLoading = false;
            });
        };

        $window.overview['form_v2'] = $scope.reloadOverview;
        $scope.reloadOverview();
    }
});

