
App.service('Translator', function($http, Url) {

    var service = {};

    service.translations = [];

    service.get = function(text) {
        return angular.isDefined(service.translations[text]) ? service.translations[text] : text;
    };

    service.findTranslations = function() {
        $http({
            method: 'GET',
            url: Url.get("/application/mobile_translation/findall"),
            cache: true,
            responseType: 'json'
        }).success(function (translations) {
            service.translations = translations;
        });
    }

    return service;
});
