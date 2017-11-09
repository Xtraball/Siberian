
App.factory('McommerceCart', function($rootScope, $http, Url) {

    var factory = {};

    factory.value_id = null;

    factory.find = function() {

        if(!this.value_id) return;

        return $http({
            method: 'GET',
            url: Url.get("mcommerce/mobile_cart/find", {value_id: this.value_id}),
            cache: false,
            responseType:'json'
        });
    };
    
    factory.addProduct = function (form) {

        if (!this.value_id) return;

        var url = Url.get("mcommerce/mobile_cart/add", {value_id: this.value_id});
        
        var data = {form: form};

        return $http.post(url, data);
    };
    
    factory.deleteLine = function (line_id) {

        if (!this.value_id) return;
        
        if (!line_id) return;

        var url = Url.get("mcommerce/mobile_cart/delete", {value_id: this.value_id, line_id: line_id});
        
        var data = {};
        
        return $http.delete(url, data);
                                          
    };

    factory.modifyLine = function (line) {

        if (!this.value_id) return;

        return $http({
            method: 'POST',
            data: {line_id: line.id, qty : line.qty, format: line.format},
            url: Url.get("mcommerce/mobile_cart/modify"),
            cache: false,
            responseType:'json'
        });

    };

    return factory;
});
