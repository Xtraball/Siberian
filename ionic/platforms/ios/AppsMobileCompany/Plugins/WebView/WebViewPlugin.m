#include <sys/types.h>
#include <sys/sysctl.h>
#import <Cordova/CDV.h>
#import "WebViewPlugin.h"

#import "MainViewController.h"
#import "Constants.h"

@implementation WebViewPlugin

@synthesize webViewController;

- (void)adjustBehavior {
#if __IPHONE_OS_VERSION_MAX_ALLOWED >= 110000
    if (@available(iOS 11.0, *)) {
        [self.webView.scrollView setContentInsetAdjustmentBehavior:UIScrollViewContentInsetAdjustmentNever];
    }
#endif
}

- (void)pluginInitialize {
    [self adjustBehavior];
    [[NSNotificationCenter defaultCenter] addObserver:self
                                             selector:@selector(onResume)
                                                 name:UIApplicationWillEnterForegroundNotification
                                               object:nil];
    [[NSNotificationCenter defaultCenter] addObserver:self
                                             selector:@selector(onPause)
                                                 name:UIApplicationWillResignActiveNotification
                                               object:nil];
}

- (void)webViewAdjustmenBehavior:(CDVInvokedUrlCommand*)command {
    [self adjustBehavior];
}

- (void)subscribeCallback:(CDVInvokedUrlCommand*)command {
    [self.commandDelegate runInBackground:^{
        @try {
            webViewFinishedCallBack = command.callbackId;
        }
        @catch (NSException *exception) {
            NSString* reason=[exception reason];
            CDVPluginResult* pluginResult = [CDVPluginResult resultWithStatus:CDVCommandStatus_ERROR messageAsString: reason];
            [self.commandDelegate sendPluginResult:pluginResult callbackId:command.callbackId];
        }
    }];
}

- (void)subscribeDebugCallback:(CDVInvokedUrlCommand*)command {
    [self.commandDelegate runInBackground:^{
        @try {
            debugCallback = command.callbackId;
        }
        @catch (NSException *exception) {
            NSString* reason=[exception reason];
            CDVPluginResult* pluginResult = [CDVPluginResult resultWithStatus:CDVCommandStatus_ERROR messageAsString: reason];
            [self.commandDelegate sendPluginResult:pluginResult callbackId:command.callbackId];
        }
    }];
}

- (void)subscribeResumeCallback:(CDVInvokedUrlCommand*)command {
    [self.commandDelegate runInBackground:^{
        @try {
            resumeCallback = command.callbackId;
        }
        @catch (NSException *exception) {
            NSString* reason=[exception reason];
            CDVPluginResult* pluginResult = [CDVPluginResult resultWithStatus:CDVCommandStatus_ERROR messageAsString: reason];
            [self.commandDelegate sendPluginResult:pluginResult callbackId:command.callbackId];
        }
    }];
}

- (void)subscribePauseCallback:(CDVInvokedUrlCommand*)command {
    [self.commandDelegate runInBackground:^{
        @try {
            pauseCallback = command.callbackId;
        }
        @catch (NSException *exception) {
            NSString* reason=[exception reason];
            CDVPluginResult* pluginResult = [CDVPluginResult resultWithStatus:CDVCommandStatus_ERROR messageAsString: reason];
            [self.commandDelegate sendPluginResult:pluginResult callbackId:command.callbackId];
        }
    }];
}

- (void)subscribeUrlCallback:(CDVInvokedUrlCommand*)command
{
    [self.commandDelegate runInBackground:^{
        @try {
            urlCallback = command.callbackId;
        }
        @catch (NSException *exception) {
            NSString* reason=[exception reason];
            CDVPluginResult* pluginResult = [CDVPluginResult resultWithStatus:CDVCommandStatus_ERROR messageAsString: reason];
            [self.commandDelegate sendPluginResult:pluginResult callbackId:command.callbackId];
        }
    }];
}


