
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

    factory.toggle = function(code, enabled) {

        return $http({
            method: 'POST',
            data: {code: code, enabled: enabled},
            url: Url.get("translation/backoffice_list/toggle"),
            cache: false,
            responseType:'json'
        });
    };

    factory.packs = function() {
        return $http({method: 'GET', url: Url.get("translation/backoffice_pack/findall"), cache: false, responseType: 'json'});
    };

    factory.comparePack = function(code) {
        return $http({method: 'GET', url: Url.get("translation/backoffice_pack/compare", {code: code}), cache: false, responseType: 'json'});
    };

    factory.switchPack = function(code, mode) {
        return $http({method: 'POST', data: {code: code, mode: mode}, url: Url.get("translation/backoffice_pack/switch"), cache: false, responseType: 'json'});
    };

    factory.restoreBackup = function(id) {
        return $http({method: 'POST', data: {id: id}, url: Url.get("translation/backoffice_pack/restore"), cache: false, responseType: 'json'});
    };

    factory.deleteBackup = function(id) {
        return $http({method: 'POST', data: {id: id}, url: Url.get("translation/backoffice_pack/delete"), cache: false, responseType: 'json'});
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

    return factory;
});
