
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

    factory.save = function(categories) {

        return $http({
            method: 'POST',
            data: {
                categories: categories
            },
            url: Url.get("template/backoffice_category_list/save"),
            responseType:'json'
        });
    };

    /**
     * Toggle a template enable state
     *
     * @param templateId
     * @returns {*}
     */
    factory.toggleTemplate = function (templateId, isActive) {
        return $http({
            method: 'POST',
            url: Url.get('template/backoffice_design/toggleactive'),
            data: {
                templateId: templateId,
                isActive: isActive
            },
            cache: false,
            responseType: 'json'
        });
    };

    return factory;
});
