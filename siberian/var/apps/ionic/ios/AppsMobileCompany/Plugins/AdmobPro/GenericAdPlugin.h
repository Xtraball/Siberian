//
//  GenericAdPlugin.h
//  TestAdMobCombo
//
//  Created by Xie Liming on 14-10-28.
//
//

#import "CDVPluginExt.h"

#define OPT_LICENSE         @"license"
#define OPT_IS_TESTING      @"isTesting"
#define OPT_LOG_VERBOSE     @"logVerbose"

#define OPT_ADID            @"adId"
#define OPT_AUTO_SHOW       @"autoShow"

#define OPT_AD_SIZE         @"adSize"
#define OPT_AD_WIDTH        @"width"
#define OPT_AD_HEIGHT       @"height"
#define OPT_OVERLAP         @"overlap"
#define OPT_ORIENTATION_RENEW   @"orientationRenew"
#define OPT_OFFSET_TOPBAR   @"offsetTopBar"
#define OPT_BG_COLOR        @"bgColor"
#define OPT_STATUSBAR_STYLE @"statusBarStyle"

#define OPT_POSITION        @"position"
#define OPT_X               @"x"
#define OPT_Y               @"y"

#define OPT_AD_EXTRAS       @"adExtras"

enum {
    POS_NO_CHANGE       = 0,
    POS_TOP_LEFT        = 1,
    POS_TOP_CENTER      = 2,
    POS_TOP_RIGHT       = 3,
    POS_LEFT            = 4,
    POS_CENTER          = 5,
    POS_RIGHT           = 6,
    POS_BOTTOM_LEFT     = 7,
    POS_BOTTOM_CENTER   = 8,
    POS_BOTTOM_RIGHT    = 9,
    POS_XY              = 10
};

#define EVENT_AD_LOADED         @"onAdLoaded"
#define EVENT_AD_FAILLOAD       @"onAdFailLoad"
#define EVENT_AD_PRESENT        @"onAdPresent"
#define EVENT_AD_LEAVEAPP       @"onAdLeaveApp"
#define EVENT_AD_DISMISS        @"onAdDismiss"
#define EVENT_AD_WILLPRESENT    @"onAdWillPresent"
#define EVENT_AD_WILLDISMISS    @"onAdWillDismiss"

#define ADTYPE_BANNER           @"banner"
#define ADTYPE_INTERSTITIAL     @"interstitial"
#define ADTYPE_NATIVE           @"native"
#define ADTYPE_REWARDVIDEO      @"rewardvideo"

@interface GenericAdPlugin : CDVPluginExt

- (void) getAdSettings:(CDVInvokedUrlCommand *)command;
- (void) setOptions:(CDVInvokedUrlCommand *)command;

- (void)createBanner:(CDVInvokedUrlCommand *)command;
- (void)showBanner:(CDVInvokedUrlCommand *)command;
- (void)showBannerAtXY:(CDVInvokedUrlCommand *)command;
- (void)hideBanner:(CDVInvokedUrlCommand *)command;
- (void)removeBanner:(CDVInvokedUrlCommand *)command;

- (void)prepareInterstitial:(CDVInvokedUrlCommand *)command;
- (void)showInterstitial:(CDVInvokedUrlCommand *)command;
- (void)removeInterstitial:(CDVInvokedUrlCommand *)command;
- (void)isInterstitialReady:(CDVInvokedUrlCommand*)command;

- (void) prepareRewardVideoAd:(CDVInvokedUrlCommand *)command;
- (void) showRewardVideoAd:(CDVInvokedUrlCommand *)command;

@property (assign) BOOL testTraffic;
@property (assign) BOOL licenseValidated;
@property (assign) BOOL isTesting;
@property (assign) BOOL logVerbose;

@property (nonatomic, retain) NSString* bannerId;
@property (nonatomic, retain) NSString* interstitialId;
@property (nonatomic, retain) NSString* rewardVideoId;

@property (assign) int adWidth;
@property (assign) int adHeight;
@property (assign) BOOL overlap;
@property (assign) BOOL orientationRenew;
@property (assign) BOOL offsetTopBar;
@property (nonatomic, retain) UIColor* bgColor;

@property (assign) int adPosition;
@property (assign) int posX;
@property (assign) int posY;

@property (assign) BOOL autoShowBanner;
@property (assign) BOOL autoShowInterstitial;
@property (assign) BOOL autoShowRewardVideo;

@property (assign) int widthOfView;

@property (nonatomic, retain) UIView *banner;
@property (nonatomic, retain) NSObject *interstitial;
@property (nonatomic, retain) NSObject *rewardvideo;

@property (assign) BOOL bannerInited;
@property (assign) BOOL bannerVisible;
@property (assign) BOOL interstitialReady;

#pragma mark virtual methods

- (void)pluginInitialize;

- (void) parseOptions:(NSDictionary*) options;
- (NSString*) md5:(NSString*) s;

- (UIColor *)getUIColorObjectByName:(NSString *)color;
- (UIColor *)getUIColorObjectFromHexString:(NSString *)hexStr alpha:(CGFloat)alpha;
- (unsigned int)intFromHexString:(NSString *)hexStr;

- (void) onOrientationChange;
- (float) getStatusBarOffset;

- (bool) __isLandscape;
- (void) __showBanner:(int) position atX:(int)x atY:(int)y;

- (NSString*) __getProductShortName;
- (NSString*) __getTestBannerId;
- (NSString*) __getTestInterstitialId;
- (NSString*) __getTestRewardVideoId;

- (UIView*) __createAdView:(NSString*)adId;
- (int) __getAdViewWidth:(UIView*)view;
- (int) __getAdViewHeight:(UIView*)view;
- (void) __loadAdView:(UIView*)view;
- (void) __pauseAdView:(UIView*)view;
- (void) __resumeAdView:(UIView*)view;
- (void) __destroyAdView:(UIView*)view;

- (NSObject*) __createInterstitial:(NSString*)adId;
- (void) __loadInterstitial:(NSObject*)interstitial;
- (void) __showInterstitial:(NSObject*)interstitial;
- (void) __destroyInterstitial:(NSObject*)interstitial;

- (NSObject*) __prepareRewardVideoAd:(NSString*)adId;
- (BOOL) __showRewardVideoAd:(NSObject*)rewardvideo;

- (void) fireAdEvent:(NSString*)event withType:(NSString*)adType;
- (void) fireAdErrorEvent:(NSString*)event withCode:(int)errCode withMsg:(NSString*)errMsg withType:(NSString*)adType;

@end
