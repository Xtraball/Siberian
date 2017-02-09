
App.factory('McommerceCategory', function($rootScope, $sbhttp, Url) {

    var factory = {};

    factory.value_id = null;
    factory.category_id = null;

    factory.findAll = function(offset) {

        if(!this.value_id) return;

        return $sbhttp({
            method: 'GET',
            url: Url.get("mcommerce/mobile_category/findall", {value_id: this.value_id, category_id: this.category_id, offset: offset}),
            cache: !$rootScope.isOverview,
            responseType:'json'
        }).success(function(data) {
            if(data.displayed_per_page) {
                factory.displayed_per_page = data.displayed_per_page;
            }
        });
    };

    return factory;
});
