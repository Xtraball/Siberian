
App.factory('Socialgaming', function($rootScope, $http, httpCache, CACHE_EVENTS, Url) {

    var factory = {};

    factory.value_id = null;

    factory.findAll = function(offset) {

        if(!this.value_id) return;

        return $http({
            method: 'GET',
            url: Url.get("socialgaming/mobile_view/findall", {value_id: this.value_id,offset: offset}),
            cache: !$rootScope.isOverview,
            responseType:'json'
        }).success(function() {
            $rootScope.$on(CACHE_EVENTS.clearSocialGaming, function() {
                httpCache.remove(Url.get("socialgaming/mobile_view/findall", { value_id : factory.value_id }));
            });
        });
    };

    return factory;
});
