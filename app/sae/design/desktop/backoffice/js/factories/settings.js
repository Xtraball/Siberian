
App.factory('Settings', function($http, Url) {

    var factory = {};

    factory.loadData = function() {

        var url = "system/backoffice_config_"+this.type;

        return $http({
            method: 'GET',
            url: Url.get(url+"/load"),
            cache: true,
            responseType:'json'
        });
    };

    factory.findAll = function() {

        var url = "system/backoffice_config_"+this.type;

        return $http({
            method: 'GET',
            url: Url.get(url+"/findall"),
            cache: true,
            responseType:'json'
        });
    };

    factory.save = function(values) {

        var url = "system/backoffice_config_"+this.type;

        return $http({
            method: 'POST',
            data: values,
            url: Url.get(url+"/save"),
            cache: false,
            responseType:'json'
        });

    }

    return factory;
});
