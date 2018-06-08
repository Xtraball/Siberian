//
//  PluginAdapterDelegate.h
//  TestAdMobCombo
//
//  Created by Xie Liming on 14-10-20.
//
//

#import <Foundation/Foundation.h>
#import <Cordova/CDV.h>

@protocol PluginAdapterDelegate <NSObject>

- (UIView*) getView;

- (UIViewController*) getViewController;

- (void) fireEvent:(NSString*)obj event:(NSString*)eventName withData:(NSString*)jsonStr;

- (void) sendPluginResult:(CDVPluginResult*)result to:(NSString*)callbackId;

@end
