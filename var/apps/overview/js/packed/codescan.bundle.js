/*global
 App, angular, BASE_PATH, DOMAIN, DEVICE_TYPE
 */
angular.module("starter").controller('CodeScanController', function ($cordovaBarcodeScanner, $cordovaClipboard,
                                                                     $ionicHistory, $rootScope, $scope, $timeout,
                                                                     $translate, $window, Dialog, SB) {

    if($rootScope.isNotAvailableInOverview()) {
        $ionicHistory.goBack();
        return;
    }

    $scope.scan_protocols = ["tel:", "http:", "https:", "geo:", "smsto:", "mailto:", "ctc:"];
    $scope.is_protocol_found = false;

    $cordovaBarcodeScanner.scan()
        .then(function(barcodeData) {
            $ionicHistory.goBack();

            if(!barcodeData.cancelled && (barcodeData.text !== "")) {

                $timeout(function () {
                    for (var i = 0; i < $scope.scan_protocols.length; i++) {
                        if (barcodeData.text.toLowerCase().indexOf($scope.scan_protocols[i]) === 0) {

                            if ($scope.scan_protocols[i] === "http:" || $scope.scan_protocols[i] === "https:") {
                                $window.open(barcodeData.text, "_blank", "location=yes");
                            } else {
                                var content_url = barcodeData.text;

                                // SMSTO:
                                if ($scope.scan_protocols[i] === "smsto:" && (DEVICE_TYPE === SB.DEVICE.TYPE_IOS)) {
                                    content_url = url.replace(/(smsto|SMSTO):/, "sms:").replace(/([0-9]):(.*)/, "$1");
                                // GEO:
                                } else if ($scope.scan_protocols[i] === "geo:" && (DEVICE_TYPE === SB.DEVICE.TYPE_IOS)) {
                                    content_url = url.replace(/(geo|GEO):/, "https://maps.apple.com/?q=");
                                }

                                $window.open(content_url, "_blank", "location=no");
                            }

                            $scope.is_protocol_found = true;
                            break;

                        } else if($scope.scan_protocols[i] === "ctc:") {

                            var buttons = ["Copy", "Done"];
                            Dialog.confirm("Scan result", barcodeData.text, buttons, "text-center")
                                .then(function(result) {

                                    if(result) {
                                        $cordovaClipboard.copy(barcodeData.text).then(function () {}, function () {});
                                    }

                                });

                            $scope.is_protocol_found = true;
                            break;
                        }
                    }

                    if (!$scope.is_protocol_found) {
                        Dialog.alert("Scan result", barcodeData.text, "OK");
                    }
                });

            }

        }, function(error) {
            $ionicHistory.goBack();

            Dialog.alert("Error", "An error occurred while reading the code.", "OK", -1);
        });

});