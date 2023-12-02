// jshint esversion:6
/* global
 process, module, __filename, require
 */

/**
 * SiberianCMS
 *
 * @version 4.17.6
 * @author Xtraball SAS <dev@xtraball.com>
 * @site https://github.com/Xtraball
 *
 */
let clc = require('cli-color'),
    fs = require('fs'),
    nopt = require('nopt'),
    path = require('path'),
    siberian = require('../../package.json'),
    sh = require('shelljs');

const notifier = require('node-notifier'),
      axios = require('axios');

let platforms = [
    'android',
    'ios',
    'browser'
];

let packModules = [];

/**
 * Defining this file path to get the SiberianCMS root project,
 * this allows to call the siberian commands from everywhere !
 */
let ROOT = path.resolve(path.dirname(__filename), '../../'),
    PWD = process.cwd(),
    DEBUG = false,
    REBUILD_MANIFEST = false,
    PHP_VERSION = 'php',
    BUILD_TYPE = '--release',
    NO_ADS = false;


/**
 * Utility method for OSX notifications
 *
 * @param message
 * @param options
 * @returns {boolean}
 */
const notify = function (message, options) {
    const NOTIF_BASE = {
        title: 'Siberian CLI',
        icon: path.join(__dirname, '../../resources/siberian/logo.300.png'), // Absolute path (doesn't work on balloons)
        sound: true
    };

    return notifier.notify(Object.assign({}, NOTIF_BASE, {
        message: message
    }, options));
};

/**
 * Utility function to move if exists
 *
 * @param from
 * @param to
 */
let moveExists = function (from, to) {
    if (fs.existsSync(from)) {
        sh.mv(from, to);
    }
};

/**
 * Utility function
 *
 * @param question
 * @param defaultValue
 * @param callback
 */
let prompt = function (question, defaultValue, callback) {
    let stdin = process.stdin, stdout = process.stdout;

    stdin.resume();
    stdout.write(clc.blue(question + ' (default: ' + defaultValue + '): '));

    stdin.once('data', function (data) {
        let localData = data.toString().trim();
        localData = (localData !== '') ? localData : defaultValue;

        callback(localData);
    });
};

/**
 * Prints a string
 *
 * @param string
 */
let sprint = function (string) {
    console.log(string);
};

/**
 * Utility method
 *
 * @param obj
 * @returns {boolean}
 */
let isObject = function (obj) {
    return ((typeof obj === 'object') && (obj !== null));
};

/**
 * CLI entry point
 *
 * @param inputArgs
 */
let cli = function (inputArgs) {
    // Use local node_modules form the siberian command-line!
    let npmBin = ROOT + '/node_modules/.bin';
    process.env.PATH = npmBin + ':' + process.env.PATH;

    // Exit from command-line if outside project!
    if (PWD.indexOf(ROOT) === -1) {
        sprint(clc.bold(clc.green('SiberianCMS command-line interface.')));
        sprint(clc.bold(clc.red('/!\\ Warning: calling siberian outside project /!\\')));
        sprint(clc.bold(clc.red('Exiting...')));
        return;
    }

    // CLI options!
    let knownOpts =
        {
            'alias': Boolean,
            'archivesources': Boolean,
            'clearcache': Boolean,
            'clearlog': Boolean,
            'patchmanifest': Boolean,
            'db': Boolean,
            'dev': Boolean,
            'deploy': Boolean,
            'init': Boolean,
            'install': Boolean,
            'ions': Boolean,
            'icons': Boolean,
            'linkmodule': Boolean,
            'manifest': Boolean,
            'minify': Boolean,
            'moduleversion': Boolean,
            'npm': Boolean,
            'pack': Boolean,
            'packall': Boolean,
            'prod': Boolean,
            'prepare': Boolean,
            'patchios': Boolean,
            'patchpods': Boolean,
            'rebuild': Boolean,
            'rebuildall': Boolean,
            'prepall': Boolean,
            'syncmodule': Boolean,
            'type': Boolean,
            'test': Boolean,
            'unlinkmodule': Boolean,
            'version': Boolean,
            'exportdb': Boolean
        };

    let shortHands =
        {
            'a': '--alias',
            'cc': '--clearcache',
            'cl': '--clearlog',
            'edb': '--exportdb',
            'i': '--init',
            'ic': '--icons',
            'lm': '--linkmodule',
            'ulm': '--unlinkmodule',
            'r': '--rebuild',
            'sm': '--syncmodule',
            't': '--type',
            'mver': '--moduleversion',
            'v': '--version'
        };

    // If no inputArgs given, use process.argv!
    let localInputArgs = inputArgs || process.argv;

    // Adding '--' to args for Cli to identify them!
    if (localInputArgs.length > 2) {
        localInputArgs[2] = '--' + localInputArgs[2].replace('-', '');
    }

    let args = nopt(knownOpts, shortHands, localInputArgs);

    if (args.version) {
        sprint(siberian.version);
    } else {
        let COPY = false;
        let RESET = false;
        let EMPTY = false;
        let INSTALL = false;
        let EXTERNAL = false;
        let SKIP_REBUILD = false;

        let remain = args.argv.remain;

        // Searching for the option debug!
        remain.forEach(function (element) {
            switch (element) {
                case 'debug':
                    DEBUG = true;
                    break;
                case 'copy':
                    COPY = true;
                    break;
                case 'empty':
                    EMPTY = true;
                    break;
                case 'reset':
                    RESET = true;
                case 'skip-rebuild':
                    SKIP_REBUILD = true;
                    break;
                case 'install':
                    INSTALL = true;
                    break;
                case 'manifest':
                    REBUILD_MANIFEST = true;
                    break;
                case 'ext': case 'external':
                    EXTERNAL = true;
                 break;
                case 'build-debug':
                    BUILD_TYPE = '--debug';
                    break;
                case 'noads':
                    NO_ADS = true;
                    break;
            }
        });

        if (args.rebuild) {
            if (remain.length >= 1) {
                rebuild(remain[0], COPY , false);
            } else {
                sprint(clc.red('Missing required argument <platform>'));
            }
        } else if (args.prepare) {
            if (remain.length >= 1) {
                rebuild(remain[0], COPY, true, SKIP_REBUILD);
            } else {
                sprint(clc.red('Missing required argument <platform>'));
            }
        } else if (args.deploy) {
            deploy();
        } else if (args.patchpods) {
            if (remain.length >= 1) {
                patchPods(remain[0], COPY);
            } else {
                sprint(clc.red('Missing required argument <platform>'));
            }
        } else if (args.patchios) {
            if (remain.length >= 1) {
                patchIos(remain[0], COPY);
            } else {
                sprint(clc.red('Missing required argument <platform>'));
            }
        } else if (args.rebuildall) {
            // Rebuild prod files once!
            builder();
            platforms.forEach(function (platform) {
                rebuild(platform, COPY, false, true);
            });
            if (REBUILD_MANIFEST) {
                rebuildManifest();
            }
        } else if (args.prepall) {
            // Rebuild prod files once!
            REBUILD_MANIFEST = false;
            builder();
            platforms.forEach(function (platform) {
                rebuild(platform, COPY, true, true);
            });
            if (REBUILD_MANIFEST) {
                rebuildManifest();
            }
        } else if (args.ions) {
            ionicServe();
        } else if (args.patchmanifest) {
            patchAndroidManifest();
        } else if (args.archivesources) {
            archiveSources();
        } else if (args.alias) {
            aliasHelp();
        } else if (args.exportdb) {
            exportDb();
        } else if (args.syncmodule) {
            syncModule(EXTERNAL);
        } else if (args.manifest) {
            rebuildManifest();
        } else if (args.type) {
            let type = '';
            if (remain.length >= 1) {
                type = remain[0].toLowerCase();
            }
            switchType(type, RESET, EMPTY);
        } else if (args.prod) {
            setProd();
        } else if (args.dev) {
            setDev();
        } else if (args.db) {
            checkDb();
        } else if (args.init) {
            init();
        } else if (args.install) {
            install();
        } else if (args.pack) {
            let moduleName = null;

            if (remain.length >= 1) {
                moduleName = remain[0];
            }

            if (moduleName === null) {
                sprint(clc.red('Missing required argument <module_name>'));
            } else {
                pack(moduleName);
            }
        } else if (args.packall) {
            sprint(clc.red('Deprecated method packall.'));
        } else if (args.moduleversion) {
            let mverModuleName = '';
            if (remain.length >= 2) {
                mverModuleName = remain[1].toLowerCase();
            }
            mver(remain[0], mverModuleName);
        } else if (args.npm) {
            // Set production before npm
            setProd();

            let npmVersion = '';
            if (remain.length >= 1) {
                npmVersion = remain[0].toLowerCase();
            }

            // Ensure we are on root!
            sh.cd(ROOT);
            sh.exec('./bin/preversion');
            sh.exec('npm version --no-git-tag-version ' + npmVersion);
            sh.exec('./bin/postversion');
        } else if (args.linkmodule) {
            if (remain.length >= 1) {
                linkModule(remain[0].toLowerCase());
            } else {
                sprint(clc.red('Missing required argument <module_name>'));
            }
        } else if (args.unlinkmodule) {
            if (remain.length >= 1) {
                unlinkModule(remain[0].toLowerCase());
            } else {
                sprint(clc.red('Missing required argument <module_name>'));
            }
        } else if (args.icons) {
            icons(INSTALL);
        } else if (args.clearcache) {
            clearcache();
        } else if (args.clearlog) {
            clearlog();
        } else if (args.test) {
            if (remain.length >= 1) {
                PHP_VERSION = remain[0].toLowerCase();
            }
            test(PHP_VERSION);
        } else {
            printHelp();
        }
    }

    /** Exit on Exception */
    process.on('uncaughtException', function () {
        process.exit(1);
    });
};

