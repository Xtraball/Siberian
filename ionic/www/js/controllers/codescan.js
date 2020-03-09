/**
 * Code scan feature
 */
angular
    .module('starter')
    .controller('CodeScanController', function (Codescan) {
        Codescan.scanGeneric();
    });