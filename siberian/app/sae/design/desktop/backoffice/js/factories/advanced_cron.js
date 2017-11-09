
App.factory('AdvancedCron', function($http, Url) {

    var factory = {};

    factory.loadData = function() {
        return $http({
            method: 'GET',
            url: Url.get("backoffice/advanced_cron/load"),
            cache: true,
            responseType:'json'
        });
    };

    factory.findAll = function() {

        return $http({
            method: 'GET',
            url: Url.get("backoffice/advanced_cron/findall"),
            cache: false,
            responseType:'json'
        });

    };


    return factory;
});
