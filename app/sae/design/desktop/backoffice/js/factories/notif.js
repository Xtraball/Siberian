
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

    factory.findAll = function() {

        return $http({
            method: 'GET',
            url: Url.get("backoffice/notif_list/findall"),
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

    return factory;
});
