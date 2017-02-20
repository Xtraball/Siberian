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

        console.log((new Date()).getTime(), "Pages.findAll().");

        if (factory.data === null) {
            $sbhttp({
                method: 'GET',
                url: Url.get('front/mobile_home/findall', { device_uid: Push.device_uid }),
                cache: false,
                responseType:'json'
            }).success(function(data) {
                factory.data = data;
            });
        }

        return $q(function(resolve, reject) {
            resolve(factory.data);
        });
    };

    return factory;
});
