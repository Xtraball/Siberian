App.factory('Wordpress', function($rootScope, $q, $sbhttp, Url) {

    var factory = {};

    factory.value_id = null;
    factory.post_id = null;
    factory.collection = null;

    factory.findAll = function(offset) {

        if(!this.value_id) return;

        var params = {
            value_id: this.value_id,
            offset: offset
        };

        return $sbhttp({
            method: 'GET',
            url: Url.get("wordpress/mobile_list/findall", params),
            cache: !$rootScope.isOverview,
            responseType:'json'
        });
    };

    return factory;
});
