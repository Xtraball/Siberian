//
//  PluginAdapterUnity.h
//  TestAdMobCombo
//
//  Created by Xie Liming on 14-10-31.
//
//

#import "PluginAdapterDelegate.h"

@interface PluginAdapterUnity : NSObject<PluginAdapterDelegate>

- (UIView*) getView;

- (UIViewController*) getViewController;

- (void) fireEvent:(NSString*)obj event:(NSString*)eventName withData:(NSString*)jsonStr;

- (void) sendPluginResult:(CDVPluginResult*)result to:(NSString*)callbackId;

@end
