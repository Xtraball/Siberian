cordova.define('cordova/plugin_list', function(require, exports, module) {
  module.exports = [
    {
      "id": "Promises.Promise",
      "file": "plugins/Promises/www/promise.js",
      "pluginId": "Promises",
      "runs": true
    },
    {
      "id": "Admob.AdMob",
      "file": "plugins/Admob/www/admob.js",
      "pluginId": "Admob",
      "clobbers": [
        "admob"
      ]
    },
    {
      "id": "AppVersion.AppVersionPlugin",
      "file": "plugins/AppVersion/www/AppVersionPlugin.js",
      "pluginId": "AppVersion",
      "clobbers": [
        "cordova.getAppVersion"
      ]
    },
    {
      "id": "Badge.Badge",
      "file": "plugins/Badge/www/badge.js",
      "pluginId": "Badge",
      "clobbers": [
        "cordova.plugins.notification.badge"
      ]
    },
    {
      "id": "BarcodeScanner.BarcodeScanner",
      "file": "plugins/BarcodeScanner/www/barcodescanner.js",
      "pluginId": "BarcodeScanner",
      "clobbers": [
        "cordova.plugins.barcodeScanner"
      ]
    },
    {
      "id": "BrowserTab.BrowserTab",
      "file": "plugins/BrowserTab/www/browsertab.js",
      "pluginId": "BrowserTab",
      "clobbers": [
        "cordova.plugins.browsertab"
      ]
    },
    {
      "id": "Camera.Camera",
      "file": "plugins/Camera/www/CameraConstants.js",
      "pluginId": "Camera",
      "clobbers": [
        "Camera"
      ]
    },
    {
      "id": "Camera.CameraPopoverOptions",
      "file": "plugins/Camera/www/CameraPopoverOptions.js",
      "pluginId": "Camera",
      "clobbers": [
        "CameraPopoverOptions"
      ]
    },
    {
      "id": "Camera.camera",
      "file": "plugins/Camera/www/Camera.js",
      "pluginId": "Camera",
      "clobbers": [
        "navigator.camera"
      ]
    },
    {
      "id": "Camera.CameraPopoverHandle",
      "file": "plugins/Camera/www/ios/CameraPopoverHandle.js",
      "pluginId": "Camera",
      "clobbers": [
        "CameraPopoverHandle"
      ]
    },
    {
      "id": "Clipboard.Clipboard",
      "file": "plugins/Clipboard/www/clipboard.js",
      "pluginId": "Clipboard",
      "clobbers": [
        "cordova.plugins.clipboard"
      ]
    },
    {
      "id": "Device.device",
      "file": "plugins/Device/www/device.js",
      "pluginId": "Device",
      "clobbers": [
        "device"
      ]
    },
    {
      "id": "File.DirectoryEntry",
      "file": "plugins/File/www/DirectoryEntry.js",
      "pluginId": "File",
      "clobbers": [
        "window.DirectoryEntry"
      ]
    },
    {
      "id": "File.DirectoryReader",
      "file": "plugins/File/www/DirectoryReader.js",
      "pluginId": "File",
      "clobbers": [
        "window.DirectoryReader"
      ]
    },
    {
      "id": "File.Entry",
      "file": "plugins/File/www/Entry.js",
      "pluginId": "File",
      "clobbers": [
        "window.Entry"
      ]
    },
    {
      "id": "File.File",
      "file": "plugins/File/www/File.js",
      "pluginId": "File",
      "clobbers": [
        "window.File"
      ]
    },
    {
      "id": "File.FileEntry",
      "file": "plugins/File/www/FileEntry.js",
      "pluginId": "File",
      "clobbers": [
        "window.FileEntry"
      ]
    },
    {
      "id": "File.FileError",
      "file": "plugins/File/www/FileError.js",
      "pluginId": "File",
      "clobbers": [
        "window.FileError"
      ]
    },
    {
      "id": "File.FileReader",
      "file": "plugins/File/www/FileReader.js",
      "pluginId": "File",
      "clobbers": [
        "window.FileReader"
      ]
    },
    {
      "id": "File.FileSystem",
      "file": "plugins/File/www/FileSystem.js",
      "pluginId": "File",
      "clobbers": [
        "window.FileSystem"
      ]
    },
    {
      "id": "File.FileUploadOptions",
      "file": "plugins/File/www/FileUploadOptions.js",
      "pluginId": "File",
      "clobbers": [
        "window.FileUploadOptions"
      ]
    },
    {
      "id": "File.FileUploadResult",
      "file": "plugins/File/www/FileUploadResult.js",
      "pluginId": "File",
      "clobbers": [
        "window.FileUploadResult"
      ]
    },
    {
      "id": "File.FileWriter",
      "file": "plugins/File/www/FileWriter.js",
      "pluginId": "File",
      "clobbers": [
        "window.FileWriter"
      ]
    },
    {
      "id": "File.Flags",
      "file": "plugins/File/www/Flags.js",
      "pluginId": "File",
      "clobbers": [
        "window.Flags"
      ]
    },
    {
      "id": "File.LocalFileSystem",
      "file": "plugins/File/www/LocalFileSystem.js",
      "pluginId": "File",
      "clobbers": [
        "window.LocalFileSystem"
      ],
      "merges": [
        "window"
      ]
    },
    {
      "id": "File.Metadata",
      "file": "plugins/File/www/Metadata.js",
      "pluginId": "File",
      "clobbers": [
        "window.Metadata"
      ]
    },
    {
      "id": "File.ProgressEvent",
      "file": "plugins/File/www/ProgressEvent.js",
      "pluginId": "File",
      "clobbers": [
        "window.ProgressEvent"
      ]
    },
    {
      "id": "File.fileSystems",
      "file": "plugins/File/www/fileSystems.js",
      "pluginId": "File"
    },
    {
      "id": "File.requestFileSystem",
      "file": "plugins/File/www/requestFileSystem.js",
      "pluginId": "File",
      "clobbers": [
        "window.requestFileSystem"
      ]
    },
    {
      "id": "File.resolveLocalFileSystemURI",
      "file": "plugins/File/www/resolveLocalFileSystemURI.js",
      "pluginId": "File",
      "merges": [
        "window"
      ]
    },
    {
      "id": "File.isChrome",
      "file": "plugins/File/www/browser/isChrome.js",
      "pluginId": "File",
      "runs": true
    },
    {
      "id": "File.iosFileSystem",
      "file": "plugins/File/www/ios/FileSystem.js",
      "pluginId": "File",
      "merges": [
        "FileSystem"
      ]
    },
    {
      "id": "File.fileSystems-roots",
      "file": "plugins/File/www/fileSystems-roots.js",
      "pluginId": "File",
      "runs": true
    },
    {
      "id": "File.fileSystemPaths",
      "file": "plugins/File/www/fileSystemPaths.js",
      "pluginId": "File",
      "merges": [
        "cordova"
      ],
      "runs": true
    },
    {
      "id": "Geolocation.Coordinates",
      "file": "plugins/Geolocation/www/Coordinates.js",
      "pluginId": "Geolocation",
      "clobbers": [
        "Coordinates"
      ]
    },
    {
      "id": "Geolocation.PositionError",
      "file": "plugins/Geolocation/www/PositionError.js",
      "pluginId": "Geolocation",
      "clobbers": [
        "PositionError"
      ]
    },
    {
      "id": "Geolocation.Position",
      "file": "plugins/Geolocation/www/Position.js",
      "pluginId": "Geolocation",
      "clobbers": [
        "Position"
      ]
    },
    {
      "id": "Geolocation.geolocation",
      "file": "plugins/Geolocation/www/geolocation.js",
      "pluginId": "Geolocation",
      "clobbers": [
        "navigator.geolocation"
      ]
    },
    {
      "id": "InAppBrowser.inappbrowser",
      "file": "plugins/InAppBrowser/www/inappbrowser.js",
      "pluginId": "InAppBrowser",
      "clobbers": [
        "cordova.InAppBrowser.open",
        "window.open"
      ]
    },
    {
      "id": "Keyboard.keyboard",
      "file": "plugins/Keyboard/www/ios/keyboard.js",
      "pluginId": "Keyboard",
      "clobbers": [
        "window.Keyboard"
      ]
    },
    {
      "id": "SocialSharing.SocialSharing",
      "file": "plugins/SocialSharing/www/SocialSharing.js",
      "pluginId": "SocialSharing",
      "clobbers": [
        "window.plugins.socialsharing"
      ]
    },
    {
      "id": "StatusBar.statusbar",
      "file": "plugins/StatusBar/www/statusbar.js",
      "pluginId": "StatusBar",
      "clobbers": [
        "window.StatusBar"
      ]
    },
    {
      "id": "LocalNotification.LocalNotification",
      "file": "plugins/LocalNotification/www/local-notification.js",
      "pluginId": "LocalNotification",
      "clobbers": [
        "cordova.plugins.notification.local"
      ]
    },
    {
      "id": "Insomnia.Insomnia",
      "file": "plugins/Insomnia/www/Insomnia.js",
      "pluginId": "Insomnia",
      "clobbers": [
        "window.plugins.insomnia"
      ]
    },
    {
      "id": "MusicControls.MusicControls",
      "file": "plugins/MusicControls/www/MusicControls.js",
      "pluginId": "MusicControls",
      "clobbers": [
        "MusicControls"
      ]
    },
    {
      "id": "MediaNative.MediaError",
      "file": "plugins/MediaNative/www/MediaError.js",
      "pluginId": "MediaNative",
      "clobbers": [
        "MediaError"
      ]
    },
    {
      "id": "MediaNative.MediaNative",
      "file": "plugins/MediaNative/www/Media.js",
      "pluginId": "MediaNative",
      "clobbers": [
        "MediaNative"
      ]
    },
    {
      "id": "Navigator.Navigator",
      "file": "plugins/Navigator/www/navigator.js",
      "pluginId": "Navigator",
      "clobbers": [
        "Navigator"
      ]
    },
    {
      "id": "Onesignal.OneSignalPlugin",
      "file": "plugins/Onesignal/dist/index.js",
      "pluginId": "Onesignal",
      "clobbers": [
        "OneSignal"
      ]
    },
    {
      "id": "Onesignal.NotificationReceivedEvent",
      "file": "plugins/Onesignal/dist/NotificationReceivedEvent.js",
      "pluginId": "Onesignal"
    },
    {
      "id": "Onesignal.Subscription",
      "file": "plugins/Onesignal/dist/Subscription.js",
      "pluginId": "Onesignal"
    },
    {
      "id": "Onesignal.OSNotification",
      "file": "plugins/Onesignal/dist/OSNotification.js",
      "pluginId": "Onesignal"
    },
    {
      "id": "Permissions.Permissions",
      "file": "plugins/Permissions/www/permissions-dummy.js",
      "pluginId": "Permissions",
      "clobbers": [
        "cordova.plugins.permissions"
      ]
    },
    {
      "id": "CorePlugin.CorePlugin",
      "file": "plugins/CorePlugin/www/CorePlugin.js",
      "pluginId": "CorePlugin",
      "clobbers": [
        "cordova.plugins.CorePlugin"
      ]
    },
    {
      "id": "Toast.Toast",
      "file": "plugins/Toast/www/Toast.js",
      "pluginId": "Toast",
      "clobbers": [
        "window.plugins.toast"
      ]
    },
    {
      "id": "InAppPurchase.InAppPurchase",
      "file": "plugins/InAppPurchase/www/store-ios.js",
      "pluginId": "InAppPurchase",
      "clobbers": [
        "store"
      ]
    },
    {
      "id": "IosXhr.formdata-polyfill",
      "file": "plugins/IosXhr/src/www/ios/formdata-polyfill.js",
      "pluginId": "IosXhr",
      "runs": true
    },
    {
      "id": "IosXhr.xhr-polyfill",
      "file": "plugins/IosXhr/src/www/ios/xhr-polyfill.js",
      "pluginId": "IosXhr",
      "runs": true
    },
    {
      "id": "IosXhr.fetch-bootstrap",
      "file": "plugins/IosXhr/src/www/ios/fetch-bootstrap.js",
      "pluginId": "IosXhr",
      "runs": true
    },
    {
      "id": "IosXhr.fetch-polyfill",
      "file": "plugins/IosXhr/src/www/ios/whatwg-fetch-2.0.3.js",
      "pluginId": "IosXhr",
      "runs": true
    },
    {
      "id": "WebView.webview",
      "file": "plugins/WebView/www/webViewPlugin.js",
      "pluginId": "WebView",
      "clobbers": [
        "window.webview"
      ]
    }
  ];
  module.exports.metadata = {
    "Promises": "4.2.2",
    "Admob": "2.0.0-alpha.13",
    "AppVersion": "0.1.8",
    "Badge": "0.8.8",
    "BarcodeScanner": "7.1.2",
    "BrowserTab": "0.2.1",
    "Camera": "7.0.0",
    "Clipboard": "0.1.0",
    "Device": "2.0.2",
    "File": "6.0.2",
    "Geolocation": "4.0.1",
    "InAppBrowser": "4.1.0",
    "Keyboard": "2.2.0",
    "SocialSharing": "5.6.4",
    "StatusBar": "2.4.1",
    "LocalNotification": "0.9.0-beta.4",
    "Insomnia": "4.3.0",
    "MusicControls": "3.0.4",
    "MediaNative": "5.0.3",
    "Navigator": "0.1.0",
    "Onesignal": "3.2.0",
    "Permissions": "1.0.0",
    "CorePlugin": "1.1.0",
    "Toast": "2.7.2",
    "InAppPurchase": "11.0.0",
    "IosXhr": "1.0.8",
    "Mediation": "1.0.0",
    "WebView": "1.0.0"
  };
});