angular.module('starter')
.controller('RequestDetailsController', function ($scope, $translate, Cabride, CabrideUtils, Dialog, Loader) {
    angular.extend($scope, {
        isLoading: false,
        enableCustomForm: $scope.request.customFormFields.length > 0,
        customFormFields: $scope.request.customFormFields,
        showPassengerName: Cabride.settings.showPassengerName,
        showPassengerPhone: Cabride.settings.showPassengerPhone,
        showPassengerPhoto: Cabride.settings.showPassengerPhoto
    });

    $scope.expiration = function (request) {
        return moment().add(parseInt(request.expires_in, 10), "seconds").fromNow();
    };

    $scope.calendar = function (timestampSeconds) {
        return moment(timestampSeconds * 1000).calendar();
    };

    $scope.distance = function (request) {
        var unit = Cabride.settings.distance_unit;
        var distance = request.distance / 1000;
        switch (unit) {
            case "mi":
                return Math.ceil(distance) + " mi";
            break;
            case "km":
            default:
                return Math.ceil(distance) + " Km";
            break;
        }
    };

    $scope.duration = function (request) {
        return CabrideUtils.toHHMM(request.duration);
    };

    $scope.source = function (source) {
        if ($scope.userType === "driver" && source === "driver") {
            return $translate.instant("You", "cabride");
        } else if ($scope.userType === "client" && source === "client") {
            return $translate.instant("You", "cabride");
        }

        switch (source) {
            case "cron":
                return $translate.instant("App", "cabride");
            case "admin":
                return $translate.instant("App manager", "cabride");
            case "driver":
                return $translate.instant("Driver", "cabride");
            case "client":
                return $translate.instant("Client", "cabride");
        }

        // Return unchanged if no match
        return source;
    };

    $scope.status = function (status) {
        // "pending", "accepted", "onway", "inprogress", "declined", "done", "aborted", "expired"
        switch (status) {
            case "pending":
                return $translate.instant("Created", "cabride");
            case "accepted":
                return $translate.instant("Accepted", "cabride");
            case "onway":
                return $translate.instant("Driver on way", "cabride");
            case "inprogress":
                return $translate.instant("Course in progress", "cabride");
            case "declined":
                return $translate.instant("Declined", "cabride");
            case "done":
                return $translate.instant("Course done", "cabride");
            case "aborted":
                return $translate.instant("Aborted", "cabride");
            case "expired":
                return $translate.instant("Expired", "cabride");
        }
    };

    $scope.rateCourse = function (request) {
        Cabride.rateCourseModal(request);
    };

    $scope.canRate = function (request) {
        var canRateStatus = ["done", "aborted"].indexOf(request.status) > -1;
        var isPassenger = ($scope.userType === "client");

        return (request.course_rating < 0) && canRateStatus && isPassenger;
    };

    $scope.canCancel = function (request) {
        var statuses = [];
        if ($scope.userType === "driver") {
            statuses = ["accepted", "onway", "inprogress", "aborted"];
        }
        if ($scope.userType === "client") {
            statuses = ["pending", "accepted", "onway", "inprogress", "aborted"];
        }

        return statuses.indexOf(request.status) >= 0;
    };

    $scope.imageCarPath = function (image) {
        if (image === "") {
            return IMAGE_URL + "app/local/modules/Cabride/resources/design/desktop/flat/images/car-icon.png";
        }
        return IMAGE_URL + "images/application" + image;
    };

    $scope.imageRoutePath = function (image) {
        if (image === "") {
            return IMAGE_URL + "app/local/modules/Cabride/resources/design/desktop/flat/images/no-route.jpg";
        }
        return IMAGE_URL + "images/application" + image;
    };

    $scope.customerPhoto = function (image) {
        if (!$scope.showPassengerPhoto || image === "" || image === null) {
            return "./features/cabride/assets/templates/images/015-no-photo.png";
        }
        return IMAGE_URL + "images/customer" + image;
    };

    $scope.creditCardBrand = function (brand) {
        if (!brand) {
            return "./features/cabride/assets/templates/images/014-cc.svg";
        }
        switch (brand.toLowerCase()) {
            case "visa":
                return "./features/cabride/assets/templates/images/011-cc-visa.svg";
            case "mastercard":
                return "./features/cabride/assets/templates/images/012-cc-mastercard.svg";
            case "american express":
                return "./features/cabride/assets/templates/images/013-cc-amex.png";
        }
        return "./features/cabride/assets/templates/images/014-cc.svg";
    };

    $scope.cancel = function (request) {
        Cabride.cancelModal(request, $scope.userType);
    };

    $scope.getIcon = function(target, value) {
        if (target === "course") {
            return ($scope.request.course_rating >= value) ? 'ion-android-star' : 'ion-android-star-outline';
        } else {
            return ($scope.request.driver_rating >= value) ? 'ion-android-star' : 'ion-android-star-outline';
        }
    };

    $scope.refresh = function () {
        Loader.show();

        Cabride
        .fetchRequest($scope.request.request_id)
        .then(function (payload) {
            $scope.request = payload.request;
        }).then(function () {
            Loader.hide();
        });
    };

    $scope.$on('cabride.updateRequest', function (event, request) {
        $scope.refresh();
    });
});