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

#import <UIKit/UIKit.h>
#import <Cordova/CDVPlugin.h>
#import <Cordova/CDVViewController.h>

@class WebViewController;

@protocol WebViewDelegate
- (void)webViewFinished;
@end


@interface WebViewPlugin : CDVPlugin<WebViewDelegate>
{
  @private NSString* webViewFinishedCallBack;
  @private NSString* debugCallback;
  @private NSString* resumeCallback;
  @private NSString* pauseCallback;
  @private NSString* urlCallback;
}
@property (nonatomic, retain) WebViewController* webViewController;

- (void)subscribeCallback:(CDVInvokedUrlCommand*)command;
- (void)subscribeDebugCallback:(CDVInvokedUrlCommand*)command;
- (void)subscribeResumeCallback:(CDVInvokedUrlCommand*)command;
- (void)subscribePauseCallback:(CDVInvokedUrlCommand*)command;
- (void)subscribeUrlCallback:(CDVInvokedUrlCommand*)command;
- (void)loadApp:(CDVInvokedUrlCommand*)command;
- (void)show:(CDVInvokedUrlCommand*)command;
- (void)load:(CDVInvokedUrlCommand*)command;
- (void)reload:(CDVInvokedUrlCommand*)command;
- (void)hide:(CDVInvokedUrlCommand*)command;
- (void)exitApp:(CDVInvokedUrlCommand*)command;
- (void)webViewAdjustmenBehavior:(CDVInvokedUrlCommand*)command;
- (void)webViewFinished;
- (void)callDebugCallback;
- (void)callUrlCallback:(NSString*)url;
- (void)callResumeCallback:(NSString*)url;
- (BOOL)shouldOverrideLoadWithRequest:(NSURLRequest*)request navigationType:(UIWebViewNavigationType)navigationType;

@end

@interface WebViewController <UIGestureRecognizerDelegate> : CDVViewController
{}
@property (nonatomic, assign) id  delegate;
- (void)viewDidDisappear:(BOOL)animated;
- (void)loadURL:(NSString *)url;
- (void)reload;
@end
