//
//  STASplashPreferences.h
//  StartApp
//
//  Created by StartApp on 6/25/14.
//  Copyright (c) 2014 StartApp. All rights reserved.
//  SDK version 4.5.0

#import <Foundation/Foundation.h>
#import <UIKit/UIKit.h>

typedef enum {
	STASplashModeUserDefined = 1,
    STASplashModeTemplate = 2
} STASplashMode;

typedef enum {
    STASplashMinTimeShort = 2,
    STASplashMinTimeRegular = 3,
    STASplashMinTimeLong = 5,
} STASplashMinTime;

typedef enum {
    STASplashAdDisplayTimeShort = 5,
    STASplashAdDisplayTimeLong = 10,
    STASplashAdDisplayTimeForEver = 86400
} STASplashAdDisplayTime;

typedef enum {
    STASplashTemplateThemeDeepBlue = 0,
    STASplashTemplateThemeSky,
    STASplashTemplateThemeAshenSky,
    STASplashTemplateThemeBlaze,
    STASplashTemplateThemeGloomy,
    STASplashTemplateThemeOcean
} STASplashTemplateTheme;

typedef enum {
    STASplashLoadingIndicatorTypeIOS = 0,
    STASplashLoadingIndicatorTypeDots
} STASplashLoadingIndicatorType;

@interface STASplashPreferences : NSObject

// Splash Type
@property (nonatomic, assign) STASplashMode splashMode;

// User Defined splash prefreneces
@property (nonatomic, strong) NSString *splashUserDefinedImageName;

// Template splash prefreneces
@property (nonatomic, assign) STASplashTemplateTheme splashTemplateTheme;
@property (nonatomic, strong) NSString *splashTemplateIconImageName;
@property (nonatomic, strong) NSString *splashTemplateAppName;

// Loading Indicator
@property (nonatomic, assign) BOOL isSplashLoadingIndicatorEnabled;
@property (nonatomic, assign) STASplashLoadingIndicatorType splashLoadingIndicatorType;
@property (nonatomic, assign) CGPoint splashLoadingIndicatorCenterPoint;

// Splash Orientation
@property (nonatomic, assign) BOOL isLandscape;

// Other Preferences
@property (nonatomic, assign) STASplashMinTime splashMinTime;
@property (nonatomic, assign) STASplashAdDisplayTime splashAdDisplayTime;

@end
