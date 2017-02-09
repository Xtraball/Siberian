
App.factory('Padlock', function($rootScope, $sbhttp, httpCache, Url) {

    var factory = {};

    factory.value_id = null;
    factory.events = {};

    factory.onStatusChange = function(id, urls) {
        factory.events[id] = urls;
    };

    factory.flushData = function() {

        for(var i in factory.events) {

            if(angular.isArray(factory.events[i])) {
                var data = factory.events[i];
                for(var j = 0; j < data.length; j++) {
                    httpCache.remove(data[j]);
                }
            }

        }
    };

    factory.findUnlockTypes = function() {
        return $sbhttp({
            method: 'GET',
            url: Url.get("padlock/mobile_view/findunlocktypes", {value_id: this.value_id}),
            cache: !$rootScope.isOverview,
            responseType:'json'
        });
    };

    factory.find = function() {

        if(!angular.isDefined(this.value_id)) return;

        return $sbhttp({
            method: 'GET',
            url: Url.get("padlock/mobile_view/find", {value_id: this.value_id}),
            cache: !$rootScope.isOverview,
            responseType:'json'
        });
    };

    factory.unlockByQRCode = function(qrcode) {
        var url = Url.get("padlock/mobile_view/unlockByQRCode");
        var data = {
            qrcode: qrcode
        };

        return $sbhttp.post(url, data);
    };

    return factory;
});
