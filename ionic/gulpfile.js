/**
 * Siberian minifier & pack for JS core files
 *
 * @type {Object}
 */
let gulp = require('gulp'),
    gulpsync = require('gulp-sync')(gulp),
    gutil = require('gulp-util'),
    bower = require('bower'),
    concat = require('gulp-concat'),
    sass = require('gulp-sass'),
    minify = require('gulp-minify'),
    minifyCss = require('gulp-minify-css'),
    rename = require('gulp-rename'),
    sh = require('shelljs'),
    clean = require('gulp-clean'),
    jshint = require('gulp-jshint'),
    uglify = require('gulp-uglify'),
    imagemin = require('gulp-imagemin');
let paths = {
    sass: ['./scss/**/*.scss'],
    scripts: ['./www/js/**/*.js', '!./www/js/app.bundle.min.js'], // exclude the file we write too
    images: ['./www/img/**/*'],
    templates: ['./www/templates/**/*.html'],
    css: ['./www/css/**/*.min.css'],
    html: ['./www/index.html'],
    ionicbundle: ['./www/lib/ionic/js/ionic.bundle.min.js'],
    ionicfonts: ['./www/lib/ionic/fonts/*'],
    lib: ['./www/lib/parse-1.2.18.min.js', './www/lib/moment.min.js', './www/lib/bindonce.min.js'],
    dist: ['./dist/']
};

let files = {
    jsbundle: 'app.bundle.min.js',
    appcss: 'app.css'
};

let siberian_dist = [
    './www/js/app.min.js',
    './www/js/utils.bundle.min.js',
    './www/js/services/services.bundle.js',
    './www/js/services/services.bundle.min.js',
    './www/js/providers/providers.bundle.js',
    './www/js/providers/providers.bundle.min.js',
    './www/js/packed/*.min.js',
    './www/js/filters/filters.min.js',
    './www/js/features/features.bundle.js',
    './www/js/features/features.bundle.min.js',
    './www/js/directives/directives.bundle.js',
    './www/js/directives/directives.bundle.min.js',
    './dist/app.bundle.min.css',
    './dist/app.libs.js',
    './dist/app.libs.min.js'
];

/** Siberian 4.12+ build files */
gulp.task('sb', gulpsync.sync(['cleanup', 'sass', 'bundle_css', 'bundle_libs', 'compress_js', 'pack_features']));

gulp.task('cleanup', function () {
    gulp
        .src(siberian_dist, {
            read: false
        }).pipe(clean());
});

gulp.task('bundle_css', function () {
    var css_src = [
        './www/css/ionRadioFix.css',
        './www/css/style.css',
        './www/css/ionic.app.min.css',
        './www/css/ng-animation.css',
        './www/css/ion-gallery.css',
        './www/css/angular-carousel.min.css',
        './www/css/app.css'
    ];

    return gulp.src(css_src)
        .pipe(concat('app.bundle.css'))
        .pipe(minifyCss())
        .pipe(rename({
            extname: '.min.css'
        }))
        .pipe(gulp.dest('./www/dist/'));
});

