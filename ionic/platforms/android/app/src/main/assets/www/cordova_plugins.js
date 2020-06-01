cordova.define('cordova/plugin_list', function(require, exports, module) {
module.exports = [
  {
    "id": "Chcp.chcp",
    "file": "plugins/Chcp/www/chcp.js",
    "pluginId": "Chcp",
    "clobbers": [
      "chcp"
    ]
  },
  {
    "id": "Geofence.TransitionType",
    "file": "plugins/Geofence/www/TransitionType.js",
    "pluginId": "Geofence",
    "clobbers": [
      "TransitionType"
    ]
  },
  {
    "id": "Geofence.geofence",
    "file": "plugins/Geofence/www/geofence.js",
    "pluginId": "Geofence",
    "clobbers": [
      "geofence"
    ]
  },
  {
    "id": "SplashScreen.SplashScreen",
    "file": "plugins/SplashScreen/www/splashscreen.js",
    "pluginId": "SplashScreen",
    "clobbers": [
      "navigator.splashscreen"
    ]
  },
  {
    "id": "Push.PushNotification",
    "file": "plugins/Push/www/push.js",
    "pluginId": "Push",
    "clobbers": [
      "PushNotification"
    ]
  },
  {
    "id": "WebView.webview",
    "file": "plugins/WebView/www/webViewPlugin.js",
    "pluginId": "WebView",
    "clobbers": [
      "window.webview"
    ]
  },
  {
    "id": "Promises.Promise",
    "file": "plugins/Promises/www/promise.js",
    "pluginId": "Promises",
    "runs": true
  },
  {
    "id": "AdmobPro.AdMob",
    "file": "plugins/AdmobPro/www/AdMob.js",
    "pluginId": "AdmobPro",
    "clobbers": [
      "window.AdMob"
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
    "file": "plugins/Camera/www/CameraPopoverHandle.js",
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
    "id": "File.androidFileSystem",
    "file": "plugins/File/www/android/FileSystem.js",
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
    "id": "Geolocation.geolocation",
    "file": "plugins/Geolocation/www/android/geolocation.js",
    "pluginId": "Geolocation",
    "clobbers": [
      "navigator.geolocation"
    ]
  },
  {
    "id": "Geolocation.PositionError",
    "file": "plugins/Geolocation/www/PositionError.js",
    "pluginId": "Geolocation",
    "runs": true
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
    "file": "plugins/Keyboard/www/android/keyboard.js",
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
    "id": "WhiteList.whitelist",
    "file": "plugins/WhiteList/whitelist.js",
    "pluginId": "WhiteList",
    "runs": true
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
    "id": "IonicWebview.IonicWebView",
    "file": "plugins/IonicWebview/src/www/util.js",
    "pluginId": "IonicWebview",
    "clobbers": [
      "Ionic.WebView"
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
    "id": "Permissions.Permissions",
    "file": "plugins/Permissions/www/permissions.js",
    "pluginId": "Permissions",
    "clobbers": [
      "cordova.plugins.permissions"
    ]
  },
  {
    "id": "Toast.Toast",
    "file": "plugins/Toast/www/Toast.js",
    "pluginId": "Toast",
    "clobbers": [
      "window.plugins.toast"
    ]
  }
];
module.exports.metadata = 
// TOP OF METADATA
{
  "Chcp": "1.5.2",
  "Geofence": "0.6.0",
  "SplashScreen": "5.0.2",
  "Push": "1.10.0",
  "WebView": "1.0.0",
  "Promises": "4.2.2",
  "AdmobPro": "2.30.1",
  "AppVersion": "0.1.8",
  "Badge": "0.8.8",
  "BarcodeScanner": "7.1.2",
  "BrowserTab": "0.2.1",
  "Camera": "4.0.3",
  "Clipboard": "0.1.0",
  "Device": "2.0.2",
  "File": "6.0.2",
  "Geolocation": "4.0.1",
  "InAppBrowser": "3.2.0",
  "Keyboard": "2.2.0",
  "SocialSharing": "5.6.4",
  "StatusBar": "2.4.1",
  "WhiteList": "1.2.1",
  "LocalNotification": "0.9.0-beta.3",
  "Insomnia": "4.3.0",
  "IonicWebview": "4.0.1",
  "MusicControls": "3.0.4",
  "MediaNative": "5.0.3",
  "Navigator": "0.1.0",
  "Permissions": "1.0.0",
  "Siberian": "1.0.0",
  "Toast": "2.7.2"
};
// BOTTOM OF METADATA
});