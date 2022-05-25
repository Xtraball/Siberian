/**
 * Siberian
 *
 * @version 4.12.18
 * @author Xtraball SAS <dev@xtraball.com>
 *
 */
let sh = require('shelljs');

const install = function (inputArgs) {
    // Copy Siberian CLI custom modification!
    // Improvement: Fork cordova-lib@6.5.0 to avoid replacing platform files!
    //sh.cp('-r', './bin/config/plugman.js', './node_modules/cordova-lib/src/plugman/plugman.js');
    //sh.cp('-r', './bin/config/main.js', './node_modules/plugman/main.js');

    // Configuring environment!
    //sh.exec('git config core.fileMode false');

    console.log('Done.');

    process.on('uncaughtException', function (err) {
        process.exit(1);
    });
};

module.exports = install;
