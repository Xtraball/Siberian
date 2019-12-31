angular.module('starter')
.controller('CabrideContextualMenuController', function ($scope, $rootScope, $state,
                                                         $ionicSideMenuDelegate,
                                                         $ionicHistory, Customer, Pages,
                                                         SB, $timeout, HomepageLayout, Cabride) {
    angular.extend($scope, {
        isOnline: false,
        customer: null,
        information: null,
        isLoggedIn: Customer.isLoggedIn(),
        isPassenger: Cabride.isPassenger,
        isDriver: Cabride.isDriver,
        cabride: null,
        taxiHeaderStyle: {
            backgroundImage: 'url("./features/cabride/assets/templates/images/008-background.png")'
        }
    });

    $scope.cs = function () {
        return Cabride.currencySymbol();
    };

    /**
     * @param identifier
     */
    $scope.loadPage = function (identifier) {
        $rootScope.$broadcast("sideMenu.close");

        switch (identifier) {
            case "my-rides":
                $state.go("cabride-my-rides");
                break;
            case "my-payments":
                $state.go("cabride-my-payments");
                break;
            case "cabride-home":
                $state.go("cabride-home");
                break;
            case "pending-requests":
                $state.go("cabride-pending-requests");
                break;
            case "accepted-requests":
                $state.go("cabride-accepted-requests");
                break;
            case "completed-rides":
                $state.go("cabride-completed-rides");
                break;
            case "cancelled":
                $state.go("cabride-cancelled");
                break;
            case "vehicle-information":
                $state.go("cabride-vehicle-information");
                break;
            case "payment-history":
                $state.go("cabride-payment-history");
                break;
        }
    };

    $scope.toggleStatus = function () {
        $scope.isOnline = !$scope.isOnline;

        // Broadcasting online/offline status
        $rootScope.$broadcast("cabride.isOnline", $scope.isOnline);
    };

    $scope.loginOrSignup = function () {
        Customer.loginModal();
    };

    $scope.customerName = function () {
        if ($scope.customer &&
            $scope.customer.firstname &&
            $scope.customer.lastname) {
            var fname = $scope.customer.firstname.toLowerCase();
            fname = fname.charAt(0).toUpperCase() + fname.slice(1);
            var lname = $scope.customer.lastname.toUpperCase();

            return fname + ' ' + lname
        }
        return '';
    };

    // On load!
    Customer
    .find()
    .then(function (customer) {
        $scope.customer = customer;
        $scope.customer.metadatas = _.isObject($scope.customer.metadatas)
            ? $scope.customer.metadatas
            : {};
        $scope.avatarUrl = Customer.getAvatarUrl($scope.customer.id);

        return customer;
    });

    $scope.cabride = _.find(Pages.getActivePages(), function (option) {
        return option.code === "cabride";
    });

    /**
     * Hooks
     */
    $scope.rebuildMenu = function () {
        $scope.isLoggedIn = Customer.isLoggedIn();
        if ($scope.isLoggedIn) {
            Customer
            .find()
            .then(function (customer) {
                $scope.customer = customer;
                $scope.customer.metadatas = _.isObject($scope.customer.metadatas)
                    ? $scope.customer.metadatas
                    : {};
                $scope.avatarUrl = Customer.getAvatarUrl($scope.customer.id);

                return customer;
            });
        } else {
            $scope.customer = null;
        }
    };

    $rootScope.$on(SB.EVENTS.AUTH.logoutSuccess, function () {
        $scope.rebuildMenu();
        $scope.isDriver = false;
        $scope.isPassenger = false;
    });

    $rootScope.$on(SB.EVENTS.AUTH.loginSuccess, function () {
        $scope.rebuildMenu();
    });

    $rootScope.$on(SB.EVENTS.AUTH.registerSuccess, function () {
        $scope.rebuildMenu();
    });

    $rootScope.$on(SB.EVENTS.AUTH.editSuccess, function () {
        $scope.rebuildMenu();
    });

    $rootScope.$on('cabride.aggregateInformation', function (event, data) {
        $timeout(function () {
            $scope.information = data.information;
        });
    });

    $rootScope.$on("cabride.setNavBackground", function (event, data) {
        $timeout(function () {
            $scope.taxiHeaderStyle.backgroundImage = data.navBackground;
        });
    });

    $rootScope.$on("cabride.setIsOnline", function (event, isOnline) {
        $scope.isOnline = isOnline;
    });

    $rootScope.$on("cabride.isPassenger", function () {
        $scope.isPassenger = true;
        $scope.isDriver = false;
        $scope.rebuildMenu();
    });

    $rootScope.$on("cabride.isDriver", function () {
        $scope.isPassenger = false;
        $scope.isDriver = true;
        $scope.rebuildMenu();
    });

    // Set nav background!
    $scope.taxiHeaderStyle.backgroundImage = "url('" + IMAGE_URL + Cabride.settings.navBackground + "')";

});
