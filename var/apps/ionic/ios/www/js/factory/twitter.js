"use strict";

App.factory('Twitter', function ($cacheFactory, $http, $q, $rootScope, Url) {

    var self = this;

    /** Features */
    self.value_id = null;
    self.last_id = null;

    self.loadData = function () {

        if (!self.value_id) {
            return;
        }

        var data = {value_id: self.value_id}
        if (self.last_id) {
            data['last_id'] = self.last_id;
        }

        return $http({
            method: 'GET',
            url: Url.get("twitter/mobile_twitter/list", data),
            cache: false,
            withCredentials: false,
            responseType: 'json'
        });
    };

    self.getInfo = function () {

        if (!self.value_id) {
            return;
        }

        return $http({
            method: 'GET',
            url: Url.get("twitter/mobile_twitter/info", {value_id: self.value_id}),
            cache: false,
            withCredentials: false,
            responseType: 'json'
        });
    };

    return self;
});