- (void)load:(CDVInvokedUrlCommand*)command {
    if (self.webViewController == nil) {
        [self show:command];
    } else {
        NSString* url =(NSString*)[command.arguments objectAtIndex:0];
        [self.webViewController loadURL:url];
    }
}

- (void)reload:(CDVInvokedUrlCommand*)command {
    if (self.webViewController == nil) {
        NSLog(@"Web View is not initialized.");
    } else {
        [self.webViewController reload];
    }
}

- (void)loadApp:(CDVInvokedUrlCommand*)command {
    [self.commandDelegate runInBackground:^{
        @try {
            dispatch_async(dispatch_get_main_queue(), ^{
                webViewController = [[WebViewController alloc] init];
                webViewController.delegate = self;
                [self.viewController presentViewController:webViewController animated:NO completion:nil];
            });

            CDVPluginResult* pluginResult = [CDVPluginResult resultWithStatus:CDVCommandStatus_OK];
            [self.commandDelegate sendPluginResult:pluginResult callbackId:command.callbackId];
        }
        @catch (NSException *exception) {
            NSString* reason=[exception reason];
            CDVPluginResult* pluginResult = [CDVPluginResult resultWithStatus:CDVCommandStatus_ERROR messageAsString: reason];
            [self.commandDelegate sendPluginResult:pluginResult callbackId:command.callbackId];
        }
    }];
}

- (void)show:(CDVInvokedUrlCommand*)command {
    [self.commandDelegate runInBackground:^{
        @try {
            dispatch_async(dispatch_get_main_queue(), ^{
                webViewController = [[WebViewController alloc] init];
                webViewController.delegate = self;
                [self.viewController presentViewController:webViewController animated:NO completion:nil];
            });

            CDVPluginResult* pluginResult = [CDVPluginResult resultWithStatus:CDVCommandStatus_OK];
            [self.commandDelegate sendPluginResult:pluginResult callbackId:command.callbackId];
        }
        @catch (NSException *exception) {
            NSString* reason=[exception reason];
            CDVPluginResult* pluginResult = [CDVPluginResult resultWithStatus:CDVCommandStatus_ERROR messageAsString: reason];
            [self.commandDelegate sendPluginResult:pluginResult callbackId:command.callbackId];
        }
    }];
}

- (void)hide:(CDVInvokedUrlCommand*)command {
    [self.commandDelegate runInBackground:^{
        @try {

            dispatch_async(dispatch_get_main_queue(), ^{
                [self.viewController dismissViewControllerAnimated:YES completion:nil];
            });

            CDVPluginResult* pluginResult = [CDVPluginResult resultWithStatus:CDVCommandStatus_OK];
            [self.commandDelegate sendPluginResult:pluginResult callbackId:command.callbackId];
        }
        @catch (NSException *exception) {
            NSString* reason=[exception reason];
            CDVPluginResult* pluginResult = [CDVPluginResult resultWithStatus:CDVCommandStatus_ERROR messageAsString: reason];
            [self.commandDelegate sendPluginResult:pluginResult callbackId:command.callbackId];
        }
    }];
}

- (void)exitApp:(CDVInvokedUrlCommand*)command {
    exit(0);
}

- (void)webViewFinished {
    NSLog(@"webViewFinished");
    webViewController = nil;

    CDVPluginResult* pluginResult = [CDVPluginResult resultWithStatus:CDVCommandStatus_OK];
    [self.commandDelegate sendPluginResult:pluginResult callbackId:webViewFinishedCallBack];
}

- (void)callDebugCallback {
    NSLog(@"callDebugCallback");
    CDVPluginResult* pluginResult = [CDVPluginResult resultWithStatus:CDVCommandStatus_OK];
    [pluginResult setKeepCallbackAsBool:YES];
    [self.commandDelegate sendPluginResult:pluginResult callbackId:debugCallback];
}

