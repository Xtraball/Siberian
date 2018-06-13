
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

    };

    factory.computeAnalytics = function() {
        var url = "system/backoffice_config_"+this.type;

        return $http({
            method: 'GET',
            url: Url.get(url+"/generateanalytics"),
            cache: false,
            responseType:'json'
        });
    };

    factory.computeAnalyticsForPeriod = function(period) {
        var url = "system/backoffice_config_"+this.type;

        return $http({
            method: 'POST',
            url: Url.get(url+"/generateanalyticsforperiod"),
            cache: false,
            responseType:'json',
            data:period
        });
    };

    factory.testemail = function(email) {
        var url = "system/backoffice_config_email/testsmtp";

        return $http({
            method: 'POST',
            url: Url.get(url, {email: email}),
            cache: false,
            responseType:'json',
        });
    };

    return factory;
});
