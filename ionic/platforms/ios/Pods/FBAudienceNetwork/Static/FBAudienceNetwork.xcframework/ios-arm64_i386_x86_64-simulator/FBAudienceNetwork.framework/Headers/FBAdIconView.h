// (c) Facebook, Inc. and its affiliates. Confidential and proprietary.

#import <UIKit/UIKit.h>

#import <FBAudienceNetwork/FBAdDefines.h>
#import <FBAudienceNetwork/FBMediaView.h>
#import <FBAudienceNetwork/UIView+FBNativeAdViewTag.h>

NS_ASSUME_NONNULL_BEGIN

FB_CLASS_EXPORT
FB_DEPRECATED_WITH_MESSAGE("This class will be removed in a future release. Use FBMediaView instead.")
@interface FBAdIconView : FBMediaView

/**
 The tag for the icon view. It always returns FBNativeAdViewTagIcon.
 */
@property (nonatomic, assign, readonly) FBNativeAdViewTag nativeAdViewTag;

@end

NS_ASSUME_NONNULL_END
