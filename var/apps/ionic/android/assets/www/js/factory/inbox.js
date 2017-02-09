/*global
    App
 */
App.factory('Inbox', function($rootScope, $sbhttp, httpCache, Url) {

    var factory = {};

    var urls_to_flush = new Array();

    factory.value_id = null;
    factory.displayed_per_page = null;

    factory.findAll = function(customer_id, offset) {

        if(!this.value_id) {
            return;
        }

        var url = Url.get("inbox/mobile_list/findall", {value_id: this.value_id, customer_id: customer_id, offset: offset});
        urls_to_flush.push(url);

        return $sbhttp({
            method: 'GET',
            url: Url.get("inbox/mobile_list/findall", {value_id: this.value_id, customer_id: customer_id, offset: offset}),
            cache: !$rootScope.isOverview,
            responseType:'json'
        }).success(function(data) {
            if(data.displayed_per_page) {
                factory.displayed_per_page = data.displayed_per_page;
            }
        });
    };

    factory.find = function(message_id) {

        return $sbhttp({
            method: 'GET',
            url: Url.get("inbox/mobile_view/find", {message_id: message_id}),
            cache: !$rootScope.isOverview,
            responseType:'json'
        });
    };

    factory.deleteRootMessage = function(message_id) {
        return $sbhttp({
            method: 'GET',
            url: Url.get("inbox/mobile_view/deleterootmessage", {message_id: message_id}),
            cache: !$rootScope.isOverview,
            responseType:'json'
        }).success(function() {
            for (var i = 0; i < urls_to_flush.length; i++) {
                httpCache.remove(urls_to_flush[i]);
            }
            urls_to_flush = [];
        });
    };

    factory.findComments = function(message_id, customer_id, offset) {

        var url = Url.get("inbox/mobile_comment_view/find", {message_id: message_id, customer_id: customer_id, offset: offset});
        urls_to_flush.push(url);

        return $sbhttp({
            method: 'GET',
            url: url,
            cache: !$rootScope.isOverview,
            responseType:'json'
        }).success(function(data) {
            if(data.displayed_per_page) {
                factory.displayed_per_page = data.displayed_per_page;
            }
        });
    };

    factory.postComment = function(message) {

        return $sbhttp({
            method: 'POST',
            data: message,
            url: Url.get("inbox/mobile_comment_view/postcomment"),
            cache: !$rootScope.isOverview,
            responseType:'json'
        }).success(function() {
            for(var i = 0; i < urls_to_flush.length; i++) {
                httpCache.remove(urls_to_flush[i]);
            }
            urls_to_flush = new Array();
        });
    };


    return factory;
});
