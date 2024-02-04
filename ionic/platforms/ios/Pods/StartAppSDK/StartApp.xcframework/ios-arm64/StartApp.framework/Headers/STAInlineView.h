//
//  STAInlineView.h
//  StartAppAdSDK
//
//  Created by StartApp on 01/25/22.
//  Copyright (c) 2022 StartApp. All rights reserved.
//  SDK version 4.10.1

#import "STABannerViewBase.h"

NS_ASSUME_NONNULL_BEGIN

/*!
 @class
 @brief This class represents Banners which support autolayout.
 
 @discussion    This class was designed to support autolayout engine. Provided size affects its intrinsic contentSize. Origin will be determined by constriants.
 */
@interface STAInlineView : STABannerViewBase

@end

NS_ASSUME_NONNULL_END
