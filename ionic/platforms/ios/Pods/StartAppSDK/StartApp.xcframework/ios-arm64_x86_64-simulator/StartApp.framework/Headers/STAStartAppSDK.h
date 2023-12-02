//
//  StartAppSDK.h
//  StartAppAdSDK
//
//  Created by StartApp on 3/13/14.
//  Copyright (c) 2014 StartApp. All rights reserved.
//  SDK version 4.10.1

#import <Foundation/Foundation.h>
#import "STAStartAppAd.h"
#import "STASplashPreferences.h"


typedef enum {
    STAGender_Undefined = 0,
    STAGender_Female = 1,
    STAGender_Male = 2
} STAGender;


@interface STASDKPreferences : NSObject

/// User age
@property (nonatomic, assign) NSUInteger age;

/// User age as string
@property (nonatomic, strong) NSString *ageStr;

/// User gender
@property (nonatomic, assign) STAGender gender;

/*!
 * @brief STASDKPreferences custructor
 * @discussion Creates STASDKPreferences instance with age and gender.
 * @param age User age
 * @param gender User gender
 * @return STASDKPreferences instance with provided age and gender
 */
+ (instancetype)prefrencesWithAge:(NSUInteger)age andGender:(STAGender)gender;

/*!
 * @brief STASDKPreferences custructor
 * @discussion Creates STASDKPreferences instance with age string and gender.
 * @param ageStr User age as string
 * @param gender User gender
 * @return STASDKPreferences instance with provided age string and gender
 */
+ (instancetype)prefrencesWithAgeStr:(NSString *)ageStr andGender:(STAGender)gender;

@end


@interface STAStartAppSDK : NSObject

/// Your Application ID
@property (nonatomic, strong) NSString *appID;

/// Your Developer ID
@property (nonatomic, strong) NSString *devID;
@property (nonatomic, strong) NSString *accountID DEPRECATED_MSG_ATTRIBUTE("accountID is deprecated");

/// SDK preferences
@property (nonatomic, strong) STASDKPreferences *preferences;

/// SDK version
@property (nonatomic, readonly) NSString *version;

@property (nonatomic, assign) BOOL isUnityEnvironment DEPRECATED_MSG_ATTRIBUTE("Will be removed in next version. Use addWrapperWithName:version: instead");
@property (nonatomic, assign) BOOL isCoronaEnvironment DEPRECATED_MSG_ATTRIBUTE("Will be removed in next version. Use addWrapperWithName:version: instead");
@property (nonatomic, assign) BOOL isCocos2DXEnvironment DEPRECATED_MSG_ATTRIBUTE("Will be removed in next version. Use addWrapperWithName:version: instead");
@property (nonatomic, assign) BOOL isAdMobMediationEnvironment DEPRECATED_MSG_ATTRIBUTE("Will be removed in next version. Use addWrapperWithName:version: instead");
@property (nonatomic, assign) BOOL isMoPubMediationEnvironment DEPRECATED_MSG_ATTRIBUTE("Will be removed in next version. Use addWrapperWithName:version: instead");
@property (nonatomic, assign) BOOL isSwiftEnvironment DEPRECATED_MSG_ATTRIBUTE("Will be removed in next version. Use addWrapperWithName:version: instead");
@property (nonatomic, strong) NSString *adMobAdapterVersion DEPRECATED_MSG_ATTRIBUTE("Will be removed in next version. Use addWrapperWithName:version: instead");
@property (nonatomic, strong) NSString *moPubAdapterVersion DEPRECATED_MSG_ATTRIBUTE("Will be removed in next version. Use addWrapperWithName:version: instead");

/// Use this flag to turn return ad on or off. Turned on by default.
@property (nonatomic, assign) BOOL returnAdEnabled;

/// Use this flag to turn embeded consent dialog on or off. Turned on by default.
@property (nonatomic, assign) BOOL consentDialogEnabled;

/// This flag allows you to receive test campaigns to test your integrations. Shoud be turned off before submitting to AppStore. Turned off by default.
@property (nonatomic, assign) BOOL testAdsEnabled;

/*!
 * @brief STAStartAppSDK singleton method
 * @return STAStartAppSDK instance
 */
+ (STAStartAppSDK *)sharedInstance;

/*!
 * @brief Initializes SDK with Developer ID and Application ID
 * @discussion Call this method to start working with SDK and set Developer ID and Application ID.
 * @param devID Your Developer ID
 * @param appID Current Application ID
 */
- (void)SDKInitialize:(NSString *)devID andAppID:(NSString *)appID;

/*!
 * @brief Adds/removes extra parameters to an ad request
 * @discussion Call this method to pass varios sdk settings such as us-privacy-string
 */
- (void)handleExtras:(void(^)(NSMutableDictionary<NSString*, id>*))block;

