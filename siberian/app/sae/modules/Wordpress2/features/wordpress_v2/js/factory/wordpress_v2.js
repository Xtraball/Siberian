/**
 * Folder rev 2
 *
 * @version 0.0.1
 */
angular.module('starter').factory('Wordpress2', function ($pwaRequest) {
    var factory = {
        value_id: null,
        folder_id: null,
        categories: [],
        searchIndex: [],
        showSearch: false,
        extendedOptions: {}
    };

    /**
     *
     * @param valueId
     */
    factory.setValueId = function (valueId) {
        factory.value_id = valueId;
    };

    /**
     *
     * @param options
     */
    factory.setExtendedOptions = function (options) {
        factory.extendedOptions = options;
    };

    /**
     *
     */
    factory.find = function (page, pullToRefresh) {
        return $pwaRequest.get('wordpress2/mobile_list/find', {
            urlParams: {
                value_id: factory.value_id,
                page: page,
                refresh: pullToRefresh
            }
        });
    };

    /**
     *
     */
    factory.loadposts = function (queryId, page, pullToRefresh) {
        return $pwaRequest.get('wordpress2/mobile_list/loadposts', {
            urlParams: {
                value_id: factory.value_id,
                queryId: queryId,
                page: page,
                refresh: pullToRefresh
            }
        });
    };

    return factory;
});
