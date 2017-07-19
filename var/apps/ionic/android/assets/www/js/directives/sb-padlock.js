/*global
 angular
 */

angular.module("starter").directive("sbPadlock", function(Application) {
    return {
        restrict: "A",
        controller: function($cordovaBarcodeScanner, $ionicHistory, Modal, $rootScope, $scope, $state, $stateParams,
                             $timeout, $translate, $window, Application, Customer, Dialog, Padlock, SB) {

            $scope.is_webview = Application.is_webview;

            if(Application.is_locked) {
                $ionicHistory.clearHistory();
            }

            $rootScope.$on(SB.EVENTS.AUTH.loginSuccess, function() {
                $scope.is_logged_in = true;
            });

            $rootScope.$on(SB.EVENTS.AUTH.logoutSuccess, function() {
                $scope.is_logged_in = false;
            });

            $scope.is_logged_in = Customer.isLoggedIn();
            $scope.value_id = Padlock.value_id = $stateParams.value_id;

            Padlock.findUnlockTypes()
                .then(function(data) {
                    $scope.unlock_by_account_type = data.unlock_by_account;
                    $scope.unlock_by_qrcode_type = data.unlock_by_qrcode;
                });

            $scope.padlock_login = function () {
                Customer.display_account_form = false;
                Customer.loginModal($scope, function() {
                    $rootScope.$broadcast(SB.EVENTS.PADLOCK.unlockFeatures);

                    if(Application.is_locked) {
                        $ionicHistory.clearHistory();
                        $state.go("home");
                    } else {
                        $ionicHistory.goBack();
                    }
                });
            };

            $scope.padlock_signup = function () {
                Customer.display_account_form = true;
                Customer.loginModal($scope);
            };

            $scope.padlock_logout = function() {
                $scope.is_loading = true;
                Customer.logout()
                    .then(function() {
                        $scope.is_loading = false;
                    });
            };

            $scope.openScanCamera = function() {
                $scope.scan_protocols = ["sendback:"];

                $cordovaBarcodeScanner.scan().then(function(barcodeData) {

                    if(!barcodeData.cancelled && (barcodeData.text !== "")) {

                        $timeout(function () {
                            for (var i = 0; i < $scope.scan_protocols.length; i++) {
                                if (barcodeData.text.toLowerCase().indexOf($scope.scan_protocols[i]) == 0) {
                                    $scope.is_loading = true;

                                    var qrcode = barcodeData.text.replace($scope.scan_protocols[i], "");

                                    Padlock.unlockByQRCode(qrcode)
                                        .then(function() {

                                            Padlock.unlocked_by_qrcode = true;

                                            $scope.is_loading = false;

                                            $window.localStorage.setItem('sb-uc', qrcode);

                                            $rootScope.$broadcast(SB.EVENTS.PADLOCK.unlockFeatures);

                                            if(Application.is_locked) {
                                                $ionicHistory.clearHistory();
                                                $state.go("home");
                                            } else {
                                                $ionicHistory.goBack();
                                            }

                                        }, function (data) {

                                            var message_text = $translate.instant('An error occurred while reading the code.');
                                            if(angular.isObject(data)) {
                                                message_text = data.message;
                                            }

                                            Dialog.alert("Error", message_text, "OK", -1);

                                        }).then(function () {
                                            $scope.is_loading = false;
                                        });

                                    break;
                                }
                            }

                        });

                    }

                }, function(error) {
                    Dialog.alert("Error", "An error occurred while reading the code.", "OK", -1);
                });
            };

        }
    };
});
