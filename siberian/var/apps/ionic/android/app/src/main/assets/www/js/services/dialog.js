/*global
 angular, IS_NATIVE_APP
 */

/**
 * Dialog
 *
 * @author Xtraball SAS
 *
 * @note $cordovaDialogs has been removed in favor of $ionicPopup which is consistent over all devices,
 * and can be automatically dismissed
 */
angular.module('starter').service('Dialog', function ($ionicPopup, $timeout, $translate, $q) {
    var service = {
        is_open: false,
        stack: []
    };

    /**
     * Un stack popups on event
     */
    service.unStack = function () {

        service.is_open = false;

        if (service.stack.length >= 1) {
            $timeout(function () {
                var dialog = service.stack.shift();

                switch (dialog.type) {
                    case 'alert':
                        service.renderAlert(dialog.data);
                        break;
                    case 'prompt':
                        service.renderPrompt(dialog.data);
                        break;
                    case 'confirm':
                        service.renderConfirm(dialog.data);
                        break;
                    case 'ionicPopup':
                        service.renderIonicPopup(dialog.data);
                        break;
                }
            }, 250);
        }
    };

    /**
     *
     * @param title
     * @param message
     * @param button
     * @param dismiss if -1 dismiss duration will be automatically calculated.
     * @param context
     * @returns {*}
     */
    service.alert = function (title, message, button, dismiss, context) {
        var deferred = $q.defer();

        // Stack alert!
        service.stack.push({
            type: 'alert',
            data: {
                title: title,
                message: message,
                button: button,
                dismiss: dismiss,
                context: context,
                promise: deferred
            }
        });

        if ((service.stack.length === 1) && !service.is_open) {
            service.unStack();
        }

        return deferred.promise;

    };

    /**
     * @param data
     */
    service.renderAlert = function (data) {
        service.is_open = true;

        var alertPromise = null;

        var message = $translate.instant(data.title);
        var cssClass = (data.title === '') ? 'popup-no-title' : '';

        alertPromise = $ionicPopup
            .alert({
                title: $translate.instant(data.title, data.context),
                template: $translate.instant(data.message, data.context),
                cssClass: cssClass,
                okText: $translate.instant(data.button, data.context)
            });

        data.promise.resolve(alertPromise);

        alertPromise.then(function () {
            service.unStack();
        });

        if (typeof data.dismiss === 'number') {
            /**
             * -1 means automatic calculation
             */
            var duration = data.dismiss;
            if (data.dismiss === -1) {
                duration = Math.min(Math.max((message.length * 50), 2000), 7000) + 400;
            }

            $timeout(function () {
                alertPromise.close();
            }, duration);
        }
    };

    /**
     *
     * @param title
     * @param message
     * @param type
     * @param value
     * @param buttonsArray
     * @param cssClass
     * @param context
     * @returns {*}
     */
    service.prompt = function (title, message, type, value, buttonsArray, cssClass, context) {
        var deferred = $q.defer();

        var localType = (type === undefined) ? 'text' : type;
        var localValue = (value === undefined) ? '' : value;
        var localButtonsArray = (buttonsArray === undefined) ? ["OK", "CANCEL"] : buttonsArray;

        /** Stack alert */
        service.stack.push({
            type: 'prompt',
            data: {
                title: title,
                message: message,
                type: localType,
                value: localValue,
                buttons_array: localButtonsArray,
                css_class: cssClass,
                context: context,
                promise: deferred
            }
        });

        if ((service.stack.length === 1) && !service.is_open) {
            service.unStack();
        }

        return deferred.promise;
    };

    /**
     * @param data
     */
    service.renderPrompt = function (data) {
        service.is_open = true;

        var cssClass = (data.title === '') ? 'popup-no-title' : '';

        return $ionicPopup
            .prompt({
                title: $translate.instant(data.title, data.context),
                template: $translate.instant(data.message, data.context),
                okText: $translate.instant(data.buttons_array[0], data.context),
                cancelText: $translate.instant(data.buttons_array[1], data.context),
                cssClass: cssClass,
                inputType: data.type,
                inputPlaceholder: $translate.instant(data.value, data.context)
            }).then(function (result) {
                if (result === undefined) {
                    data.promise.reject(result);
                } else {
                    data.promise.resolve(result);
                }

                service.unStack();
            });
    };

    /**
     * @param message
     * @param title
     * @param buttonsArray - ex: ['Ok', 'Cancel']
     * @param cssClass
     * @param context
     *
     * @returns Integer: 0 - no button, 1 - button 1, 2 - button 2
     */
    service.confirm = function (title, message, buttonsArray, cssClass, context) {
        var deferred = $q.defer();

        /** Stack alert */
        service.stack.push({
            type: 'confirm',
            data: {
                title: title,
                message: message,
                buttons_array: buttonsArray,
                css_class: cssClass,
                context: context,
                promise: deferred
            }
        });

        if ((service.stack.length === 1) && !service.is_open) {
            service.unStack();
        }

        return deferred.promise;
    };

    /**
     * @param data
     *
     * @return Promise
     */
    service.renderConfirm = function (data) {
        service.is_open = true;

        var cssClass = (data.title === '') ? 'popup-no-title' : '';

        return $ionicPopup
            .confirm({
                title: $translate.instant(data.title, data.context),
                cssClass: data.css_class + ' ' + cssClass,
                template: $translate.instant(data.message, data.context),
                okText: $translate.instant(data.buttons_array[0], data.context),
                cancelText: $translate.instant(data.buttons_array[1], data.context)
            }).then(function (result) {
                data.promise.resolve(result);
                service.unStack();
            });
    };

    /**
     * @param config
     *
     * @return Promise
     */
    service.ionicPopup = function (config) {
        var deferred = $q.defer();

        /** Stack alert */
        service.stack.push({
            type: 'ionicPopup',
            data: {
                config: config,
                promise: deferred
            }
        });

        if ((service.stack.length === 1) && !service.is_open) {
            service.unStack();
        }

        return deferred.promise;
    };

    /**
     * @param data
     *
     * @return Promise
     */
    service.renderIonicPopup = function (data) {
        service.is_open = true;

        return $ionicPopup
            .show(data.config)
            .then(function (result) {
                data.promise.resolve(result);
                service.unStack();
            });
    };

    return service;
});

/** @deprecated, use Dialog instead, will be removed by mid-2017, SafePopups is a proxy to Dialog. */
angular.module('starter').service('SafePopups', function (Dialog) {
    var service = {};
    service.show = function (type, params) {
        var button = {};
        switch (type) {
            case 'alert':
                if (params.buttons.length === 1) {
                    button = params.buttons[0];
                }
                return Dialog.alert(params.title, params.template, button);
            case 'confirm':
                return Dialog.confirm(params.title, params.template, params.buttons, '');
            default:
                return Dialog.ionicPopup(params);
        }
    };
    return service;
});
