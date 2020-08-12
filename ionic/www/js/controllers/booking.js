/**
 * Booking controller
 *
 * @author Xtraball SAS <dev@xtraball.com>
 * @version 4.16.11
 *
 */
angular.module("starter").controller("BookingController", function ($scope, $stateParams, $translate, Booking, Customer,
                                                                    Dialog, Loader) {
    angular.extend($scope, {
        isLoading: false,
        value_id: $stateParams.value_id,
        formData: {},
        people: [],
        cardDesign: false,
        settings: {
            design: "list",
            datepicker: "single",
            date_format: "MM/DD/YYYY HH:mm",
        },
        dateTime: {
            date: $translate.instant("Date & time", "booking"),
            checkIn: $translate.instant("Checkin", "booking"),
            checkOut: $translate.instant("Checkout", "booking")
        }
    });

    Booking.setValueId($stateParams.value_id);

    $scope.coverSrc = function (cover) {
        return IMAGE_URL + "images/application" + cover;
    };

    $scope.loadContent = function () {
        $scope.isLoading = true;

        Booking
        .findStores()
        .then(function (data) {
            $scope.populate(data);
        }).then(function () {
            $scope.isLoading = false;
        });
    };

    $scope.populate = function (data) {
        $scope.stores = data.stores;
        $scope.pageTitle = data.pageTitle;
        $scope.settings = data.settings;
        $scope.cardDesign = (data.settings.design === "card");

        if (Customer.isLoggedIn()) {
            $scope.formData.name = Customer.customer.firstname + " " + Customer.customer.lastname;
            $scope.formData.email = Customer.customer.email;
        }

        if ($scope.stores.length === 1) {
            $scope.formData.store = $scope.stores[0].id;
        }
    };

    $scope.clearForm = function () {
        $scope.formData = {};

        if ($scope.stores.length === 1) {
            $scope.formData.store = $scope.stores[0].id;
        }
    };

    $scope.submitForm = function () {
        var loaderText = $translate.instant("Sending request ...", "booking");
        Loader.show(loaderText);

        Booking
        .submitForm($scope.formData)
        .then(function (data) {
            Dialog.alert("Success", data.message, "OK", 2350, "booking");
            // Reset form on success!
            $scope.formData = {};
        }, function (data) {
            var message = $translate.instant("Please fill out the following fields", "booking");
            data.errorLines.forEach(function (item) {
                message += "<br />" + $translate.instant(item, "booking");
            });

            Dialog.alert("Error", message, "OK", -1, "booking");
        }).then(function () {
            Loader.hide();
        });
    };

    $scope.loadContent();
});
