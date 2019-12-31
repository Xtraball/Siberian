/**
 *
 */
App.factory("Cabride", function ($http, Url) {
    var factory = {};

    /**
     *
     * @returns {*}
     */
    factory.loadData = function () {
        return $http({
            method: "GET",
            url: Url.get("cabride/backoffice_view/load"),
            cache: false
        });
    };

    /**
     *
     * @returns {*}
     */
    factory.liveLog = function (offset) {
        return $http({
            method: "GET",
            url: Url.get("cabride/backoffice_view/live-log", {offset: offset}),
            cache: false
        });
    };

    /**
     *
     * @param settings
     * @returns {*}
     */
    factory.save = function (settings) {
        return $http({
            method: "POST",
            url: Url.get("cabride/backoffice_view/save"),
            data: settings,
            cache: false
        });
    };

    /**
     *
     * @returns {*}
     */
    factory.restartSocket = function () {
        return $http({
            method: "POST",
            url: Url.get("cabride/backoffice_view/restart-socket"),
            data: settings,
            cache: false
        });
    };
	
    return factory;
});
