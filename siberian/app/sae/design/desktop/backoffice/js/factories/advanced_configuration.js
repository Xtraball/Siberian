/*global
    App
 */
App.factory('AdvancedConfiguration', function ($http, Url) {

    var factory = {};

    factory.loadData = function() {
        return $http({
            method: 'GET',
            url: Url.get("backoffice/advanced_configuration/load"),
            cache: true,
            responseType: 'json'
        });
    };

    factory.findAll = function() {

        return $http({
            method: 'GET',
            url: Url.get("backoffice/advanced_configuration/findall"),
            cache: false,
            responseType: 'json'
        });

    };

    factory.save = function (values) {

        var url = "backoffice/advanced_configuration/save";

        return $http({
            method: 'POST',
            data: values,
            url: Url.get(url + "/save"),
            cache: false,
            responseType: 'json'
        });

    };

    factory.testSsl = function () {

        var url = "backoffice/advanced_configuration/testssl";
        return $http({
            method: 'POST',
            url: url,
            cache: false,
            responseType: 'json'
        });

    };

    factory.generateSsl = function (hostname, force) {

        var url = "backoffice/advanced_configuration/generatessl";

        if(force) {
            url = Url.get(url, {hostname: hostname, force_regenerate: force});
        } else {
            url = Url.get(url, {hostname: hostname});
        }

        return $http({
            method: 'POST',
            url: url,
            cache: false,
            responseType: 'json'
        });

    };

    factory.createCertificate = function (data) {

        var url = "backoffice/advanced_configuration/createcertificate";

        return $http({
            method: 'POST',
            url: url,
            data: data,
            cache: false,
            responseType: 'json'
        });

    };

    factory.removeCertificate = function (id) {

        var url = "backoffice/advanced_configuration/removecert/cert_id/" + id;

        return $http({
            method: "GET",
            url: url,
            cache: false,
            responseType: 'json'
        });

    };

    factory.submitReport = function(message) {
        var url = "backoffice/advanced_configuration/submitreport";

        return $http({
            method: "POST",
            data: {
                message: message
            },
            url: url,
            cache: false,
            responseType: 'json'
        });
    };


    return factory;
});
