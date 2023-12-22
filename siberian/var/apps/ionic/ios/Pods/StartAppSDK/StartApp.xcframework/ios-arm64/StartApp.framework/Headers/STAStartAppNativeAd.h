//
//  STAStartAppNativeAd.h
//  NativeAd
//
//  Created by StartApp on 9/17/14.
//  Copyright (c) 2014 StartApp. All rights reserved.
//  SDK version 4.10.1


#import <UIKit/UIKit.h>
#import <Foundation/Foundation.h>
#import "STAAbstractAd.h"
#import "STAAdPreferences.h"

typedef NS_ENUM(NSInteger, STANativeAdBitmapSize) {
    SIZE_72X72      = 0,
    SIZE_100X100    = 1,
    /// Default size
    SIZE_150X150    = 2,
    SIZE_340X340    = 3,
    /// Not supported by secondaryImageSize, default will be used instead
    SIZE_1200X628   = 4,
};

typedef NS_ENUM(NSUInteger, STANativeAdVideoMode) {
    STANativeAdVideoExcluded    = 0,
    STANativeAdVideoIncluded    = 1,
    STANativeAdVideoOnly        = 2
};

@interface STANativeAdPreferences : STAAdPreferences

/// Primary image size
@property (nonatomic, assign) STANativeAdBitmapSize primaryImageSize;
/// Secondary image size
@property (nonatomic, assign) STANativeAdBitmapSize secondaryImageSize;
/// Desired amount of native ad details in adsDetails after ad is loaded. Actual amount may be less. Default is 1
@property (nonatomic, assign) NSInteger adsNumber;
/// Flag indicating that primary and secondary images will be loaded during ad loading. Default is YES
@property (nonatomic, assign) BOOL autoBitmapDownload;

@property (nonatomic, assign) STANativeAdVideoMode videoMode;
@property (nonatomic, assign) BOOL contentAd;
@end


@interface STAStartAppNativeAd : STAAbstractAd

/// Native ad preferences
@property (nonatomic, strong) STANativeAdPreferences *preferences;
/// Flag indicating that ad finished loading
@property (nonatomic, readonly) BOOL adIsLoaded;
/// Array of loaded STANativeAdDetails for requested parameters
@property (nonatomic, readonly) NSMutableArray<STANativeAdDetails *> *adsDetails;

/*!
 * @brief Loads native ad details with default parameters
 * @discussion Call this method to load native ad details with default parameters.
 */
- (void)loadAd;

/*!
 * @brief Loads native ad details with delegate
 * @discussion Call this method to load native ad details. Pass delegate to be notified when ad is loaded or any other events.
 * @param delegate Delegate object for ad events callbacks
 */
- (void)loadAdWithDelegate:(id<STADelegateProtocol>)delegate;

/*!
 * @brief Loads native ad details with specific native ad preferences
 * @discussion Call this method to load native ad details with specific ad preferences.
 * @param nativeAdPrefs Specific native ad preferences
 */
- (void)loadAdWithNativeAdPreferences:(STANativeAdPreferences *)nativeAdPrefs;

/*!
 * @brief Loads native ad details with specific native ad preferences and delegate
 * @discussion Call this method to load native ad details with specific ad preferences. Pass delegate to be notified when ad is loaded or any other events.
 * @param delegate Delegate object for ad events callbacks
 * @param nativeAdPrefs Specific native ad preferences
 */
- (void)loadAdWithDelegate:(id<STADelegateProtocol>)delegate withNativeAdPreferences:(STANativeAdPreferences *)nativeAdPrefs;

/*!
 * @brief Sets native ad tag
 * @discussion Call this method to provide tag that will be sent within impression.
 * @param adTag A string tag to be sent within impression
 */
- (void)setAdTag:(NSString *)adTag __deprecated_msg("adTag on impression is deprecated. Please provide adTag for ad request in STANativeAdPreferences object via loadAdWithNativeAdPreferences: or loadAdWithDelegate:withNativeAdPreferences: method.");

@end
