#import <Cordova/CDV.h>

@interface SBOfflineModeDownloader : NSObject {
    NSString *callback;
    NSURL *URL;
    NSObject<CDVCommandDelegate> *cmdDelegate;
}

- (id)initWithCommandDelegate:(NSObject<CDVCommandDelegate> *)commandDelegate callback:(NSString *)callbackId andURL:(NSURL *)URL;
- (void)start;

@end
