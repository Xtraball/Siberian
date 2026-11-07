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

    NSString *tmpModulePath = [NSTemporaryDirectory() stringByAppendingPathComponent:@"module.js"];

    if ([[NSFileManager defaultManager] fileExistsAtPath:tmpModulePath])
    {
        NSLog(@"[WebView] Removing webView module.js temp file!");
        [[NSFileManager defaultManager] removeItemAtPath:tmpModulePath error:NULL];
    } else {
        NSLog(@"[WebView] Nothing to do!");
    }

    // UIScene life cycle: the window is created by the SceneDelegate when the scene connects, so the window set up of
    // [super application:didFinishLaunchingWithOptions:] (CDVAppDelegate, which uses UIScreen and a bare UIWindow) is
    // deliberately not called.
    self.viewController = [[MainViewController alloc] init];
    return YES;
}

@end

@implementation SceneDelegate

- (void)scene:(UIScene*)scene willConnectToSession:(UISceneSession*)session options:(UISceneConnectionOptions*)connectionOptions
{
    if (![scene isKindOfClass:[UIWindowScene class]]) {
        return;
    }

    AppDelegate* appDelegate = (AppDelegate*)[[UIApplication sharedApplication] delegate];
    if (appDelegate.viewController == nil) {
        appDelegate.viewController = [[MainViewController alloc] init];
    }

    self.window = [[UIWindow alloc] initWithWindowScene:(UIWindowScene*)scene];
    self.window.autoresizesSubviews = YES;
    self.window.rootViewController = appDelegate.viewController;
    // Plugins and the web view delegate read the window through the application delegate.
    appDelegate.window = self.window;
    [self.window makeKeyAndVisible];

    // A URL that launched the app is delivered here, not in application:openURL:options:. Forwarded once the view
    // controller is on screen, so the HandleOpenUrl plugin is loaded and keeps it until the page has loaded.
    [self forwardURLContexts:connectionOptions.URLContexts];
}

- (void)scene:(UIScene*)scene openURLContexts:(NSSet<UIOpenURLContext*>*)URLContexts
{
    [self forwardURLContexts:URLContexts];
}

/** Hands opened URLs to CDVAppDelegate, which posts the usual Cordova notifications to the plugins. */
- (void)forwardURLContexts:(NSSet<UIOpenURLContext*>*)URLContexts
{
    AppDelegate* appDelegate = (AppDelegate*)[[UIApplication sharedApplication] delegate];

    for (UIOpenURLContext* context in URLContexts) {
        NSMutableDictionary* options = [[NSMutableDictionary alloc] init];
        if (context.options.sourceApplication) {
            options[UIApplicationOpenURLOptionsSourceApplicationKey] = context.options.sourceApplication;
        }
        if (context.options.annotation) {
            options[UIApplicationOpenURLOptionsAnnotationKey] = context.options.annotation;
        }
        [appDelegate application:[UIApplication sharedApplication] openURL:context.URL options:options];
    }
}

@end
