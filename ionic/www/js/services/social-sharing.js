/**
 * SocialSharing
 *
 * @author Xtraball SAS
 * @version 4.20.9
 */
angular
    .module('starter')
    .service('SocialSharing', function ($cordovaSocialSharing, $translate, $ionicActionSheet, $q,
                                        Application, Dialog, LinkService) {
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
         *
         * @return Promise promise
         */
        service.share = function (content, message, subject, link, file) {
            if (service.is_sharing) {
                return $q.reject();
            }

            service.is_sharing = true;

            if (content === undefined) {
                content = $translate.instant('this', 'social_sharing');
            }

            // For mobile! (uses the share domain, which can be the whitelabel
            var domain = Application.application.share_domain;
            var download_app_link = 'https://' + domain + '/application/device/downloadapp/app_id/' + Application.app_id;

            // Generic message!
            var generic_message = $translate.instant('Hi. I just found $1 in the $2 app.')
                .replace('$1', content)
                .replace('$2', Application.app_name);

            if (message !== undefined) {
                message = $translate.instant(message)
                    .replace('$1', content)
                    .replace('$2', Application.app_name);
            }

            var _link = (link === undefined) ? download_app_link : link;
            var _file = (file === undefined) ? '' : file;
            var _message = (message === undefined) ? generic_message : message;
            var _subject = (subject === undefined) ? '' : subject;

            var deferred = $q.defer();

            if (IS_NATIVE_APP) {
                try {
                    $cordovaSocialSharing
                        .share(_message, _subject, _file, _link)
                        .then(function (result) {
                            deferred.resolve(result);
                            service.is_sharing = false;
                        }, function (error) {
                            deferred.reject(error);
                            service.is_sharing = false;
                        });
                } catch (e) {
                    Dialog.alert('Sharing', 'An error occured while sharing your content, please try again!', 'Dismiss', -1, 'social_sharing');
                }
            } else if (navigator.share !== undefined) {
                try {
                    navigator.share({
                        'title': _subject,
                        'text': _message,
                        'url': _link ? _link : _file
                    }).then(function () {
                        service.is_sharing = false;
                    }).catch(function (error) {
                        Dialog.alert('Sharing', 'An error occured while sharing your content, please try again!', 'Dismiss', -1, 'social_sharing');
                        service.is_sharing = false;
                    });
                } catch (e) {
                    Dialog.alert('Sharing', 'An error occured while sharing your content, please try again!', 'Dismiss', -1, 'social_sharing');
                }
            } else {
                service.webShare(_message, _subject, _link, _file);
            }


            return deferred.promise;
        };

        /**
         *
         * @returns {*}
         */
        service.webShare = function (message, subject, link, file) {
            var payload = link ? link : file;
            var payloadMsg = [message, payload].join(' ').trim();

            var android = navigator.userAgent.match(/Android/i);
            var ios = navigator.userAgent.match(/iPhone|iPad|iPod/i);
            var isDesktop = !(ios || android); // on those two support "mobile deep links", so HTTP based fallback for all others.

            // sms on ios 'sms:;body='+payload, on Android 'sms:?body='+payload
            var shareUrls = {
                whatsapp: (isDesktop ? 'https://api.whatsapp.com/send?text=' : 'whatsapp://send?text=') + payloadMsg,
                facebook: 'https://www.facebook.com/sharer/sharer.php?u=' + payload,
                twitter: 'https://twitter.com/intent/tweet?text=' + payloadMsg,
                email: 'mailto:?subject=' + subject + '&body=' + payloadMsg,
                sms: 'sms:?body=' + payloadMsg
            };

            var _buttons = [
                {
                    text: '<i class="icon ion-sb-whatsapp-outline"></i>' + $translate.instant('Whatsapp', 'social_sharing')
                },
                {
                    text: '<i class="icon ion-social-facebook-outline"></i>' + $translate.instant('Facebook', 'social_sharing')
                },
                {
                    text: '<i class="icon ion-social-twitter-outline"></i>' + $translate.instant('Twitter', 'social_sharing')
                },
                {
                    text: '<i class="icon ion-ios-email-outline"></i>' + $translate.instant('Email', 'social_sharing')
                },
                {
                    text: '<i class="icon ion-ios-chatboxes-outline"></i>' + $translate.instant('SMS', 'social_sharing')
                }
            ];

            service.sheetResolver = $ionicActionSheet.show({
                buttons: _buttons,
                titleText: $translate.instant('Share content!', 'social_sharing'),
                cancelText: $translate.instant('Cancel', 'social_sharing'),
                cancel: function () {
                    service.sheetResolver();
                    service.is_sharing = false;
                },
                buttonClicked: function (index) {
                    var selectedLink = null;
                    switch (index) {
                        case 0:
                            selectedLink = shareUrls.whatsapp;
                            break;
                        case 1:
                            selectedLink = shareUrls.facebook;
                            break;
                        case 2:
                            selectedLink = shareUrls.twitter;
                            break;
                        case 3:
                            selectedLink = shareUrls.email;
                            break;
                        case 4:
                            selectedLink = shareUrls.sms;
                            break;
                    }

                    if (selectedLink !== null) {
                        LinkService.openLink(selectedLink, {}, true);
                    }

                    service.sheetResolver();
                    service.is_sharing = false;

                    return true;
                }
            });
        };

        return service;
    });
