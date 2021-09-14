/**
 * Form version 2 controllers
 */
angular
.module('starter')
.controller('Form2HomeController', function ($ionicScrollDelegate, $window, $scope, $stateParams, $timeout, $translate,
                                             $filter, Form2, Loader, Dialog, Modal, Customer) {
    angular.extend($scope, {
        isLoading: true,
        value_id: $stateParams.value_id,
        pageTitle: 'Form 2',
        originalFields: {},
        cardDesign: false,
        fields: {},
        valueId: 0,
        currentTab: 'form',
        modalResult: null
    });

    Form2.setValueId($stateParams.value_id);

    $scope.pristineFields = function () {
        $scope.fields = angular.copy($scope.originalFields);
        $scope.sections = $scope.buildSections($scope.fields);
    };

    $scope.setTab = function (tab) {
        $timeout(function () {
            $scope.currentTab = tab;
            if (tab === 'history') {
                $ionicScrollDelegate.scrollTop(false);
            }
        });
    };

    // Check if section has at least a real field (not spacer)!
    $scope.validateSection = function (sections) {
        var hasOneValidField = false;
        sections.forEach(function (checkField) {
            if (checkField.type !== 'spacer') {
                hasOneValidField = true;
            }
        });

        return hasOneValidField;
    };

    $scope.displayModal = function (fieldSet) {
        // Build fields/sections
        var fields = angular.copy(fieldSet.payload);
        var sections = $scope.buildSections(fieldSet.payload);
        var title = $filter('moment_calendar')(fieldSet.timestamp * 1000);

        Modal
            .fromTemplateUrl('./features/form_v2/assets/templates/l1/form/display-modal.html', {
                scope: angular.extend($scope.$new(), {
                    modalFields: fields,
                    modalSections: sections,
                    modalTitle: title,
                    modalFormatLocation: function (field) {
                        if (!field.is_checked) {
                            return '';
                        }

                        var html;
                        if (field.value.address) {
                            html = field.value.address + '<br />' +
                                field.value.coords.lat + ', ' +
                                field.value.coords.lng;
                        } else {
                            html = field.value.coords.lat + ', ' +
                                field.value.coords.lng;
                        }

                        return $filter('trusted_html')(html);
                    },
                    modalClose: function () {
                        $scope.modalResult.remove();
                    }
                }),
                animation: 'slide-in-right-left'
            }).then(function (modal) {
                $scope.modalResult = modal;
                $scope.modalResult.show();

                return modal;
            });
    };

    $scope.submitDate = function (timestamp) {
        return $filter('moment_calendar')(timestamp * 1000);
    };

    $scope.buildSections = function (fields) {
        var sections = [];
        var _tmpSection = [];
        fields.forEach(function (field) {
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

    $scope.checkValue = function (field) {
        field.value += '';
        field.value = field.value.replace(/[^0-9\.,\-]/, '');
    };

    $scope.formIsValid = function () {
        var required = ['number', 'password', 'text', 'textarea', 'date', 'datetime', 'clickwrap', 'select'];
        var isValid = true;
        var invalidFields = [];
        $scope.fields.forEach(function (field) {
            if (required.indexOf(field.type) >= 0 && field.is_required) {
                if (field.type === 'clickwrap' &&
                    field.value !== true) {
                    invalidFields.push('&nbsp;-&nbsp;' + field.label);
                    isValid = false;
                } else if (field.type === 'number') {
                    var current = parseFloat(field.value);
                    if (!Number.isFinite(current)) {
                        text = $translate.instant('is not a number', 'form2');
                        invalidFields.push('&nbsp;-&nbsp;' + field.label + ' ' + text);
                        isValid = false;
                    }
                    var min = Number.parseInt(field.min);
                    var max = Number.parseInt(field.max);
                    var step = parseFloat(field.step);
                    var text;
                    if (current < min || current > max) {
                        text = $translate.instant('is not inside range', 'form2') + ' ' + min + '-' + max;
                        invalidFields.push('&nbsp;-&nbsp;' + field.label + ' ' + text);
                        isValid = false;
                    }
                    if (step !== 0 && !Number.isInteger(current / step)) {
                        text = $translate.instant('must match increment', 'form2') + ' ' + step;
                        invalidFields.push('&nbsp;-&nbsp;' + field.label + ' ' + text);
                        isValid = false;
                    }
                } else if (field.value === undefined ||
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
        Loader.show($translate.instant('Sending...', 'woocommerce2'));
        var isValid = $scope.formIsValid();
        if (!isValid) {
            Loader.hide();
            return;
        }

        Form2
            .submit($scope.fields)
            .then(function (data) {
                $scope.pristineFields();
                $scope.history = data.history;
                Dialog.alert('Success', data.message, 'OK', 3200, 'form2');
            }, function (data) {
                Dialog.alert('Error', data.message, 'OK', -1, 'form2');
            }).then(function () {
                Loader.hide();
            });
    };

    /** Customer features */
    $scope.historyIsEnabled = function () {
        return Customer.isLoggedIn() && $scope.enableHistory;
    };

    $scope.populate = function (payload) {
        $scope.originalFields = payload.formFields;
        $scope.pageTitle = payload.pageTitle;
        $scope.history = payload.history;
        $scope.cardDesign = payload.cardDesign;
        $scope.enableHistory = payload.enableHistory;
        $scope.pristineFields();
    };

    $scope.loadContent = function () {
        $scope.isLoading = true;

        Form2
            .find()
            .then($scope.populate
            , function (error) {
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
                .then($scope.populate
                , function (error) {
                    Dialog.alert('Error', error.message, 'OK');
                }).then(function () {
                    $scope.isLoading = false;
                });
        };

        $window.overview['form_v2'] = $scope.reloadOverview;
        $scope.reloadOverview();
    }
});

