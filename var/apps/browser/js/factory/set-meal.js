
App.factory('SetMeal', function($rootScope, $sbhttp, Url) {

    var factory = {};

    factory.value_id = null;
    factory.displayed_per_page = null;

    factory.findAll = function(offset) {

        if(!this.value_id) return;

        return $sbhttp({
            method: 'GET',
            url: Url.get("catalog/mobile_setmeal_list/findall", {value_id: this.value_id, offset: offset}),
            cache: !$rootScope.isOverview,
            responseType:'json'
        });
    };

    factory.find = function(set_meal_id) {

        if(!this.value_id) return;

        var url = Url.get('catalog/mobile_setmeal_view/find', {set_meal_id: set_meal_id, value_id: this.value_id});

        return $sbhttp({
            method: 'GET',
            url: url,
            cache: !$rootScope.isOverview,
            responseType:'json'
        });
    };

    return factory;
});
