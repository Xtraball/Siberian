/*global
 App, BASE_PATH
 */

angular.module("starter").controller("RadioController", function ($scope, $stateParams, MusicTracksLoader, MediaPlayer,
                                                                  Radio) {

    Radio.setValueId($stateParams.value_id);

    $scope.loadContent = function () {

        Radio.find()
            .then(function (data) {
                var tracks_loader = MusicTracksLoader.loadSingleTrack({
                    name        : data.radio.title,
                    artistName  : "",
                    streamUrl   : data.radio.url,
                    albumCover  : data.radio.background,
                    albumName   : ""
                });

                MediaPlayer.init(tracks_loader, true, 0);
            });

    };

    $scope.loadContent();

});;/*global
 App, device, angular
 */

/**
 * Radio
 *
 * @author Xtraball SAS
 */
angular.module('starter').factory('Radio', function ($pwaRequest) {
    var factory = {
        value_id: null,
        extendedOptions: {}
    };

    /**
     *
     * @param valueId
     */
    factory.setValueId = function (valueId) {
        factory.value_id = valueId;
    };

    /**
     *
     * @param options
     */
    factory.setExtendedOptions = function (options) {
        factory.extendedOptions = options;
    };


    factory.find = function () {
        if (!this.value_id) {
            return $pwaRequest.reject('[Factory::Radio.find] missing value_id');
        }

        /* Instant content */
        var payload = $pwaRequest.getPayloadForValueId(factory.value_id);
        if (payload !== false) {
            return $pwaRequest.resolve(payload);
        }

        /** Otherwise fallback on PWA */
        return $pwaRequest.get('radio/mobile_radio/find', angular.extend({
            urlParams: {
                value_id: this.value_id
            }
        }, factory.extendedOptions));
    };

    return factory;
});
