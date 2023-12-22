//
//  StartAppAd.h
//  StartAppAdSDK
//
//  Copyright (c) 2013 StartApp. All rights reserved.
//  SDK version 4.10.1

#import <UIKit/UIKit.h>
#import "STAAbstractAd.h"
#import "STAAdPreferences.h"

@interface STAStartAppAd : STAAbstractAd

/// Using this property you can check whether ad supports rotation
@property (nonatomic, readonly) BOOL STAShouldAutoRotate;

/// Parallel bidding mediation token
@property (nonatomic, readonly, copy) NSString* bidToken;

- (instancetype)init;

/*!
 * @brief Loads rewarded video ad with delegate
 * @discussion Call this method to load rewarded video ad. Pass delegate to be notified when ad is loaded or any other events.
 * @param delegate Delegate object for ad events callbacks
 */
- (void)loadRewardedVideoAdWithDelegate:(id<STADelegateProtocol>)delegate;

/*!
 * @brief Loads rewarded video ad with delegate and specific ad preferences
 * @discussion Call this method to load rewarded video ad with specific ad preferences. Pass delegate to be notified when ad is loaded or any other events.
 * @param delegate Delegate object for ad events callbacks
 * @param adPrefs Custom ad preferences
 */
- (void)loadRewardedVideoAdWithDelegate:(id<STADelegateProtocol>)delegate withAdPreferences:(STAAdPreferences *)adPrefs;

/*!
 * @brief Loads video ad
 * @discussion Call this method to load video ad.
 */
- (void)loadVideoAd;

/*!
 * @brief Loads video ad with specific ad preferences
 * @discussion Call this method to load video ad with specific ad preferences.
 * @param adPrefs Custom ad preferences
 */
- (void)loadVideoAdWithAdPreferences:(STAAdPreferences *)adPrefs;

/*!
 * @brief Loads video ad with delegate
 * @discussion Call this method to load video ad. Pass delegate to be notified when ad is loaded or any other events.
 * @param delegate Delegate object for ad events callbacks
 */
- (void)loadVideoAdWithDelegate:(id<STADelegateProtocol>)delegate;

/*!
 * @brief Loads video ad with delegate and specific ad preferences
 * @discussion Call this method to load video ad with specific ad preferences. Pass delegate to be notified when ad is loaded or any other events.
 * @param delegate Delegate object for ad events callbacks
 * @param adPrefs Custom ad preferences
 */
- (void)loadVideoAdWithDelegate:(id<STADelegateProtocol>)delegate withAdPreferences:(STAAdPreferences *)adPrefs;

/*!
 * @brief Loads interstitial ad
 * @discussion Call this method to load interstitial ad.
 */
- (void)loadAd;

/*!
 * @brief Loads interstitial ad with specific ad preferences
 * @discussion Call this method to load interstitial ad with specific ad preferences.
 * @param adPrefs Custom ad preferences
 */
- (void)loadAdWithAdPreferences:(STAAdPreferences *)adPrefs;

/*!
 * @brief Loads interstitial ad with delegate
 * @discussion Call this method to load interstitial ad with delegate.
 * @param delegate Delegate object for ad events callbacks
 */
- (void)loadAdWithDelegate:(id<STADelegateProtocol>)delegate;

/*!
 * @brief Loads interstitial ad with delegate and specific ad preferences
 * @discussion Call this method to load interstitial ad with specific ad preferences. Pass delegate to be notified when ad is loaded or any other events.
 * @param delegate Delegate object for ad events callbacks
 * @param adPrefs Custom ad preferences
 */
- (void)loadAdWithDelegate:(id<STADelegateProtocol>)delegate withAdPreferences:(STAAdPreferences *)adPrefs;

/*!
 * @brief Shows loaded ad
 * @discussion Call this method to show loaded ad.
 */
- (void)showAd;

/*!
 * @brief Shows interstitial ad with tag
 * @discussion Call this method to show loaded ad using provided tag.
 * @param adTag A string tag sent within impression
 */
- (void)showAdWithAdTag:(NSString *)adTag __deprecated_msg("adTag on impression is deprecated. Please provide adTag for ad request in STAAdPreferences object via loadVideoAdWithDelegate:withAdPreferences: method.");

/*!
 * @brief Force closes ad
 * @discussion Call this method to force close ad.
 */
- (void)closeAd;

@end


