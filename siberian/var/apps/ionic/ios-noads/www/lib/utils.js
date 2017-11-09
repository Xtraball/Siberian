/**
 * Calculate distance between two coordinates.
 *
 * @param lat1
 * @param lon1
 * @param lat2
 * @param lon2
 * @param unit
 * @returns {number}
 */
window.calculateDistance = function(latitude_a, longitude_a, latitude_b, longitude_b, unit) {
    var rad_latitude_a  = Math.PI * latitude_a / 180;
    var rad_latitude_b  = Math.PI * latitude_b / 180;
    var theta           = longitude_a - longitude_b;
    var rad_theta       = Math.PI * theta/180;
    var dist            = Math.sin(rad_latitude_a) * Math.sin(rad_latitude_b) + Math.cos(rad_latitude_a) * Math.cos(rad_latitude_b) * Math.cos(rad_theta);
    dist                = Math.acos(dist);
    dist                = dist * 180 / Math.PI;
    dist                = dist * 60 * 1.1515;

    if (unit==="K") {
        dist = dist * 1.609344;
    } else if (unit==="N") {
        dist = dist * 0.8684;
    }

    return dist;
};