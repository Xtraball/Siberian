angular.module('starter').provider('$pwaCache', function () {
    var provider = this;

    provider.$get = function () {
        var provider = {};

        if (typeof window.localforage === 'undefined') {
            provider = {
                isEnabled: false
            };

            return provider;
        }

        provider = {
            isEnabled: true,
            defaultDrivers: [window.localforage.INDEXEDDB, window.localforage.LOCALSTORAGE],
            defaultStoreName: 'content-cache',
            valueidStoreName: 'valueid-index',
            registryStoreName: 'registry-index',
            cacheKey: 'pwa-cache-' + APP_KEY,
            /** Fixed to 64MB */
            cacheMaxSize: 64000000,
            /** Caches */
            defaultCache: null,
            valueidCache: null,
            registryCache: null,
            backgroundImages: []
        };

        provider.defaultCache = window.localforage.createInstance({
            driver: provider.defaultDrivers,
            name: provider.cacheKey,
            storeName: provider.defaultStoreName,
            size: provider.cacheMaxSize
        });

        provider.valueidCache = window.localforage.createInstance({
            driver: provider.defaultDrivers,
            name: provider.cacheKey,
            storeName: provider.valueidStoreName,
            size: (provider.cacheMaxSize / 16)
        });

        provider.registryCache = window.localforage.createInstance({
            driver: window.localforage.LOCALSTORAGE,
            name: provider.cacheKey,
            storeName: provider.registryStoreName,
            size: (provider.cacheMaxSize / 16)
        });

        /**
         * Default cache for all http based requests & assets
         *
         * @returns {null}
         */
        provider.getDefaultCache = function () {
            return provider.defaultCache;
        };

        /**
         *
         * @returns {*}
         */
        provider.getValueidCache = function () {
            return provider.valueidCache;
        };

        /**
         *
         * @returns {*}
         */
        provider.getRegistryCache = function () {
            return provider.registryCache;
        };

        return provider;
    };
});