gulp.task('pack_features', function () {
    var features = {
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

    for(var feature in features) {
        var src = features[feature];
        var filename = feature + '.bundle.js';
        gulp
            .src(src)
            .pipe(concat(filename, {
                newLine: ';'
            }))
            .pipe(minify({
                mangle: false,
                ext: {
                    min: '.min.js'
                }
            }))
            .pipe(gulp.dest('./www/js/packed/'));
    }
});

gulp.task('compress_js', function () {

    // Controllers!
    var controllers = [
        './www/js/controllers/**/*.js',
        '!./www/js/controllers/**/*.min.js'
    ];

    gulp.src(controllers)
        .pipe(minify({
            mangle: false,
            ext: {
                min: '.min.js'
            }
        }))
        .pipe(gulp.dest('./www/js/controllers/'));

    gulp.src('./www/js/controllers/**/*.min.js')
        .pipe(concat('controllers.bundle.min.js', {
            newLine: ';'
        }))
        .pipe(gulp.dest('./www/dist/'));


    // Directives!
    var directives = [
        './www/js/directives/*.js',
        '!./www/js/directives/directives.bundle.js',
        '!./www/js/directives/*.min.js'
    ];

    gulp.src(directives)
        .pipe(minify({
            mangle: false,
            ext: {
                min: '.min.js'
            }
        }))
        .pipe(gulp.dest('./www/dist/'));

    gulp.src('./www/js/directives/*.min.js')
        .pipe(concat('directives.bundle.min.js', {
            newLine: ';'
        }))
        .pipe(gulp.dest('./www/dist/'));


    // Factory!
    var factories = [
        './www/js/factory/**/*.js',
        '!./www/js/factory/**/*.min.js'
    ];

    gulp.src(factories)
        .pipe(minify({
            mangle: false,
            ext: {
                min: '.min.js'
            }
        }))
        .pipe(gulp.dest('./www/js/factory/'));

    gulp.src('./www/js/factory/*.min.js')
        .pipe(concat('directives.bundle.min.js', {
            newLine: ';'
        }))
        .pipe(gulp.dest('./www/dist/'));


    // Feature routes!
    var features = [
        './www/js/features/*.js',
        '!./www/js/features/features.bundle.js',
        '!./www/js/features/*.min.js'
    ];

    gulp.src(features)
        .pipe(concat('features.bundle.js', {
            newLine: ';'
        }))
        .pipe(minify({
            mangle: false,
            ext: {
                min: '.min.js'
            }
        }))
        .pipe(gulp.dest('./www/js/features/'));

    gulp.src('./www/js/features/features.bundle.min.js')
        .pipe(gulp.dest('./www/dist/'));


    // Filters!
    var filters = [
        './www/js/filters/filters.js'
    ];

    gulp.src(filters)
        .pipe(minify({
            mangle: false,
            ext: {
                min: '.min.js'
            }
        }))
        .pipe(gulp.dest('./www/dist/'));


    // Libraries!
    var libraries = [
        './www/js/libraries/*.js',
        '!./www/js/libraries/*.min.js'
    ];

    gulp.src(libraries)
        .pipe(minify({
            mangle: false,
            ext: {
                min: '.min.js'
            }
        }))
        .pipe(gulp.dest('./www/js/libraries/'));

    var libraries_bundle = [
        './www/js/libraries/*.min.js',
        '!./www/js/libraries/moment.min.js',
        '!./www/js/libraries/angular-carousel.min.js',
        '!./www/js/libraries/progressbar.min.js',
        '!./www/js/libraries/libraries.bundle.min.js'
    ];

    gulp.src(libraries_bundle)
        .pipe(concat('libraries.bundle.min.js', {
            newLine: ';'
        }))
        .pipe(gulp.dest('./www/dist/'));


    // Providers!
    var providers = [
        './www/js/providers/*.js',
        '!./www/js/providers/providers.bundle.js',
        '!./www/js/providers/*.min.js'
    ];

    gulp.src(providers)
        .pipe(minify({
            mangle: false,
            ext: {
                min: '.min.js'
            }
        }))
        .pipe(concat('providers.bundle.js', {
            newLine: ';'
        }))
        .pipe(gulp.dest('./www/dist/'));



    // Services!
    var services = [
        './www/js/services/*.js',
        '!./www/js/services/services.bundle.js',
        '!./www/js/services/*.min.js'
    ];

    gulp.src(services)
        .pipe(minify({
            mangle: false,
            ext: {
                min: '.min.js'
            }
        }))
        .pipe(concat('services.bundle.js', {
            newLine: ';'
        }))
        .pipe(gulp.dest('./www/dist/'));


    // Utils!
    var utils = [
        './www/js/utils/features.js',
        './www/js/utils/form-post.js',
        '!./www/js/utils/utils.bundle.js'
    ];

    gulp.src(utils)
        .pipe(minify({
            mangle: false,
            ext: {
                min: '.min.js'
            }
        }))
        .pipe(concat('utils.bundle.js', {
            newLine: ';'
        }))
        .pipe(gulp.dest('./www/dist/'));


    // On load requried features!
    var onLoadChunks = [
        './www/js/factory/facebook.min.js',
        './www/js/factory/padlock.min.js',
        './www/js/factory/pages.min.js',
        './www/js/factory/tc.min.js',
        './www/js/factory/cms.min.js',
        './www/js/factory/push.min.js',
        './www/js/controllers/push.min.js',
        './www/js/factory/search.min.js',
        './www/js/controllers/customer.min.js',
        './www/js/factory/customer.min.js',
        './www/js/filters/filters.min.js'
    ];

    gulp.src(onLoadChunks)
        .pipe(concat('onloadchunks.bundle.js', {
            newLine: ';'
        }))
        .pipe(minify({
            mangle: false,
            ext: {
                min: '.min.js'
            }
        }))
        .pipe(gulp.dest('./www/dist/'));


    // Main App!
    var app = [
        './www/js/app.js',
        '!./www/js/app.min.js'
    ];

    gulp.src(app)
        .pipe(minify({
            mangle: false,
            ext: {
                min: '.min.js'
            }
        }))
        .pipe(gulp.dest('./www/js/'));


});

gulp.task('bundle_libs', function () {
    var js_src_lib = [
        './www/lib/polyfills.js',
        './www/lib/utils.js',
        './www/lib/ionic/js/ionic.bundle.min.js',
        './www/lib/ionic/js/angular/angular-route.js',
        './www/lib/ngCordova/dist/ng-cordova.min.js'
    ];

    gulp.src(js_src_lib)
        .pipe(concat('app.libs.js', {
            newLine: ';'
        }))
        .pipe(minify({
            mangle: false,
            ext: {
                min: '.min.js'
            }
        }))
        .pipe(gulp.dest('./www/dist/'));
});


/** Default ionic gulp tasks */
gulp.task('default', ['sass']);

gulp.task('build', ['sass', 'scripts', 'styles', 'imagemin', 'index', 'copy']);

gulp.task('clean', function () {
    return gulp.src(paths.dist, {
        read: false
    })
        .pipe(clean());
});

// Copy all other files to dist directly
gulp.task('copy', ['clean'], function () {
    // Copy ionic bundle file
    gulp.src(paths.ionicbundle)
        .pipe(gulp.dest(paths.dist + 'lib/ionic/js/.'));

    // Copy ionic fonts
    gulp.src(paths.ionicfonts)
        .pipe(gulp.dest(paths.dist + 'lib/ionic/fonts'));

    // Copy lib scripts
    gulp.src(paths.lib)
        .pipe(gulp.dest(paths.dist + 'lib'));
});

// styles - min app css then copy min css to dist
gulp.task('minappcss', function () {
    return gulp.src('./www/css/' + files.appcss)
        .pipe(minifyCss())
        .pipe(rename({
            extname: '.min.css'
        }))
        .pipe(gulp.dest('./www/css/'));
});

// styles - min app css then copy min css to dist
gulp.task('styles', ['clean', 'minappcss'], function () {
    gulp.src(paths.css)
        .pipe(gulp.dest(paths.dist + 'css'));
});

// Imagemin images and ouput them in dist
gulp.task('imagemin', ['clean'], function () {
    gulp.src(paths.images)
        .pipe(imagemin())
        .pipe(gulp.dest(paths.dist + 'img'));
});

gulp.task('watch', function () {
    gulp.watch(paths.sass, ['sass']);
});

gulp.task('install', ['git-check'], function () {
    return bower.commands.install()
        .on('log', function (data) {
            gutil.log('bower', gutil.colors.cyan(data.id), data.message);
        });
});

gulp.task('sass', function (done) {
  gulp.src('./scss/ionic.app.scss')
    .pipe(sass())
    .on('error', sass.logError)
    .pipe(gulp.dest('./www/css/'))
    .pipe(minifyCss({
      keepSpecialComments: 0
    }))
    .pipe(rename({ extname: '.min.css' }))
    .pipe(gulp.dest('./www/css/'))
    .on('end', done);
});

gulp.task('watch', function () {
  gulp.watch(paths.sass, ['sass']);
});

gulp.task('install', ['git-check'], function () {
  return bower.commands.install()
    .on('log', function (data) {
      gutil.log('bower', gutil.colors.cyan(data.id), data.message);
    });
});

gulp.task('git-check', function (done) {
  if (!sh.which('git')) {
    console.log(
      '  ' + gutil.colors.red('Git is not installed.'),
      '\n  Git, the version control system, is required to download Ionic.',
      '\n  Download git here:', gutil.colors.cyan('http://git-scm.com/downloads') + '.',
      '\n  Once git is installed, run \'' + gutil.colors.cyan('gulp install') + '\' again.'
    );
    process.exit(1);
  }
  done();
});
