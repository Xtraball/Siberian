/*global
    App
 */
App.factory('Pages', function ($sbhttp, $q, Push, Url) {

    var factory = {};

    factory.data = null;

    /**
     * @returns {*}
     */
    factory.findAll = function() {
        var q = $q.defer();

        console.log((new Date()).getTime(), "Pages.findAll().");

        if (factory.data === null) {
            $sbhttp({
                method: 'GET',
                url: Url.get('front/mobile_home/findall', { device_uid: Push.device_uid }),
                cache: false,
                responseType:'json'
            }).success(function(data) {
                factory.data = data;
                q.resolve(factory.data);
            }).error(q.reject);
        } else {
            q.resolve(factory.data);
        }

        return q.promise;
    };

    return factory;
});
