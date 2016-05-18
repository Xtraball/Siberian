App.factory('Pages', function($http, $rootScope, Push, Url) {

    var factory = {};

    factory.findAll = function() {
        return $http({
            method: 'GET',
            url: Url.get('front/mobile_home/findall', { device_uid: Push.device_uid }),
            cache: !$rootScope.isOverview,
            responseType:'json'
        });
    };

    return factory;
});
