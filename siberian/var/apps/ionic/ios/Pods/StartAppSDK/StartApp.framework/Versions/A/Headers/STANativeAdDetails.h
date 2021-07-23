//
//  STANativeAdDetails.h
//  StartApp
//
//  Created by StartApp on 9/15/14.
//  Copyright (c) 2014 StartApp. All rights reserved.
//  SDK version 4.6.6

#import <Foundation/Foundation.h>
#import <UIKit/UIKit.h>

@interface STANativeAdDetails : NSObject

@property (nonatomic, copy) NSString *title;
@property (nonatomic, copy) NSString *description;
@property (nonatomic, copy) NSNumber *rating;
@property (nonatomic, copy) NSString *imageUrl;
@property (nonatomic, copy) NSString *secondaryImageUrl;
@property (nonatomic, copy) UIImage *imageBitmap;
@property (nonatomic, copy) UIImage *secondaryImageBitmap;
@property (nonatomic, copy) NSString *category;
@property (nonatomic, copy) NSString *adId;
@property (nonatomic, copy) NSString *clickToInstall;
@property (nonatomic, copy) NSString *eulaUrl;
@property (nonatomic, copy) NSString *policyImageUrl;
@property (nonatomic, copy) NSString *policyImagePath DEPRECATED_MSG_ATTRIBUTE("Use policyImage instead");
@property (nonatomic, copy, readonly) UIImage *policyImage;
@property (nonatomic, copy) NSString* callToAction;

@property (nonatomic, readonly) UIView *mediaView;
@property (nonatomic, readonly) CGFloat videoAspectRatio;
@property (nonatomic, readonly) BOOL isVideo;
@property (nonatomic, assign) BOOL videoMuted; //NO by default.

- (void)registerViewForImpressionAndClick:(UIView *)view;
- (void)registerViewForImpression:(UIView *)view andViewsForClick:(NSArray<UIView *> *)clickableViews;

- (void)unregisterViews;

@end
