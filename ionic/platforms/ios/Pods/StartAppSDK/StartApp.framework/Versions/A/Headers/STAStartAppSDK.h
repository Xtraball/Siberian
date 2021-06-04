//
//  StartAppSDK.h
//  StartAppAdSDK
//
//  Created by StartApp on 3/13/14.
//  Copyright (c) 2014 StartApp. All rights reserved.
//  SDK version 4.5.0

#import <Foundation/Foundation.h>
#import "STAStartAppAd.h"
#import "STASplashPreferences.h"


typedef enum {
    STAGender_Undefined = 0,
    STAGender_Female = 1,
    STAGender_Male = 2
} STAGender;

// STAAdPreferences holds params specific to an ad
@interface STASDKPreferences : NSObject

@property (nonatomic, assign) NSUInteger age;
@property (nonatomic, strong) NSString *ageStr;
@property (nonatomic, assign) STAGender gender;

+ (instancetype)prefrencesWithAge:(NSUInteger)age andGender:(STAGender)gender;
+ (instancetype)prefrencesWithAgeStr:(NSString *)ageStr andGender:(STAGender)gender;

@end


@interface STAStartAppSDK : NSObject

@property (nonatomic, strong) NSString *appID;
@property (nonatomic, strong) NSString *devID;
@property (nonatomic, strong) NSString *accountID;
@property (nonatomic, strong) STASDKPreferences *preferences;

@property (nonatomic, readonly) NSString *version;
@property (nonatomic, readonly) long buildNumber;

@property (nonatomic, assign) BOOL isUnityEnvironment;
@property (nonatomic, assign) BOOL isCoronaEnvironment;
@property (nonatomic, assign) BOOL isCocos2DXEnvironment;
@property (nonatomic, assign) BOOL isAdMobMediationEnvironment;
@property (nonatomic, assign) BOOL isMoPubMediationEnvironment;
@property (nonatomic, assign) BOOL isSwiftEnvironment;

@property (nonatomic, assign) BOOL returnAdEnabled; //Default is YES
@property (nonatomic, assign) BOOL consentDialogEnabled; //Default is YES
@property (nonatomic, assign) BOOL testAdsEnabled; //Default is NO

+ (STAStartAppSDK *)sharedInstance;
- (void)SDKInitialize:(NSString *)devID andAppID:(NSString *)appID;

// Disable Return Ad
- (void)disableReturnAd __deprecated_msg("Use returnAdEnabled property");

// Initialize Splash Ad
- (void)showSplashAd;
- (void)showSplashAdWithDelegate:(id<STADelegateProtocol>)delegate;
- (void)showSplashAdWithPreferences:(STASplashPreferences *)splashPreferences;
- (void)showSplashAdWithDelegate:(id<STADelegateProtocol>)delegate withPreferences:(STASplashPreferences *)splashPreferences;
- (void)showSplashAdWithDelegate:(id<STADelegateProtocol>)delegate withAdPreferences:(STAAdPreferences *)adPrefs withPreferences:(STASplashPreferences *)splashPreferences;
- (void)showSplashAdWithDelegate:(id<STADelegateProtocol>)delegate withAdPreferences:(STAAdPreferences *)adPrefs withPreferences:(STASplashPreferences *)splashPreferences withAdTag:(NSString *)adTag;

- (void)inAppPurchaseMade;
- (void)inAppPurchaseMadeWithAmount:(CGFloat)amount;
- (void)startNewSession;

- (void)setUserConsent:(BOOL)consent forConsentType:(NSString *)consentType withTimestamp:(long)ts;

//Unity methods
- (void)unitySDKInitialize;
- (void)unityAppWillEnterForeground;
- (void)unityAppDidEnterBackground;
- (void)setUnitySupportedOrientations:(NSInteger)supportedOrientations;
- (void)setUnityAutoRotation:(NSInteger)autoRotation;
- (void)setUnityVersion:(NSString *)unityVersion;

@end
