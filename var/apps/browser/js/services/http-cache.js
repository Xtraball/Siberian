App.service("httpCache", function($sbhttp, $cacheFactory, Connection) {
    return {
        remove: function(url) {
            var sid = localStorage.getItem("sb-auth-token");
            if(sid && url.indexOf(".html") == -1 && Connection.isOnline) {
                url = url + "?sb-token=" + sid;
            }

            if(angular.isDefined($cacheFactory.get('$http').get(url))) {
                $cacheFactory.get('$http').remove(url);
            }

            $sbhttp.removeCached(url);

            return this;
        }
    };
});
