/**
 * $translate service
 */
angular.module("starter").service("$translate", function ($injector) {
    var service = {};

    /**
     *
     * @type {Array}
     */
    service.translations = [];

    /**
     *
     * @type {Array}
     */
    service.extractBulk = [];

    /**
     *
     * @param text
     * @param context
     * @returns {*}
     */
    service.instant = function (text, context) {
        // Extracting translations!
        service.debugExtract(text, context);

        var translated = text;
        if (context === undefined) {
            if (angular.isDefined(service.translations[text])) {
                translated = service.translations[text];
            }

            return translated;
        }

        if (angular.isDefined(service.translations["_context"][context]) &&
            angular.isDefined(service.translations["_context"][context][text])) {
            translated = service.translations["_context"][context][text];
        } else if (angular.isDefined(service.translations[text])) {
            // Will however try to fallback on translation with no context (backward compat') !
            translated = service.translations[text];
        }

        return translated;
    };

    /**
     * Extract translations
     * @param text
     * @param context
     */
    service.debugExtract = function (text, context) {
        /**
         * Blind extract of the processed strings
         */
         try {
            if (window.extractI18n === true) {
                // No thanks!
                if (context === undefined) {
                    return;
                }

                var _t = btoa(text);
                var _c = (context === undefined) ? "" : btoa(context);

                service.extractBulk.push({
                    text: _t,
                    context: _c,
                });
            }
        } catch (e) {
            // Do nothing
        }
    };

    service.debugPost = function () {
        $injector.get("$pwaRequest").post("/translation/extract/index", {
            data: service.extractBulk,
            cache: false
        }).then(function () {
            service.extractBulk = [];
        });
    };

    window.debugPost = service.debugPost;

    return service;
});
