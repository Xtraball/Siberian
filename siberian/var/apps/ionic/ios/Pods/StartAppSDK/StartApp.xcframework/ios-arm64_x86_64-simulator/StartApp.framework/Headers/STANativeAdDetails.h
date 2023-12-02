//
//  STANativeAdDetails.h
//  StartApp
//
//  Created by StartApp on 9/15/14.
//  Copyright (c) 2014 StartApp. All rights reserved.
//  SDK version 4.10.1

#import <Foundation/Foundation.h>
#import <UIKit/UIKit.h>

@interface STANativeAdDetails : NSObject

/// Title string
@property (nonatomic, copy) NSString *title;
/// Description string
@property (nonatomic, copy) NSString *description;
/// Rating of the ad in the App Store. The rating range is 1-5
@property (nonatomic, copy) NSNumber *rating;
/// Primary image URL of the ad, according to the selected size
@property (nonatomic, copy) NSString *imageUrl;
/// Secondary image URL of the ad, according to the selected size
@property (nonatomic, copy) NSString *secondaryImageUrl;
/// Primary image. Is loaded if autoBitmapDownload was set to YES in native ad preferences during loading
@property (nonatomic, copy) UIImage *imageBitmap;
/// Secondary image. Is loaded if autoBitmapDownload was set to YES in native ad preferences during loading
@property (nonatomic, copy) UIImage *secondaryImageBitmap;
/// Category of the app in the App Store
@property (nonatomic, copy) NSString *category;
/// Ad's internal number (for communication with AM)
@property (nonatomic, copy) NSString *adId;
/// Call to action for the ad (either "install" or "open"
@property (nonatomic, copy) NSString *clickToInstall;
/// End-user license agreement URL
@property (nonatomic, copy) NSString *eulaUrl;
/// Policy image URL
@property (nonatomic, copy) NSString *policyImageUrl;
/// Policy image
@property (nonatomic, copy, readonly) UIImage *policyImage;
/// Short text to place on the "call to action" button\area
@property (nonatomic, copy) NSString* callToAction;

@property (nonatomic, readonly) UIView *mediaView;
@property (nonatomic, readonly) CGFloat videoAspectRatio;
@property (nonatomic, readonly) BOOL isVideo;
@property (nonatomic, assign) BOOL videoMuted; //NO by default.
///  Parallel bigging mediation token
@property (nonatomic, readonly, copy) NSString* bidToken;

/*!
 * @brief Registers view for automatic impression and click tracking
 * @discussion Call this method to provide a view for automatic impression and click tracking.
 * @param view View for automatic impression and click tracking
 */
- (void)registerViewForImpressionAndClick:(UIView *)view;

/*!
 * @brief Registers view for automatic impression tracking and array of views for automatic click tracking
 * @discussion Call this method to provide a view for automatic impression tracking and views for automatic click tracking.
 * @param view View for automatic impression tracking
 * @param clickableViews Array of views for automatic click tracking
 */
- (void)registerViewForImpression:(UIView *)view andViewsForClick:(NSArray<UIView *> *)clickableViews;

/*!
 * @brief Unregisters all views previously registered for tracking
 * @discussion Use this method to stop tracking impression and click.
 */
- (void)unregisterViews;

@end
