//
//  CDVPluginExt.h
//  TestAdMobCombo
//
//  Created by Xie Liming on 14-10-28.
//
//

#import "PluginAdapterDelegate.h"

@interface CDVPluginExt : CDVPlugin <PluginAdapterDelegate>

@property(nonatomic, retain) id<PluginAdapterDelegate> adapter;

- (UIView*) getView;
- (UIViewController*) getViewController;
- (void) fireEvent:(NSString *)obj event:(NSString *)eventName withData:(NSString *)jsonStr;
- (void) sendPluginResult:(CDVPluginResult *)result to:(NSString *)callbackId;

@end
