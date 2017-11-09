
App.factory('Preview', function($http, Url) {

    var factory = {};

    factory.loadListData = function() {
        return $http({
            method: 'GET',
            url: Url.get("preview/backoffice_list/load"),
            cache: true,
            responseType:'json'
        });
    };

    factory.findAll = function() {

        return $http({
            method: 'GET',
            url: Url.get("preview/backoffice_list/findall"),
            cache: false,
            responseType:'json'
        });
    };

    factory.find = function(preview_id) {
        var param = {};
        if(preview_id) {
            param = {preview_id: preview_id};
        }

        return $http({
            method: 'GET',
            url: Url.get("preview/backoffice_edit/find", param),
            cache: false,
            responseType:'json'
        });
    };

    factory.save = function(preview_id,option_id,previews) {
        var param = {};
        if(preview_id) {
            param.preview_id = preview_id;
        }
        if(option_id) {
            param.option_id = option_id;
        }
        param.previews = previews;

        return $http({
            method: 'POST',
            data: param,
            url: Url.get("preview/backoffice_edit/save"),
            cache: false,
            responseType:'json'
        });
    };

    factory.delete = function(preview_id,language_code) {
        var param = {preview_id: preview_id,language_code:language_code};

        return $http({
            method: 'POST',
            data: param,
            url: Url.get("preview/backoffice_edit/delete"),
            cache: false,
            responseType:'json'
        });
    };

    factory.fullDelete = function(preview_id) {
        var param = {preview_id: preview_id};

        return $http({
            method: 'POST',
            data: param,
            url: Url.get("preview/backoffice_list/delete"),
            cache: false,
            responseType:'json'
        });
    };

    return factory;
});
