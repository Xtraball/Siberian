
App.factory('Role', function($http, Url) {

    var factory = {};

    factory.loadListData = function() {
        return $http({
            method: 'GET',
            url: Url.get("acl/backoffice_role_list/load"),
            cache: true,
            responseType:'json'
        });
    };

    factory.findAll = function() {
        return $http({
            method: 'GET',
            url: Url.get("acl/backoffice_role_list/findall"),
            cache: false,
            responseType:'json'
        });
    };

    factory.find = function(role_id) {
        var param = {};
        if(role_id) {
            param = {role_id: role_id};
        }

        return $http({
            method: 'GET',
            url: Url.get("acl/backoffice_role_edit/find", param),
            responseType:'json'
        });

    };

    factory.getResourceHierarchy = function() {
        return $http({
            method: 'GET',
            url: Url.get("acl/backoffice_role_edit/getresourcehierarchical"),
            cache: true,
            responseType:'json'
        });
    };

    factory.save = function(role) {
        return $http({
            method: 'POST',
            data: role,
            url: Url.get("acl/backoffice_role_edit/save"),
            responseType:'json'
        });
    };

    factory.delete = function(role_id) {
        var param = {};
        if(role_id) {
            param = {role_id: role_id};
        }

        return $http({
            method: 'GET',
            url: Url.get("acl/backoffice_role_edit/delete", param),
            responseType:'json'
        });

    };

    return factory;
});
