/**
 * Job module
 *
 * @author Xtraball SAS <dev@xtraball.com>
 * @version 4.16.11
 */
angular.module("starter").controller('CompanyViewController', function (Modal,
                                                 $ionicScrollDelegate, $rootScope, $scope, $state, $stateParams,
                                                 $timeout, $translate, $window, Application, Dialog, Job, Loader) {

    angular.extend($scope, {
        is_loading: true,
        value_id: $stateParams.value_id,
        is_admin: false,
        manage_modal: null,
        create_modal: null,
        place_create: {
            name: null,
            description: null,
            location: null,
            email: null,
            contract_type: null,
            income_from: null,
            income_to: null,
            category_id: null,
            keywords: null,
            is_active: null
        },
        social_sharing_active: false,
        cardDesign: Job.settings.cardDesign
    });

    Job.setValueId($stateParams.value_id);

    $scope.loadContent = function () {

        Job
        .findCompany($stateParams.company_id)
        .then(function (data) {

            $scope.company_edit = $scope.company = data.company;
            $scope.categories = data.categories;
            $scope.page_title = data.page_title;
            $scope.is_admin = data.is_admin;

            $scope.socialSharing = (data.socialSharing && IS_NATIVE_APP);

        }).then(function () {
            $scope.is_loading = false;
            $ionicScrollDelegate.scrollTop();
        });
    };

    $scope.submitManage = function () {
        if ($rootScope.isNotAvailableOffline()) {
            return;
        }

        if ($scope.company_edit.title === "" ||
            $scope.company_edit.location === "" ||
            $scope.company_edit.email === "") {

            Dialog.alert("Form error", "Required fields are missing!", "OK", -1);

            return;
        }

        var options = angular.extend($scope.company_edit, {
            company_id: $stateParams.company_id,
            value_id: $scope.value_id
        });

        Job.editCompany(options)
        .then(function (data) {
            Dialog.alert("Thank you", data.message, "OK", -1)
            .then(function () {
                $scope.closeManageModal();
                $scope.loadContent();
            });

        }, function (data) {
            Dialog.alert("Form error", data.message, "OK", -1);

        }).then(function () {

        });
    };

    $scope.manageModal = function () {
        if ($rootScope.isNotAvailableOffline()) {
            return;
        }

        Modal.fromTemplateUrl("features/job/assets/templates/l1/manage-company.html", {
            scope: $scope
        }).then(function (modal) {
            $scope.manage_modal = modal;
            $scope.manage_modal.show();
        });
    };

    $scope.closeManageModal = function () {
        $scope.manage_modal.hide();
    };

    $scope.submitCreate = function () {
        if ($rootScope.isNotAvailableOffline()) {
            return;
        }

        var options = angular.extend($scope.place_create, {
            company_id: $stateParams.company_id,
            value_id: $scope.value_id
        });

        Loader.show();

        Job.createPlace(options)
        .then(function (data) {
            Dialog.alert("Thank you", data.message, "OK", -1)
            .then(function () {
                $scope.closeCreateModal();
                $scope.loadContent();
            });


        }, function (data) {
            Dialog.alert("Form error", data.message, "OK", -1);

        }).then(function () {
            Loader.hide();
        });
    };

    $scope.createModal = function () {
        Modal.fromTemplateUrl("features/job/assets/templates/l1/create-place.html", {
            scope: $scope
        }).then(function (modal) {
            $scope.create_modal = modal;
            $scope.create_modal.show();
        });
    };

    $scope.closeCreateModal = function () {
        $scope.place_create = {
            name: null,
            description: null,
            location: null,
            email: null,
            contract_type: null,
            income_from: null,
            income_to: null,
            category_id: null,
            keywords: null,
            is_active: null
        };

        $scope.create_modal.hide();
    };

    $scope.showPlace = function (place_id) {
        $state.go("job-view", {
            value_id: $scope.value_id,
            place_id: place_id
        });
    };

    $scope.goHome = function (item) {
        $state.go("job-list", {
            value_id: $scope.value_id
        });
    };

    $scope.loadContent();

});
