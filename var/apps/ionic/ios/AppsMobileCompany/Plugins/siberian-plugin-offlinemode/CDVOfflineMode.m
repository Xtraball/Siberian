#import <Cordova/CDV.h>
#import "CDVOfflineMode.h"
#import "SBOfflineModeManager.h"
#import "SBOfflineModeDownloader.h"

@implementation CDVOfflineMode

NSString *icb;

- (void)pluginInitialize {
    [[SBOfflineModeManager sharedManager] watchReachability];
    [[NSNotificationCenter defaultCenter] addObserver:self
                                             selector:@selector(connectionStatusChanged:)
                                                 name: @"SBOfflineModeManagerConnectionStatusChanged"
                                               object:nil];

    // Prepare the cache AppsMobileCompany
    [NSURLProtocol registerClass:[RNCachingURLProtocol class]];
}


- (void)setInternalCallback:(CDVInvokedUrlCommand*)command
{
    icb = command.callbackId;
}

- (void)setCanCache:(CDVInvokedUrlCommand*)command
{
    [SBOfflineModeManager sharedManager].canCache = YES;
    [[NSUserDefaults standardUserDefaults] setBool:YES forKey:@"canCache"];
    [[NSUserDefaults standardUserDefaults] synchronize];
}

- (void)setCheckConnectionURL:(CDVInvokedUrlCommand*)command
{
    NSString *url = [command.arguments objectAtIndex:0];
    NSLog(@"[ios] use URL: %@", url);
    
    [SBOfflineModeManager sharedManager].checkConnectionURL = url;
    
    CDVPluginResult* result = [CDVPluginResult resultWithStatus:CDVCommandStatus_OK];
    [self.commandDelegate sendPluginResult:result callbackId:command.callbackId];
}

- (void)cacheURL:(CDVInvokedUrlCommand*)command {
    NSString *uri = [command.arguments objectAtIndex:0];
    NSLog(@"[ios] cacheURL : %@", uri);
    NSURL *url = [NSURL URLWithString:uri];
    if(url) {
        
        [[[SBOfflineModeDownloader alloc]
         initWithCommandDelegate:self.commandDelegate
         callback:command.callbackId
         andURL:url] start];
    } else {
        [self.commandDelegate sendPluginResult:[CDVPluginResult resultWithStatus:CDVCommandStatus_ERROR]
                                    callbackId:command.callbackId];
    }
}

- (void)connectionStatusChanged:(NSNotification*)notification {
    if(icb) {
       NSDictionary *dico = [NSDictionary
                              dictionaryWithObjectsAndKeys:
                              [NSNumber numberWithBool:[SBOfflineModeManager sharedManager].isOnline],
                              @"isOnline",
                              nil
                              ];

        CDVPluginResult* result = [CDVPluginResult resultWithStatus:CDVCommandStatus_OK
                                                messageAsDictionary:dico];
        [result setKeepCallbackAsBool:YES];
        [self.commandDelegate sendPluginResult:result callbackId:icb];
    }
}

@end
