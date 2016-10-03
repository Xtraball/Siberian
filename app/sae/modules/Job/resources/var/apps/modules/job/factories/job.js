
App.factory('Job', function($rootScope, $http, httpCache, Url, CACHE_EVENTS, Customer) {

    var factory = {};

    factory.value_id = null;
    factory.admin_companies = null;

    factory.findAll = function(options) {

        if(!this.value_id) return;

        angular.extend(options, {
            value_id: this.value_id
        });

        if(options.position !== false) {
            angular.extend(options, {
                latitude: options.position.latitude,
                longitude: options.position.longitude,
                accuracy: options.position.accuracy
            });
        }

        return $http({
            method: 'POST',
            url: Url.get("job/mobile_list/findall"),
            data: options,
            cache: false,
            responseType:'json'
        });
    };

    factory.find = function(place_id) {

        if(!this.value_id) return;

        return $http({
            method: 'GET',
            url: Url.get("job/mobile_list/find", {value_id: this.value_id, place_id: place_id}),
            cache: false,
            responseType:'json'
        });
    };

    factory.findCompany = function(company_id) {

        if(!this.value_id) return;

        return $http({
            method: 'GET',
            url: Url.get("job/mobile_list/findcompany", {value_id: this.value_id, company_id: company_id}),
            //cache: !$rootScope.isOverview,
            cache: false,
            responseType:'json'
        });
    };

    factory.contactForm = function(values) {

        if(!this.value_id) {
            return;
        }

        return $http({
            method: 'POST',
            url: Url.get("job/mobile_list/contactform"),
            data: values,
            cache: false,
            responseType:'json'
        });
    };

    factory.editPlace = function(values) {

        if(!this.value_id) {
            return;
        }

        return $http({
            method: 'POST',
            url: Url.get("job/mobile_list/editplace"),
            data: values,
            cache: false,
            responseType:'json'
        });
    };

    factory.createPlace = function(values) {

        if(!this.value_id) {
            return;
        }

        return $http({
            method: 'POST',
            url: Url.get("job/mobile_list/createplace"),
            data: values,
            cache: false,
            responseType:'json'
        });
    };

    factory.editCompany = function(values) {

        if(!this.value_id) {
            return;
        }

        return $http({
            method: 'POST',
            url: Url.get("job/mobile_list/editcompany"),
            data: values,
            cache: false,
            responseType:'json'
        });
    };

    return factory;
});
