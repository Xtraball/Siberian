#import <Foundation/Foundation.h>

@interface SBOfflineModeManager : NSObject {
    BOOL isOnline;
    BOOL canCache;

    NSString *checkConnectionURL;
}

@property (nonatomic, retain) NSString *checkConnectionURL;
@property (nonatomic) BOOL isOnline;
@property (nonatomic) BOOL canCache;

+ (SBOfflineModeManager *)sharedManager;
- (void)watchReachability;
- (void)setUnreachable;
- (void)setReachable;


@end
