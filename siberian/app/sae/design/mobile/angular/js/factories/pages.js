App.factory('Pages', function($rootScope, $http) {

    var factory = {};

    factory.findAll = function() {
        return $http({
            method: 'GET',
            url: BASE_URL+'/front/mobile_home/findall',
            cache: !$rootScope.isOverview,
            responseType:'json'
        });
    };

    return factory;
});
