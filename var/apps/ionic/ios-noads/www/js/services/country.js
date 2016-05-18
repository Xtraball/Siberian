App.service('Country', function($http, Url) {

    var service = {};

    service.findAll = function() {
        return $http({
            method: 'GET',
            url: Url.get("/application/mobile_country/findall"),
            cache: true,
            responseType: 'json'
        });
    };

    return service;
});