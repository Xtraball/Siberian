
App.factory('Admin', function($http, Url, DataLoader) {

    var factory = {};

    factory.loadListData = function() {
        return $http({
            method: 'GET',
            url: Url.get("admin/backoffice_list/load"),
            cache: true,
            responseType:'json'
        });
    };
    factory.loadEditData = function() {
        return $http({
            method: 'GET',
            url: Url.get("admin/backoffice_edit/load"),
            cache: true,
            responseType:'json'
        });
    };
    factory.loadExportData = function() {
        return $http({
            method: 'GET',
            url: Url.get("admin/backoffice_export/load"),
            cache: true,
            responseType:'json'
        });
    };

    factory.findAll = function() {
        return new DataLoader().sequencedLoading("admin/backoffice_list/findall");
    };

    factory.find = function(admin_id) {

        var param = {};
        if(admin_id) {
            param = {admin_id: admin_id};
        }

        return $http({
            method: 'GET',
            url: Url.get("admin/backoffice_edit/find", param),
            cache: false,
            responseType:'json'
        });
    };

    factory.save = function(admin) {

        return $http({
            method: 'POST',
            data: admin,
            url: Url.get("admin/backoffice_edit/save"),
            cache: false,
            responseType:'json'
        });
    };

    factory.delete = function(admin_id) {

        return $http({
            method: 'POST',
            data: {admin_id: admin_id},
            url: Url.get("admin/backoffice_list/delete"),
            cache: false,
            responseType:'json'
        });
    };

    factory.setApplication = function(admin_id, application) {

        var data = {
            admin_id: admin_id,
            app_id: application.id,
            is_allowed_to_add_pages: application.is_allowed_to_add_pages
        };

        return $http({
            method: 'POST',
            data: data,
            url: Url.get("admin/backoffice_edit/setapplicationtoadmin"),
            cache: false,
            responseType:'json'
        });
    };

    factory.setPermissions = function(admin_id, application) {

        var data = {
            admin_id: admin_id,
            app_id: application.id,
            is_allowed_to_add_pages: application.is_allowed_to_add_pages
        };

        return $http({
            method: 'POST',
            data: data,
            url: Url.get("admin/backoffice_edit/setpermissionstoadmin"),
            cache: false,
            responseType:'json'
        });
    };

    return factory;
});
