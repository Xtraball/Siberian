/**
 * Siberian minifier & pack for JS core files
 *
 * @type {Object}
 */
const concat = require('concat'),
    sass = require('node-sass'),
    UglifyJS = require('uglify-es'),
    CleanCss = require('clean-css'),
    fs = require('fs'),
    sh = require('shelljs'),
    nopt = require('nopt'),
    clc = require('cli-color'),
    globArray = require('glob-array'),
    Deferred = require('native-promise-deferred');

let debug = true,
    levels = ['info', 'debug', 'warning', 'error', 'exception', 'throw'],
    cssSrcs = [
        './www/css/ionRadioFix.css',
        './www/css/style.css',
        './www/css/ionic.app.min.css',
        './www/css/ng-animation.css',
        './www/css/ion-gallery.css',
        './www/css/angular-carousel.min.css',
        './www/css/app.css'
    ],
    features = {
        'application': [
            './www/js/controllers/application.js'
        ],
        'booking': [
            './www/js/controllers/booking.js',
            './www/js/factory/booking.js'
        ],
        'catalog': [
            './www/js/controllers/catalog.js',
            './www/js/factory/catalog.js',
            './www/js/controllers/set-meal.js',
            './www/js/factory/set-meal.js'
        ],
        'cms': [
            './www/js/controllers/cms.js'
        ],
        'codescan': [
            './www/js/controllers/codescan.js'
        ],
        'contact': [
            './www/js/controllers/contact.js',
            './www/js/factory/contact.js'
        ],
        'discount': [
            './www/js/controllers/discount.js',
            './www/js/factory/discount.js'
        ],
        'event': [
            './www/js/controllers/event.js',
            './www/js/factory/event.js'
        ],
        'facebook': [
            './www/js/controllers/facebook.js'
        ],
        'folder': [
            './www/js/controllers/folder.js',
            './www/js/factory/folder.js'
        ],
        'form': [
            './www/js/controllers/form.js',
            './www/js/factory/form.js'
        ],
        'homepage': [
            './www/js/controllers/homepage.js'
        ],
        'image': [
            './www/js/controllers/image.js',
            './www/js/factory/image.js'
        ],
        'links': [
            './www/js/controllers/links.js',
            './www/js/factory/links.js'
        ],
        'loyalty_card': [
            './www/js/controllers/loyalty-card.js',
            './www/js/factory/loyalty-card.js'
        ],
        'maps': [
            './www/js/controllers/maps.js',
            './www/js/factory/maps.js'
        ],
        'media': [
            './www/js/controllers/media-player.js',
            './www/js/controllers/music.js',
            './www/js/factory/music.js'
        ],
        'newswall': [
            './www/js/controllers/newswall.js',
            './www/js/factory/newswall.js'
        ],
        'padlock': [
            './www/js/controllers/padlock.js'
        ],
        'places': [
            './www/js/controllers/places.js',
            './www/js/factory/places.js'
        ],
        'privacy_policy': [
            './www/js/controllers/privacy-policy.js'
        ],
        'm_commerce': [
            './www/js/controllers/mcommerce/cart.js',
            './www/js/controllers/mcommerce/category.js',
            './www/js/controllers/mcommerce/product.js',
            './www/js/controllers/mcommerce/sales/confirmation.js',
            './www/js/controllers/mcommerce/sales/customer.js',
            './www/js/controllers/mcommerce/sales/delivery.js',
            './www/js/controllers/mcommerce/sales/error.js',
            './www/js/controllers/mcommerce/sales/history.js',
            './www/js/controllers/mcommerce/sales/payment.js',
            './www/js/controllers/mcommerce/sales/store.js',
            './www/js/controllers/mcommerce/sales/stripe.js',
            './www/js/controllers/mcommerce/sales/success.js',
            './www/js/factory/mcommerce/cart.js',
            './www/js/factory/mcommerce/category.js',
            './www/js/factory/mcommerce/product.js',
            './www/js/factory/mcommerce/sales/customer.js',
            './www/js/factory/mcommerce/sales/delivery.js',
            './www/js/factory/mcommerce/sales/payment.js',
            './www/js/factory/mcommerce/sales/store.js',
            './www/js/factory/mcommerce/sales/stripe.js'
        ],
        'push': [
            './www/js/controllers/push.js'
        ],
        'radio': [
            './www/js/controllers/radio.js',
            './www/js/factory/radio.js'
        ],
        'rss': [
            './www/js/controllers/rss.js',
            './www/js/factory/rss.js'
        ],
        'social_gaming': [
            './www/js/controllers/social-gaming.js',
            './www/js/factory/social-gaming.js'
        ],
        'source_code': [
            './www/js/controllers/source-code.js',
            './www/js/factory/source-code.js'
        ],
        'tip': [
            './www/js/controllers/tip.js',
            './www/js/factory/tip.js'
        ],
        'topic': [
            './www/js/controllers/topic.js',
            './www/js/factory/topic.js'
        ],
        'twitter': [
            './www/js/controllers/twitter.js',
            './www/js/factory/twitter.js'
        ],
        'video': [
            './www/js/controllers/video.js',
            './www/js/factory/video.js'
        ],
        'weather': [
            './www/js/controllers/weather.js',
            './www/js/factory/weather.js'
        ],
        'wordpress': [
            './www/js/controllers/wordpress.js',
            './www/js/factory/wordpress.js'
        ],
        'youtube': [
            './www/js/factory/youtube.js'
        ]
    };