let install = function () {
    sh.cd(ROOT);

    sh.cp('-r', './bin/config/plugman.js', './node_modules/cordova-lib/src/plugman/plugman.js');
    sh.cp('-r', './bin/config/main.js', './node_modules/plugman/main.js');

    // Configuring environment!
    sh.exec('git config core.fileMode false');

    sprint('Done.');
};

let deploy = function () {
    const developer = require(ROOT + '/developer.json');
    const host = developer.deploy.host;
    const path = developer.deploy.path;

    //sh.exec('rsync -avz --delete --exclude-from ' + ROOT + '/rsync_exclude.txt ./ ' + host + ':' + path + ';')
};

/**
 * Eport DB Schema to php schemes
 */
let exportDb = function () {
    sh.cd(ROOT + '/siberian');
    sh.exec('php -f cli export-database');
};

/**
 *
 */
let rebuildManifest = function () {
    sprint('Rebuilding app manifest.');
    const developer = require(ROOT + '/developer.json');
    const port = developer.config.port || 80;
    const requestDefaultHeaders = {
        'Authorization': 'Basic ' + new Buffer(developer.dummyEmail + ':' + developer.dummyPassword).toString('base64')
    };
    const protocol = (port === 80) ? 'http://' : 'https://';

    axios.get(protocol + developer.config.domain + '/backoffice/api_options/manifest', {
        responseType: 'json',
        headers: requestDefaultHeaders
    }).then(function (response) {
        if (response.data.success) {
            sprint(clc.green(response.data.message));
            notify('Rebuild manifest succeeded');
        } else {
            throw (new Error(response.data.message));
        }
    }).catch(function (error) {
        if (typeof error === 'object' && error.hasOwnProperty('message')) {
            sprint('Unexpected error: ' + clc.red(error.message));
            console.log(error);
        }
        sprint(clc.red('Catch: Manifest rebuild error, run `siberian init` to set your dummyEmail & dummyPassword.'));
        notify('Rebuild manifest FAILED.', {
            sound: 'Frog'
        });
    });
};

/**
 *
 */
let patchAndroidManifest = function () {
    let AndoidManifestFile = ROOT + '/ionic/platforms/android/app/src/main/AndroidManifest.xml';
    let AndroidManifestContent = fs.readFileSync(AndoidManifestFile, {
        encoding: 'utf8'
    });

    AndroidManifestContent = AndroidManifestContent.replace(
        /(\<activity\s+android\:configChanges)/,
        '<activity android:screenOrientation="unspecified" android:configChanges');
    sprint('Patching app/src/main/AndroidManifest.xml...');
    fs.writeFileSync(AndoidManifestFile, AndroidManifestContent, {
        encoding: 'utf8'
    });
};

/**
 *
 */
let builder = function () {
    sh.cd(ROOT + '/ionic/');
    sh.exec('node builder --prod');
};

/**
 *
 * @param platform
 * @param copy
 * @param prepare
 * @param skipRebuild
 */
