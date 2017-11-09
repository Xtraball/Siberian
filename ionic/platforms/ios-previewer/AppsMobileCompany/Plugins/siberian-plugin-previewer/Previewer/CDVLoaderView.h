//
//  CDVLoaderView.h
//  Siberian
//
//  Created by The Tiger App Creator Team on 24/02/14.
//
//

#import <UIKit/UIKit.h>
#import <QuartzCore/QuartzCore.h>

@protocol CDVLoaderView
@optional

- (void)cancelLoader;

@end

@interface CDVLoaderView : UIView {
    id <NSObject, CDVLoaderView> delegate;
}

@property (retain) id <NSObject, CDVLoaderView> delegate;

@property(nonatomic, strong) UIActivityIndicatorView *indicator;
@property(nonatomic, strong) UIButton *btnCancel;

- (bool) isVisible;
- (void) show;
- (void) hide;
- (void) addCancelButton;
- (void) replaceIndicator;

- (IBAction)cancel:(id)sender;

@end
