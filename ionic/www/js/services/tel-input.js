/**
 * TelInput
 *
 * @author Xtraball SAS
 * @version 4.20.30
 */
angular.module('starter').service('TelInput', function ($ocLazyLoad, $q) {
    var service = {
        loadedPromise: $q.defer(),
    };

    // Loading datetime picker
    $ocLazyLoad.load([
        './dist/lazy/intl-tel-input/css/intlTelInput.css',
        './dist/lazy/intl-tel-input/js/intlTelInput.js',
        './dist/lazy/intl-tel-input/js/utils.js',
    ])
    .then(function () {
        service.loadedPromise.resolve();
    }, function () {
        // The scripts could not be loaded: the callers must not wait forever (the login modal waits on this),
        // the mobile number is then checked without the library, see Customer.normalizeMobile.
        service.failed = true;
        service.loadedPromise.resolve();
    });

    service.isLoaded = function () {
        return service.loadedPromise.promise;
    };

    return service;
});