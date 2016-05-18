
App.factory('Installer', function($http, Url) {

    var factory = {};

    factory.filename = null;

    factory.loadData = function() {
        return $http({
            method: 'GET',
            url: Url.get("installer/backoffice_module/load"),
            cache: true,
            responseType:'json'
        });
    };

    factory.checkForUpdates = function() {

        return $http({
            method: 'GET',
            url: Url.get("installer/backoffice_module/checkforupdates"),
            cache: false,
            responseType:'json'
        });

    };

    factory.downloadUpdate = function() {

        return $http({
            method: 'GET',
            url: Url.get("installer/backoffice_module/downloadupdate"),
            cache: false,
            responseType:'json'
        });

    };

    factory.checkPermissions = function() {

        return $http({
            method: 'GET',
            url: Url.get("installer/backoffice_module/checkpermissions", {file: factory.filename}),
            cache: false,
            responseType:'json'
        });

    };

    factory.saveFtp = function(ftp) {

        return $http({
            method: 'POST',
            data: ftp,
            url: Url.get("installer/backoffice_module/saveftp"),
            responseType:'json'
        });

    };

    factory.copy = function() {

        return $http({
            method: 'GET',
            url: Url.get("installer/backoffice_module/copy", {file: factory.filename}),
            cache: false,
            responseType:'json'
        });
    };

    factory.install = function() {

        return $http({
            method: 'GET',
            url: Url.get("installer/backoffice_module/install"),
            cache: false,
            responseType:'json'
        });
    };

    return factory;
});
