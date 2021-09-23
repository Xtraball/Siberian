
App.factory('TemplateIcons', function($http, Url) {

    var factory = {};

    factory.loadData = function() {
        return $http({
            method: 'GET',
            url: Url.get("template/backoffice_icons_list/load"),
            cache: true,
            responseType:'json'
        });
    };

    factory.findAll = function() {
        return $http({
            method: 'GET',
            url: Url.get("template/backoffice_icons_list/findall"),
            cache: true,
            responseType:'json'
        });
    };

    factory.saveSettings = function(settings) {
        return $http({
            method: 'POST',
            data: settings,
            url: Url.get("template/backoffice_icons_list/save-settings"),
            responseType:'json'
        });
    };

    /**
     * Toggle an icon enable state
     *
     * @param iconId
     * @param isActive
     * @returns {*}
     */
    factory.toggleIcon = function (iconId, isActive) {
        return $http({
            method: 'POST',
            url: Url.get('template/backoffice_icons_list/toggle-active'),
            data: {
                iconId: iconId,
                isActive: isActive
            },
            cache: false,
            responseType: 'json'
        });
    };

    return factory;
});
