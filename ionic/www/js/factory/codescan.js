/**
 * Codescan
 *
 * @author Xtraball SAS
 */
angular
    .module('starter')
    .factory('Codescan', function ($cordovaBarcodeScanner, $cordovaClipboard, $ionicHistory, LinkService,
                                   $state, $window, $rootScope, Application, Dialog, $injector, $ocLazyLoad, SB,
                                   Customer, Modal, $q) {
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
            ]
        };

        factory.checkCascade = function (qrCode, next) {
            if (next.length > 0) {
                var callback = next.shift();
                callback(qrCode, next);
            }
        };

        factory.scanDiscount = function () {
            if (!Customer.isLoggedIn()) {
                Dialog
                    .confirm('Login required', 'You must be logged in to unlock a coupon.', ['LOGIN OR SIGNUP', 'DONE'], 'text-center', 'codescan')
                    .then(function (result) {
                        if (result) {
                            Customer.loginModal($rootScope, factory.scanDiscount);
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

        factory.checkDiscount = function (qrCode, next) {
            $ocLazyLoad
                .load('./dist/packed/discount.bundle.min.js')
                .then(function () {
                    // If whe have no protocol, search for any qr code promotional
                    var DiscountFactory = $injector.get('Discount');

                    DiscountFactory
                        .isQrCode(qrCode)
                        .then(function (payload) {
                            DiscountFactory.setValueId(payload.value_id);
                            DiscountFactory
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
                                }, function (error) {
                                    Dialog.alert('Thanks', 'You have already unlocked this coupon.', 'OK', 2350, 'codescan');
                                });
                        }, function (error) {
                            if (error.isLoginError) {
                                factory.scanDiscount();
                            } else if (next.length > 0) {
                                var callback = next.shift();
                                callback(qrCode, next);
                            }
                        });

                });
        };

        factory.scanPadlock = function () {
            $cordovaBarcodeScanner
                .scan()
                .then(function (scannedData) {
                    var qrCode = scannedData.text.replace('sendback:', '');
                    factory.checkPadlock(qrCode);
                });
        };

        factory.checkPadlock = function (qrCode, next) {
            var PadlockFactory = $injector.get('Padlock');

            PadlockFactory
                .isQrCode(qrCode)
                .then(function (payload) {
                    PadlockFactory
                        .unlockByQRCode(payload.qr_code)
                        .then(function (unlockPayload) {

                            PadlockFactory.unlocked_by_qrcode = true;
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
                    if (next.length > 0) {
                        var callback = next.shift();
                        callback(qrCode, next);
                    }
                });

        };

        factory.scanPassword = function () {
            var defer = $q.defer();

            $cordovaBarcodeScanner
                .scan()
                .then(function (scannedData) {
                    if (scannedData.cancelled || scannedData.text === '') {
                        defer.reject(scannedData);
                    } else {
                        defer.resolve(scannedData.text);
                    }
                }, function (error) {
                    defer.reject(error);
                });

            return defer.promise;
        };

        factory.copyToClipboard = function (text) {
            Dialog
                .confirm('Result', text, ['COPY', 'DONE'], 'text-center', 'codescan')
                .then(function (result) {
                    if (result) {
                        $cordovaClipboard
                            .copy(text)
                            .then(function () {
                            }, function () {
                            });
                    }
                });
        };

        factory.scanGeneric = function () {
            var defer = $q.defer();

            $cordovaBarcodeScanner
                .scan()
                .then(function (scannedData) {

                    // We resolve regardless what's going on after.
                    defer.resolve(scannedData);

                    if (!scannedData.cancelled && (scannedData.text !== '')) {

                        var scannedProtocol = scannedData.text.toLowerCase().split(':')[0] + ':';

                        // The protocol exists!
                        if (factory.scanProtocols.indexOf(scannedProtocol) !== -1) {

                            var contentUrl = scannedData.text;

                            // Handling geo with the navigator plugin
                            if (scannedProtocol === 'geo:') {
                                var coords = contentUrl.replace('geo:', '').split(',');
                                Navigator.navigate({lat: parseFloat(coords[0]), lng: parseFloat(coords[1])});

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

                            LinkService.openLink(contentUrl, {global: {browser: 'external_browser'}});
                        } else {
                            var text = scannedData.text;
                            var qrCode = text.replace('sendback:', '');

                            // Check discount, then padlock, then clipboard
                            factory.checkCascade(qrCode, [factory.checkDiscount, factory.checkPadlock, factory.copyToClipboard]);
                        }
                    }
                }, function (error) {
                    // We reject regardless what's going on after.
                    defer.reject(error);

                    Dialog.alert('Error', 'An error occurred while reading the code.', 'OK', 2350, 'codescan');
                });

            return defer.promise;
        };

        // Section for html5 qrcode scanner with camera api!
        factory.browserScanModal = null;
        factory.browserScan = function (success, error) {
            var localScan = function (success, error) {
                Modal
                    .fromTemplateUrl('templates/codescan/modal.html', {
                        scope: angular.extend($rootScope.$new(true), {
                            close: function () {
                                factory.browserScanModal.hide();
                            }
                        })
                    }).then(function(modal) {
                        factory.browserScanModal = modal;
                        factory.browserScanModal.show();
                        // This method will trigger user permissions
                        Html5Qrcode
                            .getCameras()
                            .then(devices => {
                                if (devices && devices.length) {
                                    var cameraId = devices[1].id;
                                    const html5QrCode = new Html5Qrcode('qrcode-reader');
                                    html5QrCode.start(
                                        cameraId,
                                        {
                                            fps: 10,    // Optional frame per seconds for qr code scanning
                                            qrbox: 250  // Optional if you want bounded box UI
                                        },
                                        function (qrCodeMessage) {
                                            // do something when code is read
                                            console.log('start qrCodeMessage', qrCodeMessage);
                                        },
                                        function (errorMessage) {
                                            // do something when code is read
                                            console.log('start errorMessage', errorMessage);
                                        })
                                        .catch(err => {
                                            // Start failed, handle it.
                                            console.log('something went wrong start', err);
                                        });
                                }
                            }).catch(err => {
                                console.log('something went wrong getCameras', err);
                            });
                    });

                //var code = window.prompt("Enter barcode value (empty value will fire the error handler):");
                //if(code) {
                //    var result = {
                //        text:code,
                //        format:"Fake",
                //        cancelled:false
                //    };
                //    success(result);
                //} else {
                //    error("No barcode");
                //}
            };

            if (typeof Html5Qrcode === 'undefined') {
                var html5QrcodeTag = document.createElement('script');
                html5QrcodeTag.type = 'text/javascript';
                html5QrcodeTag.src = './dist/lazy/html5-qrcode.min.js';
                html5QrcodeTag.onload = function () {
                    localScan(success, error);
                };
                document.body.appendChild(html5QrcodeTag);
            } else {
                localScan(success, error);
            }
        };

        return factory;
    });
