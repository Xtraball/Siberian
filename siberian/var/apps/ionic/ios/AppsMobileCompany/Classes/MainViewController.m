/*
 Licensed to the Apache Software Foundation (ASF) under one
 or more contributor license agreements.  See the NOTICE file
 distributed with this work for additional information
 regarding copyright ownership.  The ASF licenses this file
 to you under the Apache License, Version 2.0 (the
 "License"); you may not use this file except in compliance
 with the License.  You may obtain a copy of the License at

 http://www.apache.org/licenses/LICENSE-2.0

 Unless required by applicable law or agreed to in writing,
 software distributed under the License is distributed on an
 "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY
 KIND, either express or implied.  See the License for the
 specific language governing permissions and limitations
 under the License.
 */

//
//  MainViewController.h
//  AppsMobileCompany
//
//  Created by ___FULLUSERNAME___ on ___DATE___.
//  Copyright ___ORGANIZATIONNAME___ ___YEAR___. All rights reserved.
//

#import "MainViewController.h"
#import "Constants.h"

@implementation MainViewController

- (id)initWithNibName:(NSString*)nibNameOrNil bundle:(NSBundle*)nibBundleOrNil
{
    self = [super initWithNibName:nibNameOrNil bundle:nibBundleOrNil];
    if (self) {
        // Uncomment to override the CDVCommandDelegateImpl used
        // _commandDelegate = [[MainCommandDelegate alloc] initWithViewController:self];
        // Uncomment to override the CDVCommandQueue used
        // _commandQueue = [[MainCommandQueue alloc] initWithViewController:self];
    }
    return self;
}

- (id)init
{
    self = [super init];
    if (self) {
        // Uncomment to override the CDVCommandDelegateImpl used
        // _commandDelegate = [[MainCommandDelegate alloc] initWithViewController:self];
        // Uncomment to override the CDVCommandQueue used
        // _commandQueue = [[MainCommandQueue alloc] initWithViewController:self];
    }

    return self;
}

- (void)didReceiveMemoryWarning
{
    // Releases the view if it doesn't have a superview.
    [super didReceiveMemoryWarning];

    // Release any cached data, images, etc that aren't in use.
}

#pragma mark View lifecycle

- (void)viewWillAppear:(BOOL)animated
{
    // View defaults to full size.  If you want to customize the view's size, or its subviews (e.g. webView),
    // you can do so here.

    if (isPreview) {
        [self.navigationController setNavigationBarHidden:YES];

        NSNotificationCenter *notifyCenter = [NSNotificationCenter defaultCenter];
        [notifyCenter addObserverForName:@"CDVwebViewDidStartLoad"
            object:nil
            queue:nil
            usingBlock:^(NSNotification *notification){
                // Explore notification
                NSLog(@"Notification found with:"
                    "\r\n     name:     %@"
                    "\r\n     object:   %@"
                    "\r\n     userInfo: %@",
                    [notification name],
                    [notification object],
                    [notification userInfo]
                );

                UIWebView *theWebView = [notification object];

                if (isPreview && ![appDomain isEqualToString:@""] && ![appKey isEqualToString:@""]) {
                    NSLog(@"appDomain: %@", appDomain);
                    NSLog(@"appKey: %@", appKey);

                    NSString *jsSetIdentifier = [[NSString alloc] initWithFormat:@"setTimeout(function () { IS_PREVIEW = true; DOMAIN = '%@'; APP_KEY = '%@'; BASE_PATH = '/' + APP_KEY; }, 1);", appDomain, appKey];

                    [theWebView stringByEvaluatingJavaScriptFromString:jsSetIdentifier];

                    appDomain = @"";
                    appKey = @"";
                }
            }];
    } else {
        NSNotificationCenter *notifyCenter = [NSNotificationCenter defaultCenter];
        [notifyCenter addObserverForName:@"CDVwebViewDidStartLoad"
            object:nil
            queue:nil
            usingBlock:^(NSNotification *notification) {
                UIWebView *theWebView = [notification object];

                NSString *jsSetIdentifier = [[NSString alloc] initWithFormat:@"IS_PREVIEW = false;"];
                [theWebView stringByEvaluatingJavaScriptFromString:jsSetIdentifier];
            }];
    }


    [super viewWillAppear:animated];
}

- (void)viewDidLoad
{
    [super viewDidLoad];

    if (isPreview) {
        //[[webViewInfo layer] setCornerRadius:5.00f];
        //[[webViewInfo layer] setBackgroundColor:[getLightWhiteColor() CGColor]];

        //webViewInfo.textColor = getWhiteColor();
        //webViewInfo.text = [[NSString alloc] initWithFormat:@"%@", NSLocalizedString(@"Tap twice to go back to apps list.", nil)];
        //[webViewInfo sizeToFit];
        //webViewInfo.frame = CGRectMake(webViewInfo.frame.origin.x,
        //                               webViewInfo.frame.origin.y,
        //                               webViewInfo.frame.size.width + 10,
        //                               webViewInfo.frame.size.height + 5);

        //UITapGestureRecognizer *tap = [[UITapGestureRecognizer alloc] initWithTarget:self action:@selector(tapAction:)];
        //tap.numberOfTapsRequired = 2;
        //tap.delegate = self;

        //[self.webView addGestureRecognizer:tap];

        //[self performSelector:@selector(hideWebViewInfo) withObject:nil afterDelay:5.0];
    }
}

- (void)viewDidUnload
{
    [super viewDidUnload];
    // Release any retained subviews of the main view.
    // e.g. self.myOutlet = nil;
    // Turn off remote control event delivery AppsMobileCompany
    //[[UIApplication sharedApplication] endReceivingRemoteControlEvents];
}


- (BOOL)gestureRecognizer:(UIGestureRecognizer *)gestureRecognizer shouldRecognizeSimultaneouslyWithGestureRecognizer:(UIGestureRecognizer *)otherGestureRecognizer {
    return YES;
}

- (void)tapAction:(id)ignored {
    [self closeApplication];
}

- (void)hideWebViewInfo {
    //[UIView animateWithDuration:0.3 animations:^{
    //    webViewInfo.alpha = 0;
    //} completion: ^(BOOL finished) {
    //    webViewInfo.hidden = YES;
    //}];
}

- (void)closeApplication {
//    [self dismissViewControllerAnimated:YES completion:nil];
    [self.navigationController popViewControllerAnimated:YES];
}

// CB-12098
#if __IPHONE_OS_VERSION_MAX_ALLOWED < 90000  
- (NSUInteger)supportedInterfaceOrientations
#else  
- (UIInterfaceOrientationMask)supportedInterfaceOrientations
#endif
{
    return [super supportedInterfaceOrientations];
}

- (BOOL)shouldAutorotateToInterfaceOrientation:(UIInterfaceOrientation)interfaceOrientation
{
    return [super shouldAutorotateToInterfaceOrientation:interfaceOrientation];
}

- (BOOL)shouldAutorotate 
{
    return [super shouldAutorotate];
}

@end

@implementation MainCommandDelegate

/* To override the methods, uncomment the line in the init function(s)
   in MainViewController.m
 */

#pragma mark CDVCommandDelegate implementation

- (id)getCommandInstance:(NSString*)className
{
    return [super getCommandInstance:className];
}

- (NSString*)pathForResource:(NSString*)resourcepath
{
    return [super pathForResource:resourcepath];
}

@end

@implementation MainCommandQueue

/* To override, uncomment the line in the init function(s)
   in MainViewController.m
 */
- (BOOL)execute:(CDVInvokedUrlCommand*)command
{
    return [super execute:command];
}

@end
