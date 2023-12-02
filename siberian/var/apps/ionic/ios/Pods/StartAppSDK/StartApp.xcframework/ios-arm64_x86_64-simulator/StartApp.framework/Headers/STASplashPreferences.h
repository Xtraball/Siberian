//
//  STASplashPreferences.h
//  StartApp
//
//  Created by StartApp on 6/25/14.
//  Copyright (c) 2014 StartApp. All rights reserved.
//  SDK version 4.10.1

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

/// Splash mode. Template or user defined.
@property (nonatomic, assign) STASplashMode splashMode;


//User defined splash preferences
/// Fullscreen Image name
@property (nonatomic, strong) NSString *splashUserDefinedImageName;


// Template splash prefreneces
/// Template splash theme
@property (nonatomic, assign) STASplashTemplateTheme splashTemplateTheme;
/// App icon image name
@property (nonatomic, strong) NSString *splashTemplateIconImageName;
/// Application name
@property (nonatomic, strong) NSString *splashTemplateAppName;

// Loading indicator preferences for splash template
/// Flag indicating whether loading indicator is enabled
@property (nonatomic, assign) BOOL isSplashLoadingIndicatorEnabled;
/// Loading indicator type
@property (nonatomic, assign) STASplashLoadingIndicatorType splashLoadingIndicatorType;
/// Loading indicator center point
@property (nonatomic, assign) CGPoint splashLoadingIndicatorCenterPoint;


// Splash Orientation
/// Splash screen orientation
@property (nonatomic, assign) BOOL isLandscape;


// Other Preferences
/// Minimum time for which splash screen will stay on screen before splash ad will appear
@property (nonatomic, assign) STASplashMinTime splashMinTime;
/// Time for which splash ad will stay on screen before closed automatically. Use STASplashAdDisplayTimeForEver to disable automatic ad close
@property (nonatomic, assign) STASplashAdDisplayTime splashAdDisplayTime;

@end
