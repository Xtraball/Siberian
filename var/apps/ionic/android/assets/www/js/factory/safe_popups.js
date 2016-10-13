App.factory('SafePopups', [
    '$ionicPopup',
    '$q',
    function ($ionicPopup, $q) {

        var firstDeferred = $q.defer();
        firstDeferred.resolve();

        var lastPopupPromise = firstDeferred.promise;

        return {
            'show': function (method, object) {
                var deferred = $q.defer();

                lastPopupPromise.then(function () {
                    var popup_promise = $ionicPopup[method](object);
                    deferred.promise.close = popup_promise.close;
                    popup_promise.then(function (res) {
                        deferred.resolve(res);
                    });
                });

                lastPopupPromise = deferred.promise;

                return lastPopupPromise;
            }
        };
    }
])
