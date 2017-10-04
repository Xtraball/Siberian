/*global
 App, angular, BASE_PATH, IMAGE_URL
 */

angular.module("starter").controller("ContactViewController", function($rootScope, $scope, $state, $stateParams,
                                                                       $window, Contact, LinkService) {

    angular.extend($scope, {
        is_loading      : true,
        value_id        : $stateParams.value_id,
        card_design     : true
    });

    Contact.setValueId($stateParams.value_id);

    Contact.find()
        .then(function(data) {
            /** Don not alter cached data */
            $scope.contact = angular.extend({}, data.contact);
            $scope.page_title   = data.page_title;

        }).then(function() {
            $scope.is_loading = false;
        });


    $scope.call = function() {
        $window.location = "tel:"+$scope.contact.phone;
    };

    $scope.openForm = function() {
        if($rootScope.isNotAvailableOffline()) {
            return;
        }

        $state.go("contact-form", {
            value_id: $stateParams.value_id
        });
    };

    $scope.openLink = function(link) {
        if($rootScope.isNotAvailableInOverview()) {
            return;
        }

        LinkService.openLink(link);
    };

    $scope.getGeoData = function() {

        var params = "";
        if($scope.contact.coordinates) {
            params = $scope.contact.coordinates.latitude + "," + $scope.contact.coordinates.longitude;
            params += "?q="+$scope.contact.coordinates.latitude + "," + $scope.contact.coordinates.longitude;
        } else {
            var address = $scope.contact.street+", "+$scope.contact.postcode+", "+$scope.contact.city;
            params = "0,0?q="+encodeURI(address);
        }

        return params;
    };

    $scope.showMap = function() {
        if($rootScope.isNotAvailableOffline()) {
            return;
        }

        $state.go("contact-map", {
            value_id: $scope.value_id
        });
    };

    if($rootScope.isOverview) {

        $window.setCoverUrl = function(cover_url) {
            $scope.contact.cover_url = cover_url;
            $scope.$apply();
        };

        $window.setAttribute = function(attribute, value) {
            $scope.contact[attribute] = value;
            $scope.$apply();
        };

        $scope.$on("$destroy", function() {
            $window.setCoverUrl = null;
            $window.setAttribute = null;
        });
    }

}).controller("ContactFormController", function($translate, $scope, $state, $stateParams, Contact, Dialog, Loader) {

    angular.extend($scope, {
        value_id        : $stateParams.value_id,
        is_loading      : false,
        card_design     : false,
        form: {
            name    : "",
            email   : "",
            info    : ""
        }
    });

    Contact.setValueId($stateParams.value_id);

    $scope.submitForm = function() {

        Loader.show();

        Contact.submitForm($scope.form)
            .then(function(data) {

                $scope.form = {
                    name: "",
                    email: "",
                    info: ""
                };

                Dialog.alert($translate.instant("Success"), data.message, $translate.instant("OK"), -1)
                    .then(function() {
                        /** On auto-dismiss or click. */
                        $state.go("contact-view", {
                            value_id: $stateParams.value_id
                        });
                    });

            }, function(data) {

                if(data && angular.isDefined(data.message)) {
                    Dialog.alert($translate.instant("Error"), data.message, $translate.instant("OK"), -1);
                }

            }).then(function() {
                Loader.hide();
            });
    };

}).controller("ContactMapController", function($scope, $stateParams, Contact) {

    $scope.value_id     = $stateParams.value_id;
    Contact.value_id    = $stateParams.value_id;

    Contact.find()
        .then(function(data) {

            $scope.contact = data.contact;
            $scope.page_title = data.page_title;
            var address = $scope.contact.street + ", " + $scope.contact.postcode + ", " + $scope.contact.city;

            var marker = {
                title: data.contact.name + "<br />" + address,
                is_centered: true
            };

            if(data.contact.coordinates) {
                marker.latitude = data.contact.coordinates.latitude;
                marker.longitude = data.contact.coordinates.longitude;
            } else {
                marker.address = address;
            }

            if(data.contact.cover_url) {
                marker.icon = {
                    url: data.contact.cover_url,
                    width: 49,
                    height: 49
                };
            }

            $scope.map_config = {
                markers: [marker]
            };

            $scope.is_loading = false;

        });

});
;/**
 * Contact
 *
 * @author Xtraball SAS
 */
angular.module('starter').factory('Contact', function ($pwaRequest) {
    var factory = {
        value_id: null,
        extendedOptions: {}
    };

    /**
     *
     * @param value_id
     */
    factory.setValueId = function (value_id) {
        factory.value_id = value_id;
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
            return $pwaRequest.reject('[Factory::Contact.find] missing value_id');
        }

        var payload = $pwaRequest.getPayloadForValueId(factory.value_id);
        if (payload !== false) {
            return $pwaRequest.resolve(payload);
        }

        /** Otherwise fallback on PWA */
        return $pwaRequest.get('contact/mobile_view/find', angular.extend({
            urlParams: {
                value_id: this.value_id
            }
        }, factory.extendedOptions));
    };

    factory.submitForm = function (form) {
        if (!this.value_id) {
            return $pwaRequest.reject('[Factory::Contact.submitForm] missing value_id');
        }

        return $pwaRequest.post('/contact/mobile_form/post', {
            urlParams: {
                value_id: this.value_id
            },
            data: form,
            cache: false
        });
    };

    return factory;
});
