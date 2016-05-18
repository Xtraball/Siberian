App.factory('Places', function ($rootScope, $http, Url) {

    var factory = {};

    factory.value_id = null;

    factory.find = function (place_id) {

        if (!this.value_id) return;

        return $http({
            method: 'GET',
            url: Url.get("places/mobile_view/find", {
                value_id: this.value_id,
                place_id: place_id
            }),
            cache: !$rootScope.isOverview,
            responseType: 'json'
        });
    };

    factory.findAll = function (position) {

        if (!this.value_id) return;

        var parameters = {
            value_id: this.value_id
        };

        if (angular.isObject(position)) {
            parameters.latitude = position.latitude;
            parameters.longitude = position.longitude;
        }

        return $http({
            method: 'GET',
            url: Url.get("places/mobile_list/findall", parameters),
            cache: !$rootScope.isOverview,
            responseType: 'json'
        });
    };

    return factory;
});