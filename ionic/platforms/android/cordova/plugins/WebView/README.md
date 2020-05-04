<!---
license: Licensed to the Apache Software Foundation (ASF) under one
or more contributor license agreements.  See the NOTICE file
distributed with this work for additional information
regarding copyright ownership.  The ASF licenses this file
to you under the Apache License, Version 2.0 (the
"License"); you may not use this file except in compliance
with the License.  You may obtain a copy of the License at

http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing,
software distributed under the License is distributed on an
"AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY
KIND, either express or implied.  See the License for the
specific language governing permissions and limitations
under the License.
-->

# (fork of kunder.cl webview plugin https://github.com/kunder-lab/cl.kunder.webview)

# cl.kunder.webview
This cordova plugin enables you to open a second webview in your app.
This webview is totally independent from the main webview, but allows you tu access plugins and other Cordova resources.

It's possible to modify this plugin to allow multiple webviews.

Report issues on [github issue tracker](https://github.com/kunder-lab/cl.kunder.webview/issues)

## Installation
```
    cordova plugin add https://github.com/walidowais/cordova-plugin-second-webview.git
```

## Supported Platforms
- Android
- iOS


## Quick Start

To open a new webview, just call in your app's js:
```javascript
    webview.Show(URL);
```

Where `URL` is the path to the page to be opened. In Android, the plugin automatically adds the prefix `file:///android_asset/www/`
But only if no `*://` or `javascript:` is present at the front of the URL.

Then, to close the second webview and return to the main view, call in your second webview (the opened webview, not the main webview):
```javascript
    webview.Close();
```

This will close and destroy the second webview.

# webView

The `webView`object provides a way to manage a second webview inside your cordova app. This could be usefull if you want to open a second page as a popup or you want to load new content that is totally unrelated to the main view, but still have the ability to use cordova plugins.

The main difference with inAppBrowser plugin is that cl.kunder.webview plugin can access and use all cordova plguins installed in your app.

## Methods

- __Show__: Opens a new webView
- __Close__: Close and destroy the webView
- __Hide__: Same as __Close__
- __SubscribeCallback__: Suscribes a callback that is fired when webView is closed

### Show
__Parameters__:
- __url__: The url to be opened. In Android, if the url does not contain a protocol prefix (`*://` or `javascript:*`), the prefix `file:///android_asset/www/` will be automatically added. _(String)_
- __successCallback__: Is triggered when the plugin is succesfully called. _(Function)_
- __errorCallback__: Is triggered when the plugin fails to be called or is called with error. _(Function)_
- __loading__: Should show a loading dialog while webview is loading. _(Boolean optional)_

### Close/Hide
__Parameters__:
- __successCallback__: Is triggered when the plugin is succesfully called. _(Function)_
- __errorCallback__: Is triggered when the plugin fails to be called or is called with error. _(Function)_

### HideLoading
Close the loading shown by Show method.

__Parameters__:
- __sucessCallback__: The callback that will be called when the loading is closed. _(Function optional)_
- __errorCallback__: Is triggered when the plugin fails to be called or is called with error. _(Function optional)_

### SubscribeCallback
Suscribes a callback that is triggered when a webView is closed.

__Parameters__:
- __successCallback__: The callback that will be called when a webview is closed. _(Function)_
- __errorCallback__: Is triggered when the plugin fails to be called or is called with error. _(Function)_

### SubscribeExitCallback (Android only)
Subscribes an exit callback that is triggered when ExitApp method is called.

__Parameters__:
- __successCallback__: The callback that will be called when a webview is closed. _(Function)_
- __errorCallback__: Is triggered when the plugin fails to be called or is called with error. This can be empty function _(Function)_

### ExitApp (Android)
This method execute the subscribed exit callback if exist. Then close the webview.
This method is usefull when onResume event is defined in your main app. You should set a flag in subscribeExitCallback success method. Then in onResume event you should verify the flag value. Finally the main app should call ionic.Platform.exitApp() method to close the app.

### ExitApp (iOS)
This method execute objective-C exit(0) method.

### SetWebViewBehavior (iOS)
This method adjust the size of the current webview using the iOS 11 status bar space. This method should be called at the beginning of the app.
No parameters required.