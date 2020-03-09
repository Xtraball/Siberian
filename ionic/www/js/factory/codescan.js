/**
 * Codescan
 *
 * @author Xtraball SAS
 */
angular
    .module('starter')
    .factory('Codescan', function ($cordovaBarcodeScanner, $cordovaClipboard, $ionicHistory, LinkService,
                                   $state, $window, $rootScope, Dialog, Discount, SB, Padlock, Customer) {
    var factory = {
        scanProtocols: [
            'tel:',
            'http:',
            'https:',
            'geo:',
            'sms:',
            'smsto:',
            'mailto:',
            'ctc:'
        ],
        is_protocol_found: false
    };

    factory.scanDiscount = function() {
        if (!Customer.isLoggedIn()) {
            Dialog
                .confirm('Login required', 'You must be logged in to unlock a coupon.', ['LOGIN OR SIGNUP', 'DONE'], 'text-center', 'codescan')
                .then(function (result) {
                    if (result) {
                        Customer.loginModal($rootScope, factory.scanDiscount, factory.scanDiscount, factory.scanDiscount);
                    }
                });
            return;
        }

        $cordovaBarcodeScanner
            .scan()
            .then(function (scannedData) {
                var qrCode = scannedData.text.replace('sendback:', '');
                factory.checkDiscount(qrCode);
            });
    };

    factory.checkDiscount = function (qrCode) {
        // If whe have no protocol, search for any qr code promotional
        Discount
            .isQrCode(qrCode)
            .then(function (payload) {
                if (!Customer.isLoggedIn()) {
                    Dialog
                        .confirm('Login required', 'You must be logged in to unlock a coupon.', ['LOGIN OR SIGNUP', 'DONE'], 'text-center', 'codescan')
                        .then(function (result) {
                            if (result) {
                                Customer.loginModal($rootScope, factory.checkDiscount, factory.checkDiscount, factory.checkDiscount);
                            }
                        });
                } else {
                    Discount.setValueId(payload.value_id);
                    Discount
                        .unlockByQRCode(payload.qr_code)
                        .then(function (unlockPayload) {
                            Dialog
                                .alert('Thanks', 'You have unlocked a coupon.', 'OK', 2350, 'codescan')
                                .then(function () {
                                    $state.go('discount-view', {
                                        value_id: payload.value_id,
                                        promotion_id: payload.promotion_id
                                    });
                                });
                        }, function (data) {
                            // Nope! we just skip by now!
                        });
                }
            }, function (error) {
                // Nope! we just skip by now!
            });

    };

    factory.scanPadlock = function() {
        $cordovaBarcodeScanner
            .scan()
            .then(function (scannedData) {
                var qrCode = scannedData.text.replace('sendback:', '');
                factory.checkPadlock(qrCode);
            });
    };

    factory.checkPadlock = function (qrCode) {
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
                                if (Application.is_locked) {
                                    $ionicHistory.clearHistory();
                                    $state.go('home');
                                } else {
                                    $ionicHistory.goBack();
                                }
                            });

                    }, function (data) {
                        // Nope! we just skip by now!
                    });
            }, function (error) {
                // Nope! we just skip by now!
            });

    };

    factory.copyToClipboard = function (text) {
        Dialog
            .confirm('Result', text, ['COPY', 'DONE'], 'text-center', 'codescan')
            .then(function (result) {
                if (result) {
                    $cordovaClipboard
                        .copy(text)
                        .then(function () {}, function () {});
                }
            });
    };

    factory.scanGeneric = function () {
        $cordovaBarcodeScanner
            .scan()
            .then(function (scannedData) {
                if (!scannedData.cancelled && (scannedData.text !== '')) {

                    var scannedText = scannedData.text.toLowerCase().split(':')[0] + ':';

                    // The protocol exists!
                    if (factory.scanProtocols.indexOf(scannedText) !== -1) {

                        var contentUrl = scannedData.text;

                        // Handling geo with the navigator plugin
                        if (scannedText === 'geo:') {
                            var coords = scannedText.replace('geo:', '').split(',');
                            Navigator.navigate({lat: coords[0], lng: coords[1]});

                            return;
                        }

                        // Special case for Apple
                        if (DEVICE_TYPE === SB.DEVICE.TYPE_IOS) {
                            if (factory.scanProtocols[i] === 'smsto:') {
                                contentUrl = contentUrl
                                    .replace(/(smsto):/i, 'sms:')
                                    .replace(/([0-9]):(.*)/, "$1");
                            }
                        }

                        LinkService.open(contentUrl, {global: {browser: 'external_browser'}});
                    } else {
                        var text = scannedData.text;
                        var qrCode = text.replace('sendback:', '');

                        // Check discount
                        factory.checkDiscount(qrCode);

                        // Or it's a padlock QR Code
                        factory.checkPadlock(qrCode);

                        // Or we just copy the text to the clipboard
                        factory.copyToClipboard(text);
                    }
                }

            }, function (error) {
                Dialog
                    .alert('Error', 'An error occurred while reading the code.', 'OK', -1, 'codescan')
                    .then(function () {
                        $ionicHistory.goBack();
                    });
            });
    };

    return factory;
});
