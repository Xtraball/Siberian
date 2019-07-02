/**
 * Module FanWall
 *
 * @author Xtraball SAS <dev@xtraball.com>
 * @version 4.17.0
 */
angular
.module("starter")
.controller("FanwallNewController", function ($scope, $rootScope, $state, $stateParams, $translate, Customer, Fanwall, FanwallPost,
                                              Dialog, Picture, Loader, Location, GoogleMaps) {

    angular.extend($scope, {
        pageTitle: $translate.instant("Create a post", "fanwall"),
        form: {
            text: "",
            picture: "",
            date: null,
            location: {
                latitude: 0,
                longitude: 0,
                locationShort: ""
            }
        },
        fetchingLocation: false,
        shortLocation: ""
    });

    FanwallPost.setValueId($stateParams.value_id);

    $scope.getCardDesign = function () {
        return Fanwall.cardDesign;
    };

    $scope.getSettings = function () {
        return Fanwall.settings;
    };

    $scope.locationIsDisabled = function () {
        return !Location.isEnabled;
    };

    $scope.requestLocation = function () {
        Dialog
        .confirm(
            "Error",
            "We were unable to request your location.<br />Please check that the application is allowed to use the GPS and that your device GPS is on.",
            ["TRY AGAIN", "DISMISS"],
            -1,
            "location")
        .then(function (success) {
            if (success) {
                Location.isEnabled = true;
                Loader.show();
                Location
                .getLocation({timeout: 30000, enableHighAccuracy: false}, true)
                .then(function (payload) {
                    // GPS is OK!!
                    Loader.hide();
                    Dialog.alert("Success", "We finally got you location", "OK", 2350, "fanwall");
                }, function () {
                    Loader.hide();
                    Dialog
                    .alert(
                        "Error",
                        "We were unable to request your location.<br />Please check that the application is allowed to use the GPS and that your device GPS is on.",
                        "OK",
                        3700,
                       "location"
                    );
                });
            }
        });
    };

    $scope.myAvatar = function () {
        // Empty image
        if (Customer.customer &&
            Customer.customer.image &&
            Customer.customer.image.length > 0) {
            return IMAGE_URL + "images/customer" + Customer.customer.image;
        }
        return "./features/fanwall2/assets/templates/images/customer-placeholder.png";
    };

    $scope.picturePreview = function () {
        // Empty image
        if ($scope.form.picture.indexOf("/") === 0) {
            return IMAGE_URL + "images/application" + $scope.form.picture;
        }
        return $scope.form.picture;
    };

    $scope.myName = function () {
        return Customer.customer.firstname + " " + Customer.customer.lastname;
    };

    $scope.takePicture = function () {
        return Picture
            .takePicture()
            .then(function (success) {
                $scope.form.picture = success.image;
            });
    };

    $scope.removePicture = function () {
        $scope.form.picture = "";
    };

    $scope.clearForm = function () {
        $scope.form = {
            text: "",
            picture: "",
            location: {
                latitude: 0,
                longitude: 0,
                locationShort: ""
            }
        };
    };

    $scope.canSend = function () {
        return ($scope.form.text.length > 0 || $scope.form.picture.length > 0);
    };

    $scope.sendPost = function () {
        var postId = ($scope.post !== undefined) ? $scope.post.id : null;

        if ($scope.fetchingLocation) {
            Dialog.alert("Wait", "Please wait while we are fetching your location.", "OK", 2350, "fanwall");
            return false;
        }

        if (!$scope.canSend()) {
            Dialog.alert("Error", "You must send at least a message or a picture.", "OK", -1, "fanwall");
            return false;
        }

        Loader.show();

        // Append now
        $scope.form.date = Math.round(Date.now() / 1000);

        return FanwallPost
            .sendPost(postId, $scope.form)
            .then(function (payload) {
                Loader.hide();
                $rootScope.$broadcast("fanwall.refresh");
                $scope.close();
            }, function (payload) {
                // Show error!
                Loader.hide();
                Dialog.alert("Error", payload.message, "OK", -1, "fanwall");
            });
    };

    if ($scope.post !== undefined) {
        $scope.pageTitle = "Edit post";
        $scope.form.text = $scope.post.text;
        if ($scope.post.image.length > 0) {
            $scope.form.picture = $scope.post.image;
        }
    }

    if (!$scope.locationIsDisabled()) {
        $scope.fetchingLocation = true;
        Location
        .getLocation({timeout: 10000, enableHighAccuracy: false}, true)
        .then(function (position) {
            $scope.form.location.latitude = position.coords.latitude;
            $scope.form.location.longitude = position.coords.longitude;

            GoogleMaps
                .reverseGeocode(position.coords)
                .then(function (results) {
                    if (results.length > 0) {
                        var place = results[0];

                        try {
                            $scope.shortLocation = _.find(place.address_components, function (item) {
                                return item.types.indexOf("locality") >= 0;
                            }).long_name;
                        } catch (e) {
                            $scope.shortLocation = place.formatted_address;
                        }

                        $scope.form.location.locationShort = $scope.shortLocation;
                        $scope.fetchingLocation = false;
                    }
                }, function () {
                    $scope.fetchingLocation = false;
                    $scope.shortLocation = Math.truncate(position.coords.latitude, 4) + ", " + Math.truncate(position.coords.longitude, 4);
                    $scope.form.location.locationShort = "unknown";
                });
        }, function () {
            $scope.fetchingLocation = false;
            $scope.form.location.latitude = 0;
            $scope.form.location.longitude = 0;
        });
    } else {
        $scope.fetchingLocation = false;
    }
});
