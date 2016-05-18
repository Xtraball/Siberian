
App.factory('Tc', function($rootScope, $http, Url) {

    var factory = {};

    factory.find = function(tc_id) {

        return $http({
            method: 'GET',
            url: Url.get("application/mobile_tc_view/find", {tc_id: tc_id}),
            cache: !$rootScope.isOverview,
            responseType:'json'
        });
    };

    return factory;
});
