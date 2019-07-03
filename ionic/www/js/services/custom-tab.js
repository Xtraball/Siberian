/**
 * CustomTab
 *
 * @author Xtraball SAS
 * @version 4.17.0
 */
angular
.module("starter")
.service("CustomTab", function (SB, LinkService) {
    return {
        openLink: function(url, options) {
            console.log("CustomTab.openLink", url, options);

            // For the browser, we just fallback on the LinkService inAppBrowser
            if (DEVICE_TYPE === SB.DEVICE.TYPE_BROWSER) {
                LinkService.openLink(url, options)
            }

            if (cordova &&
                cordova.plugins &&
                cordova.plugins.browsertab) {
                cordova.plugins.browsertab.openUrl(url, options);
            } else {
                console.error("Plugin BrowserTab is missing.");
            }
        }
    };
});
