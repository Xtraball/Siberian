"use strict";

App.service('MathsMapService', function () {

    return {
        _updateBoundsFromPoints: function (bounds, p) {
            var latitude = parseFloat(p.latitude);
            var longitude = parseFloat(p.longitude);
            
            if (bounds[0][0] === null || bounds[0][0] > latitude) {
                bounds[0][0] = latitude;
            }

            if (bounds[1][0] === null || bounds[1][0] < latitude) {
                bounds[1][0] = latitude;
            }

            if (bounds[0][1] === null || bounds[0][1] > longitude) {
                bounds[0][1] = longitude;
            }

            if (bounds[1][1] === null || bounds[1][1] < longitude) {
                bounds[1][1] = longitude;
            }

            return bounds;
        },

        _extendBounds: function (bounds, margin) {
            if (margin) {
                var latitudeMargin = (bounds[1][0] - bounds[0][0]) * margin;
                if (latitudeMargin === 0) {
                    latitudeMargin = 0.02;
                }
                bounds[0][0] -= latitudeMargin;
                bounds[1][0] += latitudeMargin

                var longitudeMargin = (bounds[1][1] - bounds[0][1]) * margin;
                if (longitudeMargin === 0) {
                    longitudeMargin = 0.01;
                }
                bounds[0][1] -= longitudeMargin;
                bounds[1][1] += longitudeMargin
            }

            return bounds;
        },

        getBoundsFromPoints: function (points, margin) {
            var self = this;

            if (points) {
                var bounds = [[null, null], [null, null]];

                points.reduce(function (output, p) {

                    if (!p.latitude) {
                        console.warn('Invalid latitude.');
                    }

                    if (!p.longitude) {
                        console.warn('Invalid longitude.');
                    }
                    bounds = self._updateBoundsFromPoints(bounds, p);

                    return output;
                }, []);

                if (points.length !== 0 && margin) {
                    bounds = this._extendBounds(bounds, margin);
                }
                return {
                    latitudeMin: bounds[0][0],
                    latitudeMax: bounds[1][0],
                    longitudeMin: bounds[0][1],
                    longitudeMax: bounds[1][1]
                };
            }
            return null;
        }
    };
});