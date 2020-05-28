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

        factory._scanner = function () {
            return (SB.DEVICE.TYPE_BROWSER === DEVICE_TYPE) ?
                factory.browserScan() : $cordovaBarcodeScanner.scan();
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

            factory
                ._scanner()
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
            factory
                ._scanner()
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

            factory
                ._scanner()
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

            factory
                ._scanner()
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
        factory.devices = [];
        factory.currentDevice = null;
        factory.currentIndex = 0;
        factory.qrCodeScanner = null;
        factory.browserScan = function () {
            var deferred = $q.defer();
            // Prompt fallback!
            var promptScan = function () {
                stopScan();

                Dialog.prompt(
                    'Manual input',
                    'Enter barcode value (empty value will fire the error handler):',
                    'text',
                    '',
                    undefined,
                    undefined,
                    'codescan')
                    .then(function (scannerValue) {
                        if (scannerValue.trim().length > 0) {
                            var result = {
                                text: scannerValue,
                                format: 'Fake',
                                cancelled: false
                            };
                            deferred.resolve(result);
                        } else {
                            deferred.reject('No code provided!');
                        }
                    });
            };

            var stopScan = function () {
                if (factory.qrCodeScanner !== null) {
                    try {
                        factory
                            .qrCodeScanner
                            .stop()
                            .then(function () {
                                factory.qrCodeScanner.clear();
                            });
                    } catch (e) {}
                }
            };

            var startScan = function (deviceId) {
                // So then start scanning!
                if (factory.qrCodeScanner === null) {
                    factory.qrCodeScanner = new Html5Qrcode('qrcode-reader');
                }
                // This method will trigger user permissions
                Html5Qrcode
                    .getCameras()
                    .then(function (devices) {
                        if (devices && devices.length) {
                            if (deviceId === undefined) {
                                factory.devices = devices;
                                for (var i = 0; i < factory.devices.length; i++)
                                {
                                    if (/back|rear|environment/gi.test(dfactory.devices[i].label)) {
                                        factory.currentDevice = factory.devices[i];
                                        factory.currentIndex = i;
                                    }
                                }
                                // Fallback on default device if no label can be identified!
                                if (factory.currentDevice === null) {
                                    factory.currentDevice = factory.devices[0];
                                    factory.currentIndex = 0;
                                }

                                deviceId = factory.currentDevice.id;
                            }

                            factory.qrCodeScanner
                                .start(deviceId, {
                                        fps: 30,    // Optional frame per seconds for qr code scanning
                                        qrbox: 250  // Optional if you want bounded box UI
                                    },
                                    function (qrCodeMessage) {
                                        var result = {
                                            text: qrCodeMessage,
                                            format: 'Fake',
                                            cancelled: false
                                        };
                                        stopScan();
                                        deferred.resolve(result);
                                    },
                                    function (errorMessage) {
                                        // Silently do nothin!
                                    }).catch(function (error) {
                                    // Start failed, try dialog!
                                    promptScan();
                                });
                        } else {
                            // Damn, no camera available!
                            promptScan();
                        }
                    }).catch(function () {
                        // Damn, no camera available!
                        promptScan();
                    });
            };

            var nextDevice = function () {
                factory.currentIndex++;
                // Loop indexes!
                if (factory.currentIndex > factory.devices.length - 1) {
                    factory.currentIndex = 0;
                }

                factory.currentDevice = factory.devices[factory.currentIndex];

                console.log('factory.currentDevice', factory.currentDevice, 'factory.currentIndex', factory.currentIndex);

                stopScan();
                startScan(factory.currentDevice.id);
            };

            // Local scan method!
            var localScan = function () {
                Modal
                    .fromTemplateUrl('templates/codescan/modal.html', {
                        scope: angular.extend($rootScope.$new(true), {
                            close: function () {
                                factory.browserScanModal.hide();
                            },
                            stopCamera: function () {
                                stopScan();
                                factory.browserScanModal.hide();
                                deferred.reject('stopped');
                            },
                            canToggle: function () {
                                return factory.devices.length > 1;
                            },
                            toggleCamera: function () {
                                nextDevice();
                            }
                        })
                    }).then(function (modal) {
                        factory.browserScanModal = modal;
                        factory.browserScanModal.show();
                        startScan();
                    });
            };

            // Lazy loading Html5Qrcode JS library!
            if (typeof Html5Qrcode === 'undefined') {
                var html5QrcodeTag = document.createElement('script');
                html5QrcodeTag.type = 'text/javascript';
                html5QrcodeTag.src = './dist/lazy/html5-qrcode.min.js';
                html5QrcodeTag.onload = function () {
                    localScan();
                };
                document.body.appendChild(html5QrcodeTag);
            } else {
                localScan();
            }

            return deferred.promise;
        };

        return factory;
    });
