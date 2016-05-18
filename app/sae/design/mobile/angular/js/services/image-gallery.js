App.service("ImageGallery", function($location, Url) {

    var body = angular.element(document.body);
    var service = {};
    service.index = 0;
    service.is_visible = false;
    service.images = new Array();

    service.show = function(images, index) {
        body.addClass("no_scroll");
        service.images = images;
        service.index = index;
        $location.path(Url.get("/gallery/view"));
    };

    service.hide = function(index) {
        body.removeClass("no_scroll");
        service.index = index;
    };

    return service;

});