angular.module("starter").directive('sbGoogleAutocomplete', function(GoogleMaps, $timeout) {
    return {
        scope: {
            location: '=?',
            address:'=?',
            place: '=?',
            onAddressChange:'&?'
        },
        require: '?ngModel', // get a hold of NgModelController
        link: function(scope, element, attrs, ngModel) {

            var options = {
                types: []
            };

            element.on("keydown", function(e) {
                if(
                    e.which == 13 &&
                        _.get(
                            document.getElementsByClassName("pac-container"),
                            "[0].style.display"
                        ) === ""
                ) e.preventDefault();
            });

            GoogleMaps.addCallback(function() {
                scope.googleAutocomplete = new google.maps.places.Autocomplete(element[0], options);

                google.maps.event.addListener(scope.googleAutocomplete, 'place_changed', function(data) {

                    var place = scope.googleAutocomplete.getPlace();
                    scope.place = place;

                    if(place.geometry) {
                        if(!angular.isObject(scope.location))
                            scope.location = {};

                        scope.location.latitude = place.geometry.location.lat();
                        scope.location.longitude = place.geometry.location.lng();
                    }

                    var val = element.val();

                    if(angular.isObject(ngModel) && angular.isFunction(ngModel.$setViewValue)) {
                        ngModel.$setViewValue(val, "keyup");
                    }

                    $timeout(function() {
                        scope.location.address = val;
                        scope.onAddressChange(scope);
                    });

                });
            });

        }
    };
});