// Siberian 4.12+ task manager!
let tasks = {
    /**
     * Log function with debug toggler
     */
    log: function () {
        if (!debug) {
            return;
        }

        let args = arguments,
            level = 'info',
            log = Function.prototype.bind.call(console.log, console);

        if (levels.indexOf(args[args.length - 1]) !== -1) {
            level = args[args.length - 1];
            delete args[args.length - 1];
        }

        switch (level) {
            case 'exception':
            case 'throw':
                throw new Error(level + ' >> ' + args.join(', '));
            case 'error':
            case 'warning':
            case 'debug':
            case 'info':
            default:
                log.apply(console, ['[' + Date.now() + ']'].concat(Array.from(args)));
                break;
        }
    },
    cli: function (inputArgs) {
        // CLI options!
        let knownOpts = {
                'build': Boolean,
                'bundlecss': Boolean,
                'bundlejs': Boolean,
                'cleanup': Boolean,
                'packfeatures': Boolean,
                'sass': Boolean,
                'version': Boolean
            },
            shortHands = {
                'b': '--build',
                'bcss': '--bundlecss',
                'bjs': '--bundlejs',
                'c': '--cleanup',
                'pf': '--packfeatures',
                's': '--sass',
                'v': '--version'
            },
            args = nopt(knownOpts, shortHands, inputArgs);

        switch (true) {
            case args.bundlecss:
                    tasks.bundleCss();
                break;
            case args.cleanup:
                    tasks.cleanUp();
                break;
            case args.packfeatures:
                tasks.packFeatures();
                break;
            case args.bundlejs:
                tasks.bundleJs();
                break;
            case args.sass:
                    tasks.ionicSass();
                break;
            case args.build:
                    tasks.build();
                break;
        }
    },
    build: function () {
        tasks.cleanUp();
        tasks.bundleCss();
        tasks.bundleJs();
        tasks.packFeatures();
    },
    cleanUp: function () {
        tasks.log('cleanUp start');
    },
    ionicSass: function () {
        tasks.log('ionicSass start');
        let promise = new Deferred();

        sass.render({
            file: './scss/ionic.app.scss',
            outFile: './www/css/ionic.app.min.css',
            outputStyle: 'compressed'
        }, function (error, result) {
            if (!error) {
                fs.writeFile('./www/css/ionic.app.min.css', result.css, function (wfError) {
                    if (!wfError) {
                        promise.resolve();
                    } else {
                        promise.reject();
                    }
                });
            } else {
                promise.reject();
            }
        });

        promise
            .then(function () {
                tasks.log('ionicSass success');
            }).catch(function () {
                tasks.log('ionicSass error');
            });

        return promise;
    },
    bundleCss: function () {
        tasks.log('bundleCss start');

        let promise = new Deferred();
        // Run ionicSass to be sure required files are present!
        tasks.ionicSass()
            .then(function () {
                concat(cssSrcs)
                    .then(function (result) {
                        let output = new CleanCss({}).minify(result);
                        fs.writeFile('./www/dist/app.bundle.min.css', output, function (wfError) {
                            if (!wfError) {
                                promise.resolve();
                            } else {
                                promise.reject();
                            }
                        });
                    }).catch(function () {
                        promise.reject();
                    });
            }).catch(function () {
                promise.reject();
            });

        promise
            .then(function () {
                tasks.log('bundleCss success');
            }).catch(function () {
                tasks.log('bundleCss error');
            });

        return promise;
    },
    packFeatures: function () {
        tasks.log('packFeatures start');

        let promise = new Deferred();
        let errors = false;

        let uglify = function (filename, result) {
            let output = UglifyJS.minify(result, {
                mangle: false
            });
            fs.writeFile(filename, output.code, function (wfError) {
                if (wfError) {
                    setIsError();
                }
            });
        };

        let setIsError = function () {
            errors = true;
        };

        for (let feature in features) {
            let src = features[feature];
            let filename = './www/dist/packed/' + feature + '.bundle.min.js';
            concat(src)
                .then(function (result) {
                    uglify(filename, result);
                })
                .catch(function (error) {
                    tasks.log('something went wrong', error);
                    setIsError();
                });
        }

        if (errors) {
            promise.reject();
        } else {
            promise.resolve();
        }

        promise
            .then(function () {
                tasks.log('packFeatures success');
            }).catch(function () {
                tasks.log('packFeatures error');
            });

        return promise;
    },
    bundleJs: function () {
        tasks.log('bundleJs start');

        let promise = new Deferred();

        let errors = false;
        let setIsError = function () {
            errors = true;
        };

        let uglify = function (filename, result) {
            let output = UglifyJS.minify(result, {
                mangle: false
            });
            fs.writeFile(filename, output.code, function (wfError) {
                if (wfError) {
                    tasks.log('wfError', wfError);
                    setIsError();
                }
            });
        };

        let internalBuilder = function (files, dest) {
            let globFiles = globArray.sync(files);
            concat(globFiles)
                .then(function (result) {
                    uglify(dest, result);
                })
                .catch(function (error) {
                    tasks.log('something went wrong', error);
                    setIsError();
                });
        };

        let bundles = {
            directives: {
                files: ['./www/js/directives/*.js'],
                dest: './www/dist/directives.bundle.min.js'
            },
            features: {
                files: ['./www/js/features/*.js'],
                dest: './www/dist/features.bundle.min.js'
            },
            libraries: {
                files: [
                    './www/js/libraries/angular-queue.js',
                    './www/js/libraries/angular-queue.min.js',
                    './www/js/libraries/angular-touch.min.js',
                    './www/js/libraries/base64.min.js',
                    './www/js/libraries/ion-gallery.min.js',
                    './www/js/libraries/ionRadio.min.js',
                    './www/js/libraries/ionic-zoom-view.min.js',
                    './www/js/libraries/lazyload.min.js',
                    './www/js/libraries/localforage.min.js',
                    './www/js/libraries/lodash.min.js',
                    './www/js/libraries/ng-img-crop.min.js'
                ],
                dest: './www/dist/libraries.bundle.min.js'
            },
            providers: {
                files: ['./www/js/providers/*.js'],
                dest: './www/dist/providers.bundle.min.js'
            },
            services: {
                files: ['./www/js/services/*.js'],
                dest: './www/dist/services.bundle.min.js'
            },
            utils: {
                files: [
                    './www/js/utils/features.js',
                    './www/js/utils/form-post.js'
                ],
                dest: './www/dist/utils.bundle.min.js'
            },
            onloadchunks: {
                files: [
                    './www/js/factory/facebook.js',
                    './www/js/factory/padlock.js',
                    './www/js/factory/pages.js',
                    './www/js/factory/tc.js',
                    './www/js/factory/cms.js',
                    './www/js/factory/push.js',
                    './www/js/controllers/push.js',
                    './www/js/factory/search.js',
                    './www/js/controllers/customer.js',
                    './www/js/factory/customer.js',
                    './www/js/filters/filters.js'
                ],
                dest: './www/dist/onloadchunks.bundle.min.js'
            },
            libs: {
                files: [
                    './www/lib/polyfills.js',
                    './www/lib/utils.js',
                    './www/lib/ionic/js/ionic.bundle.min.js',
                    './www/lib/ionic/js/angular/angular-route.js',
                    './www/lib/ngCordova/dist/ng-cordova.min.js'
                ],
                dest: './www/dist/libs.min.js'
            },
            app: {
                files: ['./www/js/app.js'],
                dest: './www/dist/app.min.js'
            }
        };

        Object.keys(bundles)
            .forEach(function (key) {
                internalBuilder(bundles[key].files, bundles[key].dest);
            });

        if (errors) {
            promise.reject();
        } else {
            promise.resolve();
        }

        promise
            .then(function () {
                tasks.log('bundleJs success');
            }).catch(function () {
                tasks.log('bundleJs error');
            });

        return promise;
    }
};

tasks.cli(process.argv);
