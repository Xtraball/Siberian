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
//  AppDelegate.m
//  AppsMobileCompany
//
//  Created by ___FULLUSERNAME___ on ___DATE___.
//  Copyright ___ORGANIZATIONNAME___ ___YEAR___. All rights reserved.
//

#import "AppDelegate.h"
#import "MainViewController.h"
#import "Constants.h"
#import <AVFoundation/AVFoundation.h>

@implementation AppDelegate

- (BOOL)application:(UIApplication*)application didFinishLaunchingWithOptions:(NSDictionary*)launchOptions
{
   //if (!MainViewControllerisPreview) {
   //     CGRect screenBounds = [[UIScreen mainScreen] bounds];

   //     #if __has_feature(objc_arc)
   //     self.window = [[UIWindow alloc] initWithFrame:screenBounds];
   //     #else
   //     self.window = [[[UIWindow alloc] initWithFrame:screenBounds] autorelease];
   //     #endif

   //     self.window.autoresizesSubviews = YES;

   //     #if __has_feature(objc_arc)
   //     self.viewController = [[MainViewController alloc] init];
   //     #else
   //     self.viewController = [[[MainViewController alloc] init] autorelease];
   //     #endif

        // Set your app's start page by setting the <content src='foo.html' /> tag in config.xml.
        // If necessary, uncomment the line below to override it.
        // self.viewController.startPage = @"index.html";

        // NOTE: To customize the view's frame size (which defaults to full screen), override
        // [self.viewController viewWillAppear:] in your view controller.

        //self.window.rootViewController = self.viewController;
      //  [self.window makeKeyAndVisible];
    //}

    /** Allow for Audio background playing */
    AVAudioSession *audioSession = [AVAudioSession sharedInstance];
    BOOL ok;
    NSError *setCategoryError = nil;
    ok = [audioSession
        setCategory:AVAudioSessionCategoryPlayback
        error:&setCategoryError];

    NSArray* tmpDirectory = [[NSFileManager defaultManager] contentsOfDirectoryAtPath:NSTemporaryDirectory() error:NULL];
    for (NSString *file in tmpDirectory) {
        [[NSFileManager defaultManager] removeItemAtPath:[NSString stringWithFormat:@"%@%@", NSTemporaryDirectory(), file] error:NULL];
    }

    self.viewController = [[MainViewController alloc] init];
    return [super application:application didFinishLaunchingWithOptions:launchOptions];
}

@end
