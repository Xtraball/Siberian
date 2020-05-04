
# Cordova Plugin Common Extension #

Extend the Cordova plugin base class with adapter interface.

Plugin written based on this interface, may also be reused for Unity, Cocos2d-X, and other frameworks.

# Purpose #

Make Cordova Plugins reusable.

To use mobile device native functionalities and integrate 3rd-party SDKs, mobile developers are writting hundreds plugins for Cordova, Unity, Cocos2d-X, and other frameworks. 

Can they be reused? Yes, it's possible. 

Cordova plugin manager is bridging function call between javascript and native languages, actually, it can be ported to bridge with C, C++, C#, then it can be reused for Unity, Cocos2d-X and other frameworks.

See:
* Adapter for Cordova, (implemented as default, [Android](https://github.com/floatinghotpot/cordova-extension/blob/master/src/android/CordovaPluginExt.java) / [iOS](https://github.com/floatinghotpot/cordova-extension/blob/master/src/ios/CDVPluginExt.m) )
* [Adapter for Unity](https://github.com/floatinghotpot/cordova-extension/tree/master/unity)
* [Adapter for Cocos2d-X](https://github.com/floatinghotpot/cordova-extension/tree/master/cocos2dx)

# How it works ? #

Android implementation:

```java
public interface PluginAdapterDelegate {
	// context
	public Activity getActivity();
	public View getView();
	// send message from plugin to container on events
	public void fireEvent(String obj, String eventName, String jsonData);
	// send call result
	public void sendPluginResult(PluginResult result, CallbackContext context);
}

public class CordovaPluginExt extends CordovaPlugin implements PluginAdapterDelegate {
	protected PluginAdapterDelegate adapter = null;
}


```

iOS implementation:

```objective-c
@protocol PluginAdapterDelegate <NSObject>
	// context
- (UIView*) getView;
- (UIViewController*) getViewController;
	// send message from plugin to container on events
- (void) fireEvent:(NSString*)obj event:(NSString*)eventName withData:(NSString*)jsonStr;
	// send call result
- (void) sendPluginResult:(CDVPluginResult*)result to:(NSString*)callbackId;
@end

@interface CDVPluginExt : CDVPlugin <PluginAdapterDelegate>
@property(nonatomic, retain) id<PluginAdapterDelegate> adapter;
@end


```

Other platform:

Not implemented yet.

# How to Use? #

This plugin is used as dependency of other plugins, for plugin developers only.

In your plugin.xml, add it as dependency:

```xml
<dependency id="cordova-plugin-extension"/>
```

Inherit Cordova Plugin Ext:

Plugin for Android:

```java
import com.rjfun.cordova.ext.*;

// your plugin class
public class YourPluginClass extends CordovaPluginExt {
	// implement the method, call the API defined in PluginAdapterDelegate
	public boolean execute(String action, JSONArray inputs, CallbackContext callbackContext) throws JSONException;
}
```

Plugin for iOS:
```objective-c
#import "CDVCordovaExt.h"

// your plugin class
@interface YourPluginClass : CDVPluginExt
	// implement the method, call the API defined in PluginAdapterDelegate
- (void) your_method:(CDVInvokedUrlCommand *)command;
@end
```

# Related Projects #

To use the plugin in other frameworks, following plugin managers are required.

* [Cordova Plugin Manager for Unity](https://github.com/floatinghotpot/cordova-for-unity)
* [Cordova Plugin Manager for Cocos2d-X](https://github.com/floatinghotpot/cordova-for-cocos2dx)

# Credit #

This project is created by Raymond Xie.

If you are interested in this project, welcome to join and contribute.

