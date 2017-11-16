/**
 * Siberian minifier & pack for JS core files
 *
 * @type {Object}
 */
const concat = require('concat'),
    sass = require('node-sass'),
    minify = require('uglify-es'),
    cleanCss = require('clean-css'),
    fs = require('fs'),
    sh = require('shelljs'),
    nopt = require('nopt'),
    runSequence = require('run-sequence');

let paths = {
        scripts: ['./www/js/**/*.js', '!./www/js/app.bundle.min.js'], // exclude the file we write too
        templates: ['./www/templates/**/*.html'],
        css: ['./www/css/**/*.min.css'],
        html: ['./www/index.html'],
        ionicbundle: ['./www/lib/ionic/js/ionic.bundle.min.js'],
        ionicfonts: ['./www/lib/ionic/fonts/*'],
        lib: ['./www/lib/parse-1.2.18.min.js', './www/lib/moment.min.js', './www/lib/bindonce.min.js'],
        dist: ['./dist/']
    },
    files = {
        jsbundle: 'app.bundle.min.js',
        appcss: 'app.css'
    },
    siberianDist = [
        './www/dist/*'
    ],
    intermediateCleanup = [];

// Siberian 4.12+ task manager!
let tasks = {
    cli: function (inputArgs) {
        // CLI options!
        let knownOpts = {
                'build': Boolean,
                'cleanup': Boolean,
                'sass': Boolean,
                'version': Boolean
            },
            shortHands = {
                'b': '--build',
                'c': '--cleanup',
                's': '--sass',
                'v': '--version'
            },
            args = nopt(knownOpts, shortHands, inputArgs);

        switch (true) {
            case args.cleanup:
                    tasks.cleanUp();
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
        tasks.ionicSass();
    },
    cleanUp: function () {
        console.log('cleanUp start');
        siberianDist.forEach(function (path) {
            sh.rm('-rf', path);
        });
    },
    ionicSass: function () {
        console.log('ionicSass start');
        sass.render({
            file: './scss/ionic.app.scss',
            outFile: './www/css/ionic.app.min.css',
            outputStyle: 'compressed'
        }, function (error, result) {
            if (!error) {
                fs.writeFile('./www/css/ionic.app.min.css', result.css, function (err) {});
            }
        });
    },
    bundleCss: function () {

    }
};

tasks.cli(process.argv);
