"use strict";

/**
 * Facebook feature
 */
App.factory('Facebook', function($http, $q, $rootScope, Url) {

    var self = this;

    /** Features */
    self.value_id = null;
    self.token = null;
    self.username = null;
    self.page_urls = new Array();
    self.displayed_per_page = 22;
    self.host = "https://graph.facebook.com/v2.7/";
    self.host_img = "https://graph.facebook.com/";

    /**
     * Fetch data for Facebook Page
     *
     * @returns null|$http
     */
    self.loadData = function() {

        if(!self.value_id) {
            return;
        }

        return $http({
            method: 'GET',
            url: Url.get("social/mobile_facebook_list/find", { value_id: self.value_id, need_token: !self.token }),
            cache: !$rootScope.isOverview,
            withCredentials: false,
            responseType:'json'
        }).success(function(response) {

            self.username = response.username;
            if(response.token) {
                self.token = response.token;
            }
        });
    };

    self.findUser = function() {
        var params = "id,about,name,genre,cover,fan_count,likes,talking_about_count";
        var url = Url.build(self.host+self.username, { fields: params, access_token: self.token });

        return self.get(url);
    };

    self.findPosts = function() {
        var params = "posts.fields(from,message,full_picture,picture,created_time,likes,comments,type,object_id)";
        var url = Url.build(self.host+self.username, { fields: params, access_token: self.token });

        if(angular.isDefined(self.page_urls['posts'])) {
            url = self.page_urls['posts'];
        }

        return self.get(url);
    };

    self.findPost = function(post_id) {
        var deferred = $q.defer();

        var params = "from,name,message,description,full_picture,created_time,likes,comments,object_id,type";
        var url = Url.build(self.host+post_id, { fields: params, access_token: self.token });

        return self.get(url);
    };

    self.findComments = function() {
        return self.get(self.page_urls['comments']);
    };

    self.get = function(url) {
        return $http({
            method: 'GET',
            url: url,
            cache: !$rootScope.isOverview,
            withCredentials: false,
            responseType:'json'
        });
    };


    return self;
});