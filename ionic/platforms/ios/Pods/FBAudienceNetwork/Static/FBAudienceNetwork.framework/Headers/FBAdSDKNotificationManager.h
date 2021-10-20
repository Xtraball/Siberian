// Copyright 2004-present Facebook. All Rights Reserved.
//
// You are hereby granted a non-exclusive, worldwide, royalty-free license to use,
// copy, modify, and distribute this software in source code or binary form for use
// in connection with the web services and APIs provided by Facebook.
//
// As with any software that integrates with the Facebook platform, your use of
// this software is subject to the Facebook Developer Principles and Policies
// [http://developers.facebook.com/policy/]. This copyright notice shall be
// included in all copies or substantial portions of the software.
//
// THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
// IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS
// FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
// COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
// IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
// CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

#import <Foundation/Foundation.h>

NS_ASSUME_NONNULL_BEGIN

@protocol FBAdSDKNotificationListener <NSObject>

/**
Method to be called when some specific SDK event will happens

@param event event type. Currently suuported following events:
  "impression" happens every time when AD got an inpression recorded on the SDK
@param eventData is a payload associated with the event.

Method would be called on the main queue when the SDK event happens.
*/
- (void)onFBAdEvent:(NSString *)event eventData:(NSDictionary<NSString *, NSString *> *)eventData;

@end

@interface FBAdSDKNotificationManager : NSObject

/**
 Adds a listener to SDK events

@param listener The listener to receive notification when the event happens

Note that SDK will hold a weak reference to listener object
*/
+ (void)addFBAdSDKNotificationListener:(id<FBAdSDKNotificationListener>)listener;

/**
 Adds a listener to SDK events

@param listener The listener to be removed from notification list.

You can call this method when you no longer want to receive SDK notifications.
*/
+ (void)removeFBAdSDKNotificationListener:(id<FBAdSDKNotificationListener>)listener;

@end

NS_ASSUME_NONNULL_END
