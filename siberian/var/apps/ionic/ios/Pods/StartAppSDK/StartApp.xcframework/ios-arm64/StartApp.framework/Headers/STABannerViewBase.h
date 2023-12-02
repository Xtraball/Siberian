//
//  STABannerViewBase.h
//  StartAppAdSDK
//
//  Created by StartApp on 11/13/13.
//  Copyright (c) 2013 StartApp. All rights reserved.
//  SDK version 4.10.1

#import <UIKit/UIKit.h>
#import "STABannerSize.h"
#import "STAAdPreferences.h"

NS_ASSUME_NONNULL_BEGIN

@class STABannerViewBase;   // Forward decleration

@protocol STABannerDelegateProtocol <NSObject>

@optional
/*!
 * @brief Notifies delegate that banner finished preloaded ad and ready to be shown
 * @discussion This method is called when banner did finish preloading ad sucessfully.
 * @param banner Banner instance that called this method
 */
- (void) bannerAdIsReadyToDisplay:(STABannerViewBase *)banner;

/*!
 * @brief Notifies delegate that banner did appear on screen
 * @discussion This method is called when banner first appears on screen and after any action with banner that forced it to be reloaded(size or ad preferences change) in case if ad was loaded successfully.
 * @param banner Banner instance that called this method
 */
- (void) didDisplayBannerAd:(STABannerViewBase *)banner;

/*!
 * @brief Notifies delegate that banner did send impression
 * @discussion This method is called when banner sends impression. It happens after banner first appears on screen and after every refresh in case if all required conditions were met for impression to be sent.
 * @param banner Banner instance that called this method
 */
- (void) didSendImpressionForBannerAd:(STABannerViewBase *)banner;

/*!
 * @brief Notifies delegate that banner failed to load ad
 * @discussion This method is called if banner failed to load ad. Check error parameter to understand the reason.
 * @param banner Banner instance that called this method
 * @param error Error describing the reason. See STAErrorCode.h for possible error codes and localizedDescription for explanation.
 */
- (void) failedLoadBannerAd:(STABannerViewBase *)banner withError:(NSError *)error;

/*!
 * @brief Notifies delegate that ad was clicked
 * @discussion This method is called when banner is clicked by user.
 * @param banner Banner instance that called this method
 */
- (void) didClickBannerAd:(STABannerViewBase *)banner;

/*!
 * @brief Notifies delegate that in-app AppStore view controller, presented after click on banner, was closed
 * @discussion This method is called when in-app AppStore view controller gets closed by cancelling it or by installing the app.
 * @param banner Banner instance that called this method
 */
- (void) didCloseBannerInAppStore:(STABannerViewBase *)banner;

@end

typedef enum {
    ///Banner auto origin at top of the parent view
	STAAdOrigin_Top = 1,
    
    ///Banner auto origin at bottom of parent view
    STAAdOrigin_Bottom = 2,
} STAAdOrigin;

@interface STABannerViewBase : UIView

/*!
 * @brief Creates banner view for specified size at fixed origin and with delegate
 * @discussion Call this method to create banner view for specified size at fixed origin. Pass delegate to be notified about banner view events.
 * @param size Banner size
 * @param origin Fixed origin
 * @param bannerDelegate Delegate object that will receive banner view callbacks
 * @return STABannerView instance
 */
- (nullable id)initWithSize:(STABannerSize)size origin:(CGPoint)origin withDelegate:(nullable id<STABannerDelegateProtocol>)bannerDelegate;

/*!
 * @brief Creates banner view for specified size at fixed origin, with ad preferences and delegate
 * @discussion Call this method to create banner view for specified size at fixed origin with custom ad preferences. Pass delegate to be notified about banner view events.
 * @param size Banner size
 * @param origin Fixed origin
 * @param adPreferences Custom ad preferences
 * @param bannerDelegate Delegate object that will receive banner view callbacks
 * @return STABannerView instance
 */
- (nullable id)initWithSize:(STABannerSize)size origin:(CGPoint)origin adPreferences:(nullable STAAdPreferences *)adPreferences withDelegate:(nullable id<STABannerDelegateProtocol>)bannerDelegate;

/*!
 * @brief Creates banner view for specified size at fixed origin, with delegate and ad tag
 * @discussion Call this method to create banner view for specified size at fixed origin. Pass delegate to be notified about banner view events. Provide ad tag that will be sent within impression.
 * @param size Banner size
 * @param origin Fixed origin
 * @param bannerDelegate Delegate object that will receive banner view callbacks
 * @param adTag A string tag sent within impression
 * @return STABannerView instance
 */
- (nullable id)initWithSize:(STABannerSize)size origin:(CGPoint)origin withDelegate:(nullable id<STABannerDelegateProtocol>)bannerDelegate withAdTag:(nullable NSString *)adTag __deprecated_msg("adTag on impression is deprecated. Please provide adTag for ad request in STAAdPreferences object via initWithSize:origin:adPreferences:withDelegate: method.");

/*!
 * @brief Creates banner view for specified size at fixed origin with ad preferences, delegate and ad tag
 * @discussion Call this method to create banner view for specified size at fixed origin with ad preferences. Pass delegate to be notified about banner view events. Provide ad tag that will be sent within impression.
 * @param size Banner size
 * @param origin Fixed origin
 * @param adPreferences Custom ad preferences
 * @param bannerDelegate Delegate object that will receive banner view callbacks
 * @param adTag A string tag sent within impression
 * @return STABannerView instance
 */
- (nullable id)initWithSize:(STABannerSize)size origin:(CGPoint)origin adPreferences:(nullable STAAdPreferences *)adPreferences withDelegate:(nullable id<STABannerDelegateProtocol>)bannerDelegate withAdTag:(nullable NSString *)adTag __deprecated_msg("adTag on impression is deprecated. Please provide adTag for ad request in STAAdPreferences object via initWithSize:origin:adPreferences:withDelegate: method.");

/*!
 * @brief Preloads ad
 * @discussion Use this method to preload the ad before adding banner to view hierarchy. Works for fixed size banners. After ad is loaded bannerAdIsReadyToDisplay: delegate method will be called.
 */
- (void)loadAd;

/*!
 * @brief Changes ad tag
 * @discussion Call this method to change ad tag that will be sent within impression.
 * @param adTag A new string tag to be sent within impression
 */
- (void)setSTABannerAdTag:(nullable NSString *)adTag __deprecated_msg("adTag on impression is deprecated. Please provide adTag for ad request in STAAdPreferences object via initWithSize:origin:adPreferences:withDelegate: method.");

/*!
 * @brief Changes banner size
 * @discussion Call this method to change banner size.
 * @param size New banner size
 */
- (void)setSTABannerSize:(STABannerSize)size;

/*!
 * @brief Changes ad preferences
 * @discussion Call this method to change ad preferences.
 * @param adPreferences New ad preferences
 */
- (void)setAdPreferneces:(nullable STAAdPreferences *)adPreferences;

/*!
 * @brief Hides banner
 * @discussion Call this method to hide the banner.
 */
- (void)hideBanner;

/*!
 * @brief Shows banner
 * @discussion Call this method to show the banner.
 */
- (void)showBanner;

/*!
 * @brief Check whether banner is visible
 * @discussion Call this method to check whether banner is visible.
 */
- (BOOL)isVisible;

@property (nonatomic, weak, nullable) IBOutlet id <STABannerDelegateProtocol> delegate;

/// Parallel bidding mediation token
@property (nonatomic, readonly, copy, nullable) NSString* bidToken;

@end

NS_ASSUME_NONNULL_END
