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
    /** Allow for Audio background playing */
    AVAudioSession *audioSession = [AVAudioSession sharedInstance];
    BOOL ok;
    NSError *setCategoryError = nil;
    ok = [audioSession
        setCategory:AVAudioSessionCategoryPlayback
        error:&setCategoryError];

    // Remove file /app/documents/module.js
    NSString *tmpModuleFile = @"module.js";
    NSString *appBundleID = [[NSBundle mainBundle] bundleIdentifier];
    NSArray *pathArray = NSSearchPathForDirectoriesInDomains(NSApplicationSupportDirectory, NSUserDomainMask, YES);
    NSString *documentsDirectory = [pathArray objectAtIndex:0];
    NSString *tmpModulePath = [[documentsDirectory stringByAppendingPathComponent:appBundleID] stringByAppendingPathComponent:tmpModuleFile];

    NSLog(@"App module path: %@", tmpModulePath);

    if ([[NSFileManager defaultManager] fileExistsAtPath:tmpModulePath])
    {
        NSLog(@"App module delete");
        [[NSFileManager defaultManager] removeItemAtPath:tmpModulePath error:NULL];
    } else {
        NSLog(@"App module pas delete");
    }

    self.viewController = [[MainViewController alloc] init];
    return [super application:application didFinishLaunchingWithOptions:launchOptions];
}

@end
