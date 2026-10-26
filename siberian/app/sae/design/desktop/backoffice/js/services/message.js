App.service("Message", function($timeout) {

    var Message = function() {

        this.is_error = false;
        // success, error, warning or info: drives the color and the icon of the message at the top
        this.type = "success";
        this.text = "";
        this.is_visible = false;
        this.timer = null;

        this.setText = function(text) {
            this.text = text;
            return this;
        };

        this.isError = function(is_error) {
            this.is_error = is_error;
            this.type = is_error ? "error" : "success";
            return this;
        };

        this.isWarning = function() {
            this.is_error = false;
            this.type = "warning";
            return this;
        };

        this.isInfo = function() {
            this.is_error = false;
            this.type = "info";
            return this;
        };

        this.getIcon = function() {
            return {
                success: "fa-check-circle",
                error: "fa-times-circle",
                warning: "fa-exclamation-triangle",
                info: "fa-info-circle"
            }[this.type] || "fa-info-circle";
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
            this.setText(message).isInfo().show();
        };

        this.warning = function(message) {
            this.setText(message).isWarning().show();
        };

    };

    return Message;

});