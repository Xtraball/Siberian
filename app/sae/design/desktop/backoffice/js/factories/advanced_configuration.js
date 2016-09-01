
App.factory('AdvancedConfiguration', function($http, Url) {

    var factory = {};

    factory.loadData = function() {
        return $http({
            method: 'GET',
            url: Url.get("backoffice/advanced_configuration/load"),
            cache: true,
            responseType:'json'
        });
    };

    factory.findAll = function() {

        return $http({
            method: 'GET',
            url: Url.get("backoffice/advanced_configuration/findall"),
            cache: false,
            responseType:'json'
        });

    };

    factory.save = function(values) {

        var url = "backoffice/advanced_configuration/save";

        return $http({
            method: 'POST',
            data: values,
            url: Url.get(url+"/save"),
            cache: false,
            responseType:'json'
        });

    };

    return factory;
});
