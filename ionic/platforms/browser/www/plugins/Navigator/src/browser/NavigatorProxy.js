cordova.define("Navigator.NavigatorProxy", function(require, exports, module) { /**
 * Browser Proxy to open intents
 *
 * @type {{navigate: Navigator.navigate}}
 */
var Navigator = {
    navigate: function (onSuccess, onError, to) {
        try {
            if (to[0] && to[1]) {
                window.open("https://www.google.com/maps/dir/?api=1&destination="+to[0]+","+to[1], "_system");
            } else {
                console.error("Latitude and longitude are required.");
            }
        } catch (e) {
            console.error("Error on navigate by position: " + e.message);
        }
    }
};

module.exports = Navigator;

require("cordova/exec/proxy").add("Navigator", Navigator);


});
