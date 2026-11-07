
/** boot test beacon: appended to url.js of the generated iOS shell by tests/mobile-boot, never shipped. */
(function () {
    var tries = 0;
    var timer = setInterval(function () {
        tries += 1;
        try {
            var injector = window.angular && window.angular.element(document.body).injector();
            if (injector) {
                var state = injector.get('$state').current.name;
                if (state) {
                    clearInterval(timer);
                    // The router of the test instance records the request; that is the evidence.
                    new Image().src = DOMAIN + '/__boot/state/' + encodeURIComponent(state) +
                        '?native=' + IS_NATIVE_APP + '&device=' + DEVICE_TYPE;
                }
            }
        } catch (e) { /* not booted yet */ }
        if (tries > 180) { clearInterval(timer); }
    }, 1000);
}());
