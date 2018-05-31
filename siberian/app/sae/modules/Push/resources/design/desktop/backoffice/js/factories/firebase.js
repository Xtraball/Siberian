/**
 * Push
 *
 * @author Xtraball SAS
 */
App.factory('Firebase', function ($http, Url) {
    var factory = {};

    factory.load = function () {
        return $http({
            method: 'GET',
            url: Url.get('push/backoffice_firebase/load'),
            cache: true,
            responseType: 'json'
        });
    };

    // Firebase Cloud Messaging
    factory.saveFirebaseCredentials = function (email, password) {
        return $http({
            method: 'POST',
            data: {
                email: email,
                password: password
            },
            url: Url.get('push/backoffice_firebase/credentials'),
            cache: true,
            responseType: 'json'
        });
    };

    factory.saveFirebaseProject = function (projectNumber, serverKey) {
        return $http({
            method: 'POST',
            data: {
                projectNumber: projectNumber,
                serverKey: serverKey
            },
            url: Url.get('push/backoffice_firebase/project'),
            cache: true,
            responseType: 'json'
        });
    };

    return factory;
});
