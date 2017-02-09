App.service('$translate', function($sbhttp, Url, tmhDynamicLocale) {
    var service = {};

    service.translations = [];

    service.instant = function(text) {
        return angular.isDefined(service.translations[text]) ? service.translations[text] : text;
    };

    service.findTranslations = function(is_retry) {
        return $sbhttp({
            method: 'GET',
            url: Url.get("/application/mobile_translation/findall", {add_language: true}),
            cache: true,
            responseType: 'json',
            timeout: 4000
        }).success(function (translations) {
            service.translations = translations;

            $sbhttp({
                method: 'GET',
                url: Url.get("/application/mobile_translation/locale", {add_language: true}),
                cache: true
            }).success(function (locale) {
                tmhDynamicLocale.set(locale);
            });
        }).error(function() {
            if(is_retry !== true) {
                return service.findTranslations(true);
            }
        });
    };

    return service;
});
