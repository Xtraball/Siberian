App.service("Message", function($timeout) {

    var Message = function() {

        this.is_error = false;
        this.text = "";
        this.is_visible = false;
        this.timer = null;

        this.setText = function(text) {
            this.text = text;
            return this;
        };

        this.isError = function(is_error) {
            this.is_error = is_error;
            return this;
        };

        this.show = function() {

            this.is_visible = true;

            if(this.timer) {
                $timeout.cancel(this.timer);
            }

            this.timer = $timeout(function() {
                this.is_visible = false;
                this.timer = null;
            }.bind(this), 4000);

            return this;
        };

        /** New simplified features. */
        this.onSuccess = function(data) {
            var message = "-";

            if(angular.isObject(data) && angular.isDefined(data.message)) {
                message = data.message;
                this.isError(false);
            } else {
                this.isError(true);
            }

            this.setText(message).show();
        };

        this.onError = function(data) {
            var message = "-";

            if(angular.isObject(data) && angular.isDefined(data.message)) {
                message = data.message;
            }

            this.setText(message).isError(true).show();
        };

        this.onUnknown = function(data) {
            if(angular.isObject(data) && angular.isDefined(data.success)) {
                this.onSuccess(data);
            }

            if(angular.isObject(data) && angular.isDefined(data.error)) {
                this.onError(data);
            }
        };

        this.information = function(message) {
            this.setText(message).isError(false).show();
        };

    };

    return Message;

});