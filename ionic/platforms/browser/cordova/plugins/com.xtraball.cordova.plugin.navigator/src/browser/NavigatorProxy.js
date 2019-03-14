/**
 * Browser Proxy to open intents
 *
 * @type {{navigate: Navigator.navigate}}
 */
var Navigator = {
    navigate: function (to) {
        try {
            if (!isNaN(to.lat) && !isNaN(to.lng)) {
                window.open("https://www.google.com/maps/dir/?api=1&destination="+to.lat+","+to.lng, "_system");
            } else {
                console.error("Latitude and longitude aren't numbers.");
            }
        } catch (e) {
            console.error("Error on navigate by position: " + e.message);
        }
    }
};

module.exports = Navigator;

require("cordova/exec/proxy").add("Navigator", Navigator);

