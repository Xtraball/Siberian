
App.service('$translate', function($http, Url, tmhDynamicLocale) {
    var service = {};

    service.translations = [];

    service.instant = function(text) {
        return angular.isDefined(service.translations[text]) ? service.translations[text] : text;
    };

    service.findTranslations = function() {
        return $http({
            method: 'GET',
            //url: Url.get("/application/mobile_translation/findall"),
            url: Url.get("/application/mobile_translation/findall", {add_language: true}),
            cache: true,
            responseType: 'json'
        }).success(function (translations) {
            service.translations = translations;

            $http({
                method: 'GET',
                url: Url.get("/application/mobile_translation/locale", {add_language: true}),
                cache: true
            }).success(function (locale) {
                tmhDynamicLocale.set(locale);
            });
        });
    };

    return service;
});
