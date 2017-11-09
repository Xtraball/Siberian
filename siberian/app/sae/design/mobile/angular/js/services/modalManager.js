App.service("modalManager", function($rootScope) {
    var service = {};

    service.instances = [];
    service.isDisplayed = false;

    service.show = function() {
        if(service.instances.length > 0 && !service.isDisplayed) {
            $rootScope.$broadcast("show_modal", service.instances[0]);
            service.isDisplayed = true;
        }
    };

    service.next = function() {
        service.instances.splice(-(service.instances.length), 1);
        service.isDisplayed = false;
        service.show();
    };

    service.addToQueue = function(elem) {
        service.instances.push(elem);
    };

    return service;

});