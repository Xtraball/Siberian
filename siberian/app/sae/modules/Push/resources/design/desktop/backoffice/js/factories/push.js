/**
 * Push
 *
 * @author Xtraball SAS
 */
App.factory('Push', function ($http, Url) {
    var factory = {};

    factory.loadData = function () {
        return $http({
            method: 'GET',
            url: Url.get('push/backoffice_certificate/load'),
            cache: true,
            responseType: 'json'
        });
    };

    factory.findAll = function () {
        return $http({
            method: 'GET',
            url: Url.get('push/backoffice_certificate/findall'),
            cache: true,
            responseType: 'json'
        });
    };

    factory.save = function (android_key, android_sender_id) {
        return $http({
            method: 'POST',
            data: {
                android_key: android_key,
                android_sender_id: android_sender_id
            },
            url: Url.get('push/backoffice_certificate/save'),
            responseType: 'json'
        });
    };

    factory.globalFindAll = function () {
        return $http({
            method: 'GET',
            url: Url.get('push/backoffice_global/findall'),
            responseType: 'json'
        });
    };

    factory.globalSend = function (params) {
        return $http({
            method: 'POST',
            url: Url.get('push/backoffice_global/send'),
            data: params,
            responseType: 'json'
        });
    };

    return factory;
});
