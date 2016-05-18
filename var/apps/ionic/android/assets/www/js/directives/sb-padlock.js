App.directive("sbPadlock", function(Application) {
    return {
        restrict: "A",
        controller: function($cordovaBarcodeScanner, $ionicHistory, $ionicModal, $rootScope, $scope, $stateParams, $timeout, $translate, $window, Application, Customer, Dialog, Padlock, AUTH_EVENTS, PADLOCK_EVENTS) {

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

            /**$scope.login = function() {

                $scope.login = function($scope) { Customer.loginModal($scope) };

                /** @TODO Am I really used somewhere ? I think my !Application.is_locked
                 *  becomes true WAY TOO LATE after I'm testing it
                 *
                $scope.$on('modal.hidden', function() {
                    sbLog("sbPadlock, modal.hidden STEP1, yeah");
                    if($scope.is_logged_in && Customer.can_access_locked_features && !Application.is_locked) {
                        $ionicHistory.goBack();
                        sbLog("sbPadlock, modal.hidden STEP2, but never shown", !Application.is_locked);
                    }

                    $timeout(function() { sbLog("sbPadlock, modal.hidden STEP3, with HUGE timeout to see", !Application.is_locked); }, 5000);
                });

            };*/

            $scope.logout = function() {
                $scope.is_loading = true;
                Customer.logout().finally(function() {
                    $scope.is_loading = false;
                });
            };

            $scope.openScanCamera = function() {
                $scope.scan_protocols = ["sendback:"];

                $cordovaBarcodeScanner.scan().then(function(barcodeData) {

                    if(!barcodeData.cancelled && barcodeData.text != "") {

                        $timeout(function () {
                            for (var i = 0; i < $scope.scan_protocols.length; i++) {
                                if (barcodeData.text.toLowerCase().indexOf($scope.scan_protocols[i]) == 0) {
                                    $scope.is_loading = true;

                                    var qrcode = barcodeData.text.replace($scope.scan_protocols[i], "");

                                    Padlock.unlockByQRCode(qrcode).success(function() {

                                        Padlock.unlock_by_qrcode = true;

                                        $scope.is_loading = false;

                                        $window.localStorage.setItem('sb-uc', qrcode);

                                        $rootScope.$broadcast(PADLOCK_EVENTS.unlockFeatures);

                                        $ionicHistory.goBack();

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
                    Dialog.alert($translate.instant('Error'), $translate.instant('An error occurred while reading the code.'), $translate.instant("OK"));
                });
            };

        }
    };
});