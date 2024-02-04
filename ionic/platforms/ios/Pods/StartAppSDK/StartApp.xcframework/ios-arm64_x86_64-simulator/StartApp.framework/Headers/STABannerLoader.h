//
//  STAInlineView.h
//  StartAppAdSDK
//
//  Created by StartApp on 01/25/22.
//  Copyright (c) 2022 StartApp. All rights reserved.
//  SDK version 4.10.1

#import <Foundation/Foundation.h>
#import "STABannerSize.h"
#import "STAAdPreferences.h"
#import "STABannerView.h"
#import "STAInlineView.h"

#ifndef STABannerLoader_h
#define STABannerLoader_h

@class STABannerViewCreator;

typedef void (^STABannerRequestCompletion)(STABannerViewCreator * _Nullable, NSError * _Nullable);

/*!
 @class
 @brief This class can be used to load banner ad without creating STABannerView and rendering ad in it.
 @discussion Commonly used for mediation adapters.
 */
@interface STABannerLoader : NSObject
- (nonnull instancetype)init NS_UNAVAILABLE;

/*!
 * @brief Creates banner loader for specified banner size with ad preferences
 * @discussion Call this method to create banner loader for specified banner size with custom ad preferences.
 * @param size Banner size
 * @param adPreferences Custom ad preferences
 * @return STABannerLoader instance
 */
- (nonnull instancetype)initWithSize:(STABannerSize)size
                        adPreferences:(nullable STAAdPreferences *)adPreferences;

/*!
 * @brief Loads banner ad
 * @discussion Call this method to load banner ad.
 * @param completion Block that will be called once banner ad is loaded or failed to load
 */
- (void)loadAdWithCompletion:(nonnull STABannerRequestCompletion)completion;

/// Banner size
@property (nonatomic, assign) STABannerSize bannerSize;
/// Custom ad preferences
@property (nonatomic, nullable, strong) STAAdPreferences *adPreferences;

@end

/*!
 @class
 @brief This class is used to create STABannerView after STABannerLoader finishes loading.
 @discussion Object of this class is sent as parameter in completion block of STABannerLoader's loadAdWithCompletion: method. Don't create it by yourself.
 */
@interface STABannerViewCreator : NSObject
- (nonnull instancetype)init NS_UNAVAILABLE;

/*!
 * @brief Creates STABannerView.
 * @discussion Call this method to create STABannerView in STABannerLoader's completion block.
 * @param bannerDelegate Delegate object that will receive banner view callbacks
 * @param supportAutolayout Flag that affects returned banner will support autolayout or not
 * @return STABannerView if supportAutolayout is NO. STAInlineView if supportAutolayout is YES
 */
- (nonnull STABannerViewBase *)createBannerViewForDelegate:(nullable id<STABannerDelegateProtocol>)bannerDelegate supportAutolayout:(BOOL)supportAutolayout;

@end
#endif /* STABannerLoader_h */
