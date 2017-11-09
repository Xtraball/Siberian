App.service("httpCache", function($http, $cacheFactory) {
    return {
        remove: function(url) {
            if(angular.isDefined($cacheFactory.get('$http').get(url))) {
                $cacheFactory.get('$http').remove(url);
            }

            return this;
        }
    }
});
