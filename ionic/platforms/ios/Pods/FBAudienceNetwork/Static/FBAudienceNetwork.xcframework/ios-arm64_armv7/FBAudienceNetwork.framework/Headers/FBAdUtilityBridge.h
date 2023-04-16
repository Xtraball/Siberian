// (c) Facebook, Inc. and its affiliates. Confidential and proprietary.

/***
 * This is a bridge file for Audience Network Unity SDK.
 *
 * Please refer to FBAdScreen.h for full documentation of the API.
 *
 * This file may be used to build your own Audience Network iOS SDK wrapper,
 * but note that we don't support customisations of the Audience Network codebase.
 *
 ***/

#import <FBAudienceNetwork/FBAdBridgeCommon.h>

FB_EXTERN_C_BEGIN

FB_EXPORT double FBAdUtilityBridgeGetDeviceWidth(void);
FB_EXPORT double FBAdUtilityBridgeGetDeviceHeight(void);
FB_EXPORT double FBAdUtilityBridgeGetWidth(void);
FB_EXPORT double FBAdUtilityBridgeGetHeight(void);

FB_EXPORT double FBAdUtilityBridgeConvertFromDeviceSize(double deviceSize);

FB_EXTERN_C_END
