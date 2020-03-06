/**
 * Code scan feature
 */
angular
    .module('starter')
    .controller('CodeScanController', function ($cordovaBarcodeScanner, $cordovaClipboard, $ionicHistory, LinkService,
                                                $rootScope, $scope, $timeout, $translate, $window, Dialog, SB) {

    if ($rootScope.isNotAvailableInOverview()) {
        $ionicHistory.goBack();
        return;
    }

    $scope.scan_protocols = [
        'tel:',
        'http:',
        'https:',
        'geo:',
        'smsto:',
        'mailto:',
        'ctc:'
    ];
    $scope.is_protocol_found = false;

    $cordovaBarcodeScanner
        .scan()
        .then(function (barcodeData) {
            $ionicHistory.goBack();

            if (!barcodeData.cancelled && (barcodeData.text !== '')) {

                $timeout(function () {
                    for (var i = 0; i < $scope.scan_protocols.length; i++) {
                        if (barcodeData.text.toLowerCase().indexOf($scope.scan_protocols[i]) === 0) {

                            if ($scope.scan_protocols[i] === 'http:' ||
                                $scope.scan_protocols[i] === 'https:') {

                                LinkService.open(barcodeData.text, {global: {browser: 'external_browser'}});
                            } else {
                                var contentUrl = barcodeData.text;

                                // Special case for Apple
                                if (DEVICE_TYPE === SB.DEVICE.TYPE_IOS) {
                                    if ($scope.scan_protocols[i] === 'smsto:') {
                                        contentUrl = contentUrl
                                            .replace(/(smsto):/i, 'sms:')
                                            .replace(/([0-9]):(.*)/, "$1");
                                        // GEO:
                                    } else if ($scope.scan_protocols[i] === 'geo:') {
                                        contentUrl = contentUrl
                                            .replace(/(geo):/i, 'https://maps.apple.com/?q=');
                                    }
                                }


                                LinkService.open(contentUrl, {global: {browser: 'external_browser'}});
                            }

                            $scope.is_protocol_found = true;
                            break;

                        } else if ($scope.scan_protocols[i] === 'ctc:') {

                            var buttons = ['Copy', 'Done'];
                            Dialog
                                .confirm('Scan result', barcodeData.text, buttons, 'text-center')
                                .then(function (result) {
                                    if (result) {
                                        $cordovaClipboard
                                            .copy(barcodeData.text)
                                            .then(function () {}, function () {});
                                    }
                                });

                            $scope.is_protocol_found = true;
                            break;
                        }
                    }

                    if (!$scope.is_protocol_found) {
                        Dialog.alert('Scan result', barcodeData.text, 'OK');
                    }
                });

            }

        }, function(error) {
            $ionicHistory.goBack();

            Dialog.alert("Error", "An error occurred while reading the code.", "OK", -1);
        });

});