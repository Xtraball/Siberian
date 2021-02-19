//
//  StartAppBannerView.h
//  StartAppAdSDK
//
//  Created by StartApp on 11/13/13.
//  Copyright (c) 2013 StartApp. All rights reserved.
//  SDK version 4.5.0

#import <UIKit/UIKit.h>
#import "STABannerSize.h"
#import "STAAdPreferences.h"

@class STABannerView;   // Forward decleration

@protocol STABannerDelegateProtocol <NSObject>

@optional
- (void) bannerAdIsReadyToDisplay:(STABannerView *)banner;
- (void) didDisplayBannerAd:(STABannerView *)banner;
- (void) failedLoadBannerAd:(STABannerView *)banner withError:(NSError *)error;
- (void) didClickBannerAd:(STABannerView *)banner;
- (void) didCloseBannerInAppStore:(STABannerView *)banner;

@end

typedef enum {
	STAAdOrigin_Top = 1,
    STAAdOrigin_Bottom = 2,
} STAAdOrigin;

@interface STABannerView : UIView

- (id)initWithSize:(STABannerSize)size origin:(CGPoint)origin withDelegate:(id<STABannerDelegateProtocol>)bannerDelegate;
- (id)initWithSize:(STABannerSize)size origin:(CGPoint)origin adPreferences:(STAAdPreferences *)adPreferences withDelegate:(id<STABannerDelegateProtocol>)bannerDelegate;
- (id)initWithSize:(STABannerSize)size autoOrigin:(STAAdOrigin)origin withDelegate:(id<STABannerDelegateProtocol>)bannerDelegate;
- (id)initWithSize:(STABannerSize)size autoOrigin:(STAAdOrigin)origin adPreferences:(STAAdPreferences *)adPreferences withDelegate:(id<STABannerDelegateProtocol>)bannerDelegate;

- (id)initWithSize:(STABannerSize)size origin:(CGPoint)origin withDelegate:(id<STABannerDelegateProtocol>)bannerDelegate withAdTag:(NSString *)adTag;
- (id)initWithSize:(STABannerSize)size origin:(CGPoint)origin adPreferences:(STAAdPreferences *)adPreferences withDelegate:(id<STABannerDelegateProtocol>)bannerDelegate withAdTag:(NSString *)adTag;
- (id)initWithSize:(STABannerSize)size autoOrigin:(STAAdOrigin)origin withDelegate:(id<STABannerDelegateProtocol>)bannerDelegate withAdTag:(NSString *)adTag;
- (id)initWithSize:(STABannerSize)size autoOrigin:(STAAdOrigin)origin adPreferences:(STAAdPreferences *)adPreferences withDelegate:(id<STABannerDelegateProtocol>)bannerDelegate withAdTag:(NSString *)adTag;

- (void)loadAd;

- (void)setSTABannerAdTag:(NSString *)adTag;

- (void)setSTABannerSize:(STABannerSize)size;
- (void)setOrigin:(CGPoint)origin;
- (void)setSTAAutoOrigin:(STAAdOrigin)origin;

- (void)setAdPreferneces:(STAAdPreferences *)adPreferences;

- (void)hideBanner;
- (void)showBanner;
- (BOOL)isVisible;

-(void)addSTABannerToCell:(UITableViewCell *)cell withIndexPath:(NSIndexPath *)indexPath atIntexPathSection:(NSInteger)section repeatEach:(NSInteger)each;
-(void)addSTABannerToCell:(UITableViewCell *)cell withIndexPath:(NSIndexPath *)indexPath atIntexPathSection:(NSInteger)section;

-(void)addSTABannerToCell:(UITableViewCell *)cell withIndexPath:(NSIndexPath *)indexPath atIntexPathRow:(NSInteger)row repeatEach:(NSInteger)each;
-(void)addSTABannerToCell:(UITableViewCell *)cell withIndexPath:(NSIndexPath *)indexPath atIntexPathRow:(NSInteger)row;

@end
