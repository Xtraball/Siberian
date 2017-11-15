/**
 * Siberian
 *
 * @version 4.12.18
 * @author Xtraball SAS <dev@xtraball.com>
 *
 */
let sh = require('shelljs'),
    nopt = require('nopt');

const init = function () {
    // Copy Siberian CLI custom modification!
    sh.exec('cp -rp ./bin/config/platforms.js ./node_modules/cordova-lib/src/cordova/platform.js');
    sh.exec('cp -rp ./bin/config/platformsConfig.json ./node_modules/cordova-lib/src/platforms/platformsConfig.json');
    sh.exec('cp -rp ./bin/config/plugman.js ./node_modules/cordova-lib/src/plugman/plugman.js');

    /** Configuring environment */
    sh.exec('git config core.fileMode false');

    console.log('Done.');
};

const install = function (inputArgs) {

    init();
    let args = nopt(inputArgs);

    // For CordovaError print only the message without stack trace unless we
    // are in a verbose mode.
    process.on('uncaughtException', function (err) {
        process.exit(1);
    });
};

module.exports = install;