//
//  STABannerView.h
//  StartAppAdSDK
//
//  Created by StartApp on 11/13/13.
//  Copyright (c) 2013 StartApp. All rights reserved.
//  SDK version 4.10.1


#import "STABannerViewBase.h"

NS_ASSUME_NONNULL_BEGIN

/*!
 @class
 @brief This class represents Banners with size and position determined by corresponding parameters
 
 @discussion    This class was designed to serve banners with typical sizes and origins, determined solely by its corresponding parameters. Banners of this class are not designed to be used with autolayout. You should use STAInlineView if you need banners layed-out by autolayout engine.
 */
@interface STABannerView : STABannerViewBase

/*!
 * @brief Creates banner view for specified size at auto origin, with ad preferences and delegate
 * @discussion Call this method to create banner view for specified size at auto origin with custom ad preferences. Pass delegate to be notified about banner view events.
 * @param size Banner size
 * @param autoOrigin Auto origin
 * @param adPreferences Custom ad preferences
 * @param bannerDelegate Delegate object that will receive banner view callbacks
 * @return STABannerView instance
 */
- (nullable id)initWithSize:(STABannerSize)size autoOrigin:(STAAdOrigin)autoOrigin adPreferences:(nullable STAAdPreferences *)adPreferences withDelegate:(nullable id<STABannerDelegateProtocol>)bannerDelegate;

/*!
 * @brief Creates banner view for specified size at auto origin, with delegate and ad tag
 * @discussion Call this method to create banner view for specified size at auto origin. Pass delegate to be notified about banner view events. Provide ad tag that will be sent within impression.
 * @param size Banner size
 * @param autoOrigin Auto origin
 * @param bannerDelegate Delegate object that will receive banner view callbacks
 * @param adTag A string tag sent within impression
 * @return STABannerView instance
 */
- (nullable id)initWithSize:(STABannerSize)size autoOrigin:(STAAdOrigin)autoOrigin withDelegate:(nullable id<STABannerDelegateProtocol>)bannerDelegate withAdTag:(nullable NSString *)adTag __deprecated_msg("adTag on impression is deprecated. Please provide adTag for ad request in STAAdPreferences object via initWithSize:autoOrigin:adPreferences:withDelegate: method.");

/*!
 * @brief Creates banner view for specified size at auto origin, with ad preferences, delegate and ad tag
 * @discussion Call this method to create banner view for specified size at auto origin with custom ad preferences. Pass delegate to be notified about banner view events. Provide ad tag that will be sent within impression.
 * @param size Banner size
 * @param autoOrigin Auto origin
 * @param adPreferences Custom ad preferences
 * @param bannerDelegate Delegate object that will receive banner view callbacks
 * @param adTag A string tag sent within impression
 * @return STABannerView instance
 */
- (nullable id)initWithSize:(STABannerSize)size autoOrigin:(STAAdOrigin)autoOrigin adPreferences:(nullable STAAdPreferences *)adPreferences withDelegate:(nullable id<STABannerDelegateProtocol>)bannerDelegate withAdTag:(nullable NSString *)adTag __deprecated_msg("adTag on impression is deprecated. Please provide adTag for ad request in STAAdPreferences object via initWithSize:autoOrigin:adPreferences:withDelegate: method.");

/*!
 * @brief Creates banner view for specified size at auto origin and with delegate
 * @discussion Call this method to create banner view for specified size at auto origin. Pass delegate to be notified about banner view events.
 * @param size Banner size
 * @param autoOrigin Auto origin
 * @param bannerDelegate Delegate object that will receive banner view callbacks
 * @return STABannerView instance
 */
- (nullable id)initWithSize:(STABannerSize)size autoOrigin:(STAAdOrigin)autoOrigin withDelegate:(nullable id<STABannerDelegateProtocol>)bannerDelegate;

/*!
 * @brief Changes fixed origin
 * @discussion Call this method to change fixed origin.
 * @param origin New fixed origin
 */
- (void)setOrigin:(CGPoint)origin;

/*!
 * @brief Changes auto origin
 * @discussion Call this method to change auto origin.
 * @param autoOrigin New auto origin
 */
- (void)setSTAAutoOrigin:(STAAdOrigin)autoOrigin;


- (void)addSTABannerToCell:(UITableViewCell *)cell withIndexPath:(NSIndexPath *)indexPath atIntexPathSection:(NSInteger)section repeatEach:(NSInteger)each __deprecated_msg("Will be removed in next SDK version");
- (void)addSTABannerToCell:(UITableViewCell *)cell withIndexPath:(NSIndexPath *)indexPath atIntexPathSection:(NSInteger)section __deprecated_msg("Will be removed in next SDK version");

- (void)addSTABannerToCell:(UITableViewCell *)cell withIndexPath:(NSIndexPath *)indexPath atIntexPathRow:(NSInteger)row repeatEach:(NSInteger)each __deprecated_msg("Will be removed in next SDK version");
- (void)addSTABannerToCell:(UITableViewCell *)cell withIndexPath:(NSIndexPath *)indexPath atIntexPathRow:(NSInteger)row __deprecated_msg("Will be removed in next SDK version");

@end

NS_ASSUME_NONNULL_END
