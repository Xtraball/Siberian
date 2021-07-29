//
//  FBAdCompanionView.h
//  AdUnitsSample-Focused
//
//  Created by Ulysses Rocha on 01/02/2021.
//

#import <Foundation/Foundation.h>
@class FBDisplayAdController;

@protocol FBAdCompanionViewDelegate;

NS_ASSUME_NONNULL_BEGIN

@interface FBAdCompanionView : UIView

/**
  Do not be used in production applications.
 */
@property (nonatomic, weak, nullable) id<FBAdCompanionViewDelegate> delegate;

@end

/**
  The methods declared by the FBAdCompanionViewDelegate protocol are experimental and should not be used in production
  applications.
 */

@protocol FBAdCompanionViewDelegate <NSObject>

@optional
- (void)companionViewDidLoad:(FBAdCompanionView *)companionView;
- (void)companionViewWillClose:(FBAdCompanionView *)companionView;
@end

NS_ASSUME_NONNULL_END
