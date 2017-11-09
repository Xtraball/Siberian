//
//  CDVLoaderView.m
//  Siberian
//
//  Created by The Tiger App Creator Team on 24/02/14.
//
//

#import "CDVLoaderView.h"

@implementation CDVLoaderView
@synthesize indicator, btnCancel;
@synthesize delegate;

- (id) initWithFrame:(CGRect)frame
{
    self = [super initWithFrame:frame];
    if (self) {
        // Création du loader
        indicator = [[UIActivityIndicatorView alloc] initWithActivityIndicatorStyle:UIActivityIndicatorViewStyleWhite];
        indicator.frame = CGRectMake(self.frame.size.width / 2 - indicator.frame.size.width / 2, self.frame.size.height / 2 - indicator.frame.size.height / 2, indicator.frame.size.width, indicator.frame.size.height);

        [self addSubview:indicator];
        
        self.backgroundColor = [UIColor colorWithRed:0.0f green:0.0f blue:0.0f alpha:0.5f];
        
        self.hidden = YES;
    }
    return self;
}

- (void) replaceIndicator {
    indicator.frame = CGRectMake(self.frame.size.width / 2 - indicator.frame.size.width / 2, self.frame.size.height / 2 - indicator.frame.size.height / 2, indicator.frame.size.width, indicator.frame.size.height);
}

- (void) addCancelButton {
    btnCancel = [UIButton buttonWithType:UIButtonTypeCustom];
    btnCancel.frame = CGRectMake(270, 10, 40, 40);
    [btnCancel setTitleColor:[UIColor whiteColor] forState:UIControlStateNormal];
    [btnCancel setTitle:@"X" forState:UIControlStateNormal];
    [btnCancel addTarget:self action:@selector(cancel:) forControlEvents:UIControlEventTouchUpInside];
    [self addSubview:btnCancel];    
}

- (bool) isVisible {
    return self.hidden == NO;
}

- (void) show{
    
//    CGFloat height = self.superview.frame.size.height;
//    CGFloat width = self.superview.frame.size.width;
//    
//    self.frame = CGRectMake(0, 0, height, width);
//    indicator.frame = CGRectMake(width / 2 - indicator.frame.size.width / 2, height / 2 - indicator.frame.size.height / 2, indicator.frame.size.width, indicator.frame.size.height);
    self.hidden = NO;
    [indicator startAnimating];
}

- (void) hide {
    self.hidden = YES;
    [indicator stopAnimating];
}

- (IBAction) cancel:(id)sender {
    if([delegate respondsToSelector:@selector(cancelLoader)]) {
        [delegate cancelLoader];
    }
}

@end
