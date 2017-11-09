App.service("SectionButton", function() {

    var service = function(action) {

        this.text = '<i class="fa fa-plus"></i>';
        this.action = function() {};

        this.setText = function(text) {
            this.text = text;
            return this;
        };

        this.setAction = function(action) {
            if(angular.isFunction(action)) {
                this.action = action;
            }
            return this;
        };

        this.setAction(action);

    };

    return service;
});