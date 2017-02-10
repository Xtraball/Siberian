App.factory('Cms', function ($rootScope, $sbhttp, Url) {

    var factory = {};

    factory.value_id = null;

    factory.findAll = function (page_id) {
        if (!this.value_id) return;

        return $sbhttp({
            method: 'GET',
            url: Url.get("cms/mobile_page_view/findall", {
                page_id: page_id,
                value_id: this.value_id
            }),
            cache: !$rootScope.isOverview,
            responseType: 'json'
        });
    };

    factory.find = function (page_id) {

        if (!this.value_id) return;

        return $sbhttp({
            method: 'GET',
            url: Url.get("cms/mobile_page_view/find", {
                page_id: page_id,
                value_id: this.value_id
            }),
            cache: !$rootScope.isOverview,
            responseType: 'json'
        });
    };

    factory.findBlock = function (block_id, page_id) {

        if (!this.value_id) return;

        return $sbhttp({
            method: 'GET',
            url: Url.get("cms/mobile_page_view/findblock", {
                block_id: block_id,
                page_id: page_id,
                value_id: this.value_id
            }),
            cache: !$rootScope.isOverview,
            responseType: 'json'
        });
    };

    factory.loadPrivacypolicy = function(value_id) {
        return $sbhttp({
            method: 'GET',
            url: Url.get("cms/mobile_privacypolicy/find", {value_id: value_id}),
            cache: !$rootScope.isOverview,
            responseType: 'json'
        });
    };

    return factory;
});
