/**
 * sb-padlock directive
 *
 * @author Xtraball SAS <dev@xtraball.com>
 * @version 4.18.11
 */
angular
    .module('starter')
    .directive('sbPadlock', function () {
        return {
            restrict: 'A',
            controller: function ($ionicHistory, Modal, $rootScope, $scope, $state, $stateParams,
                                  $timeout, $translate, $window, Application, Customer, Dialog, Padlock, SB, Codescan) {


                // Application is locked, we clear the navigation history!
                if (Application.is_locked) {
                    $ionicHistory.clearHistory();
                }

                $rootScope.$on(SB.EVENTS.AUTH.loginSuccess, function () {
                    $scope.isLoggedIn = true;
                });

                $rootScope.$on(SB.EVENTS.AUTH.logoutSuccess, function () {
                    $scope.isLoggedIn = false;
                });

                $scope.isLoggedIn = Customer.isLoggedIn();
                $scope.value_id = $stateParams.value_id;
                Padlock.value_id = $stateParams.value_id;

                Padlock
                    .findUnlockTypes()
                    .then(function (data) {
                        $scope.unlock_by_account_type = data.unlock_by_account;
                        $scope.unlock_by_qrcode_type = data.unlock_by_qrcode;
                    });

                $scope.padlockLogin = function () {
                    Customer.display_account_form = false;
                    Customer.loginModal($scope, function () {
                        $rootScope.$broadcast(SB.EVENTS.PADLOCK.unlockFeatures);

                        if (Application.is_locked) {
                            $ionicHistory.clearHistory();
                            $state.go('home');
                        } else {
                            $ionicHistory.goBack();
                        }
                    });
                };

                $scope.padlockSignup = function () {
                    Customer.display_account_form = true;
                    Customer.loginModal($scope);
                };

                $scope.padlockLogout = function () {
                    $scope.isLoading = true;
                    Customer
                        .logout()
                        .then(function () {
                            $scope.isLoading = false;
                        });
                };

                $scope.openScanCamera = function () {
                    Codescan.scanPadlock();
                };
            }
        };
    });
