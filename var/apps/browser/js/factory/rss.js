
App.factory('Rss', function($rootScope, $http, Url) {

    var factory = {};

    factory.value_id = null;

    factory.findAll = function() {

        if(!this.value_id) return;

        return $http({
            method: 'GET',
            url: Url.get("rss/mobile_feed_list/findall", {value_id: this.value_id}),
            cache: !$rootScope.isOverview,
            responseType:'json'
        });
    };

    factory.find = function(feed_id) {

        if(!this.value_id) return;

        var url = Url.get('rss/mobile_feed_view/find', {feed_id: feed_id, value_id: this.value_id});

        return $http({
            method: 'GET',
            url: url,
            cache: true,
            responseType:'json'
        });
    };

    return factory;
});
