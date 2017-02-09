App.service('Country', function($sbhttp, Url) {

    var service = {};

    service.findAll = function() {
        return $sbhttp({
            method: 'GET',
            url: Url.get("/application/mobile_country/findall"),
            cache: true,
            responseType: 'json'
        });
    };

    return service;
});