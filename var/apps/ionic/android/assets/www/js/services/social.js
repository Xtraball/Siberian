App.service('Social', function($cordovaDialogs, $cordovaSocialSharing, $location, $translate, Application) {

    var service = {
        is_webview: null,
        is_sharing: false
    };

    /**
     * Unified social sharing
     *
     * @param message
     * @param link
     * @param file
     */
    service.share = function(message, subject, link, file) {
        if(service.is_sharing) {
            return;
        }

        service.is_sharing = true;

        /** For mobile */
        var download_app_link = DOMAIN + "/application/device/downloadapp/app_id/" + Application.app_id;

        /** For browser */
        var html5_app_link = DOMAIN + "/" + Application.app_id + "?__goto__=" + $location.path;

        /** Generic message */
        var generic_message = $translate.instant("Hi. I just found $1 in the $2 app.").replace("$2", Application.app_name);

        var _link = download_app_link;
        var _file = (typeof file == "undefined") ? "" : file;
        var _message = (typeof message == "undefined") ? generic_message : message;
        var _subject = (typeof subject == "undefined") ? "" : subject;

        $cordovaSocialSharing.share(_message, _subject, _file, _link).then(
            function (result) {
                service.is_sharing = false;
            }, function (err) {
                service.is_sharing = false;
            }
        );

    };

    return service;
});