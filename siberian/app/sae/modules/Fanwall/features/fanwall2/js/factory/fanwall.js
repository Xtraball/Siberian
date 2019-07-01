/**
 * Module FanWall
 *
 * @author Xtraball SAS <dev@xtraball.com>
 * @version 4.17.0
 */
angular.module("starter").factory("Fanwall", function ($pwaRequest) {
    var factory = {
        value_id: null,
        settings: [],
        cardDesign: false
    };

    /**
     *
     * @param value_id
     */
    factory.setValueId = function (value_id) {
        factory.value_id = value_id;
    };

    factory.loadSettings = function () {
        var payload = $pwaRequest.getPayloadForValueId(factory.value_id);
        if (payload !== false) {
            return $pwaRequest.resolve(payload);
        }

        // Otherwise fallback on PWA!
        return $pwaRequest.get("fanwall/mobile_home/load-settings", {
            urlParams: {
                value_id: factory.value_id
            }
        });
    };

    factory.toggleDesign = function () {
        factory.cardDesign = !factory.cardDesign;
    };

    return factory;
});