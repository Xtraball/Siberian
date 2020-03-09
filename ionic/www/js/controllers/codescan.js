/**
 * Code scan feature
 */
angular
    .module('starter')
    .controller('CodeScanController', function ($cordovaBarcodeScanner, $cordovaClipboard, $ionicHistory, LinkService,
                                                $scope, $state, $window, $rootScope, Dialog, Discount, SB) {

        $scope.scanProtocols = [
            'tel:',
            'http:',
            'https:',
            'geo:',
            'sms:',
            'smsto:',
            'mailto:',
            'ctc:'
        ];
        $scope.is_protocol_found = false;

        $cordovaBarcodeScanner
            .scan()
            .then(function (scannedData) {
                $ionicHistory.goBack();

                if (!scannedData.cancelled && (scannedData.text !== '')) {

                    var scannedText = scannedData.text.toLowerCase().split(':')[0] + ':';

                    // The protocol exists!
                    if ($scope.scanProtocols.indexOf(scannedText) !== -1) {

                        var contentUrl = scannedData.text;

                        // Handling geo with the navigator plugin
                        if (scannedText === 'geo:') {
                            var coords = scannedText.replace('geo:', '').split(',');
                            Navigator.navigate({lat: coords[0], lng: coords[1]});

                            return;
                        }

                        // Special case for Apple
                        if (DEVICE_TYPE === SB.DEVICE.TYPE_IOS) {
                            if ($scope.scanProtocols[i] === 'smsto:') {
                                contentUrl = contentUrl
                                    .replace(/(smsto):/i, 'sms:')
                                    .replace(/([0-9]):(.*)/, "$1");
                            }
                        }

                        LinkService.open(contentUrl, {global: {browser: 'external_browser'}});
                    } else {
                        var qrCode = scannedData.text.replace('sendback:', '');

                        // If whe have no protocol, search for any qr code promotional
                        Discount
                            .isQrCode(qrCode)
                            .then(function (payload) {
                                Discount.setValueId(payload.value_id);
                                Discount
                                    .unlockByQRCode(payload.qr_code)
                                    .then(function (unlockPayload) {
                                        Dialog
                                            .alert('Thanks', 'You have successfully unlocked a coupon.', 'OK', 2350, 'codescan')
                                            .then(function () {
                                                $state.go('discount-view', {
                                                    value_id: payload.value_id,
                                                    promotion_id: payload.promotion_id
                                                });
                                            });
                                    }, function (data) {
                                        // Nope! we just skip by now!
                                    });
                            }, function (error) {
                                // Nope! we just skip by now!
                            });

                        // Or it's a padlock QR Code
                        Padlock
                            .isQrCode(qrCode)
                            .then(function (payload) {
                                Padlock
                                    .unlockByQRCode(payload.qr_code)
                                    .then(function (unlockPayload) {

                                        Padlock.unlocked_by_qrcode = true;
                                        $window.localStorage.setItem('sb-uc', payload.qr_code);
                                        $rootScope.$broadcast(SB.EVENTS.PADLOCK.unlockFeatures);

                                        Dialog
                                            .alert('Thanks', 'You have successfully unlocked features.', 'OK', 2350, 'codescan')
                                            .then(function () {
                                                $ionicHistory.clearHistory();
                                                $state.go('home');
                                            });

                                    }, function (data) {
                                        // Nope! we just skip by now!
                                    });
                            }, function (error) {
                                // Nope! we just skip by now!
                            });

                        // Or we just copy the text to the clipboard
                        var text = scannedData.text;
                        //$translate.instant('Text is copied to clipboard.', 'codescan');
                        Dialog
                            .confirm('Result', text, ['COPY', 'DONE'], 'text-center', 'codescan')
                            .then(function (result) {
                                if (result) {
                                    $cordovaClipboard
                                        .copy(scannedData.text)
                                        .then(function () {}, function () {});
                                }
                            });
                    }
                }

            }, function (error) {
                $ionicHistory.goBack();

                Dialog.alert('Error', 'An error occurred while reading the code.', 'OK', -1, 'codescan');
            });

    });