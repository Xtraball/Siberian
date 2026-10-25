App.factory('Advanced', function ($http, Url) {
    var factory = {};

    factory.loadData = function () {
        return $http({
            method: 'GET',
            url: Url.get('backoffice/advanced_module/load'),
            cache: true,
            responseType: 'json'
        });
    };

    factory.findAll = function () {
        return $http({
            method: 'GET',
            url: Url.get('backoffice/advanced_module/findall'),
            cache: false,
            responseType: 'json'
        });
    };

    factory.moduleAction = function (module, action) {
        return $http({
            method: 'POST',
            url: Url.get('backoffice/advanced_module/execute'),
            data: {
                module: module,
                action: action
            },
            cache: false,
            responseType: 'json'
        });
    };

    /**
     * Toggle a feature enable state
     *
     * @param featureId
     * @param isEnabled
     * @returns {*}
     */
    factory.toggleFeature = function (featureId, isEnabled) {
        return $http({
            method: 'POST',
            url: Url.get('backoffice/advanced_module/togglefeature'),
            data: {
                featureId: featureId,
                isEnabled: isEnabled
            },
            cache: false,
            responseType: 'json'
        });
    };

    /**
     * Toggle a module enable state
     *
     * @param moduleId
     * @param isEnabled
     * @returns {*}
     */
    factory.toggleModule = function (moduleId, isEnabled) {
        return $http({
            method: 'POST',
            url: Url.get('backoffice/advanced_module/togglemodule'),
            data: {
                moduleId: moduleId,
                isEnabled: isEnabled
            },
            cache: false,
            responseType: 'json'
        });
    };

    return factory;
});

/**
 * Store account (extensions.siberiancms.com), see BackofficeAdvancedStoreController.
 */
App.factory('AdvancedStore', function ($http, Url) {

    var factory = {};

    factory.loadData = function () {
        return $http({
            method: 'GET',
            url: Url.get('backoffice/advanced_store/load'),
            cache: true,
            responseType: 'json'
        });
    };

    factory.status = function () {
        return $http({
            method: 'GET',
            url: Url.get('backoffice/advanced_store/status'),
            cache: false,
            responseType: 'json'
        });
    };

    factory.connect = function () {
        return $http({
            method: 'POST',
            data: {},
            url: Url.get('backoffice/advanced_store/connect'),
            cache: false,
            responseType: 'json'
        });
    };

    factory.poll = function () {
        return $http({
            method: 'GET',
            url: Url.get('backoffice/advanced_store/poll'),
            cache: false,
            responseType: 'json'
        });
    };

    factory.purchases = function (refresh) {
        return $http({
            method: 'GET',
            url: Url.get('backoffice/advanced_store/purchases', refresh ? {refresh: 1} : {}),
            cache: false,
            responseType: 'json'
        });
    };

    factory.disconnect = function () {
        return $http({
            method: 'POST',
            data: {},
            url: Url.get('backoffice/advanced_store/disconnect'),
            cache: false,
            responseType: 'json'
        });
    };

    return factory;
});
