App.service("Header", function($window) {

    var service = function() {

        this.title = "";
        this.loader_is_visible = false;

        var button = {
            left: {
                is_visible: false,
                title: "Back",
                action: function() {
                    $window.history.back();
                }
            },
            right: {
                is_visible: false,
                title: "Next",
                action: function() {

                }
            }
        };

        this.button = button;

    }

    return service;

});