#import <Foundation/Foundation.h>
#import <Cordova/CDVPlugin.h>
#import "RNCachingURLProtocol.h"

@interface CDVOfflineMode : CDVPlugin

- (void)pluginInitialize;
- (void)setInternalCallback:(CDVInvokedUrlCommand*)command;
- (void)setCheckConnectionURL:(CDVInvokedUrlCommand*)command;

@end
