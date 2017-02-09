
App.factory('Tc', function($rootScope, $sbhttp, Url) {

    var factory = {};

    factory.find = function(tc_id) {

        return $sbhttp({
            method: 'GET',
            url: Url.get("application/mobile_tc_view/find", {tc_id: tc_id}),
            cache: !$rootScope.isOverview,
            responseType:'json'
        });
    };

    return factory;
});
