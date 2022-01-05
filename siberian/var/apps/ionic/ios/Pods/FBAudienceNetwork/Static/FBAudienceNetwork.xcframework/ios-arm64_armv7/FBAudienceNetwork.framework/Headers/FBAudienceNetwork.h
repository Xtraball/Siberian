// (c) Facebook, Inc. and its affiliates. Confidential and proprietary.

#import <UIKit/UIKit.h>

#import <FBAudienceNetwork/FBAdChoicesView.h>
#import <FBAudienceNetwork/FBAdDefines.h>
#import <FBAudienceNetwork/FBAdExperienceConfig.h>
#import <FBAudienceNetwork/FBAdExtraHint.h>
#import <FBAudienceNetwork/FBAdIconView.h>
#import <FBAudienceNetwork/FBAdOptionsView.h>
#import <FBAudienceNetwork/FBAdSDKNotificationManager.h>
#import <FBAudienceNetwork/FBAdSettings.h>
#import <FBAudienceNetwork/FBAdView.h>
#import <FBAudienceNetwork/FBAudienceNetworkAds.h>
#import <FBAudienceNetwork/FBDynamicBannerAd.h>
#import <FBAudienceNetwork/FBInterstitialAd.h>
#import <FBAudienceNetwork/FBMediaView.h>
#import <FBAudienceNetwork/FBMediaViewVideoRenderer.h>
#import <FBAudienceNetwork/FBNativeAd.h>
#import <FBAudienceNetwork/FBNativeAdCollectionViewAdProvider.h>
#import <FBAudienceNetwork/FBNativeAdCollectionViewCellProvider.h>
#import <FBAudienceNetwork/FBNativeAdScrollView.h>
#import <FBAudienceNetwork/FBNativeAdTableViewAdProvider.h>
#import <FBAudienceNetwork/FBNativeAdTableViewCellProvider.h>
#import <FBAudienceNetwork/FBNativeAdView.h>
#import <FBAudienceNetwork/FBNativeAdsManager.h>
#import <FBAudienceNetwork/FBNativeBannerAd.h>
#import <FBAudienceNetwork/FBNativeBannerAdView.h>
#import <FBAudienceNetwork/FBRewardedInterstitialAd.h>
#import <FBAudienceNetwork/FBRewardedVideoAd.h>
#import <FBAudienceNetwork/UIView+FBNativeAdViewTag.h>

// Unity Bridge
#import <FBAudienceNetwork/FBAdBridgeCommon.h>
#import <FBAudienceNetwork/FBAdBridgeContainer.h>
#import <FBAudienceNetwork/FBAdSettingsBridge.h>
#import <FBAudienceNetwork/FBAdUtilityBridge.h>
#import <FBAudienceNetwork/FBAdViewBridge.h>
#import <FBAudienceNetwork/FBInterstitialAdBridge.h>
#import <FBAudienceNetwork/FBRewardedVideoAdBridge.h>

// NOTE: Any changes should also be made to the module.modulemap
// to ensure comptability with Swift apps using Cocoapods

#define FB_AD_SDK_VERSION @"6.9.0"
