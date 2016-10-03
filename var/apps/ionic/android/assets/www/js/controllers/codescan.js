App.config(function ($stateProvider) {

    $stateProvider.state('codescan', {
        url: BASE_PATH + "/codescan/mobile_view/index/value_id/:value_id",
        controller: 'CodeScanController',
        templateUrl: 'templates/html/l1/loading.html',
        cache: false
    });

}).controller('CodeScanController', function ($cordovaBarcodeScanner, $cordovaClipboard, $ionicHistory, $scope, $timeout, $translate, $window, Dialog) {

    $scope.scan_protocols = ["tel:", "http:", "https:", "geo:", "smsto:", "mailto:", "ctc:"];
    $scope.is_protocol_found = false;

    $cordovaBarcodeScanner.scan().then(function(barcodeData) {
        $ionicHistory.goBack();

        if(!barcodeData.cancelled && barcodeData.text != "") {

            $timeout(function () {
                for (var i = 0; i < $scope.scan_protocols.length; i++) {
                    if (barcodeData.text.toLowerCase().indexOf($scope.scan_protocols[i]) == 0) {

                        if ($scope.scan_protocols[i] == "http:" || $scope.scan_protocols[i] == "https:") {
                            $window.open(barcodeData.text, "_blank", "location=yes");
                        } else {
                            var content_url = barcodeData.text;

                            // SMSTO:
                            if ($scope.scan_protocols[i] == "smsto:" && ionic.Platform.isIOS()) {
                                content_url = url.replace(/(smsto|SMSTO):/, "sms:").replace(/([0-9]):(.*)/, "$1");
                            // GEO:
                            } else if ($scope.scan_protocols[i] == "geo:" && ionic.Platform.isIOS()) {
                                content_url = url.replace(/(geo|GEO):/, "https://maps.apple.com/?q=");
                            }

                            $window.open(content_url, "_blank", "location=no");
                        }

                        $scope.is_protocol_found = true;
                        break;

                    } else if($scope.scan_protocols[i] == "ctc:") {

                        var buttons = [$translate.instant("Done"), $translate.instant("Copy")];
                        Dialog.confirm($translate.instant("Scan result"), barcodeData.text, buttons, "text-center").then(function(res) {

                            if(((typeof res  == "number") && res == 2) || ((typeof res  == "boolean") && res)) {
                                $cordovaClipboard.copy(barcodeData.text).then(function () {}, function () {});
                            }

                        });

                        $scope.is_protocol_found = true;
                        break;
                    }
                }

                if (!$scope.is_protocol_found) {
                    Dialog.alert($translate.instant("Scan result"), barcodeData.text, $translate.instant("OK"));
                }
            });

        }

    }, function(error) {
        $ionicHistory.goBack();

        Dialog.alert($translate.instant("Error"), $translate.instant("An error occurred while reading the code."), $translate.instant("OK"));
    });

});