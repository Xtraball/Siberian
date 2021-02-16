//
//  StartAppAd.h
//  StartAppAdSDK
//
//  Copyright (c) 2013 StartApp. All rights reserved.
//  SDK version 4.5.0

#import <UIKit/UIKit.h>
#import "STAAbstractAd.h"
#import "STAAdPreferences.h"

@interface STAStartAppAd : STAAbstractAd

@property (nonatomic, assign) BOOL STAShouldAutoRotate;

- (instancetype)init;

- (void)loadRewardedVideoAdWithDelegate:(id<STADelegateProtocol>)delegate;
- (void)loadRewardedVideoAdWithDelegate:(id<STADelegateProtocol>)delegate withAdPreferences:(STAAdPreferences *)adPrefs;

- (void)loadVideoAd;
- (void)loadVideoAdWithAdPreferences:(STAAdPreferences *)adPrefs;
- (void)loadVideoAdWithDelegate:(id<STADelegateProtocol>)delegate;
- (void)loadVideoAdWithDelegate:(id<STADelegateProtocol>)delegate withAdPreferences:(STAAdPreferences *)adPrefs;

- (void)loadAd;
- (void)loadAdWithAdPreferences:(STAAdPreferences *)adPrefs;
- (void)loadAdWithDelegate:(id<STADelegateProtocol>)delegate;
- (void)loadAdWithDelegate:(id<STADelegateProtocol>)delegate withAdPreferences:(STAAdPreferences *)adPrefs;

- (void)showAd;
- (void)showAdWithAdTag:(NSString *)adTag;

- (void)closeAd;

- (BOOL)isReady;

@end