- (void)callResumeCallback:(NSString*)url {
    NSLog(@"callResumeCallback");
    CDVPluginResult* pluginResult = [CDVPluginResult resultWithStatus:CDVCommandStatus_OK messageAsString:url];
    [pluginResult setKeepCallbackAsBool:YES];
    [self.commandDelegate sendPluginResult:pluginResult callbackId:resumeCallback];
}

- (void)callPauseCallback:(NSString*)url {
    NSLog(@"callPauseCallback");
    CDVPluginResult* pluginResult = [CDVPluginResult resultWithStatus:CDVCommandStatus_OK messageAsString:url];
    [pluginResult setKeepCallbackAsBool:YES];
    [self.commandDelegate sendPluginResult:pluginResult callbackId:pauseCallback];
}

- (void)callUrlCallback:(NSString*)url didNavigate:(BOOL)didNavigate {
    NSError  *error;
    NSLog(@"callUrlCallback");
    NSDictionary *resultDictionary = @{@"url"         : url,
                                       @"didNavigate" : [NSNumber numberWithBool:didNavigate]};
    NSData   *serialized      = [NSJSONSerialization dataWithJSONObject:resultDictionary options:0 error:&error];
    NSString *serializedString = [[NSString alloc] initWithData:serialized encoding:NSUTF8StringEncoding];
    CDVPluginResult* pluginResult = [CDVPluginResult resultWithStatus:CDVCommandStatus_OK messageAsString:serializedString];
    [pluginResult setKeepCallbackAsBool:YES];
    [self.commandDelegate sendPluginResult:pluginResult callbackId:urlCallback];
}

- (BOOL)shouldOverrideLoadWithRequest:(NSURLRequest*)request navigationType:(UIWebViewNavigationType)navigationType {
    return true;
}

- (void) onResume {
    [self callResumeCallback:[self.webViewController.webViewEngine URL].absoluteString];
}

- (void) onPause {
    [self callPauseCallback:[self.webViewController.webViewEngine URL].absoluteString];
}

@end

@implementation WebViewController

@synthesize delegate;

- (id)init {
    self = [super init];
    return self;
}

#pragma mark View lifecycle

- (void)viewWillAppear:(BOOL)animated
{
    // View defaults to full size.  If you want to customize the view's size, or its subviews (e.g. webView),
    // you can do so here.

    [super viewWillAppear:animated];
}

- (void)viewDidLoad {
    [super viewDidLoad];

    // register plugin for shouldOverrideLoadWithRequest
    [self.pluginObjects setObject:delegate forKey:@"WebViewPlugin"];

    UITapGestureRecognizer *tapGestureRecognizer = [[UITapGestureRecognizer alloc] initWithTarget:self action:@selector(tapGestureTriggered:)];
    [tapGestureRecognizer setNumberOfTapsRequired:3];
    [tapGestureRecognizer setNumberOfTouchesRequired:1];
    [self.webView addGestureRecognizer:tapGestureRecognizer];
}

- (void) tapGestureTriggered: (UITapGestureRecognizer *)recognizer {
    //Code to handle the gesture
    NSLog(@"WebViewController tapGestureTriggered");
    [delegate callDebugCallback];
}

- (void)viewDidDisappear:(BOOL)animated {
    NSLog(@"viewDidDisappear");
    [super viewDidDisappear:animated];
    [delegate webViewFinished];
    delegate = nil;
}

- (void)loadURL: (NSString *)url {
    [self.webViewEngine loadRequest:[NSURLRequest requestWithURL:[NSURL URLWithString:url]]];
}

- (void)reload {
    [self.webViewEngine loadRequest:[NSURLRequest requestWithURL:[self.webViewEngine URL]]];
}

- (void)dealloc {
    // de-register plugin to keep it from being disposed in implicit call to [super dealloc]
    [self.pluginObjects removeObjectForKey:@"WebViewPlugin"];
}

@end
