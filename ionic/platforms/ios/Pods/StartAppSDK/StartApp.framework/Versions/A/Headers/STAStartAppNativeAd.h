//
//  STAStartAppNativeAd.h
//  NativeAd
//
//  Created by StartApp on 9/17/14.
//  Copyright (c) 2014 StartApp. All rights reserved.
//  SDK version 4.5.0


#import <UIKit/UIKit.h>
#import <Foundation/Foundation.h>
#import "STAAbstractAd.h"
#import "STAAdPreferences.h"

typedef NS_ENUM(NSInteger, STANativeAdBitmapSize) {
    SIZE_72X72      = 0,
    SIZE_100X100    = 1,
    SIZE_150X150    = 2, //Default
    SIZE_340X340    = 3,
    SIZE_1200X628   = 4, //Not supported by secondaryImageSize, default will be used instead
    SIZE_320X480    = 5,
    SIZE_480X320    = 6
};

typedef NS_ENUM(NSUInteger, STANativeAdVideoMode) {
    STANativeAdVideoExcluded    = 0, //Default
    STANativeAdVideoIncluded    = 1,
    STANativeAdVideoOnly        = 2
};

@interface STANativeAdPreferences : STAAdPreferences

@property (nonatomic, assign) STANativeAdBitmapSize primaryImageSize;
@property (nonatomic, assign) STANativeAdBitmapSize secondaryImageSize;
@property (nonatomic, assign) STANativeAdVideoMode videoMode;
@property (nonatomic, assign) NSInteger adsNumber;      //1 by default
@property (nonatomic, assign) BOOL contentAd;           //NO by default
@property (nonatomic, assign) BOOL autoBitmapDownload;  //YES by default. Always YES if videoMode is STANativeAdVideoIncluded or STANativeAdVideoOnly.

@end


@interface STAStartAppNativeAd : STAAbstractAd

@property (nonatomic, strong) STANativeAdPreferences *preferences;
@property (nonatomic, readonly) BOOL adIsLoaded;
@property (nonatomic, readonly) NSMutableArray *adsDetails;

- (void)loadAd;
- (void)loadAdWithDelegate:(id<STADelegateProtocol>)delegate;
- (void)loadAdWithNativeAdPreferences:(STANativeAdPreferences *)nativeAdPrefs;
- (void)loadAdWithDelegate:(id<STADelegateProtocol>)delegate withNativeAdPreferences:(STANativeAdPreferences *)nativeAdPrefs;

- (void)setAdTag:(NSString *)adTag;

@end
