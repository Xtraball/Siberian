//
//
#import <Cordova/CDV.h>

@interface CDVNavigator : CDVPlugin

- (void)openByUrl:(NSString *)url;
- (void)navigate:(CDVInvokedUrlCommand*)command;

@end

