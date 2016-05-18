
App.factory('Wordpress', function($rootScope, $q, $http, Url) {

    var factory = {};

    factory.value_id = null;
    factory.post_id = null;

    factory.findAll = function(offset) {

        if(!this.value_id) return;

        return $http({
            method: 'GET',
            url: Url.get("wordpress/mobile_list/findall", {value_id: this.value_id, offset: offset}),
            cache: !$rootScope.isOverview,
            responseType:'json'
        }).success(function(data) {
            if(data.displayed_per_page) {
                factory.displayed_per_page = data.displayed_per_page;
            }
        });
    };

    factory.find = function(post_id) {

        var deferred = $q.defer();
        if(!this.value_id) return;

        this.findAll().success(function(data) {
            var posts = data.collection;
            var cover = data.cover;
            var post = {};

            if(cover && cover.id  == post_id) {
                post = cover;
            } else {
                for(var i in posts) {
                    if(posts[i].id == post_id) {
                        post = posts[i];
                        break;
                    }
                }
            }

            deferred.resolve(post);
        }).error(function(data) {
            deferred.reject(data);
        });

        return deferred.promise;
    };

    return factory;
});
