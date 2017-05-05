App.directive("sbPadlock", function(Application) {
    return {
        restrict: "A",
        controller: function($cordovaBarcodeScanner, $ionicHistory, $ionicModal, $rootScope, $scope, $state, $stateParams, $timeout, $translate, $window, Application, Customer, Dialog, Padlock, AUTH_EVENTS, PADLOCK_EVENTS) {

            $scope.is_webview = Application.is_webview;

            if(Application.is_locked) {
                $ionicHistory.clearHistory();
            }

            $rootScope.$on(AUTH_EVENTS.loginSuccess, function() {
                $scope.is_logged_in = true;
            });
            $rootScope.$on(AUTH_EVENTS.logoutSuccess, function() {
                $scope.is_logged_in = false;
            });

            $scope.is_logged_in = Customer.isLoggedIn();
            $scope.value_id = Padlock.value_id = $stateParams.value_id;

            Padlock.findUnlockTypes().success(function(data) {
                $scope.unlock_by_account_type = data.unlock_by_account;
                $scope.unlock_by_qrcode_type = data.unlock_by_qrcode;
            });

            $scope.login = function($scope) { 
            	$rootScope.loginFeature = true; 
            	Customer.loginModal($scope) ;
            };

            $scope.logout = function() {
                $scope.is_loading = true;
                Customer.logout().finally(function() {
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

                                    Padlock.unlockByQRCode(qrcode).success(function() {

                                        Padlock.unlocked_by_qrcode = true;

                                        $scope.is_loading = false;

                                        $window.localStorage.setItem('sb-uc', qrcode);

                                        $rootScope.$broadcast(PADLOCK_EVENTS.unlockFeatures);

                                        if(Application.is_locked) {
                                            $ionicHistory.clearHistory();
			                                      $state.go("home");
                                        } else {
                                            $ionicHistory.goBack();
                                        }

                                    }).error(function (data) {

                                        var message_text = $translate.instant('An error occurred while reading the code.');
                                        if(angular.isObject(data)) {
                                            message_text = data.message;
                                        }

                                        Dialog.alert($translate.instant('Error'), message_text, $translate.instant("OK"));

                                    }).finally(function () {
                                        $scope.is_loading = false;
                                    });

                                    break;
                                }
                            }

                        });

                    }

                }, function(error) {
                    Dialog.alert(
                        $translate.instant('Error'),
                        $translate.instant('An error occurred while reading the code.'),
                        $translate.instant("OK")
                    );
                });
            };

        }
    };
});
