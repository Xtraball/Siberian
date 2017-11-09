App.directive('sbGoogleAutocomplete', function($timeout) {
    return {
        scope: {
            location: '=',
            lng:'=',
            address:'=',
            onAddressChange:'&'
        },
        link: function(scope, element, attrs) {
            var options = {
                types: [],
                componentRestrictions: {}
            };

            scope.googleAutocomplete = new google.maps.places.Autocomplete(element[0], options);

            google.maps.event.addListener(scope.googleAutocomplete, 'place_changed', function(data) {
                var place = scope.googleAutocomplete.getPlace();

                if(place.geometry) {
                    scope.location.latitude = place.geometry.location.lat();
                    scope.location.longitude = place.geometry.location.lng();
                }

                $timeout(function() {
                    scope.location.address = element.val();
                    scope.onAddressChange();
                });

            });

        }
    };
});