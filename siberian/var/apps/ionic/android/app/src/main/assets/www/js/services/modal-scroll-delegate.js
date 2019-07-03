/**
 * Modal scroll delegate
 */
angular
.module("starter")
.service('ModalScrollDelegate', function ($ionicScrollDelegate) {
    var service = {
        $getByHandle: function(name) {
            var instances = $ionicScrollDelegate.$getByHandle(name)._instances;
            return instances.filter(function(element) {
                return (element['$$delegateHandle'] == name);
            })[0];
        }
    };

    return service;
});
