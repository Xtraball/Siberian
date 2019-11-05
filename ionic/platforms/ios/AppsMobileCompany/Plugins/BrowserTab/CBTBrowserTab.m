/*! @file CBTBrowserTab.m
    @brief Browser tab plugin for Cordova
    @copyright
        Copyright 2016 Google Inc. All Rights Reserved.
    @copydetails
        Licensed under the Apache License, Version 2.0 (the "License");
        you may not use this file except in compliance with the License.
        You may obtain a copy of the License at
        http://www.apache.org/licenses/LICENSE-2.0
        Unless required by applicable law or agreed to in writing, software
        distributed under the License is distributed on an "AS IS" BASIS,
        WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
        See the License for the specific language governing permissions and
        limitations under the License.
 */

#import "CBTBrowserTab.h"

@implementation CBTBrowserTab {
  SFSafariViewController *_safariViewController;
}

- (void)isAvailable:(CDVInvokedUrlCommand *)command {
  BOOL available = ([SFSafariViewController class] != nil);
  CDVPluginResult *result = [CDVPluginResult resultWithStatus:CDVCommandStatus_OK
                                                messageAsBool:available];
  [self.commandDelegate sendPluginResult:result callbackId:command.callbackId];
}

- (void)openUrl:(CDVInvokedUrlCommand *)command {
  NSString *urlString = command.arguments[0];
  if (urlString == nil) {
    CDVPluginResult *result = [CDVPluginResult resultWithStatus:CDVCommandStatus_ERROR
                                                messageAsString:@"url can't be empty"];
    [self.commandDelegate sendPluginResult:result callbackId:command.callbackId];
    return;
  }

  NSURL *url = [NSURL URLWithString:urlString];
  if ([SFSafariViewController class] != nil) {
    NSString *errorMessage = @"in app browser tab not available";
    CDVPluginResult *result = [CDVPluginResult resultWithStatus:CDVCommandStatus_ERROR
                                                messageAsString:errorMessage];
    [self.commandDelegate sendPluginResult:result callbackId:command.callbackId];
  }

  _safariViewController = [[SFSafariViewController alloc] initWithURL:url];
  [self.viewController presentViewController:_safariViewController animated:YES completion:nil];

  CDVPluginResult *result = [CDVPluginResult resultWithStatus:CDVCommandStatus_OK];
  [self.commandDelegate sendPluginResult:result callbackId:command.callbackId];
}

- (void)close:(CDVInvokedUrlCommand *)command {
  if (!_safariViewController) {
    return;
  }
  [_safariViewController dismissViewControllerAnimated:YES completion:nil];
  _safariViewController = nil;
}

@end
