/**
 * Job module
 *
 * @author Xtraball SAS <dev@xtraball.com>
 * @version 4.16.11
 */
angular.module("starter").controller('JobViewController', function (SocialSharing, Modal, $location, $rootScope, $scope, $state,
                                             $stateParams, $timeout, $translate, $window, Application,
                                             Dialog, Job, Loader) {

    angular.extend($scope, {
        is_loading: true,
        value_id: $stateParams.value_id,
        is_admin: false,
        modal: null,
        manage_modal: null,
        social_sharing_active: false,
        options: Job.options,
        form: {
            fullname: "",
            email: "",
            phone: "",
            address: "",
            message: ""
        },
        card_design: false
    });

    Job.setValueId($stateParams.value_id);

    $scope.loadContent = function () {

        Job.find($stateParams.place_id)
        .then(function (data) {

            $scope.place_edit = $scope.place = data.place;
            $scope.page_title = data.page_title;
            $scope.is_admin = data.is_admin;

            $scope.social_sharing_active = !!(data.social_sharing_is_active == 1 && !Application.is_webview);

        }).then(function () {
            $scope.is_loading = false;
        });
    };

    $scope.contactAction = function () {
        switch ($scope.place.display_contact) {
            case "contactform":
            case "both":
                $scope.contactModal();
                break;
            case "email":
                $window.open("mailto:" + $scope.place.email + "?subject=" + $translate.instant("New contact for: ") + $scope.place.title, "_self");
                break;
        }
    };

    $scope.contactModal = function () {
        if ($rootScope.isNotAvailableOffline()) {
            return;
        }

        Modal.fromTemplateUrl('features/job/assets/templates/l1/contact.html', {
            scope: $scope
        }).then(function (modal) {
            $scope.modal = modal;
            $scope.modal.show();
        });
    };

    $scope.closeContactModal = function () {
        /** Clear form */
        $scope.form.fullname = "";
        $scope.form.email = "";
        $scope.form.phone = "";
        $scope.form.address = "";
        $scope.form.message = "";

        $scope.modal.hide();
    };

    $scope.submitContact = function () {
        if ($rootScope.isNotAvailableOffline()) {
            return;
        }

        if ($scope.form.fullname === "" ||
            $scope.form.email === "" ||
            $scope.form.message === "") {
            Dialog.alert("Form error", "Fullname, E-mail & Message fields are required !", "OK", -1);
            return;
        }

        Loader.show();

        var options = angular.extend($scope.form, {
            place_id: $stateParams.place_id,
            value_id: $scope.value_id
        });

        Job.contactForm(options)
        .then(function (data) {
            Dialog.alert("Thank you", data.message, "OK", -1)
            .then(function () {
                $scope.closeContactModal();
            });
        }).then(function () {
            Loader.hide();
        });
    };

    $scope.submitManage = function () {
        if ($rootScope.isNotAvailableOffline()) {
            return;
        }

        if ($scope.place_edit.title === "" ||
            $scope.place_edit.subtitle === "") {
            Dialog.alert("Form error", "All fields are required !", "OK", -1);
            return;
        }

        Loader.show();

        var options = angular.extend($scope.place_edit, {
            place_id: $stateParams.place_id,
            value_id: $scope.value_id
        });

        Job.editPlace(options)
        .then(function (data) {
            Dialog.alert("Thank you", data.message, "OK", -1)
            .then(function () {
                $scope.closeManageModal();
            });

            $scope.loadContent();
        }, function (data) {
            Dialog.alert("Error", data.message, "OK", -1);
        }).then(function () {
            Loader.hide();
        });
    };

    $scope.manageModal = function () {
        if ($rootScope.isNotAvailableOffline()) {
            return;
        }

        Modal.fromTemplateUrl("features/job/assets/templates/l1/manage-place.html", {
            scope: $scope
        }).then(function (modal) {
            $scope.manage_modal = modal;
            $scope.manage_modal.show();
        });
    };

    $scope.closeManageModal = function () {
        $scope.manage_modal.hide();
    };

    $scope.showCompany = function (company_id) {
        $state.go("company-view", {
            value_id: $scope.value_id,
            company_id: company_id
        });
    };

    $scope.goHome = function (item) {
        $state.go("job-list", {
            value_id: $scope.value_id
        });
    };

    $scope.share = function () {
        SocialSharing.share($translate.instant("job: $1").replace("$1", $scope.place.title));
    };

    $scope.loadContent();

});