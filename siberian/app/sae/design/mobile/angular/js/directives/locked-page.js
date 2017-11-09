App.directive('lockedPage', function ($window, $rootScope, $location, $timeout, Application, Customer, LayoutService, Padlock) {
    return {
        restrict: 'E',
        template: '<div ng-include src="\'locked_page.html\'"></div>',
        replace: true,
        controller: function($scope, Customer) {

            Padlock.findUnlockTypes().success(function(data) {
                $scope.unlock_by_account_type = data.unlock_by_account;
                $scope.unlock_by_qrcode_type = data.unlock_by_qrcode;
            });

            $window.stored_data = JSON.stringify(["uc"]);
            Application.getStoredData({data: ["uc"]}, function(json) {

                var data = {};
                try {
                    data = JSON.parse(json);
                } catch(e) {
                    data = {};
                }

                if(data.uc) {
                    Padlock.unlock_by_qrcode = true;
                    $rootScope.$broadcast("application_state_changed");
                }

            }, function() {});

            $scope.$on("ready_for_code_scan", function() { $scope.handle_code_scan = true });

            $scope.openScanCamera = function() {

                $window.scan_camera_protocols = JSON.stringify(["sendback:"]);
                Application.openScanCamera({protocols: ["sendback:"]}, function(qrcode) {

                    $scope.is_loading = true;

                    // load data
                    Padlock.unlockByQRCode(qrcode).success(function(data) {

                        $rootScope.$broadcast("application_state_changed");

                        $window.store_data = JSON.stringify({uc: qrcode});
                        Application.storeData({data: [qrcode] }, function() {}, function() {}); 

                    }).error(function (data) {

                        var message_text = "An error occurred while loading. Please, try again later.";
                        if(angular.isObject(data)) {
                            message_text = data.message;
                        }

                        $scope.message = new Message();
                        $scope.message.setText(message_text)
                            .isError(true)
                            .show()
                        ;

                    }).finally(function () {
                        $scope.is_loading = false;
                    });
                }, function() {});
            }

            $scope.logout = function() {
                Customer.logout();
            }
        }
    };
});