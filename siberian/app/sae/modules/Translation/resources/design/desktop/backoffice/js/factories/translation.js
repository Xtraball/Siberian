
App.factory('Translations', function($http, Url) {

    var factory = {};

    factory.loadData = function() {

        var url = "translation/backoffice_"+this.type;

        return $http({
            method: 'GET',
            url: Url.get(url+"/load"),
            cache: true,
            responseType:'json'
        });
    };

    factory.findAll = function() {

        var url = "translation/backoffice_list/findall";

        return $http({
            method: 'GET',
            url: Url.get(url),
            cache: false,
            responseType:'json'
        });
    };

    factory.find = function(lang_id) {

        var param = {};
        if(lang_id) {
            param = {langId: lang_id+""};
        }
        var url = "translation/backoffice_"+this.type;

        return $http({
            method: 'GET',
            url: Url.get(url+"/find", param),
            cache: false,
            responseType:'json'
        });
    };

    factory.save = function(values) {

        var url = "translation/backoffice_"+this.type;

        return $http({
            method: 'POST',
            data: values,
            url: Url.get(url+"/save"),
            cache: false,
            responseType:'json'
        });

    };

    factory.suggest = function(values) {
        return $http({
            method: 'POST',
            data: values,
            url: Url.get("translation/backoffice_edit/suggest"),
            cache: false,
            responseType:'json'
        });
    };

    return factory;
});
