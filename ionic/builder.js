/**
 * Siberian minifier & pack for JS core files
 *
 * @type {Object}
 */
const VERSION = '0.0.1',
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
        './www/css/layouts/layout.css',
        './www/css/layouts/l1.css',
        './www/css/layouts/l2.css',
        './www/css/layouts/l3.css',
        './www/css/layouts/l3_h_l11.css',
        './www/css/layouts/l4.css',
        './www/css/layouts/l4_h_l12.css',
        './www/css/layouts/l5.css',
        './www/css/layouts/l5_h_l13.css',
        './www/css/layouts/l6.css',
        './www/css/layouts/l7.css',
        './www/css/layouts/l8.css',
        './www/css/layouts/l9.css',
        './www/css/layouts/l10.css',
        './www/css/layouts/l14.css',
        './www/css/layouts/l15.css',
        './www/css/layouts/l16.css',
        './www/css/layouts/l17.css',
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
        'privacy_policy': [
            './www/js/controllers/privacy-policy.js'
        ],
        // DUMMY DEPRECATED PUSH
        'push': [
            './www/js/controllers/push.js'
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
        'youtube': [
            './www/js/factory/youtube.js'
        ]
    },
    bundles = {
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
                './www/js/controllers/not-supported-controller.js',
                './www/js/factory/facebook.js',
                './www/js/factory/codescan.js',
                './www/js/factory/padlock.js',
                './www/js/factory/pages.js',
                './www/js/factory/tc.js',
                './www/js/factory/cms.js',
                './www/js/factory/push.js',// DUMMY DEPRECATED PUSH
                './www/js/controllers/push.js',// DUMMY DEPRECATED PUSH
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
                './www/lib/ionic/js/ionic.bundle.js',
                './www/lib/ionic/js/angular/angular-route.js',
                './www/lib/ngCordova/dist/ng-cordova.js'
            ],
            dest: './www/dist/app.libs.min.js'
        },
        app: {
            files: ['./www/js/app.js'],
            dest: './www/dist/app.min.js'
        }
    },
    help = `
Available options:
    --prod
    --bundlecss
    --bundlejs
    --packfeatures
    --sass
    --watch
    --version
`;

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
                'packfeatures': Boolean,
                'sass': Boolean,
                'version': Boolean,
                'watch': Boolean
            },
            shortHands = {
                'p': '--prod',
                'bcss': '--bundlecss',
                'bjs': '--bundlejs',
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
                promise.reject(error);
            }
        });

        promise
            .then(function () {
                tasks.log(greenColor('ionicSass success'));
            }).catch(function (error) {
                tasks.log(redColor('ionicSass error'));
                tasks.log(redColor(error));
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
            }).catch(function (e) {
                promise.reject(e);
            });

        promise
            .then(function () {
                tasks.log(greenColor('bundleCss success'));
            }).catch(function (e) {
                tasks.log(redColor('bundleCss error'));
                tasks.log(redColor(e.message));
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
            if (output.error) {
                tasks.log(redColor('UglifyJS'), filename, output.error);
                instancePromise.reject();
            } else {
                fs.writeFile(filename, output.code, function (wfError) {
                    if (wfError) {
                        tasks.log(redColor('wfError'), wfError);
                        instancePromise.reject();
                    } else {
                        instancePromise.resolve();
                    }
                });
            }
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
            if (output.error) {
                tasks.log(redColor('UglifyJS'), filename, output.error);
                instancePromise.reject();
            } else {
                fs.writeFile(filename, output.code, function (wfError) {
                    if (wfError) {
                        tasks.log(redColor('wfError'), wfError);
                        instancePromise.reject();
                    } else {
                        instancePromise.resolve();
                    }
                });
            }
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
    }
};

tasks.cli(process.argv);
