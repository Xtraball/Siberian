/* global
 App, angular
 */

/**
 * Facebook
 *
 * @author Xtraball SAS
 */
angular.module('starter').factory('Facebook', function ($cacheFactory, $pwaRequest, Url) {
    var factory = {
        value_id: null,
        token: null,
        username: null,
        page_urls: [],
        displayed_per_page: 22,
        cache: $cacheFactory('facebook'),
        host: 'https://graph.facebook.com/v2.7/',
        host_img: 'https://graph.facebook.com/v2.7/'
    };

    /**
     *
     * @param value_id
     */
    factory.setValueId = function (value_id) {
        factory.value_id = value_id;
    };

    factory.preFetch = function () {
        factory.loadData()
            .then(function () {
                factory.findUser()
                    .then(function () {
                        factory.findPosts();
                    });
            });
    };

    /**
     * Build picture url
     *
     * @param picture_id
     * @param size
     * @returns {string}
     */
    factory.getPictureUrl = function (picture_id, size) {
        return factory.host_img + picture_id + '/picture?width=' + size + '&access_token=' + factory.token;
    };

    /**
     * Fetch data for Facebook Page
     */
    factory.loadData = function (refresh) {
        if (!this.value_id) {
            return $pwaRequest.reject('[Factory::Facebook.loadData] missing value_id');
        }

        // Warning about API v2.7 for facebook expiration!
        if (Math.ceil(Date.now()/1000) > 1533420000) {
            console.error('Facebook API v2.7 will shutdown 5 October 2018, please upgrade to latest API Version.');
        }

        return $pwaRequest.get('social/mobile_facebook_list/find', {
            urlParams: {
                value_id: this.value_id,
                need_token: !this.token
            },
            withCredentials: false,
            refresh: refresh
        }).then(function (response) {
            factory.username = response.username;
            if (response.token) {
                factory.token = response.token;
            }

            return response;
        });
    };

    factory.findUser = function () {
        var params = 'id,about,name,genre,cover,fan_count,likes,talking_about_count';
        var url = Url.build(factory.host + factory.username, { fields: params, access_token: this.token });

        return factory.get(url);
    };

    factory.findPosts = function () {
        var params = 'posts.fields(from,message,full_picture,picture,created_time,likes,comments,type,object_id,name,link)';
        var url = Url.build(factory.host + factory.username, { fields: params, access_token: factory.token });

        if (angular.isDefined(factory.page_urls.posts)) {
            url = factory.page_urls.posts;
        }

        return factory.get(url);
    };

    factory.findPost = function (post_id) {
        var params = 'from,message,description,full_picture,created_time,likes,comments,object_id,type,name,link';
        var url = Url.build(factory.host + post_id, { fields: params, access_token: factory.token });

        return factory.get(url);
    };

    factory.findComments = function () {
        return factory.get(factory.page_urls.comments);
    };

    factory.get = function (url) {
        return $pwaRequest.get(url, {
            withCredentials: false
        });
    };


    return factory;
});
