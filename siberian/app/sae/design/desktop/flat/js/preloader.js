/*global
    localStorage, XMLHttpRequest, current_release
 */

$(document).ready(function() {
    if(localStorage.getItem("latest-cache") !== current_release) {

        var preload = [
            "/var/apps/overview/dist/app.bundle-min.js",
            "/var/apps/overview/cordova.js",
            "/var/apps/overview/cordova_plugins.js",
            "/var/apps/overview/img/placeholder/530.272.png",
            "/var/apps/overview/img/pictos/sprite-play.png",
            "/var/apps/overview/img/ionic.png",
            "/var/apps/overview/img/loyaltycard/point.png",
            "/var/apps/overview/img/loyaltycard/point-validated.png",
            "/var/apps/overview/js/libraries/ion-gallery.min.js",
            "/var/apps/overview/js/libraries/angular-touch.min.js",
            "/var/apps/overview/lib/ngCordova/dist/ng-cordova.min.js",
            "/var/apps/overview/lib/ionic/fonts/ionicons.ttf",
            "/var/apps/overview/lib/ionic/fonts/ionicons.eot",
            "/var/apps/overview/lib/ionic/fonts/ionicons.woff",
            "/var/apps/overview/lib/ionic/fonts/ionicons.svg"
        ];

        var request = function(filename) {
            var xhr = new XMLHttpRequest();
            xhr.open('GET', filename);
            xhr.send(null);
        };

        preload.forEach(function(element) {
            request(element + "?version=" + current_release);
        });

        /** Save information */
        localStorage.setItem("latest-cache", current_release);
    }

});