let rebuild = function (platform, copy, prepare, skipRebuild) {
    let localPrepare = (prepare === undefined) ? false : prepare;
    let originalIndexContent = null;
    let indexFile = ROOT + '/ionic/www/index.html';

    try {
        originalIndexContent = fs.readFileSync(indexFile, {
            encoding: 'utf8'
        });

        let regexp = /\n?\t?\t?<script src="http:\/\/www.siberiancms.dev\/installer\/module\/getfeature\/[^"]+" data-feature="[^"]+"><\/script>\n?\t?/g;
        let indexContent = originalIndexContent.replace(regexp, '');

        if (indexContent === originalIndexContent) {
            originalIndexContent = null; // reset if unused
        } else {
            sprint('Unpatching ionic/www/index.html temporarily....');
            fs.writeFileSync(indexFile, indexContent, {
                encoding: 'utf8'
            });
            sprint('Unpatched!');
        }

        // Compile/pack/bundle files!
        if (!skipRebuild) {
            builder();
        }

        if (platform === 'android' ||
            platform === 'ios' ||
            platform === 'browser') {
            let silent = '--silent';
            if (DEBUG) {
                silent = '';
            }

            let platformFolder = platform.replace(/-([a-z])/g, function (c) {
                return c[1].toUpperCase();
            });
            platformFolder = platformFolder[0].toUpperCase() + platformFolder.substring(1);

            let platformPath = ROOT + '/platforms/' + platformFolder;
            let installPath = ROOT + '/ionic/platforms/' + platform;

            // Ensure the script is in the good directory Cordova is serious!
            sh.cd(ROOT + '/ionic/');

            if (localPrepare) {
                sprint(clc.blue('Prepping: ') + clc.green(platform + ' ...'));
                console.log('cordova ' + silent + ' prepare ' + platform);
                sh.exec('cordova ' + silent + ' prepare ' + platform);

                if (platform === 'android') {
                    patchAndroidManifest();
                }
            } else {
                sprint(clc.blue('Rebuilding: ') + clc.green(platform + ' ...'));

                // Delete only if not preparing!
                try {
                    console.log('cordova ' + silent + ' platform remove ' + platform + ' --nosave');
                    sh.exec('cordova ' + silent + ' platform remove ' + platform + ' --nosave');
                } catch (e) {
                    // nothing to do!
                }
                try {
                    sh.rm('-rf', ROOT + '/ionic/platforms/' + platform);
                } catch (e) {
                    // nothing to do!
                }

                try {
                    console.log('cordova ' + silent + ' platform add ' + platformPath + ' --nosave');
                    sh.exec('cordova ' + silent + ' platform add ' + platformPath + ' --nosave');
                } catch (e) {
                    console.log(e.message);
                    console.log('aborting');
                    process.exit(1);
                }

                // tmp object for the rebuild all, otherwise this will extends upon each platform!
                let localPlugins = require(ROOT + '/ionic/plugins.json');
                let tmpPlugins = localPlugins.default;
                let platformPlugins = localPlugins[platform];
                let requiredPlugins = Object.assign(tmpPlugins, platformPlugins);

                Object.keys(requiredPlugins).forEach(function (pluginName) {
                    installPlugin(pluginName, platform, requiredPlugins[pluginName]);
                });

                switch (platform) {
                    case 'android':
                        sh.cp('-f', installPath + '/app/src/main/res/xml/config.xml', installPath + '/config.bck.xml');
                        break;
                    case 'ios':
                        sh.cp('-f', installPath + '/AppsMobileCompany/config.xml', installPath + '/config.bck.xml');
                        break;
                }

                let type = BUILD_TYPE;

                if (platform === 'ios') {
                    // Install cocoapods
                    sh.cd(ROOT + '/ionic/platforms/' + platform);
                    sh.exec('pod deintegrate --verbose');
                    sh.exec('pod install --verbose');
                    sh.exec('pod update --verbose');
                    sh.cd(ROOT + '/ionic/');

                    // Ios specific, run push.rb to patch push notifications!
                    if (!prepare) {
                        patchPods(platform);
                    }
                }

                if (platform === 'android') {
                    // Edit AndroidManifest.xml
                    // android:screenOrientation="unspecified" > <activity

                    var gradleArgs = '';
                    //var gradleArgs = 'cdvBuildDebug -x=:app:processDebugGoogleServices';
                    //if (type === '--release') {
                    //    gradleArgs = 'cdvBuildRelease -x=:app:processReleaseGoogleServices';
                    //}

                    // org.gradle.jvmargs=-Xmx1536M
                    var cordovaGradleArgs = '';
                    //var cordovaGradleArgs = '--debug --gradleArg=-x=:app:processDebugGoogleServices';
                    //if (type === '--release') {
                    //    cordovaGradleArgs = '--debug --gradleArg=-x=:app:processReleaseGoogleServices';
                    //}

                    sprint('cordova ' + silent + ' build ' + type + ' ' + platform + ' -- ' + cordovaGradleArgs);
                    sh.exec('cordova ' + silent + ' build ' + type + ' ' + platform + ' -- ' + cordovaGradleArgs);

                    patchAndroidManifest();

                } else {
                    sprint('cordova ' + silent + ' build ' + type + ' ' + platform + ' -- ' + gradleArgs);
                    sh.exec('cordova ' + silent + ' build ' + type + ' ' + platform + ' -- ' + gradleArgs);
                }
            }

            // Replace <!-- #PREVIEWER# --> with the correct tag
            patchPreviewer(platform);

            // Ios specific, run push.rb to patch push notifications!
            if (!prepare) {
                patchIos(platform);
            }

            // Cleaning up build files!
            if (copy) {
                copyPlatform(platform);
            }

            sprint(clc.green('Done.'));
        } else {
            throw new Error('Unknown platform ' + platform);
        }
    } catch (e) {
        throw e; // Dont mess with me!
    } finally { // Yes I checked, finally are executed even if exception is re-thrown!
        if (originalIndexContent !== null) {
            sprint('Repatching ionic/www/index.html....');
            fs.writeFileSync(indexFile, originalIndexContent, { encoding: 'utf8' });
            sprint('Repatched!');
            originalIndexContent = null;
        }
    }
};

let patchPods = function (platform) {
    sh.cd(ROOT + '/bin/scripts/');
    if (platform === 'ios') {
        sprint(clc.green('Patching platform project for Pods ...'));
        sh.exec('./PatchPod ' + ROOT + '/ionic/platforms/' + platform + '/');
    }
    sh.cd(ROOT + '/ionic/');
};

let patchIos = function (platform) {
    sh.cd(ROOT + '/bin/scripts/');
    if (platform === 'ios') {
        sprint(clc.green('Patching platform project for Push entitlements ...'));
        sh.exec('./Patch ' + ROOT + '/ionic/platforms/' + platform + '/');
    }
    sh.cd(ROOT + '/ionic/');
};

// Patching index.html for the previewer tmpFile!
let patchPreviewer = function (platform) {
    sprint(clc.green('Patching platform project for Previewer ...'));

    let indexFile = null;
    let indexContent = null;

    switch (platform) {
        case "browser":
            sprint(clc.green('[Browser]'));
            indexFile = ROOT + "/ionic/platforms/browser/www/index.html";
            indexContent = fs.readFileSync(indexFile, {
                encoding: 'utf8'
            });

            indexContent = indexContent.replace(
                "<!-- #PREVIEWER# -->",
                "");

            break;
        case "android":
            sprint(clc.green('[Android]'));
            indexFile = ROOT + "/ionic/platforms/android/app/src/main/assets/www/index.html";
            indexContent = fs.readFileSync(indexFile, {
                encoding: 'utf8'
            });

            indexContent = indexContent.replace(
                "<!-- #PREVIEWER# -->",
                "<!-- Ensure file will never be cached. -->\n" +
                "<script type=\"text/javascript\">\n" +
                "    var cdvModulePath = localStorage.getItem('shared-cdv-module-path');\n" +
                "    if (cdvModulePath !== null) {\n" +
                "        document.write('<script src=\"' + cdvModulePath + '?t=' + Date.now() + '\"><\\/script>');\n" +
                "    }\n" +
                "</script>");

            break;
        case "ios":
            sprint(clc.green('[iOS]'));
            indexFile = ROOT + "/ionic/platforms/ios/www/index.html";
            indexContent = fs.readFileSync(indexFile, {
                encoding: 'utf8'
            });

            indexContent = indexContent.replace(
                "<!-- #PREVIEWER# -->",
                "<!-- Ensure file will never be cached. -->\n" +
                "<script type=\"text/javascript\">\n" +
                "    var cdvModulePath = localStorage.getItem('shared-cdv-module-path');\n" +
                "    if (cdvModulePath !== null) {\n" +
                "        document.write('<script src=\"' + cdvModulePath + '?t=' + Date.now() + '\"><\\/script>');\n" +
                "    }\n" +
                "</script>");

            break;
    }

    fs.writeFileSync(indexFile, indexContent, {
        encoding: 'utf8'
    });

    sprint(clc.green('Patching done.'));
};

/**
 *
 * @param pluginName
 * @param platform
 * @param opts
 */