/*!
 * @brief Displays a splash ad with default splash preferences settings
 * @discussion Call this method to display a splash ad with default splash preferences settings.
 */
- (void)showSplashAd;

/*!
 * @brief Displays a splash ad with default splash preferences and calls corresponding delegate methods
 * @discussion Call this method to display a splash ad with default splash preferences and pass delegate to be notified about splash ad events.
 * @param delegate Delegate object that will receive splash ad callbacks
 */
- (void)showSplashAdWithDelegate:(id<STADelegateProtocol>)delegate;

/*!
 * @brief Displays a splash ad with specific splash preferences
 * @discussion Call this method to display a splash ad with specific splash preferences.
 * @param splashPreferences Splash preferences to customize splash ad representation
 */
- (void)showSplashAdWithPreferences:(STASplashPreferences *)splashPreferences;

/*!
 * @brief Displays a splash ad with specific splash preferences and calls corresponding delegate methods
 * @discussion Call this method to display a splash ad with specific splash preferences and pass delegate to be notified about splash ad events.
 * @param delegate Delegate object that will receive splash ad callbacks
 * @param splashPreferences Splash preferences to customize splash ad representation
 */
- (void)showSplashAdWithDelegate:(id<STADelegateProtocol>)delegate withPreferences:(STASplashPreferences *)splashPreferences;

/*!
 * @brief Displays a splash ad with specific splash preferences, ad preferences and calls corresponding delegate methods
 * @discussion Call this method to display a splash ad with specific splash preferences, ad preferences and delegate to be notified about splash ad events.
 * @param delegate Delegate object that will receive splash ad callbacks
 * @param adPrefs Custom ad preferences
 * @param splashPreferences Splash preferences to customize splash ad representation
 */
- (void)showSplashAdWithDelegate:(id<STADelegateProtocol>)delegate withAdPreferences:(STAAdPreferences *)adPrefs withPreferences:(STASplashPreferences *)splashPreferences;

/*!
 * @brief Displays a splash ad with specific splash preferences, ad preferences, ad tag and calls corresponding delegate methods
 * @discussion Call this method to display a splash ad with specific splash preferences, ad preferences, ad tag and delegate to be notified about splash ad events.
 * @param delegate Delegate object that will receive splash ad callbacks
 * @param adPrefs Custom ad preferences
 * @param splashPreferences Splash preferences to customize splash ad representation
 * @param adTag Ad tag is a unique string that is sent within impression
 */
- (void)showSplashAdWithDelegate:(id<STADelegateProtocol>)delegate withAdPreferences:(STAAdPreferences *)adPrefs withPreferences:(STASplashPreferences *)splashPreferences withAdTag:(NSString *)adTag __deprecated_msg("adTag on impression is deprecated. Please provide adTag for ad request in STAAdPreferences object via showSplashAdWithDelegate:withAdPreferences:withPreferences: method.");;



- (void)inAppPurchaseMade;
- (void)inAppPurchaseMadeWithAmount:(CGFloat)amount;
- (void)startNewSession;

/*!
 * @brief Notifies SDK that user consent (GDPR) was given or changed
 * @discussion Use this method to provide user consent (GDPR) to SDK.
 * @param consent User consent flag
 * @param consentType User consent key. "pas" in case of GDPR
 * @param ts Timestamp representing the specific time a consent was given by the user
 */
- (void)setUserConsent:(BOOL)consent forConsentType:(NSString *)consentType withTimestamp:(long)ts;

/*!
 * @brief Notifies SDK that application uses wrappers (e.g. Unity)
 * @discussion Use this method to notify SDK that you use wrappers.
 * @param wrapperName Short name of the wrapper (e.g. "Unity")
 * @param versionString Wrapper version string
 */
- (void)addWrapperWithName:(NSString *)wrapperName version:(NSString *)versionString;

/*!
 * @brief Notifies SDK that application uses mediation (e.g. AdMob, MoPub, IronSource, AppLovin MAX or others). Also enables mediation mode.
 * @discussion Use this method to notify SDK that you use mediation and to enable mediation mode(to disable return ad and consent dialog).
 * @param mediationName Short name of the mediation (e.g. "Unity", "AdMob", "MoPub", "IronSource", "AppLovin" or others)
 * @param versionString Adapter version string
 */
- (void)enableMediationModeFor:(NSString *)mediationName version:(NSString *)versionString;

// Unity methods
- (void)unitySDKInitialize;
- (void)unityAppWillEnterForeground;
- (void)unityAppDidEnterBackground;
- (void)setUnitySupportedOrientations:(NSInteger)supportedOrientations;
- (void)setUnityAutoRotation:(NSInteger)autoRotation;
- (void)setUnityVersion:(NSString *)unityVersion;

@end
