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
//  AppDelegate.h
//  AppsMobileCompany
//
//  Created by ___FULLUSERNAME___ on ___DATE___.
//  Copyright ___ORGANIZATIONNAME___ ___YEAR___. All rights reserved.
//

#import <Cordova/CDVViewController.h>
#import <Cordova/CDVAppDelegate.h>

@interface AppDelegate : CDVAppDelegate {}

@end

/**
 * UIScene life cycle (mandatory from iOS 27: an app built with the iOS 27 SDK that does not adopt it fails to launch).
 * The window is created here, once the scene connects, and handed to the AppDelegate so that everything reading
 * [[UIApplication sharedApplication] delegate].window keeps working. Declared in this file so the Xcode project does
 * not need a new source file. Named by UISceneDelegateClassName in AppsMobileCompany-Info.plist.
 */
@interface SceneDelegate : UIResponder <UIWindowSceneDelegate>

@property (nonatomic, strong) UIWindow* window;

@end