let installPlugin = function (pluginName, platform, opts) {
    if (NO_ADS && ['Admob', 'Mediation'].indexOf(pluginName) !== -1) {
        sprint(clc.blue('Excluding (NO_ADS): ') + clc.red(pluginName + '...'));
        return;
    }

    let platformBase = platform,
        platformPath = ROOT + '/ionic/platforms/' + platform,
        pluginPath = ROOT + '/plugins/' + pluginName,
        pluginVariables = opts.variables || '';

    // Read plugin platforms!
    let pluginConfigPath = ROOT + '/plugins/' + pluginName + '/package.json';
    let pluginConfig = false;
    if (fs.existsSync(pluginConfigPath)) {
        pluginConfig = require(pluginConfigPath);
    }

    // Skip plugin (from default) if not targeted by the package.json!
    let skipPlugin = true;
    if (pluginConfig !== false) {
        Object.keys(pluginConfig.cordova.platforms).forEach(function (objectPlatform) {
            if (platformBase === pluginConfig.cordova.platforms[objectPlatform]) {
                skipPlugin = false;
            }
        });
    }

    if (skipPlugin) {
        msg = clc.xterm(253);
        sprint(msg('Skipping: ' + pluginName + '...'));
    } else {
        sprint(clc.blue('Installing: ') + clc.red(pluginName + '...'));

        let cliVariables = '';
        Object.keys(pluginVariables).forEach(function (variable) {
            let key = variable;
            let value = pluginVariables[variable];

            cliVariables = cliVariables + ' --variable ' + key + '=' + value;
        });

        let silent = '--silent';
        if (DEBUG) {
            silent = '';
        }

        console.log('plugman install --platform ' + platformBase +
            ' --project ' + platformPath + ' ' + silent + ' --plugin ' + pluginPath + ' ' + cliVariables);
        sh.exec('plugman install --platform ' + platformBase +
            ' --project ' + platformPath + ' ' + silent + ' --plugin ' + pluginPath + ' ' + cliVariables);
    }
};

/**
 * Alias to build icons, some external packages are required to build however
 * see specific platform documentation about building icons.
 */
let icons = function (INSTALL) {
    sprint('ionicons start!');
    if (INSTALL) {
        if (process.platform === 'darwin') {
            sh.exec('brew install fontforge ttfautohint');
            sh.exec('sudo gem install sass');
        }
    }

    sh.exec("php -f " + ROOT + "/resources/ionicons/builder/custom-png-to-svg.php");

    sh.exec('chmod +x ' + ROOT + '/resources/ionicons/builder/scripts/sfnt2woff');
    sh.exec('python ' + ROOT + '/resources/ionicons/builder/generate.py');

    // Copy inside Siberian
    sh.cp('-rf', ROOT + '/resources/ionicons/fonts',
        ROOT + '/siberian/app/sae/design/desktop/flat/css/webfonts/ionicons/');
    sh.cp('-rf', ROOT + '/resources/ionicons/css',
        ROOT + '/siberian/app/sae/design/desktop/flat/css/webfonts/ionicons/');

    // Copy inside Ionic!
    sh.cp('-rf', ROOT + '/resources/ionicons/fonts/*',
        ROOT + '/ionic/www/lib/ionic/scss/ionicons/');
    sh.cp('-rf', ROOT + '/resources/ionicons/fonts/*',
        ROOT + '/ionic/www/lib/ionic/fonts/');
    sh.cp('-rf', ROOT + '/resources/ionicons/scss/*',
        ROOT + '/ionic/www/lib/ionic/scss/ionicons/');

    // Rebuild Ionic SCSS
    sh.cd(ROOT + '/ionic/');
    sh.exec('node builder.js --sass');
    sh.exec('node builder.js --bundlecss');

    sprint('ionicons rebuild done!');
};

/**
 *
 * @param platform
 */
let copyPlatform = function (platform) {
    sprint('Copying ' + platform + ' ...');

    let ionicPlatformPath = ROOT + '/ionic/platforms/' + platform,
        siberianPlatformBasePath = ROOT + '/siberian/var/apps/ionic/',
        siberianPlatformPath = siberianPlatformBasePath + platform;

    switch (platform) {
        case 'android':
            sh.rm('-rf', ionicPlatformPath + '/app/build');
            sh.rm('-rf', ionicPlatformPath + '/CordovaLib/build');
            sh.rm('-rf', ionicPlatformPath + '/cordova/plugins');
            sh.rm('-rf', ionicPlatformPath + '/assets/www/modules/*');

            // Clean-up!
            sh.rm('-rf', siberianPlatformPath);

            // Copy with rsync to preserve dot files!
            sh.exec('rsync -arv ' + ionicPlatformPath + ' ' + siberianPlatformBasePath);
            sh.rm('-rf', siberianPlatformPath + '/platform_www');
            cleanupWww(siberianPlatformPath + '/assets/www/');

            break;

        case 'browser':
            // Different path for Browser!
            siberianPlatformPath = siberianPlatformPath.replace('ionic/', '');
            ionicPlatformPath = ionicPlatformPath + '/www';
            sh.rm('-rf', ionicPlatformPath + '/modules/*');

            // Clean-up!
            sh.rm('-rf', siberianPlatformPath);
            sh.rm('-rf', siberianPlatformPath.replace('browser', 'overview'));

            // Copy!
            sh.cp('-r', ionicPlatformPath, siberianPlatformPath);
            sh.mkdir('-p', siberianPlatformPath + '/scss/');
            sh.cp('-r', ROOT + '/ionic/scss/ionic.siberian*.scss', siberianPlatformPath + '/scss/');
            cleanupWww(siberianPlatformPath + '/', true);

            // Duplicate in 'overview'!
            sh.cp('-r', siberianPlatformPath, siberianPlatformPath.replace('browser', 'overview'));

            // Apply overview patch
            patchOverview(siberianPlatformPath.replace("browser", "overview"));

            break;

        case 'ios':
            if (NO_ADS === true) {
                siberianPlatformPath = siberianPlatformPath.replace(/ios/, 'ios-noads');
            }
            sh.rm('-rf', ionicPlatformPath + '/build');
            sh.rm('-rf', ionicPlatformPath + '/CordovaLib/build');
            sh.rm('-rf', ionicPlatformPath + '/cordova/plugins');
            sh.rm('-rf', ionicPlatformPath + '/www/modules/*');

            // Clean-up!
            sh.rm('-rf', siberianPlatformPath);

            // Copy!
            sh.cp('-r', ionicPlatformPath + '/', siberianPlatformPath);
            sh.rm('-rf', siberianPlatformPath + '/platform_www');
            cleanupWww(siberianPlatformPath + '/www/');

        break;
    }

    sprint('Copy done');
};

let patchOverview = function (overviewPath) {
    sprint("===");
    sprint("Patching overview/index.html");

    let indexFile = overviewPath + "/index.html",
        indexContent = fs.readFileSync(indexFile, { encoding: "utf8" });

    indexContent = indexContent.replace("platform-browser", "platform-overview");

    fs.writeFileSync(indexFile, indexContent, { encoding: "utf8" });

    sprint("Patched!");
    sprint("===");
};

/**
 *
 * @param basePath
 * @param browser skip scss removal if browser
 */
let cleanupWww = function (basePath, browser) {
    let filesToRemove = [
        'img/ionic.png',
        'css/ionic.app.css',
        'lib/ionic/css/ionic.css',
        'lib/ionic/js/ionic.js',
        'lib/ionic/js/ionic.bundle.js',
        'lib/ionic/js/ionic-angular.js',
        'lib/ionic/js/angular/angular.js',
        'lib/ionic/js/angular/angular-animate.js',
        'lib/ionic/js/angular/angular-resource.js',
        'lib/ionic/js/angular/angular-sanitize.js',
        'lib/ionic/js/angular-ui/angular-ui-router.js',
        'css',
        'js/controllers',
        'js/directives',
        'js/factory',
        'js/providers',
        'js/services',
        'js/features',
        'js/filters',
        'js/libraries',
        'js/app.js',
        'js/utils/features.js',
        'js/utils/form-post.js'
    ];

    if (browser !== true) {
        filesToRemove.push('lib/ionic/scss');
    }

    Object.keys(filesToRemove)
        .forEach(function (key) {
            sh.rm('-rf', basePath + filesToRemove[key]);
        });
};

