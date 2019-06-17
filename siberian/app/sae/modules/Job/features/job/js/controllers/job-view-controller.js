/**
 * Job module
 *
 * @author Xtraball SAS <dev@xtraball.com>
 * @version 4.16.11
 */
angular
.module("starter")
.controller('JobViewController', function (SocialSharing, Modal, $location, $rootScope, $scope, $state, $stateParams,
                                           $timeout, $translate, $window, Application, Customer, Dialog, Job, Loader,
                                           Picture) {

    angular.extend($scope, {
        is_loading: true,
        value_id: $stateParams.value_id,
        is_admin: false,
        modal: null,
        manage_modal: null,
        social_sharing_active: false,
        settings: Job.settings,
        form: {
            fullname: "",
            email: "",
            phone: "",
            address: "",
            message: "",
            resumes: []
        },
        cardDesign: Job.settings.cardDesign
    });

    Job.setValueId($stateParams.value_id);

    $scope.loadContent = function () {

        Job.find($stateParams.place_id)
        .then(function (data) {

            $scope.place_edit = $scope.place = data.place;
            $scope.page_title = data.page_title;
            $scope.is_admin = data.is_admin;
            $scope.socialSharing = (data.socialSharing && IS_NATIVE_APP);

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
                $window.open("mailto:" + $scope.place.email + "?subject=" + $translate.instant("New contact for: ", "job") + $scope.place.title, "_self");
                break;
        }
    };

    $scope.contactModal = function () {
        // pre-fill form!
        if (Customer.isLoggedIn()) {
            $scope.form.fullname = Customer.customer.firstname + " " + Customer.customer.lastname;
            $scope.form.email = Customer.customer.email;
        }

        Modal.fromTemplateUrl("features/job/assets/templates/l1/contact.html", {
            scope: $scope
        }).then(function (modal) {
            $scope.modal = modal;
            $scope.modal.show();
        });
    };

    $scope.takePicture = function () {
        Picture
        .takePicture()
        .then(function (success) {
            $scope.form.resumes.push(success.image);
        });
    };

    $scope.removePicture = function (index) {
        $scope.form.resumes.splice(index, 1);
    };

    $scope.clearForm = function () {
        /** Clear form */
        $scope.form.fullname = "";
        $scope.form.email = "";
        $scope.form.phone = "";
        $scope.form.address = "";
        $scope.form.message = "";
        $scope.form.resumes = [];
    };

    $scope.closeContactModal = function () {
        $scope.clearForm();

        $scope.modal.hide();
    };

    $scope.submitContact = function () {
        if ($rootScope.isNotAvailableOffline()) {
            return;
        }

        if ($scope.form.fullname === "" ||
            $scope.form.email === "" ||
            $scope.form.message === "") {
            Dialog.alert("Form error", "Name, e-mail & message are required !", "OK", -1, "job");
            return;
        }

        Loader.show();

        var options = angular.extend($scope.form, {
            place_id: $stateParams.place_id,
            value_id: $scope.value_id
        });

        Job.contactForm(options)
        .then(function (data) {
            Dialog.alert("Thank you", data.message, "OK", -1, "job")
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
            Dialog.alert("Form error", "All fields are required !", "OK", -1, "job");
            return;
        }

        Loader.show();

        var options = angular.extend($scope.place_edit, {
            place_id: $stateParams.place_id,
            value_id: $scope.value_id
        });

        Job
        .editPlace(options)
        .then(function (data) {
            Dialog.alert("Thank you", data.message, "OK", -1, "job")
            .then(function () {
                $scope.closeManageModal();
            });

            $scope.loadContent();
        }, function (data) {
            Dialog.alert("Error", data.message, "OK", -1, "job");
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


    $scope.displayContact = function (place) {
        return (place.display_contact !== "hidden" &&
                place.email.length > 0);
    };

    $scope.displayContactForm = function (place) {
        return (place.display_contact === "contactform" ||
                place.display_contact === "both");
    };

    $scope.displayEmail = function (place) {
        return (place.display_contact === "email" ||
                place.display_contact === "both") &&
                place.email.length > 0;
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
        SocialSharing.share($translate.instant("job: $1", "job").replace("$1", $scope.place.title));
    };

    $scope.loadContent();

});