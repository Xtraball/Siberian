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

    factory.saveFirebaseProject = function (senderID, serverKey) {
        return $http({
            method: 'POST',
            data: {
                senderID: senderID,
                serverKey: serverKey
            },
            url: Url.get('push/backoffice_firebase/project'),
            cache: true,
            responseType: 'json'
        });
    };

    return factory;
});
