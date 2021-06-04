/**
 * AdmobService
 *
 * @version 4.20.10
 */
angular
    .module('starter')
    .service('AdmobService', function ($log, $rootScope) {
        var service = {
            interstitialWeights: {
                start: {
                    'show': 0.333,
                    'skip': 0.667
                },
                medium: {
                    'show': 0.25,
                    'skip': 0.75
                },
                low: {
                    'show': 0.05,
                    'skip': 0.95
                },
                default: {
                    'show': 0.15,
                    'skip': 0.85
                }
            },
            interstitialState: 'start',
            viewEnterCount: 0,
            options: {},
            forbiddenStates: [],
            canReloadBanner: true,
            currentPlatform: null,
            willShowInterstitial: false,
            /**
             *
             * 0 = notDetermined
             * 1 = restricted
             * 2 = denied
             * 3 = authorized
             */
            trackingAuthorizationStatus: 0,
            interstitialObj: null,
            interstitialObjPromise: null,
            bannerObj: null,
            npa: '0'
        };

        /**
         * Add states that must never load Ads!
         * @param states
         */
        service.forbidStates = function (states) {
            service.forbiddenStates = service.forbiddenStates.concat(states);
        };

        /**
         * Random weight function
         * @param probs
         * @returns {string}
         */
        service.getWeight = function (probs) {
            var random = _.random(0, 1000);
            var offset = 0;
            var keyUsed = 'start';
            var match = false;
            _.forEach(probs, function (value, key) {
                offset = offset + (value * 1000);
                if (!match && (random <= offset)) {
                    keyUsed = key;
                    match = true;
                }
            });
            $log.debug('AdMob key used: ', keyUsed);
            return keyUsed;
        };

        service.init = function (options) {
            if (!$rootScope.isNativeApp || !admob) {
                $log.info('Admob init stopped.');
                return;
            }

            if (ionic.Platform.isIOS()) {
                service.currentPlatform = 'ios';
                $log.debug('AdMob init iOS');
                service.options = options.app.ios;

                // App Tracking Transparency!
                admob
                    .requestTrackingAuthorization()
                    .then(function (result) {
                        service.trackingAuthorizationStatus = parseInt(result);
                        service.npa = '0';
                        if (service.trackingAuthorizationStatus !== 3) {
                            service.npa = '1';
                        }
                        service.initWithOptions(options);
                    });
            }

            if (ionic.Platform.isAndroid()) {
                service.currentPlatform = 'android';
                $log.debug('AdMob init Android');
                service.options = options.app.android;
                service.initWithOptions(options);
            }

            // Ionic view enter is global (if banner and/or interstitial are enabled)
            if (service.options.banner ||
                service.options.interstitial) {

                // Entering a new view
                $rootScope.$on('$ionicView.enter', function (event, data) {

                    service.admobReady.then(function () {

                        // Check for any forbidden stateName
                        service.canReloadBanner = false;
                        if (service.forbiddenStates.indexOf(data.stateName) !== -1) {
                            service.hideBanner();
                            $log.info('admob $ionicView.enter forbidden state.', data.stateName);
                            return;
                        }

                        // Show banner
                        if (service.options.banner) {
                            service.showBanner();
                        }

                        // Increase counter only on non-forbidden states
                        service.viewEnterCount = service.viewEnterCount + 1;

                        // After 10 views, increase chances to show an Interstitial ad!
                        if (service.viewEnterCount >= 9) {
                            service.interstitialState = 'medium';
                        }

                        var action = service.getWeight(service.interstitialWeights[service.interstitialState]);
                        $log.info('admob action', action);

                        if (service.willShowInterstitial === false &&
                            action === 'show') {
                            service.willShowInterstitial = true;
                            $log.info('admob action service.willShowInterstitial = true;');
                        }

                        if (service.willShowInterstitial) {
                            $log.info('admob enter service.willShowInterstitial');
                            try {
                                if (service.interstitialObjPromise !== null) {
                                    $log.info('service.interstitialObjPromise !== null');
                                    service.interstitialObjPromise.then(function () {

                                        // Show interstitial!
                                        service.interstitialObj.show();

                                        $log.info('service.interstitialObjPromise.then OK');
                                        service.willShowInterstitial = false;

                                        /** On success, we change the randomness */
                                        if (service.interstitialState === 'start') {
                                            service.interstitialState = 'low';
                                        } else {
                                            service.interstitialState = 'default';
                                        }

                                        service.viewEnterCount = 0;
                                        service.loadInterstitial();
                                    }, function () {
                                        $log.error('Failed to load interstitial! (Promise)');
                                        $log.info('service.interstitialPromise.then KO');
                                        service.willShowInterstitial = false;
                                        service.loadInterstitial();
                                    });
                                }
                            } catch (e) {
                                $log.error('Interstitial failed to show (Exception)');
                                service.willShowInterstitial = false;
                                service.loadInterstitial();
                            }
                        }
                    });
                });
            }

            // Extensive logging of admob
            if (service.options.banner) {
                document.addEventListener('admob.banner.load', async () => {
                    $log.info('admob banner load.');
                });
                document.addEventListener('admob.banner.load_fail', async () => {
                    $log.info('admob banner load_fail.');
                });
                document.addEventListener('admob.banner.impression', async () => {
                    $log.info('admob banner impression.');
                });
                document.addEventListener('admob.banner.size', async () => {
                    $log.info('admob banner size.');
                });
            }

            // Extensive logging of admob
            if (service.options.interstitial) {
                document.addEventListener('admob.interstitial.load', async () => {
                    $log.info('admob interstitial load.');
                });
                document.addEventListener('admob.interstitial.loadfail', async () => {
                    $log.info('admob interstitial loadfail.');
                });
                document.addEventListener('admob.interstitial.show', async () => {
                    $log.info('admob interstitial show.');
                });
                document.addEventListener('admob.interstitial.showfail', async () => {
                    $log.info('admob interstitial showfail.');
                    service.loadInterstitial();
                });
                document.addEventListener('admob.interstitial.dismiss', async () => {
                    $log.info('admob interstitial dismiss.');
                    service.loadInterstitial();
                });
                document.addEventListener('admob.interstitial.impression', async () => {
                    $log.info('admob interstitial impression.');
                });
            }

        };

        /**
         *
         */
        service.initWithOptions = function (options) {
            // Enable dev mode from backoffice settings
            if (options.isTesting) {
                if (ionic.Platform.isIOS()) {
                    service.options.banner_id = 'ca-app-pub-3940256099942544/2934735716';
                    service.options.interstitial_id = 'ca-app-pub-3940256099942544/4411468910';
                }
                if (ionic.Platform.isAndroid()) {
                    service.options.banner_id = 'ca-app-pub-3940256099942544/6300978111';
                    service.options.interstitial_id = 'ca-app-pub-3940256099942544/1033173712';
                }
            }

            service.admobReady = admob.start();
            service.admobReady.then(function () {
                if (service.options.banner) {
                    service.bannerObj = new admob.BannerAd({
                        adUnitId: service.options.banner_id,
                        position: 'bottom',
                        npa: service.npa,
                    });
                    service.showBanner();
                }

                if (service.options.interstitial) {
                    service.interstitialObj = new admob.InterstitialAd({
                        adUnitId: service.options.interstitial_id,
                        npa: service.npa,
                    });
                    service.loadInterstitial();
                }
            });
        };

        service.loadInterstitial = function () {
            service.admobReady.then(function () {
                service.interstitialObjPromise = service.interstitialObj.load();
            });
        };

        service.hideBanner = function () {
            service.admobReady.then(function () {
                service.bannerObj.hide();
            });
        };

        service.showBanner = function () {
            service.admobReady.then(function () {
                service.bannerObj.load().then(function () {
                    service.bannerObj.show();
                });
            });
        };

        return service;
    })
;
