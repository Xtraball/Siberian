#import <Foundation/Foundation.h>
#import <Cordova/CDVPlugin.h>

BOOL useCache;

@interface CDVOfflineMode : CDVPlugin
- (void)useCache:(CDVInvokedUrlCommand*)command;
@end

@interface RNCachingURLProtocol : NSURLProtocol
- (NSString *)cachePathForRequest:(NSURLRequest *)aRequest;
@end
