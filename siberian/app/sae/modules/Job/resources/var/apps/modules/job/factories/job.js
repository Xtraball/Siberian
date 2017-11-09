/*global
   App, angular
 */

/**
 * Job
 *
 * Xtraball SAS
 */
angular.module("starter").factory("Job", function($rootScope, $pwaRequest) {

    var factory = {};

    factory.value_id = null;
    factory.admin_companies = null;
    factory.options =  {
        display_place_icon: false,
        display_search: true,
        display_income: true
    };

    factory.setValueId = function(value_id) {
        factory.value_id = value_id;
    };

    factory.findAll = function(options, refresh) {

        if(!this.value_id) {
            return $pwaRequest.reject("[Factory::Job.findAll] missing value_id.");
        }

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

        return $pwaRequest.post("job/mobile_list/findall", {
            urlParams: {
                value_id: this.value_id
            },
            refresh: refresh,
            data: options,
            cache: !$rootScope.isOverview
        });
    };

    factory.find = function(place_id) {

        if(!this.value_id || (place_id === undefined)) {
            return $pwaRequest.reject("[Factory::Job.find] missing value_id or place_id.");
        }

        return $pwaRequest.get("job/mobile_list/find", {
            urlParams:  {
                value_id: this.value_id,
                place_id: place_id
            }
        });
    };

    factory.findCompany = function(company_id) {

        if(!this.value_id || (company_id === undefined)) {
            return $pwaRequest.reject("[Factory::Job.findCompany] missing value_id or company_id.");
        }

        return $pwaRequest.get("job/mobile_list/findcompany", {
            urlParams: {
                value_id: this.value_id,
                company_id: company_id
            }
        });
    };

    factory.contactForm = function(values) {

        if(!this.value_id) {
            return $pwaRequest.reject("[Factory::Job.contactForm] missing value_id.");
        }

        return $pwaRequest.post("job/mobile_list/contactform", {
            data: values,
            cache: false
        });
    };

    factory.editPlace = function(values) {

        if(!this.value_id) {
            return $pwaRequest.reject("[Factory::Job.editPlace] missing value_id.");
        }

        return $pwaRequest.post("job/mobile_list/editplace", {
            data: values,
            cache: false
        });
    };

    factory.createPlace = function(values) {

        if(!this.value_id) {
            return $pwaRequest.reject("[Factory::Job.createPlace] missing value_id.");
        }

        return $pwaRequest.post("job/mobile_list/createplace", {
            data: values,
            cache: false
        });
    };

    factory.editCompany = function(values) {

        if(!this.value_id) {
            return $pwaRequest.reject("[Factory::Job.editCompany] missing value_id.");
        }

        return $pwaRequest.post("job/mobile_list/editcompany", {
            data: values,
            cache: false
        });
    };

    return factory;
});
