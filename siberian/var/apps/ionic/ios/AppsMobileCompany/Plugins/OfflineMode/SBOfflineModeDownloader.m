#import "SBOfflineModeDownloader.h"
#import "RNCachingURLProtocol.h"
#import "RNCachedData.h"

@implementation SBOfflineModeDownloader

BOOL running = NO;
BOOL sentResult = NO;

- (id)initWithCommandDelegate:(NSObject<CDVCommandDelegate> *)commandDelegate callback:(NSString *)callbackId andURL:(NSURL *)downloadURL {
    if(self = [self init]) {
        callback = callbackId;
        URL = downloadURL;
        cmdDelegate = commandDelegate;
        running = NO;
        sentResult = NO;
    }
    
    return self;
}

- (void)start {
    if(running) return;
    running = YES;

    NSURLRequest *request = [[NSURLRequest alloc] initWithURL:URL];
    NSString *cachePath = [RNCachingURLProtocol cachePathForRequest:request];

    NSURLSession *session = [NSURLSession sharedSession];
    [[session dataTaskWithRequest:request
            completionHandler:^(NSData *data,
                                NSURLResponse *response,
                                NSError *error) {
                
                if(!error) {
                    RNCachedData *cache = [RNCachedData new];
                    [cache setResponse: [RNCachingURLProtocol addCacheHeaderToResponse:response]];
                    [cache setData:data];
                    NSLog(@"Cached URL: %@", [request URL]);
                    [NSKeyedArchiver archiveRootObject:cache toFile:cachePath];
                    [self sendResult:CDVCommandStatus_OK];
                } else {
                    NSLog(@"Error caching URL : %@", [request URL]);
                    NSLog(@"%@", error);
                    [self sendResult:CDVCommandStatus_ERROR];
                }
            }] resume];
}

#pragma mark -
#pragma mark Utils


- (void)sendResult:(CDVCommandStatus)status {
    if(sentResult) return;
    [cmdDelegate sendPluginResult:[CDVPluginResult resultWithStatus:status] callbackId:callback];
    sentResult = YES;
}

@end
