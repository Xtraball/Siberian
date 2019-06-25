/**
 * Lightbox
 *
 * @author Xtraball SAS
 * @version 4.16.6
 */
angular.module('starter').service('Lightbox', function ($ocLazyLoad, $q) {
    var service = {
        loadedPromise: $q.defer(),
        defaults: {
            captions: true,
            buttons: "auto",
            fullScreen: false,
            noScrollbars: true,
            animation: "fadeIn"
        }
    };

    // Loading datetime picker
    $ocLazyLoad.load([
        "./dist/lazy/baguette/baguetteBox.min.css",
        "./dist/lazy/baguette/baguetteBox.min.js"
    ])
    .then(function () {
        service.loadedPromise.resolve();
    });

    service.isLoaded = function () {
        return service.loadedPromise.promise;
    };

    service.run = function (container, options) {
        service
        .isLoaded()
        .then(function () {
            baguetteBox.run(container, angular.extend({}, service.defaults, options));
        });
    };

    return service;
});