/**
 *
 */
let test = function () {
    sprint(clc.green('Running PHP syntax test, for version: ' + PHP_VERSION + ''));

    sh.exec('find ' + ROOT + '/siberian/app -name \'*.php\' -exec ' +
        PHP_VERSION + ' -l {} \\; | grep -v \'No syntax errors detected\'');
    sh.exec('find ' + ROOT + '/siberian/lib -name \'*.php\' -exec ' +
        PHP_VERSION + ' -l {} \\; | grep -v \'No syntax errors detected\'');

    sprint(clc.green('Test done.'));
};

/**
 *
 */
let ionicServe = function () {
    sprint(clc.blue('Starting ionic server & node builder --watch in background screen, use \'screen -r ions\' and/or \'screen -r watch\' ' +
        'to attach & \'ctrl+a then d\' to detach ...'));

    /** Ensure the script is in the good directory Cordova is serious ... */
    sh.cd(ROOT + '/ionic/');
    sh.exec('if screen -ls ions | grep -q .ions; then echo \'ionic server already running\'; ' +
        'else screen -dmS ions ionic serve -a; fi');

    sh.exec('if screen -ls watch | grep -q .ions; then echo \'node builder --watch already running\'; ' +
        'else screen -dmS watch node builder --watch; fi');
};

/** Sync git submodules */
let syncModule = function (external) {
    sh.cd(ROOT);

    let submodules = require(ROOT + '/submodules.json'),
        localPlugins = submodules.plugins,
        localPlatforms = submodules.platforms;

    Object.keys(localPlugins).forEach(function (key) {
        let plugin = localPlugins[key];
        let gitUrl = plugin.git;
        let gitBranch = plugin.branch;

        createOrSyncGit(ROOT + '/plugins/' + key, gitUrl, gitBranch);
    });

    Object.keys(localPlatforms).forEach(function (key) {
        let platform = localPlatforms[key];
        let gitUrl = platform.git;
        let gitBranch = platform.branch;

        createOrSyncGit(ROOT + '/platforms/' + key, gitUrl, gitBranch);
    });
};

/**
 *
 * @param gitPath
 * @param url
 * @param branch
 */
let createOrSyncGit = function (gitPath, url, branch) {
    sprint(clc.blue('Git sync: ') + clc.red(url + '@' + branch));
    if (fs.existsSync(gitPath)) {
        sh.cd(gitPath);
        sh.exec('git fetch', { silent: false });
        let localStatus;
        try {
            localStatus = sh.exec('git status', { silent: true }).output.trim();
        } catch (e) {
            localStatus = '';
        }

        if (localStatus.indexOf('branch is up-to-date') === -1) {
            sh.exec('git checkout ' + branch);
            sh.exec('git pull origin ' + branch);
        } else {
            sh.exec('git config core.fileMode false');
            sh.exec('git status')
        }
    } else {
        sh.exec('git clone -b ' + branch + ' ' + url + ' ' + gitPath);
    }

    let revision = sh.exec('git rev-parse HEAD^0', { silent: true }).stdout.trim();
    sprint('Up-to-date (' + revision + ')');
};

/**
 * Switching from sae/mae/pe
 *
 * @param type
 * @param reinstall
 * @param emptydb
 */
