/**
 * Module FanWall
 *
 * @author Xtraball SAS <dev@xtraball.com>
 * @version 4.17.0
 */
angular
.module("starter")
.controller("FanwallHomeController", function ($rootScope, $scope, $state, $stateParams, $translate, $timeout,
                                               $ionicSideMenuDelegate, Customer, Dialog,
                                               Location, Fanwall, FanwallUtils, GoogleMaps) {
    angular.extend($scope, {
        settingsIsLoaded: false,
        value_id: $stateParams.value_id,
        collection: [],
        pageTitle: "",
        hasMore: false,
        currentTab: "post"
    });

    Fanwall.setValueId($stateParams.value_id);

    $scope.getCardDesign = function () {
        return Fanwall.cardDesign;
    };

    $scope.getSettings = function () {
        return Fanwall.settings;
    };

    $scope.locationIsDisabled = function () {
        return !Location.isEnabled;
    };

    /**
     * Are we in a tab that requires the location
     * @returns {*|number}
     */
    $scope.locationTab = function () {
        return ["nearby", "map"].indexOf($scope.currentTab) !== -1;
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

    $scope.toggleDesign = function () {
        Fanwall.toggleDesign();
    };

    $scope.showTab = function (tabName) {
        $ionicSideMenuDelegate.canDragContent(tabName !== "map");

        var homeScope = $scope;

        if (tabName === "profile" &&
            !Customer.isLoggedIn()) {
            return Customer.loginModal(
                undefined,
                function () {
                    homeScope.showTab("profile");
                },
                undefined,
                function () {
                    homeScope.showTab("profile");
                }
            );
        }

        $scope.currentTab = tabName;
    };

    $scope.$on('$ionicView.afterLeave', function () {
        $ionicSideMenuDelegate.canDragContent(true);
    });

    $scope.classTab = function (key) {
        if ($scope.currentTab === key) {
            return ["fw-icon-selected", "icon-active-custom"];
        }
        return ["icon-custom"];
    };

    $scope.isEnabled = function (key) {
        var features = $scope.getSettings().features;

        return features[key];
    };

    $scope.displayProfile = function () {
        var features = $scope.getSettings().features;

        return features.enableUserLike ||
            features.enableUserPost ||
            features.enableUserComment;
    };

    $scope.displaySubHeader = function () {
        var features = $scope.getSettings().features;

        return features.enableNearby ||
               features.enableMap ||
               features.enableGallery ||
               features.enableUserPost;
    };

    $scope.displayIcon = function (key) {
        var icons = $scope.getSettings().icons;
        switch (key) {
            case "post":
                return (icons.post !== null) ?
                    "<img class=\"fw-icon-header icon-topics\" src=\"" + icons.post + "\" />" :
                    "<i class=\"icon ion-sb-fw-topics\"></i>";
            case "nearby":
                return (icons.nearby !== null) ?
                    "<img class=\"fw-icon-header icon-nearby\" src=\"" + icons.nearby + "\" />" :
                    "<i class=\"icon ion-sb-fw-nearby\"></i>";
            case "map":
                return (icons.map !== null) ?
                    "<img class=\"fw-icon-header icon-map\" src=\"" + icons.map + "\" />" :
                    "<i class=\"icon ion-sb-fw-map\"></i>";
            case "gallery":
                return (icons.gallery !== null) ?
                    "<img class=\"fw-icon-header icon-gallery\" src=\"" + icons.gallery + "\" />" :
                    "<i class=\"icon ion-sb-fw-gallery\"></i>";
            case "new":
                return (icons.new !== null) ?
                    "<img class=\"fw-icon-header icon-post\" src=\"" + icons.new + "\" />" :
                    "<i class=\"icon ion-sb-fw-post\"></i>";
            case "profile":
                return (icons.profile !== null) ?
                    "<img class=\"fw-icon-header icon-post\" src=\"" + icons.profile + "\" />" :
                    "<i class=\"icon ion-sb-fw-profile\"></i>";
        }
    };

    $scope.refresh = function () {
        $rootScope.$broadcast("fanwall.refresh");
    };

    // Modal create post!
    $scope.newPost = function () {
        if (!Customer.isLoggedIn()) {
            return Customer.loginModal();
        }

        return FanwallUtils.postModal();
    };

    GoogleMaps.init();

    Fanwall
    .loadSettings()
    .then(function (payload) {
        Fanwall.settings = angular.copy(payload.settings);
        Fanwall.cardDesign = Fanwall.settings.cardDesign;

        $scope.settingsIsLoaded = true;
    });

    $rootScope.$on("fanwall.pageTitle", function (event, payload) {
        $timeout(function () {
            $scope.pageTitle = payload.pageTitle;
        });
    });
});