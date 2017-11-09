App.config(function($routeProvider) {

    $routeProvider.when(BASE_URL+"/padlock/mobile_view/index/value_id/:value_id", {
        controller: 'PadlockController',
        templateUrl: BASE_URL+"/padlock/mobile_view/template",
        code: "padlock"
    });

}).controller('PadlockController', function($window, $scope, $routeParams, Application, Message, Padlock, Customer, LayoutService) {

    if(Customer.can_access_locked_features) {
        $window.history.back();
        return;
    }

    $scope.handle_code_scan = Application.handle_code_scan;
    $scope.customerIsLoggedIn = Customer.isLoggedIn();
    $scope.value_id = Padlock.value_id = $routeParams.value_id;

    Padlock.find().success(function(data) {
        $scope.page_title = data.page_title;
    });

    Padlock.findUnlockTypes().success(function(data) {
        $scope.unlock_by_account_type = data.unlock_by_account;
        $scope.unlock_by_qrcode_type = data.unlock_by_qrcode;
    });

    $scope.openScanCamera = function() {

        $window.scan_camera_protocols = JSON.stringify(["sendback:"]);
        Application.openScanCamera({protocols: ["sendback:"]}, function(qrcode) {

            $scope.is_loading = true;

            // load data
            Padlock.unlockByQRCode(qrcode).success(function(data) {

                Padlock.unlock_by_qrcode = true;

                LayoutService.setNeedToBuildTheOptions(true);

                $window.store_data = JSON.stringify({uc: qrcode});
                Application.storeData({data: {uc: qrcode} }, function() {
                    $window.history.back();
                }, function() {

                }); 

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

});