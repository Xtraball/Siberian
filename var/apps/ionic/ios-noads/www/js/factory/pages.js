App.factory('Pages', function($sbhttp, $rootScope, Push, Url) {

    var factory = {};

    factory.findAll = function() {
        return $sbhttp({
            method: 'GET',
            url: Url.get('front/mobile_home/findall', { device_uid: Push.device_uid }),
            cache: false,
            responseType:'json'
        });
    };

    return factory;
});
