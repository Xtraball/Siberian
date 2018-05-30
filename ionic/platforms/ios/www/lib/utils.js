/**
 * Calculate distance between two coordinates.
 *
 * @param latitude_a
 * @param longitude_a
 * @param latitude_b
 * @param longitude_b
 * @param unit
 * @returns {number}
 */
window.calculateDistance = function (latitude_a, longitude_a, latitude_b, longitude_b, unit) {
    var radLatitudeA = Math.PI * latitude_a / 180;
    var radLatitudeB = Math.PI * latitude_b / 180;
    var theta = longitude_a - longitude_b;
    var radTheta = Math.PI * theta/180;
    var dist = Math.sin(radLatitudeA) * Math.sin(radLatitudeB) +
        Math.cos(radLatitudeA) * Math.cos(radLatitudeB) * Math.cos(radTheta);
    dist = Math.acos(dist);
    dist = dist * 180 / Math.PI;
    dist = dist * 60 * 1.1515;

    if (unit === 'K') {
        dist *= 1.609344;
    } else if (unit === 'N') {
        dist *= 0.8684;
    }

    return dist;
};
