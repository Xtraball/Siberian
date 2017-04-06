
App.factory('McommerceCart', function($rootScope, $sbhttp, httpCache, Url, $q) {

    var factory = {};

    factory.value_id = null;

    factory.find = function() {

        if(!this.value_id) return;

        return $sbhttp({
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

        return $sbhttp.post(url, data).success(function() {
            httpCache.remove(Url.get("mcommerce/mobile_cart/find", {value_id: this.value_id}));
        });
    };


    factory.adddiscount = function (discount_code, use_clean_code) {

        if (!this.value_id) return use_clean_code ? $q.reject() : false;

        //if no discount added, it's valid
        if(discount_code.length === 0 && !use_clean_code) return true;

        var url = Url.get("mcommerce/mobile_cart/adddiscount", {value_id: this.value_id});

        var data = {discount_code: discount_code, customer_uuid: window.device.uuid};

        return $sbhttp.post(url, data);
    };

    factory.addTip = function (cart) {

        if (!this.value_id) return;

        var url = Url.get("mcommerce/mobile_cart/addtip", {value_id: this.value_id});

        var data = {tip: cart.tip ? cart.tip : 0};

        return $sbhttp.post(url, data).success(function() {
            httpCache.remove(Url.get("mcommerce/mobile_cart/find", {value_id: this.value_id}));
        });
    };

    factory.compute = function () {

        if (!this.value_id) return;

        var url = Url.get("mcommerce/mobile_cart/compute", {value_id: this.value_id, customer_uuid: window.device.uuid});

        var data = {};

        return $sbhttp.post(url, data).success(function(results) {
            httpCache.remove(Url.get("mcommerce/mobile_cart/find", {value_id: this.value_id}));
        });
    };

    factory.deleteLine = function (line_id) {

        if (!this.value_id) return;
        
        if (!line_id) return;

        var url = Url.get("mcommerce/mobile_cart/delete", {value_id: this.value_id, line_id: line_id});
        
        var data = {};
        
        return $sbhttp.get(url, data);
                                          
    };

    factory.modifyLine = function (line) {

        if (!this.value_id) return;

        return $sbhttp({
            method: 'POST',
            data: {line_id: line.id, qty : line.qty, format: line.format},
            url: Url.get("mcommerce/mobile_cart/modify"),
            cache: false,
            responseType:'json'
        });

    };

    factory.useFidelityPoints = function(points) {
        if (!this.value_id) return;

        return $sbhttp({
            method: 'POST',
            data: {points: points},
            url: Url.get("mcommerce/mobile_cart/usefidelitypointsforcart"),
            cache: false,
            responseType:'json'
        });
    };

    factory.removeAllDiscount = function() {
        if (!this.value_id) return;

        return $sbhttp({
            method: 'POST',
            url: Url.get("mcommerce/mobile_cart/removealldiscount"),
            cache: false,
            responseType:'json'
        });
    };

    return factory;
});
