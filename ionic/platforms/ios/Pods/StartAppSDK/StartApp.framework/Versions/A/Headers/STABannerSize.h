//
//  StartAppBannerSize.h
//  StartAppAdSDK
//
//  Created by StartApp on 11/25/13.
//  Copyright (c) 2013 StartApp. All rights reserved.!
//  SDK version 4.5.0

#import <UIKit/UIKit.h>


typedef struct STABannerSizeEnum {
    CGSize size;
    BOOL isAuto;
} STABannerSize;

#pragma mark Standard Sizes

// iPhone and iPod Touch in portrait mode = 320x50.
extern STABannerSize  const STA_PortraitAdSize_320x50;

// iPhone and iPod Touch in landscape mode = 480x50.
extern STABannerSize const STA_LandscapeAdSize_480x50;

// iPhone and iPod Touch in landscape mode = 568x50.
extern STABannerSize const STA_LandscapeAdSize_568x50;

// iPad in portrait mode = 768x90.
extern STABannerSize const STA_PortraitAdSize_768x90;

// iPad in landscape mode = 1024x90.
extern STABannerSize const STA_LandscapeAdSize_1024x90;

// MRec = 300x250.
extern STABannerSize const STA_MRecAdSize_300x250;

extern STABannerSize const STA_CoverAdSize;

extern STABannerSize const STA_AutoAdSize;








