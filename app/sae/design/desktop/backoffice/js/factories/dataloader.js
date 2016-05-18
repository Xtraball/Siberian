App.factory('DataLoader', function($http, $q, $timeout, Url) {

    return function DataLoader() {

        var _that = this;
        _that.deferred = $q.defer();
        _that.offset = null;
        _that.data = new Array();

        this.sequencedLoading = function(data_url) {
            $http({
                method: 'GET',
                url: Url.get(data_url, {offset: this.offset}),
                cache: false,
                responseType:'json'
            }).success(function(data){
                _that.data = _that.data.concat(data.collection);
                if(data.collection.length > 0) {
                    _that.offset = _that.data.length;
                    if(data.collection.length == data.display_per_page) {
                        $timeout(function () {
                            _that.sequencedLoading(data_url)
                        }, 100);
                    } else {
                        _that.deferred.resolve(_that.data);
                    }
                } else {
                    _that.deferred.resolve(_that.data);
                }
            });

            return this.deferred.promise;
        };

    };

});
