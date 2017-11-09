
App.factory('Notif', function($http, Url) {

    var factory = {};

    factory.loadData = function() {
        return $http({
            method: 'GET',
            url: Url.get("backoffice/notif_list/load"),
            cache: true,
            responseType:'json'
        });
    };

    factory.loadMessageData = function() {
        return $http({
            method: 'GET',
            url: Url.get("backoffice/notif_message/load"),
            cache: true,
            responseType:'json'
        });
    };

    factory.findAll = function() {

        return $http({
            method: 'GET',
            url: Url.get("backoffice/notif_list/findall"),
            cache: false,
            responseType:'json'
        });
    };

    factory.findMessage = function(message_id) {

        return $http({
            method: 'GET',
            url: Url.get("backoffice/notif_message/find", {message_id: message_id}),
            cache: true,
            responseType:'json'
        });
    };

    factory.markAs = function(notif) {

        return $http({
            method: 'POST',
            data: {notif_id: notif.id, is_read: !notif.is_read},
            url: Url.get("backoffice/notif_list/markas"),
            responseType:'json'
        });

    };

    factory.markAllRead = function() {

        return $http({
            method: "GET",
            url: Url.get("backoffice/notif_list/markallread"),
            responseType:'json'
        });

    };

    return factory;
});
