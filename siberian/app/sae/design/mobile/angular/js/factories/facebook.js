
App.factory('Facebook', function($window, $rootScope, $http, Url, $facebook) {

    var factory = {};

    factory.value_id = null;
    factory.token = null;
    factory.username = null;
    factory.page_urls = new Array();
    factory.displayed_per_page = 22;

    factory.loadData = function() {

        if(!this.value_id) return;

        var params = {
            value_id: this.value_id,
            need_token: !this.token
        };

        return $http({
            method: 'GET',
            url: Url.get("social/mobile_facebook_list/find", params),
            cache: !$rootScope.isOverview,
            responseType:'json'
        }).success(function(data) {
            factory.username = data.username;
            if(data.token) {
                factory.token = data.token;
            }
        });
    };

    factory.findUser = function() {
        var params = "id,about,name,genre,cover,likes,talking_about_count";
        return $facebook.cachedApi("/"+this.username+"/?access_token="+this.token+"&fields="+params);
    };

    factory.findPosts = function() {
        var params = "posts.fields(from,message,picture,created_time,likes,comments,type)";
        if(angular.isDefined(factory.page_urls['posts'])) {
            return $facebook.api(factory.page_urls['posts']);
        }
        return $facebook.api("/"+this.username+"/?access_token="+this.token+"&fields="+params);
    };

    factory.findPost = function(post_id) {
        var params = "from,name,message,description,full_picture,created_time,likes,comments,object_id,type";
        return $facebook.api("/"+post_id+"?access_token="+this.token+"&fields="+params);
    };

    factory.findComments = function() {
        return $facebook.api(factory.page_urls['comments']);
    };

    return factory;

});
