#import "SBOfflineModeManager.h"
#import "TMReachability.h"

@implementation SBOfflineModeManager

@synthesize isOnline, checkConnectionURL;

BOOL isAwareOfReachability = NO;
BOOL postNotifications = NO;
BOOL canCache = NO;
NSTimer *checkTimer;

#pragma mark Singleton Methods

+ (id)sharedManager {
    static SBOfflineModeManager *sharedMyManager = nil;
    static dispatch_once_t onceToken;
    dispatch_once(&onceToken, ^{
        sharedMyManager = [[self alloc] init];
    });
    return sharedMyManager;
}

- (id)init {
    if (self = [super init]) {
        checkConnectionURL = nil;
        isOnline = YES;
        canCache = [[NSUserDefaults standardUserDefaults] boolForKey:@"canCache"];

        // Allocate a reachability object
        TMReachability* reach = [TMReachability reachabilityWithHostname:@"www.google.com"];
        
        reach.reachableBlock = ^(TMReachability *reach)
        {
            [self checkConnection];
        };
        
        reach.unreachableBlock = ^(TMReachability *reach)
        {
            [self checkConnection];
        };
        
        [reach startNotifier];
        
    }
    return self;
}

- (void)dealloc {
    // Should never be called, but just here for clarity really.
}

#pragma mark Actual Code

- (void)watchReachability {
    postNotifications = YES;
    [self postNotification];
}

- (void)setUnreachable {
    isAwareOfReachability = YES;
    isOnline = NO;
    
    [self postNotification];
}

- (void)setReachable {
    isAwareOfReachability = YES;
    isOnline = YES;
    
    [self postNotification];
}

- (void)postNotification {
    if(isAwareOfReachability && postNotifications) {
        [[NSNotificationCenter defaultCenter]
         postNotificationName:@"SBOfflineModeManagerConnectionStatusChanged"
         object:self];
    }
}

- (void)checkConnection {
    if(self.checkConnectionURL) {
        NSURL *URL = [NSURL URLWithString:self.checkConnectionURL];
        
        if ([[UIApplication sharedApplication] canOpenURL:[URL absoluteURL]] ) {
            NSURLSession *session = [NSURLSession sharedSession];
            [[session dataTaskWithURL:URL
                    completionHandler:^(NSData *data,
                                        NSURLResponse *response,
                                        NSError *error) {
                        
                        NSString *resp = [[NSString alloc] initWithData:data encoding:NSUTF8StringEncoding];
                        
                        if([resp isEqualToString:@"1"]) {
                            [self setReachable];
                        } else {
                            [self setUnreachable];
                        }
                        
                        [self startTimer];
                    }] resume];
            return;
        }
    }
    
    [self startTimer]; // Reschedule check for later, we might get URL later
}

- (void)startTimer {
    
    dispatch_async(dispatch_get_main_queue(), ^{
        if([checkTimer isKindOfClass: [NSTimer class]]) {
            [checkTimer invalidate];
            checkTimer = nil;
        }

        checkTimer = [NSTimer
                      scheduledTimerWithTimeInterval:3
                      target:[NSBlockOperation
                              blockOperationWithBlock:^{
                                  [self checkConnection];
                              }]
                      selector:@selector(main)
                      userInfo:nil
                      repeats:NO];
    });
}

@end
