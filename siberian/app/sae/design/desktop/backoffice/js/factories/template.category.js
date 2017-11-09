
App.factory('TemplateCategory', function($http, Url) {

    var factory = {};

    factory.loadData = function() {
        return $http({
            method: 'GET',
            url: Url.get("template/backoffice_category_list/load"),
            cache: true,
            responseType:'json'
        });
    };

    factory.findAll = function() {

        return $http({
            method: 'GET',
            url: Url.get("template/backoffice_category_list/findall"),
            cache: true,
            responseType:'json'
        });
    };

    factory.save = function(data) {

        return $http({
            method: 'POST',
            data: data,
            url: Url.get("template/backoffice_category_list/save"),
            responseType:'json'
        });
    };

    return factory;
});
