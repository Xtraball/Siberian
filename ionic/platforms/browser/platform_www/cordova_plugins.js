cordova.define('cordova/plugin_list', function(require, exports, module) {
module.exports = [
    {
        "file": "plugins/Promises/www/promise.js",
        "id": "Promises.Promise",
        "pluginId": "Promises",
        "runs": true
    },
    {
        "file": "plugins/BarcodeScanner/www/barcodescanner.js",
        "id": "BarcodeScanner.BarcodeScanner",
        "pluginId": "BarcodeScanner",
        "clobbers": [
            "cordova.plugins.barcodeScanner"
        ]
    },
    {
        "file": "plugins/BarcodeScanner/src/browser/BarcodeScannerProxy.js",
        "id": "BarcodeScanner.BarcodeScannerProxy",
        "pluginId": "BarcodeScanner",
        "runs": true
    },
    {
        "file": "plugins/Camera/www/CameraConstants.js",
        "id": "Camera.Camera",
        "pluginId": "Camera",
        "clobbers": [
            "Camera"
        ]
    },
    {
        "file": "plugins/Camera/www/CameraPopoverOptions.js",
        "id": "Camera.CameraPopoverOptions",
        "pluginId": "Camera",
        "clobbers": [
            "CameraPopoverOptions"
        ]
    },
    {
        "file": "plugins/Camera/www/Camera.js",
        "id": "Camera.camera",
        "pluginId": "Camera",
        "clobbers": [
            "navigator.camera"
        ]
    },
    {
        "file": "plugins/Camera/src/browser/CameraProxy.js",
        "id": "Camera.CameraProxy",
        "pluginId": "Camera",
        "runs": true
    },
    {
        "file": "plugins/Device/www/device.js",
        "id": "Device.device",
        "pluginId": "Device",
        "clobbers": [
            "device"
        ]
    },
    {
        "file": "plugins/Device/src/browser/DeviceProxy.js",
        "id": "Device.DeviceProxy",
        "pluginId": "Device",
        "runs": true
    },
    {
        "file": "plugins/File/www/DirectoryEntry.js",
        "id": "File.DirectoryEntry",
        "pluginId": "File",
        "clobbers": [
            "window.DirectoryEntry"
        ]
    },
    {
        "file": "plugins/File/www/DirectoryReader.js",
        "id": "File.DirectoryReader",
        "pluginId": "File",
        "clobbers": [
            "window.DirectoryReader"
        ]
    },
    {
        "file": "plugins/File/www/Entry.js",
        "id": "File.Entry",
        "pluginId": "File",
        "clobbers": [
            "window.Entry"
        ]
    },
    {
        "file": "plugins/File/www/File.js",
        "id": "File.File",
        "pluginId": "File",
        "clobbers": [
            "window.File"
        ]
    },
    {
        "file": "plugins/File/www/FileEntry.js",
        "id": "File.FileEntry",
        "pluginId": "File",
        "clobbers": [
            "window.FileEntry"
        ]
    },
    {
        "file": "plugins/File/www/FileError.js",
        "id": "File.FileError",
        "pluginId": "File",
        "clobbers": [
            "window.FileError"
        ]
    },
    {
        "file": "plugins/File/www/FileReader.js",
        "id": "File.FileReader",
        "pluginId": "File",
        "clobbers": [
            "window.FileReader"
        ]
    },
    {
        "file": "plugins/File/www/FileSystem.js",
        "id": "File.FileSystem",
        "pluginId": "File",
        "clobbers": [
            "window.FileSystem"
        ]
    },
    {
        "file": "plugins/File/www/FileUploadOptions.js",
        "id": "File.FileUploadOptions",
        "pluginId": "File",
        "clobbers": [
            "window.FileUploadOptions"
        ]
    },
    {
        "file": "plugins/File/www/FileUploadResult.js",
        "id": "File.FileUploadResult",
        "pluginId": "File",
        "clobbers": [
            "window.FileUploadResult"
        ]
    },
    {
        "file": "plugins/File/www/FileWriter.js",
        "id": "File.FileWriter",
        "pluginId": "File",
        "clobbers": [
            "window.FileWriter"
        ]
    },
    {
        "file": "plugins/File/www/Flags.js",
        "id": "File.Flags",
        "pluginId": "File",
        "clobbers": [
            "window.Flags"
        ]
    },
    {
        "file": "plugins/File/www/LocalFileSystem.js",
        "id": "File.LocalFileSystem",
        "pluginId": "File",
        "clobbers": [
            "window.LocalFileSystem"
        ],
        "merges": [
            "window"
        ]
    },
    {
        "file": "plugins/File/www/Metadata.js",
        "id": "File.Metadata",
        "pluginId": "File",
        "clobbers": [
            "window.Metadata"
        ]
    },
    {
        "file": "plugins/File/www/ProgressEvent.js",
        "id": "File.ProgressEvent",
        "pluginId": "File",
        "clobbers": [
            "window.ProgressEvent"
        ]
    },
    {
        "file": "plugins/File/www/fileSystems.js",
        "id": "File.fileSystems",
        "pluginId": "File"
    },
    {
        "file": "plugins/File/www/requestFileSystem.js",
        "id": "File.requestFileSystem",
        "pluginId": "File",
        "clobbers": [
            "window.requestFileSystem"
        ]
    },
    {
        "file": "plugins/File/www/resolveLocalFileSystemURI.js",
        "id": "File.resolveLocalFileSystemURI",
        "pluginId": "File",
        "merges": [
            "window"
        ]
    },
    {
        "file": "plugins/File/www/browser/isChrome.js",
        "id": "File.isChrome",
        "pluginId": "File",
        "runs": true
    },
    {
        "file": "plugins/File/www/browser/Preparing.js",
        "id": "File.Preparing",
        "pluginId": "File",
        "runs": true
    },
    {
        "file": "plugins/File/src/browser/FileProxy.js",
        "id": "File.browserFileProxy",
        "pluginId": "File",
        "runs": true
    },
    {
        "file": "plugins/File/www/fileSystemPaths.js",
        "id": "File.fileSystemPaths",
        "pluginId": "File",
        "merges": [
            "cordova"
        ],
        "runs": true
    },
    {
        "file": "plugins/File/www/browser/FileSystem.js",
        "id": "File.firefoxFileSystem",
        "pluginId": "File",
        "merges": [
            "window.FileSystem"
        ]
    },
    {
        "file": "plugins/InAppBrowser/www/inappbrowser.js",
        "id": "InAppBrowser.inappbrowser",
        "pluginId": "InAppBrowser",
        "clobbers": [
            "cordova.InAppBrowser.open",
            "window.open"
        ]
    },
    {
        "file": "plugins/InAppBrowser/src/browser/InAppBrowserProxy.js",
        "id": "InAppBrowser.InAppBrowserProxy",
        "pluginId": "InAppBrowser",
        "runs": true
    },
    {
        "file": "plugins/MusicControls/www/MusicControls.js",
        "id": "MusicControls.MusicControls",
        "pluginId": "MusicControls",
        "clobbers": [
            "MusicControls"
        ]
    },
    {
        "file": "plugins/MusicControls/src/browser/MusicControlsProxy.js",
        "id": "MusicControls.MusicControlsProxy",
        "pluginId": "MusicControls",
        "runs": true
    },
    {
        "file": "plugins/Navigator/www/navigator.js",
        "id": "Navigator.Navigator",
        "pluginId": "Navigator",
        "clobbers": [
            "Navigator"
        ]
    },
    {
        "file": "plugins/Navigator/src/browser/NavigatorProxy.js",
        "id": "Navigator.NavigatorProxy",
        "pluginId": "Navigator",
        "runs": true
    },
    {
        "file": "plugins/Permissions/www/permissions-dummy.js",
        "id": "Permissions.Permissions",
        "pluginId": "Permissions",
        "clobbers": [
            "cordova.plugins.permissions"
        ]
    }
];
module.exports.metadata = 
// TOP OF METADATA
{
    "Promises": "4.2.2",
    "BarcodeScanner": "7.1.2",
    "Camera": "4.0.3",
    "Device": "2.0.2",
    "File": "6.0.1",
    "InAppBrowser": "3.0.0",
    "MusicControls": "2.2.0",
    "Navigator": "0.1.0",
    "Permissions": "1.0.0"
}
// BOTTOM OF METADATA
});