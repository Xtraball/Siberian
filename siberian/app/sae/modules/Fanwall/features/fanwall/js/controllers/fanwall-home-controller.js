/**
 * Module FanWall
 *
 * @author Xtraball SAS <dev@xtraball.com>
 * @version 4.16.13
 */
angular
.module("starter")
.controller("FanwallHomeController", function ($rootScope, $scope, $state, $stateParams, $translate, Customer, Location,
                                               Fanwall) {
    angular.extend($scope, {
        settingsIsLoaded: false,
        value_id: $stateParams.value_id,
        collection: [],
        pageTitle: $translate.instant("Fan Wall", "fanwall"),
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

    $scope.showTab = function (tabName) {
        $scope.currentTab = tabName;
    };

    $scope.classTab = function (key) {
        if ($scope.currentTab === key) {
            return ["fw-icon-selected", "icon-active-custom"];
        }
        return ["icon-custom"];
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
        }
    };

    $scope.refresh = function () {
        $rootScope.$broadcast("fanwall.refresh");
    };

    // Modal create post!
    $scope.createPost = function () {
        if (!Customer.isLoggedIn()) {
            return Customer.loginModal();
        }
    };

    Fanwall
    .loadSettings()
    .then(function (payload) {
        Fanwall.settings = angular.copy(payload.settings);
        Fanwall.cardDesign = Fanwall.settings.cardDesign;

        $scope.settingsIsLoaded = true;
    });
});