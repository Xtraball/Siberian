//
//  UIViewController+previewInfoViewController.m
//  SiberianCMS
//
//  Created by Adrien Sala on 02/10/2014.
//  Copyright (c) 2014 Adrien Sala. All rights reserved.
//

#import "previewerInfoViewController.h"

@implementation previewerInfoViewController

@synthesize label1, label2;

- (void)viewWillAppear:(BOOL)animated {
    [super viewWillAppear:animated];
    
    self.navigationController.navigationBar.translucent = NO;
    if([self.navigationController.navigationBar respondsToSelector:@selector(barTintColor)]) {
        self.navigationItem.title = NSLocalizedString(@"Info", nil);
    }
    else {
        UILabel *navbarLabel = [[UILabel alloc] initWithFrame:CGRectZero];
        navbarLabel.backgroundColor = [UIColor clearColor];
        navbarLabel.shadowColor = [UIColor clearColor];
        navbarLabel.font = [UIFont boldSystemFontOfSize:17.0f];
        navbarLabel.textAlignment = SBTextAlignmentCenter;
        navbarLabel.textColor = getWhiteColor();
        self.navigationItem.titleView = navbarLabel;
        navbarLabel.text = NSLocalizedString(@"Info", nil);
        [navbarLabel sizeToFit];
        
        UIButton *navbarButton = [UIButton buttonWithType:UIButtonTypeCustom];
        navbarButton.frame = CGRectMake(0, 0, 80, 30);
        navbarButton.backgroundColor = [UIColor clearColor];
        [navbarButton setTitle:NSLocalizedString(@"Back", nil) forState:UIControlStateNormal];
        [navbarButton setTitleColor:getWhiteColor() forState:UIControlStateNormal];
        [navbarButton setImage:[UIImage imageNamed:@"back_arrow_white.png"] forState:UIControlStateNormal];
        [navbarButton setTitleColor:getBlueColor() forState:UIControlStateHighlighted];
        [navbarButton.titleLabel setFont:[UIFont systemFontOfSize:16.0f]];
        [navbarButton setImage:[UIImage imageNamed:@"back_arrow_black.png"] forState:UIControlStateHighlighted];
        [navbarButton addTarget:self action:@selector(back:) forControlEvents:UIControlEventTouchUpInside];
        UIBarButtonItem *item = [[UIBarButtonItem alloc] initWithCustomView:navbarButton];
        self.navigationItem.leftBarButtonItem = item;
    }
    
    [[self view] setBackgroundColor:getWhiteColor()];
    
    label1.textColor = getBlueColor();
    label2.textColor = getBlueColor();
    
}

- (void)viewDidUnload {
    [self setLabel1:nil];
    [self setLabel2:nil];
    [super viewDidUnload];
}

-(IBAction)back:(id)sender {
    [self.navigationController popViewControllerAnimated:YES];
}

- (BOOL)shouldAutorotateToInterfaceOrientation:(UIInterfaceOrientation)interfaceOrientation {
    return (interfaceOrientation == UIInterfaceOrientationPortrait);
}

- (BOOL)shouldAutorotate {
    UIDeviceOrientation deviceOrientation = (UIDeviceOrientation) [[UIDevice currentDevice] orientation];
    return (deviceOrientation == UIDeviceOrientationPortrait);
}

- (NSUInteger)supportedInterfaceOrientations
{
    return UIInterfaceOrientationMaskPortrait;
}

@end
