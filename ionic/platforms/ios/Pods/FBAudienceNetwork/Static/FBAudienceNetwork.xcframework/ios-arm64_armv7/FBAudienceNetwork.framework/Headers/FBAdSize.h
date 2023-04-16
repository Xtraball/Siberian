// (c) Facebook, Inc. and its affiliates. Confidential and proprietary.

#import <UIKit/UIKit.h>

#import <FBAudienceNetwork/FBAdDefines.h>

NS_ASSUME_NONNULL_BEGIN

/// Represents the ad size.
struct FBAdSize {
    /// Internal size
    CGSize size;
};

/// Represents the ad size.
typedef struct FBAdSize FBAdSize;

/**
  DEPRECATED - Represents the fixed banner ad size - 320pt by 50pt.
 */
FB_EXPORT FBAdSize const kFBAdSize320x50;

/**
  Represents the flexible banner ad size, where banner width depends on
 its container width, and banner height is fixed as 50pt.
 */
FB_EXPORT FBAdSize const kFBAdSizeHeight50Banner;

/**
  Represents the flexible banner ad size, where banner width depends on
 its container width, and banner height is fixed as 90pt.
 */
FB_EXPORT FBAdSize const kFBAdSizeHeight90Banner;

/**
Represents the flexible dynamic banner ad size, where banner width depends on
its container width, and banner height is set by the backend.
*/
FB_EXPORT FBAdSize const kFBAdDynamicSizeHeightBanner;

/**
  Represents the interstitial ad size.
 */
FB_EXPORT FBAdSize const kFBAdSizeInterstitial;

/**
  Represents the flexible rectangle ad size, where width depends on
 its container width, and height is fixed as 250pt.
 */
FB_EXPORT FBAdSize const kFBAdSizeHeight250Rectangle;

NS_ASSUME_NONNULL_END
