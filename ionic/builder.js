/**
 * Siberian minifier & pack for JS core files
 *
 * @type {Object}
 */
const VERSION = '0.1.0',
    path = require('path'),
    concat = require('concat'),
    sass = require('node-sass'),
    UglifyJS = require('uglify-es'),
    CleanCss = require('clean-css'),
    fs = require('fs'),
    nopt = require('nopt'),
    clc = require('cli-color'),
    globArray = require('glob-array'),
    watch = require('glob-watcher'),
    sh = require('shelljs'),
    Deferred = require('native-promise-deferred'),
    here = path.dirname(__filename);

let debug = true,
    blueColor = clc.xterm(68),
    redColor = clc.xterm(125),
    greenColor = clc.xterm(23),
    timeColor = clc.xterm(209),
    levels = ['info', 'debug', 'warning', 'error', 'exception', 'throw'],
    cssSrcs = [
        './www/css/ionRadioFix.css',
        './www/css/style.css',
        './www/css/ionic.app.min.css',
        './www/css/ng-animation.css',
        './www/css/ion-gallery.css',
        './www/css/app.css'
    ],
    features = {
        'application': [
            './www/js/controllers/application.js'
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
        'radio': [
            './www/js/controllers/radio.js',
            './www/js/factory/radio.js'
        ],
        'social_gaming': [
            './www/js/controllers/social-gaming.js',
            './www/js/factory/social-gaming.js'
        ],
        'source_code': [
            './www/js/controllers/source-code.js',
            './www/js/factory/source-code.js'
        ],
        'video': [
            './www/js/controllers/video.js',
            './www/js/factory/video.js'
        ],
        'youtube': [
            './www/js/factory/youtube.js'
        ]
    },
    bundles = {
        libraries: {
            files: [
                './www/js/libraries/angular-queue.js',
                './www/js/libraries/angular-touch.min.js',
                './www/js/libraries/base64.min.js',
                './www/js/libraries/ion-gallery.min.js',
                './www/js/libraries/ionRadio.min.js',
                './www/js/libraries/ionic-zoom-view.min.js',
                './www/js/libraries/lazyload.min.js',
                './www/js/libraries/localforage.min.js',
                './www/js/libraries/lodash.min.js',
                './www/js/libraries/ng-img-crop.min.js',
                './www/js/libraries/markerclusterer.js'
            ],
            dest: './www/dist/libraries.bundle.min.js'
        },
        core: {
            files: [
                './www/js/utils/features.js',
                './www/js/utils/form-post.js',
                './www/js/factory/fallback-ng-cordova.js', // To be removed in 4.18 for ever.
                './www/js/services/*.js',
                './www/js/directives/*.js',
                './www/js/providers/*.js',
                './www/js/features/*.js',
                './www/js/factory/facebook.js',
                './www/js/factory/padlock.js',
                './www/js/factory/pages.js',
                './www/js/factory/tc.js',
                './www/js/factory/cms.js',
                './www/js/factory/search.js',
                './www/js/controllers/customer.js',
                './www/js/factory/customer.js',
                './www/js/filters/filters.js'
            ],
            dest: './www/dist/core.bundle.min.js'
        },
        libs: {
            files: [
                './www/lib/utils.js',
                './www/lib/ionic/js/ionic.bundle.min.js',
                './www/lib/ionic/js/angular/angular-route.js',
            ],
            dest: './www/dist/app.libs.min.js'
        },
        app: {
            files: ['./www/js/app.js'],
            dest: './www/dist/app.min.js'
        }
    },
    bundlesCordova = {
        cdvAndroid: {
            files: [
                './platforms/android/app/src/main/assets/www/cordova.js',
                './platforms/android/app/src/main/assets/www/cordova_plugins.js',
                './platforms/android/app/src/main/assets/www/plugins/**/*.js'
            ],
            dest: './platforms/android/app/src/main/assets/www/dist/cordova.js'
        },
        cdvBrowser: {
            files: [
                './platforms/browser/www/cordova.js',
                './platforms/browser/www/cordova_plugins.js',
                './platforms/browser/www/plugins/**/*.js'
            ],
            dest: './platforms/browser/www/dist/cordova.js'
        },
        cdvIos: {
            files: [
                './platforms/ios/www/cordova.js',
                './platforms/ios/www/cordova_plugins.js',
                './platforms/ios/www/plugins/**/*.js'
            ],
            dest: './platforms/ios/www/dist/cordova.js'
        }
    },
    help = `
Available options:
    --prod
    --bundlecss
    --bundlejs
    --bundlecordova
    --packfeatures
    --sass
    --watch
    --version
`;

// Siberian 4.17+ task manager!
let tasks = {
    /**
     * Log function with debug
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

        let date = new Date();
        let currentTime = date.getHours() + ':' + date.getMinutes() + ':' + date.getSeconds() + '.' + ('000' + date.getMilliseconds()).slice(-3);

        switch (level) {
            case 'exception':
            case 'throw':
                throw new Error(level + ' >> ' + args.join(', '));
            case 'error':
            case 'warning':
            case 'debug':
            case 'info':
            default:
                log.apply(console, [timeColor('[' + currentTime + ']')].concat(Array.from(args)));
                break;
        }
    },
    cli: function (inputArgs) {
        tasks.log(blueColor('builder start'));

        // Move into current directory!
        sh.cd(here);

        // CLI options!
        let knownOpts = {
                'prod': Boolean,
                'bundlecss': Boolean,
                'bundlejs': Boolean,
                'bundlecordova': Boolean,
                'packfeatures': Boolean,
                'sass': Boolean,
                'version': Boolean,
                'watch': Boolean
            },
            shortHands = {
                'p': '--prod',
                'bcss': '--bundlecss',
                'bjs': '--bundlejs',
                'bc': '--bundlecordova',
                'pf': '--packfeatures',
                's': '--sass',
                'v': '--version',
                'w': '--watch'
            },
            args = nopt(knownOpts, shortHands, inputArgs);

        switch (true) {
            case args.bundlecss:
                    tasks.bundleCss();
                break;
            case args.bundlejs:
                    tasks.bundleJs();
                break;
            case args.packfeatures:
                    tasks.packFeatures();
                break;
            case args.sass:
                    tasks.ionicSass();
                break;
            case args.prod:
                    tasks.prod();
                break;
            case args.version:
                    tasks.version();
                break;
            case args.watch:
                    tasks.watch();
                break;
            case args.bundlecordova:
                    tasks.bundleCordovaJs();
                break;
            default:
                    tasks.log(help);
        }
    },
    prod: function () {
        tasks.bundleCss();
        tasks.bundleJs();
        tasks.packFeatures();
    },
    version: function () {
        console.log(blueColor('builder.js version: ' + VERSION));
    },
    watch: function () {
        tasks.log(greenColor('Watching js/css files changes ...'));


        // Features!
        // Features have their own files list, so we segment watchers to improve build time!
        Object.keys(features)
            .forEach(function (key) {
                watch(features[key], function (done) {
                    tasks.log(greenColor('Feature JS changed ...'));
                    tasks.packFeatures(key)
                        .then(function () {
                            tasks.log(greenColor('Feature JS done ...'));
                            done();
                        });
                });
            });

        // Bundles!
        // Bundles have their own files list, so we segment watchers to improve build time!
        Object.keys(bundles)
            .forEach(function (key) {
                watch(bundles[key].files, function (done) {
                    tasks.log(greenColor('Bundle JS changed ...'));
                    tasks.bundleJs(key)
                        .then(function () {
                            tasks.log(greenColor('Bundle JS done ...'));
                            done();
                        });
                });
            });

        let cssWatcher = watch([
            './www/css/*.css'
        ]);

        cssWatcher.on('change', function (lpath, stat) {
            if (lpath.indexOf('ionic.app.min.css') !== -1) {
                return;
            }
            tasks.log(greenColor('CSS changed ...'));
            tasks.bundleCss()
                .then(function () {
                    tasks.log(greenColor('CSS done ...'));
                });
        });
    },
    ionicSass: function () {
        tasks.log(blueColor('ionicSass start'));
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
                tasks.log(greenColor('ionicSass success'));
            }).catch(function () {
                tasks.log(redColor('ionicSass error'));
            });

        return promise;
    },
    bundleCss: function () {
        tasks.log(blueColor('bundleCss start'));

        let promise = new Deferred();
        // ionicSass is a pre-requisite to bundleCss
        tasks.ionicSass()
            .then(function () {
                concat(cssSrcs)
                    .then(function (result) {
                        let output = new CleanCss({}).minify(result);
                        fs.writeFile('./www/dist/app.bundle.min.css', output.styles, function (wfError) {
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
                tasks.log(greenColor('bundleCss success'));
            }).catch(function () {
                tasks.log(redColor('bundleCss error'));
            });

        return promise;
    },
    packFeatures: function (segment) {
        tasks.log(blueColor('packFeatures start'));

        let promise = new Deferred(),
            promises = [];

        let uglify = function (filename, result, instancePromise) {
            let output = UglifyJS.minify(result, {
                mangle: false
            });
            fs.writeFile(filename, output.code, function (wfError) {
                if (wfError) {
                    tasks.log(redColor('wfError'), wfError);
                    instancePromise.reject();
                } else {
                    instancePromise.resolve();
                }
            });
        };

        let internalBuilder = function (files, dest) {
            let instancePromise = new Deferred();
            promises.push(instancePromise);
            concat(files)
                .then(function (result) {
                    uglify(dest, result, instancePromise);
                })
                .catch(function (error) {
                    tasks.log(redColor('something went wrong'), error);
                    instancePromise.reject();
                });
        };

        if (segment !== undefined && features.hasOwnProperty(segment)) {
            tasks.log('packing feature segment: ' + segment);
            let src = features[segment];
            let filename = './www/dist/packed/' + segment + '.bundle.min.js';
            internalBuilder(src, filename);
        } else {
            Object.keys(features)
                .forEach(function (segment) {
                    let src = features[segment];
                    let filename = './www/dist/packed/' + segment + '.bundle.min.js';
                    internalBuilder(src, filename);
                });
        }

        Promise.all(promises)
            .then(function () {
                promise.resolve();
            }).catch(function () {
                promise.reject();
            });

        promise
            .then(function () {
                tasks.log(greenColor('packFeatures success'));
            }).catch(function () {
                tasks.log(redColor('packFeatures error'));
            });

        return promise;
    },
    bundleJs: function (segment) {
        tasks.log(blueColor('bundleJs start'));

        let promise = new Deferred(),
            promises = [];

        let uglify = function (filename, result, instancePromise) {
            let output = UglifyJS.minify(result, {
                mangle: false
            });
            fs.writeFile(filename, output.code, function (wfError) {
                if (wfError) {
                    tasks.log(redColor('wfError'), wfError);
                    instancePromise.reject();
                } else {
                    instancePromise.resolve();
                }
            });
        };

        let internalBuilder = function (files, dest) {
            let instancePromise = new Deferred();
            promises.push(instancePromise);
            let globFiles = globArray.sync(files);
            concat(globFiles)
                .then(function (result) {
                    uglify(dest, result, instancePromise);
                })
                .catch(function (error) {
                    tasks.log(redColor('something went wrong'), error);
                    instancePromise.reject();
                });
        };

        if (segment !== undefined && bundles.hasOwnProperty(segment)) {
            tasks.log(blueColor('bundling segment: ' + segment));
            internalBuilder(bundles[segment].files, bundles[segment].dest);
        } else {
            Object.keys(bundles)
                .forEach(function (segment) {
                    internalBuilder(bundles[segment].files, bundles[segment].dest);
                });
        }

        Promise.all(promises)
            .then(function () {
                promise.resolve();
            }).catch(function () {
                promise.reject();
            });

        promise
            .then(function () {
                tasks.log(greenColor('bundleJs success'));
            }).catch(function () {
                tasks.log(redColor('bundleJs error'));
            });

        return promise;
    },
    bundleCordovaJs: function (segment) {
        tasks.log(blueColor('bundleCordovaJs start'));

        let promise = new Deferred(),
            promises = [];

        let uglify = function (filename, result, instancePromise) {
            let output = UglifyJS.minify(result, {
                mangle: false
            });
            fs.writeFile(filename, output.code, function (wfError) {
                if (wfError) {
                    tasks.log(redColor('wfError'), wfError);
                    instancePromise.reject();
                } else {
                    instancePromise.resolve();
                }
            });
        };

        let internalBuilder = function (files, dest) {
            let instancePromise = new Deferred();
            promises.push(instancePromise);
            let globFiles = globArray.sync(files);
            concat(globFiles)
            .then(function (result) {
                uglify(dest, result, instancePromise);
            })
            .catch(function (error) {
                tasks.log(redColor('something went wrong'), error);
                instancePromise.reject();
            });
        };

        if (segment !== undefined && bundlesCordova.hasOwnProperty(segment)) {
            tasks.log(blueColor('bundling segment: ' + segment));
            internalBuilder(bundlesCordova[segment].files, bundlesCordova[segment].dest);
        } else {
            Object.keys(bundlesCordova)
            .forEach(function (segment) {
                internalBuilder(bundlesCordova[segment].files, bundlesCordova[segment].dest);
            });
        }

        Promise.all(promises)
        .then(function () {
            promise.resolve();
        }).catch(function () {
            promise.reject();
        });

        promise
        .then(function () {
            tasks.log(greenColor('bundleCordovaJs success'));
        }).catch(function () {
            tasks.log(redColor('bundleCordovaJs error'));
        });

        return promise;
    }
};

tasks.cli(process.argv);