let switchType = function (type, reinstall, emptydb) {
    let versionTplPath = ROOT + '/bin/templates/Version.php',
        versionPath = ROOT + '/siberian/lib/Siberian/Version.php',
        iniPath = ROOT + '/siberian/app/configs/app.ini',
        developer = require(ROOT + '/developer.json'),
        tmpPath = '/tmp/sbtype';

    if (type === '' || type === 'minimal') {
        let currentVersion = fs.readFileSync(versionPath, 'utf8');
        let currentEdition = currentVersion.match(/const TYPE = '([a-z])+';/gi);
        currentEdition = currentEdition[0].replace(/(const TYPE = '|';)/g, '');

        if (type === 'minimal') {
            sprint(currentEdition);
        } else {
            sprint(clc.red('You are currently working on: ') + clc.blue(currentEdition));
        }

        return;
    }

    if ((type !== 'pe') && (type !== 'mae') && (type !== 'sae')) {
        sprint(clc.red('Error: bad type \'' + type + '\''));
        return;
    }

    let version = fs.readFileSync(versionTplPath, 'utf8');
    version = version
        .replace('%TYPE%', type.toUpperCase())
        .replace('%NAME%', 'Development')
        .replace('%VERSION%', siberian.version)
        .replace('%NATIVE_VERSION%', siberian.nativeVersion);

    fs.writeFileSync(versionPath, version, 'utf8');

    if (!fs.existsSync(iniPath)) {
        // Copying app.sample.ini to app.ini if not initialized!
        sh.cp(iniPath.replace('app.ini', 'app.sample.ini'), iniPath);
    }

    let appIni = fs.readFileSync(iniPath, 'utf8');
    appIni = appIni.replace(/dbname = ('|")(.*)('|")/, 'dbname = "' +
        developer.mysql.databasePrefix+type.toLowerCase() + '"');

    // Reset the isInstalled var.!
    if (reinstall) {
        appIni = appIni.replace(/isInstalled = ('|")(.*)('|")/, 'isInstalled = "0"');
    }

    // Empty database!
    if (emptydb) {
        let mysqlUsername = developer.mysql.username;
        let mysqlPassword = developer.mysql.password;
        let mysqlDatabasePrefix = developer.mysql.databasePrefix;

        sh.exec('mysql -u ' + mysqlUsername +
            ' -p' + mysqlPassword +
            ' -e \'DROP DATABASE ' + mysqlDatabasePrefix + type.toLowerCase() + '; ' +
            'CREATE DATABASE ' + mysqlDatabasePrefix + type.toLowerCase() + ';\'');
    }

    fs.writeFileSync(iniPath, appIni, 'utf8');
    fs.writeFileSync(tmpPath, type.toLowerCase() + ' ', 'utf8');

    // Clearing out css cache (avoiding same app id across editions to not rebuild css & load bad colors)!
    clearcache();

    sprint(clc.red('You are now working on: ') + clc.blue(type.toUpperCase()));
};

/**
 * Switch environment to development
 */
let setDev = function () {
    sprint(clc.red('Switched environment to development'));
    sh.rm('-f', ROOT + '/siberian/config.php');
    sh.cp(ROOT + '/bin/templates/config_dev.php', ROOT + '/siberian/config.php');
};

/**
 * Switch environment to production
 */
let setProd = function () {
    sprint(clc.blue('Switched environment to production'));
    sh.rm('-f', ROOT + '/siberian/config.php');
    sh.cp(ROOT + '/bin/templates/config_prod.php', ROOT + '/siberian/config.php');
};

/**
 * Clear application cache
 */
let clearcache = function () {
    let cachePath = ROOT + '/siberian/var/cache/*';

    sh.rm('-rf', ROOT + '/siberian/var/cache/*');
    sh.rm('-f', ROOT + '/siberian/app/local/design/design-cache.json');

    sprint(clc.blue('Cache has been cleared.'));
};

/**
 * Clear application logs
 */
let clearlog = function () {
    let logPath = ROOT+'/siberian/var/log/*';

    sh.rm('-rf', logPath);

    sprint(clc.blue('Logs have been cleared.'));
};

/**
 * Fills the developer.json local configuration for developments
 */
let init = function () {
    sprint(clc.bold(clc.green('\n\nPlease fill the required informations to process the ' +
        'SiberianCMS initialization: ')));

    let developerPath = ROOT + '/developer.json',
        serverType = 'apache',
        domain = 'siberian.local';

    if (fs.existsSync(developerPath)) {
        sprint(clc.bold(clc.red('Warning: you are about to erase your local developer.json file. ' +
            'Ctrl-C to exit.')));
    }

    // Default dummy values!
    let developer = {
        name: 'Siberian Admin',
        email: 'developer@localhost',
        dummyEmail: 'developer@localhost',
        dummyPassword: 'dummy',
        config: {
            domain: 'siberian.local',
            serverType: 'apache'
        },
        mysql: {
            host: 'localhost',
            username: 'dumb',
            password: 'dumber',
            databasePrefix: 'siberiancms_'
        }
    };

    if (fs.existsSync(developerPath)) {
        let localDev = require(developerPath);
        // Use local developer.json as default values if existing!
        developer = Object.assign(developer, localDev);
    }

    // Really annoying!
    prompt('Name', developer.name, function (name) {
        prompt('E-mail', developer.email, function (email) {
            prompt('Dummy email for app login', developer.dummyEmail, function (dummyEmail) {
                prompt('Dummy password for app login', developer.dummyPassword, function (dummyPassword) {
                    prompt('Domain', developer.config.domain, function (configDomain) {
                        prompt('Server type nginx|apache', developer.config.server_type, function (configServerType) {
                            prompt('Mysql Hostname', developer.mysql.host, function (mysqlHost) {
                                prompt('Mysql Username', developer.mysql.username, function (mysqlUsername) {
                                    prompt('Mysql Password', developer.mysql.password, function (mysqlPassword) {
                                        prompt('Mysql Database prefix', developer.mysql.databasePrefix, function (mysqlDatabasePrefix) {

                                            let newDeveloper = {
                                                name: name,
                                                email: email,
                                                dummyEmail: dummyEmail,
                                                dummyPassword: dummyPassword,
                                                config: {
                                                    domain: configDomain,
                                                    serverType: configServerType
                                                },
                                                mysql: {
                                                    host: mysqlHost,
                                                    username: mysqlUsername,
                                                    password: mysqlPassword,
                                                    databasePrefix: mysqlDatabasePrefix
                                                }
                                            };

                                            serverType = configServerType;
                                            domain = configDomain;

                                            fs.writeFileSync(developerPath, JSON.stringify(newDeveloper), 'utf8');

                                            sh.rm('-f', ROOT + '/apache.default');
                                            sh.rm('-f', ROOT + '/nginx.default');

                                            let serverConfigPath = ROOT + '/bin/templates/' + serverType + '.default';
                                            let serverConfigLocalPath = ROOT + '/' + serverType + '.default';

                                            let serverConfig = fs.readFileSync(serverConfigPath, 'utf8');
                                            serverConfig = serverConfig
                                                .replace(/%PATH%/gm, ROOT)
                                                .replace(/%DOMAIN%/gm, domain)
                                            ;
                                            fs.writeFileSync(serverConfigLocalPath, serverConfig, 'utf8');

                                            sh.exec('mysql -u ' + mysqlUsername +
                                                ' -p' + mysqlPassword +
                                                ' -e \'CREATE DATABASE IF NOT EXISTS ' +
                                                mysqlDatabasePrefix + 'sae; ' +
                                                'CREATE DATABASE IF NOT EXISTS ' +
                                                mysqlDatabasePrefix + 'mae; ' +
                                                'CREATE DATABASE IF NOT EXISTS ' +
                                                mysqlDatabasePrefix + 'pe;\'');

                                            // Asking for git config sync!
                                            prompt('Would you like to update your local ' +
                                                'git config with user.name & user.email (Y/n) ?', 'n',
                                                function (answerGit) {
                                                    if (answerGit === 'Y') {
                                                        sh.exec('git config --unset-all user.name');
                                                        sh.exec('git config user.name "' + name + '"');
                                                        sh.exec('git config --unset-all user.email');
                                                        sh.exec('git config user.email "' + email + '"');
                                                    }

                                                    sprint(clc.green('\nThank you, your developer.json file is ready'));
                                                    sprint(clc.green('\nYou can find your ' +
                                                        serverType + ' configuration file here: ' +
                                                        ROOT + '/' + serverType + '.default'));

                                                    process.exit();
                                                });
                                        });
                                    });
                                });
                            });
                        });
                    });
                });
            });
        });
    });
};

/**
 * Check and/or init all three dbs
 */
let checkDb = function () {
    let developerPath = ROOT + '/developer.json';
    if (fs.existsSync(developerPath)) {
        let localDev = require(developerPath);
        let mysql = localDev.mysql;

        sh.exec('mysql -u ' + mysql.username +
            ' -p' + mysql.password +
            ' -e \'CREATE DATABASE IF NOT EXISTS ' + mysql.databasePrefix + 'sae; ' +
            'CREATE DATABASE IF NOT EXISTS ' + mysql.databasePrefix + 'mae; ' +
            'CREATE DATABASE IF NOT EXISTS ' + mysql.databasePrefix + 'pe;\'');
    } else {
        init();
    }
};

/**
 *
 * @param modulePath
 * @returns {Array}
 */
let getFeatures = function (modulePath) {
    let finalFeatures = [];
    let featuresDir = modulePath + '/resources/features';
    if (fs.existsSync(featuresDir)) {
        let features = fs.readdirSync(featuresDir).map(function (f) {
            return path.join(featuresDir, f);
        }).filter(function (f) {
            return fs.statSync(f).isDirectory();
        });

        features.forEach(function (featureDir) {
            let featJsonPath = path.join(featuresDir, 'feature.json');
            if (fs.existsSync(featJsonPath)) {
                let featJson = require(featJsonPath);
                if ((typeof featJson === 'object') && (featJson !== null)) {
                    featJson.__DIR__ = featureDir;
                    featJson.__FILE__ = featJsonPath;
                    if (Array.isArray(featJson.files)) {
                        let invalid = false;
                        ['name', 'code', 'model', 'desktop_uri', 'routes', 'icons'].forEach(function (k) {
                            if (!featJson.hasOwnProperty(k)) {
                                invalid = false;
                            }
                        });

                        if (!invalid) {
                            invalid = featJson.routes.reduce(function (carry, el) {
                                return (el.root === true);
                            }, false);
                            if (!invalid) {
                                finalFeatures.push(featJson);
                            }
                        }
                    }
                }
            }
        });
    }

    return finalFeatures;
};

/**
 * Links an external module for dev purposes
 *
 * @param module
 * @returns {boolean}
 */
let linkModule = function (module) {
    // ModulePath Siberian!
    let modFolder = 'modules',
        modulePath = ROOT + '/' + modFolder + '/' + module;

    if (!fs.existsSync(ROOT + '/modules/' + module + '/package.json')) {
        sprint(clc.red('Module \'' + module + '\' has no package.json.'));

        // Search in external modules!
        modFolder = 'ext-modules';
        modulePath = ROOT + '/' + modFolder + '/' + module;
        if (!fs.existsSync(ROOT + '/' + modFolder + '/' + module + '/package.json')) {
            sprint(clc.red('External module \'' + module + '\' has no package.json.'));

            return false;
        }
    }

    let modulePackage = require(ROOT + '/' + modFolder + '/' + module + '/package.json'),
        moduleLinkName = modulePackage.name,
        moduleLinkPath = ROOT + '/siberian/app/local/modules/' + moduleLinkName,
        moduleLinkPathShort = 'app/local/modules/' + moduleLinkName;

    if (!fs.existsSync(modulePath)) {
        sprint(clc.red('Module \'' + module + '\' doesn\'t exists.'));
        return false;
    }

    if (fs.existsSync(moduleLinkPath)) {
        sprint(clc.blue('Module \'' + module + '\' is already linked.'));
        return true;
    }

    if (fs.existsSync(modulePath) && !fs.existsSync(moduleLinkPath)) {
        sh.ln('-s', modulePath, moduleLinkPath);

        if (isObject(modulePackage.assets) && modulePackage.assets.path) {
            let assetsPath = modulePath + '/' + modulePackage.assets.path,
                ionicAssetsPath = ROOT + '/ionic/www/modules/' + modulePackage.assets.folder,
                ionicAssetsPathShort = 'ionic/www/modules/' + modulePackage.assets.folder;

            sh.ln('-s', assetsPath, ionicAssetsPath);

            sprint('Linked ' + module + ' to ' +
                clc.green(moduleLinkPathShort) + ' & ' + clc.green(ionicAssetsPathShort));
        } else {
            sprint('Linked ' + module + ' to ' + clc.green(moduleLinkPathShort));
        }

        // Look for features!
        sprint('');
        sprint('Looking for features... ');
        let features = getFeatures(modulePath);
        features.forEach(function (featJson) {
            sprint('===');
            sprint('Found ' + featJson.name + ' feature. Linking.');
            sprint('===');

            let featureDir = featJson.__DIR__,
                ionicFeaturesDir = ROOT + '/ionic/www/features',
                featureDestDir = ionicFeaturesDir + '/' + path.basename(featureDir);

            if (!fs.existsSync(ionicFeaturesDir)) {
                fs.mkdirSync(ionicFeaturesDir);
            }

            sh.ln('-s', featureDir, featureDestDir);

            sprint('Linked to ' + featureDestDir.replace(ROOT+'/', ''));
            sprint('Patching ionic/www/index.html');

            let indexFile = ROOT + '/ionic/www/index.html',
                indexContent = fs.readFileSync(indexFile, { encoding: 'utf8' });

            indexContent = indexContent.replace('</head>',
                '\n\t\t<script src=\'http://www.siberiancms.dev/installer/module/getfeature/mod/' +
                modulePackage.name + '/feat/' +
                featJson.code + '\' data-feature=\'' + featJson.code + '\'></script>\n\t</head>');

            fs.writeFileSync(indexFile, indexContent, { encoding: 'utf8' });

            sprint('Patched!');
            sprint('===');
        });
        if (features.length > 0) {
            rebuildManifest();
        }
    } else {
        sprint(clc.red('Something went wront, please check if the module exists, or unlink it before.'));
        return false;
    }

    return true;
};

/**
 * Unlink an external module for dev purposes
 *
 * @param module
 * @returns {boolean}
 */
let unlinkModule = function (module) {
    let modFolder = 'modules';
    let modulePath = ROOT + '/' + modFolder + '/' + module;

    if (!fs.existsSync(ROOT + '/' + modFolder + '/' + module + '/package.json')) {
        sprint(clc.red('Module \'' + module + '\' has no package.json.'));

        // Search in external modules!
        modFolder = 'ext-modules';
        modulePath = ROOT + '/' + modFolder + '/' + module;
        if (!fs.existsSync(ROOT + '/' + modFolder + '/' + module + '/package.json')) {
            sprint(clc.red('External module \'' + module + '\' has no package.json.'));

            return false;
        }
    }

    let modulePackage = require(ROOT + '/' + modFolder + '/' + module + '/package.json'),
        moduleLinkName = modulePackage.name,
        moduleLinkPath = ROOT + '/siberian/app/local/modules/'+moduleLinkName,
        moduleLinkPathShort = 'app/local/modules/'+moduleLinkName,
        ionicAssetsPath = isObject(modulePackage.assets) ?
        ROOT + '/ionic/www/modules/'+modulePackage.assets.folder : null,
        ionicAssetsPathShort = isObject(modulePackage.assets) ?
        'ionic/www/modules/' + modulePackage.assets.folder : null;

    if (!fs.existsSync(modulePath)) {
        sprint(clc.red('Module \'' + module + '\' doesn\'t exists.'));
        return false;
    }

    if (fs.existsSync(modulePath) && fs.existsSync(moduleLinkPath)) {
        sh.rm('-f', moduleLinkPath);
        let unliked = 'Unlinked ' + module + ' from ' + clc.green(moduleLinkPathShort);

        if (ionicAssetsPath && fs.existsSync(ionicAssetsPath)) {
            sh.rm('-f', ionicAssetsPath);
            unliked = unliked + ' & ' + clc.green(ionicAssetsPathShort);
        }

        sprint(unliked);
        sprint('');
        sprint('Looking for features... ');

        let features = getFeatures(modulePath);
        features.forEach(function (featJson) {
            sprint('===');
            sprint('Found ' + featJson.name + ' feature. Unlinking.');
            sprint('===');

            let featureDir = featJson.__DIR__,
                ionicFeaturesDir = ROOT + '/ionic/www/features',
                featureDestDir = ionicFeaturesDir + '/' + path.basename(featureDir);

            if (!fs.existsSync(ionicFeaturesDir)) {
                fs.mkdirSync(ionicFeaturesDir);
            }

            sh.rm('-f', featureDestDir);

            sprint('Unlinked '+featureDestDir.replace(ROOT+'/', ''));
            sprint('Unpatching ionic/www/index.html');

            let indexFile = ROOT + '/ionic/www/index.html',
                indexContent = fs.readFileSync(indexFile, { encoding: 'utf8' });

            indexContent = indexContent.replace(
                new RegExp(
                    '\n?\t*<script[^<]+data-feature=\'' + featJson.code + '\'></script>\n?\t*',
                    'g'
                ),
                ''
            ).replace(
                new RegExp(
                    '\n?\t*<link[^<]+data-feature=\'' + featJson.code + '\'>\n?\t*',
                    'g'
                ),
                ''
            );

            fs.writeFileSync(indexFile, indexContent, { encoding: 'utf8' });

            sprint('Unpatched!');
            sprint('===');
        });
        if (features.length > 0) {
            rebuildManifest();
        }
    } else {
        sprint('Nothing to do.');
    }

    return true;
};

/**
 * Packing any module to installable zip archive
 *
 * @param module
 */
let pack = function (module) {
    let zipExclude = '--exclude=*.DS_Store* --exclude=*.idea* --exclude=*.git*',
        modulePath = ROOT + '/modules/' + module;

    if (!fs.existsSync(modulePath)) {
        sprint(clc.red('Module `' + module + '` doesn\'t exists.'));
        return;
    }

    let modulePackage = require(modulePath+'/package.json');
    let version = modulePackage.version;
    let buildPath = ROOT + '/packages/modules/';
    let zipName = modulePackage.name.toLowerCase() + '-' + version + '.zip';

    // Case when tools/pack.sh exists!
    if (fs.existsSync(modulePath + '/tools/pack.sh')) {
        sprint(clc.blue('Building ' + module + ' version with tools/pack.sh: ' + version));
        sh.cd(modulePath);
        sh.exec('./tools/pack.sh');
        sh.cp('-f', './*.zip', buildPath);
        sprint(clc.green('Package done. ' + buildPath + zipName));
    } else {
        // Otherwise with integrated tool
        sprint(clc.blue('Building ' + module + ' version: ' + version));

        // Zip the Module!
        sh.cd(modulePath);
        sh.rm('-f', buildPath + zipName);
        sprint('zip -r -9 ' + zipExclude + ' ' + buildPath + zipName + ' ./');
        sh.exec('zip -r -9 ' + zipExclude + ' ' + buildPath + zipName + ' ./');

        sprint(clc.green('Package done. ' + buildPath + zipName));
    }
};

/**
 * Build archives for updates & restore purpose
 */
let archiveSources = function () {
    sprint(clc.blue('Building archives for Apps sources restore'));

    let excludes = '--exclude=\'*.DS_Store*\' --exclude=\'*.idea*\' --exclude=\'*.gitignore*\' --exclude=\'*.localized*\'';

    // Android!
    sh.cd(ROOT + '/siberian/var/apps/ionic');
    sh.rm('-rf', './android/app/src/main/assets/www/features/*');
    sh.rm('-rf', './android/app/src/main/assets/www/modules/*');
    sh.rm('-rf', './android/app/src/main/assets/www/chcp.json');
    sh.rm('-rf', './android/app/src/main/assets/www/chcp.manifest');
    sh.rm('-rf', './android/app/src/main/assets/www/index-prod.html');
    sh.chmod('-R', '777', './android');
    sh.exec('tar ' + excludes + ' -pcvf - ./android | pigz -9 - > ./android.tgz');

    // iOS (with AdMob)
    sh.rm('-rf', './ios/www/features/*');
    sh.rm('-rf', './ios/www/modules/*');
    sh.rm('-rf', './ios/www/chcp.json');
    sh.rm('-rf', './ios/www/chcp.manifest');
    sh.rm('-rf', './ios/www/index-prod.html');
    sh.chmod('-R', '777', './ios');
    sh.exec('tar ' + excludes + ' -pcvf - ./ios | pigz -9 - > ./ios.tgz');

    // iOS (without AdMob)
    sh.rm('-rf', './ios-noads/www/features/*');
    sh.rm('-rf', './ios-noads/www/modules/*');
    sh.rm('-rf', './ios-noads/www/chcp.json');
    sh.rm('-rf', './ios-noads/www/chcp.manifest');
    sh.rm('-rf', './ios-noads/www/index-prod.html');
    sh.chmod('-R', '777', './ios-noads');
    sh.exec('tar ' + excludes + ' -pcvf - ./ios-noads | pigz -9 - > ./ios-noads.tgz');

    // Browser/HTML5
    sh.cd(ROOT + '/siberian/var/apps');
    sh.rm('-rf', './browser/features/*');
    sh.rm('-rf', './browser/modules/*');
    sh.rm('-rf', './browser/chcp.json');
    sh.rm('-rf', './browser/chcp.manifest');
    sh.rm('-rf', './browser/index-prod.html');
    sh.chmod('-R', '777', './browser');
    sh.exec('tar ' + excludes + ' -pcvf - ./browser | pigz -9 - > ./browser.tgz');

    sprint(clc.green('Archives done!'));
};

/**
 * Changes a module versionb in database, for update purpose
 *
 * @param version
 * @param module
 */
let mver = function (version, module) {
    let versionPath = ROOT + '/siberian/lib/Siberian/Version.php',
        developer = require(ROOT + '/developer.json'),
        currentVersion = fs.readFileSync(versionPath, 'utf8'),
        currentEdition = currentVersion.match(/const TYPE = '([a-z])+';/gi),
        mysqlHost = developer.mysql.host,
        mysqlUsername = developer.mysql.username,
        mysqlPassword = developer.mysql.password,
        mysqlDatabasePrefix = developer.mysql.databasePrefix,
        query = 'UPDATE `module` SET `version` = "' + version + '" ';

    currentEdition = currentEdition[0].replace(/(const TYPE = '|';)/g, '').toLowerCase();

    if (module.trim() !== '') {
        query = query + ' WHERE `name` LIKE "%' + module.trim() + '%"';
    }

    var sqlQuery = [
        'mysql -u',
        mysqlUsername,
        '-h',
        mysqlHost,
        '-p'+mysqlPassword,
        mysqlDatabasePrefix+currentEdition,
        '-e \'' + query + ';\''
    ].join(' ');

    sh.exec(sqlQuery);
};

/**
 * Helper for common aliases
 */
let aliasHelp = function () {
    sprint('alias sb=\'./sb\'');
    sprint('alias sbr=\'sb rebuild\'');
    sprint('alias sbi=\'sb ions\'');
    sprint('alias sbt=\'sb type\'');
    sprint('alias sbsm=\'sb sync-module\'');
    sprint('alias sbp=\'sb prod\'');
    sprint('alias sbd=\'sb dev\'');
    sprint('alias sbcc=\'sb clearcache\'');
    sprint('alias sbcl=\'sb clearlog\'');
    sprint('alias sbm=\'sb mver\'');
    sprint('alias sblm=\'sb lm\'');
    sprint('alias sbulm=\'sb ulm\'');
};

/**
 * CLI Helper
 */
let printHelp = function () {
    sprint(clc.blue('###############################################'));
    sprint(clc.blue('#                                             #'));
    sprint(clc.blue('#     ' + clc.bold('SiberianCMS command-line interface.') + '     #'));
    sprint(clc.blue('#                                             #'));
    sprint(clc.blue('###############################################'));
    sprint('');
    sprint(clc.blue('Available commands are: '));
    sprint('');
    let help = `
alias                   Prints bash aliases to help development

clearcache, cc          Clear siberian/var/cache

clearlog, cl            Clear siberian/var/log

db                      Check if databases exists, otherwise create them

export-db               Export db tables to schema files

init                    Initializes DB, project, settings.

install                 Install forks for cordova-lib.

icons                   Build ionicons font
                            - install: install required dependencies (OSX Only).
        icons [install]

ions                    Start ionic serve in background

rebuild                 Rebuild a platform (requires Android SDK & Xcode, Command-Line Tools):
                            - debug: option will show more informations.
                            - copy: copy platform to siberian/var/apps.
        rebuild <platform> [copy] [debug]

rebuild-all             Rebuild all platforms (requires Android SDK & Xcode, Command-Line Tools)

syncmodule, sm          Resync a module in the Application

type                    Switch the Application type 'sae|mae|pe' or print the current if blank
                        note: clearcache is called when changing type.
                            - reset: optional, will set is_installed to 0.
                            - empty: optional, clear all the database.
        type [type] [reset] [empty]
test                    Test PHP syntax

pack                    Pack a module into zip, file is located in ./packages/modules/
                            - If using from a module forlders module_name is optional
        pack <module_name>

packall                 Pack all referenced modules

prepare                 Prepare a platform (Doesn't requires Android SDK & Xcode, it's suitable for any HTML/JS/CSS Customization in the Apps):
                            - debug: option will show more informations.
                            - copy: copy platform to siberian/var/apps.
        prepare <platform> [copy] [debug]
        
prepall                 Prepare all platforms
                            - debug: option will show more informations.
                            - copy: copy platform to siberian/var/apps.
        prepare [copy] [debug]

manifest                Rebuilds app manifest

moduleversion, mver     Update all module version to <version> or only the specified one, in database.
                            - module_name is case-insensitive and is searched with LIKE %module_name%
                            - module_name is optional and if empty all modules versions are changed
        mver <version> [module_name]

npm                     Hook for npm version.
    npm <version>

prod                    Switch the Application mode to 'production'.

dev                     Switch the Application mode to 'development'.

version                 Prints the current SiberianCMS version.

linkmodule, lm          Symlink a module from ./modules/ to ./siberian/app/local/modules/
        lm <module>

unlinkmodule, ulm       Remove module symlink
        ulm <module>

syncmodule, sm          Sync all sub-modules/platforms/plugins from git
`;

    sprint(help);
};

module.exports = cli;
