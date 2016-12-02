App.service('Dialog', function($cordovaDialogs, $ionicPopup, $translate) {

    var service = {
        is_webview: null
    };

    /**
     * @param alert_title
     * @param alert_message
     * @param alert_button
     * @returns {*}
     */
    service.alert = function(alert_title, alert_message, alert_button) {

        if(!service.is_webview) {
            return $cordovaDialogs.alert(
                alert_message.replace("<br \/>", "\n\r"),
                alert_title,
                alert_button
            );
        } else {
            return $ionicPopup.alert(
                {
                    title: alert_title,
                    template: alert_message,
                    okText: alert_button
                }
            );
        }

    };

    /**
     * @param alert_message
     * @param alert_title
     * @param alert_buttons_array - ex: ['Ok', 'Cancel']
     * @returns Integer: 0 - no button, 1 - button 1, 2 - button 2
     */
    service.confirm = function(alert_title, alert_message, alert_buttons_array, alert_css_class) {
        if(!service.is_webview) {
            return $cordovaDialogs.confirm(
                alert_message.replace("<br \/>", "\n\r"),
                alert_title,
                alert_buttons_array
            );
        } else {
            return $ionicPopup.confirm({
                title: alert_title,
                cssClass: alert_css_class,
                template: alert_message,
                cancelText: alert_buttons_array[0],
                okText: alert_buttons_array[1]
            });
        }
    };

    return service;
});