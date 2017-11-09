/*global
    App, DOMAIN
 */

/**
 * SocialSharing
 *
 * @author Xtraball SAS
 */
angular.module("starter").service("SocialSharing", function($cordovaSocialSharing, $translate, $q, Application) {

    var service = {
        is_sharing: false
    };

    /**
     * Unified social sharing
     *
     * @param content
     * @param message
     * @param subject
     * @param link
     * @param file
     */
    service.share = function(content, message, subject, link, file) {

        if(service.is_sharing) {
            return;
        }

        service.is_sharing = true;

        if(content === undefined) {
            content = "this";
        }

        /** For mobile */
        var download_app_link = DOMAIN + "/application/device/downloadapp/app_id/" + Application.app_id;

        /** Generic message */
        var generic_message = $translate.instant("Hi. I just found $1 in the $2 app.")
                                .replace("$1", content)
                                .replace("$2", Application.app_name);

        if(message !== undefined) {
            message = $translate.instant(message)
                .replace("$1", content)
                .replace("$2", Application.app_name);
        }

        var _link       = (link === undefined) ? download_app_link : link;
        var _file       = (file === undefined) ? "" : file;
        var _message    = (message === undefined) ? generic_message : message;
        var _subject    = (subject === undefined) ? "" : subject;

        var deferred = $q.defer();

        $cordovaSocialSharing
            .share(_message, _subject, _file, _link)
            .then(function (result) {
                deferred.resolve(result);
                service.is_sharing = false;
            }, function (error) {
                deferred.reject(error);
                service.is_sharing = false;
            });

        return deferred.promise;

    };

    return service;
});