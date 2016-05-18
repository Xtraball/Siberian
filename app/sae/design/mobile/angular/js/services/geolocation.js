App.service("Geolocation", function(Application) {

    var factory = {};
    factory.origLatitude = null;
    factory.origLongitude = null;

    factory.calcDistance = function(latitude, longitude) {

        if(!factory.origLatitude || !factory.origLongitude) return null;
        var rad = Math.PI / 180;
        var lat_a = this.origLatitude * rad;
        var lat_b = latitude * rad;
        var lon_a = this.origLongitude * rad;
        var lon_b = longitude * rad;

        var distance = 2 * Math.asin(Math.sqrt(Math.pow(Math.sin((lat_a-lat_b)/2) , 2) + Math.cos(lat_a)*Math.cos(lat_b)* Math.pow(Math.sin((lon_a-lon_b)/2) , 2)));

        distance *= 6378;

        return !isNaN(distance) ? parseFloat(distance.toFixed(2)) : null;

    }

    factory.refreshPosition = function(success, error) {

        Application.getLocation(function(params) {
            factory.origLatitude = params.coords.latitude;
            factory.origLongitude = params.coords.longitude;
            if(angular.isFunction(success)) {
                success(params);
            }
        }, error);
    }

    return factory;

});