/**
 * Radio
 *
 * @author Xtraball SAS <dev@xtraball.com>
 * @version 4.18.17
 */
angular
    .module('starter')
    .factory('Radio', function ($pwaRequest, MusicTracksLoader, MediaPlayer) {
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

        factory.openCallback = function (feature) {
            factory.setValueId(feature.value_id);
            factory
                .find()
                .then(function (data) {
                    var tracksLoader = MusicTracksLoader.loadSingleTrack({
                        name: data.radio.title,
                        artistName: '',
                        streamUrl: data.radio.url,
                        albumCover: data.radio.background,
                        albumName: ''
                    });

                    MediaPlayer.init(tracksLoader, true, 0);
                });
        };

        return factory;
    });
