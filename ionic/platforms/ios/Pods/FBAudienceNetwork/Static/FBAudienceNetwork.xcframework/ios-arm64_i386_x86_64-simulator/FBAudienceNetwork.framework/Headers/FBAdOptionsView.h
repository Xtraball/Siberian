// (c) Facebook, Inc. and its affiliates. Confidential and proprietary.

#import <UIKit/UIKit.h>

NS_ASSUME_NONNULL_BEGIN

@class FBNativeAdBase;

/**
 Minimum dimensions of the view.
 */
extern const CGFloat FBAdOptionsViewWidth;
extern const CGFloat FBAdOptionsViewHeight;

@interface FBAdOptionsView : UIView

/**
 The native ad that provides AdChoices info, such as click url. Setting this updates the nativeAd.
 */
@property (nonatomic, weak, readwrite, nullable) FBNativeAdBase *nativeAd;

/**
 The color to be used when drawing the AdChoices view.
 */
@property (nonatomic, strong, nullable) UIColor *foregroundColor;

/**
 Only show the ad choices triangle icon. Default is NO.

 Sizing note:
    - Single icon is rendered in a square frame, it will default to the smallest dimension.
    - Non single icon requires aspect ratio of the view to be 2.4 or less.
 */
@property (nonatomic, assign) BOOL useSingleIcon;

@end

NS_ASSUME_NONNULL_END
