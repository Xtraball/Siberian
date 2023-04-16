// (c) Facebook, Inc. and its affiliates. Confidential and proprietary.

/***
 * This is a bridge file for Audience Network Unity SDK.
 *
 * Please refer to FBInterstitialAd.h and FBAdExtraHint.h for full documentation of the API.
 *
 * This file may be used to build your own Audience Network iOS SDK wrapper,
 * but note that we don't support customisations of the Audience Network codebase.
 *
 ***/

#import <FBAudienceNetwork/FBAdBridgeCommon.h>

FB_EXTERN_C_BEGIN

FB_EXPORT int32_t FBInterstitialAdBridgeCreate(char const *placementID);
FB_EXPORT int32_t FBInterstitialAdBridgeLoad(int32_t uniqueId);
FB_EXPORT int32_t FBInterstitialAdBridgeLoadWithBidPayload(int32_t uniqueId, char *bidPayload);

FB_EXPORT bool FBInterstitialAdBridgeIsValid(int32_t uniqueId);
FB_EXPORT char const *FBInterstitialAdBridgeGetPlacementId(int32_t uniqueId);
FB_EXPORT bool FBInterstitialAdBridgeShow(int32_t uniqueId);
FB_EXPORT void FBInterstitialAdBridgeSetExtraHints(int32_t uniqueId, char const *extraHints);
FB_EXPORT void FBInterstitialAdBridgeRelease(int32_t uniqueId);

FB_EXPORT void FBInterstitialAdBridgeOnLoad(int32_t uniqueId, FBAdBridgeCallback callback);
FB_EXPORT void FBInterstitialAdBridgeOnImpression(int32_t uniqueId, FBAdBridgeCallback callback);
FB_EXPORT void FBInterstitialAdBridgeOnClick(int32_t uniqueId, FBAdBridgeCallback callback);
FB_EXPORT void FBInterstitialAdBridgeOnError(int32_t uniqueId, FBAdBridgeErrorCallback callback);
FB_EXPORT void FBInterstitialAdBridgeOnDidClose(int32_t uniqueId, FBAdBridgeCallback callback);
FB_EXPORT void FBInterstitialAdBridgeOnWillClose(int32_t uniqueId, FBAdBridgeCallback callback);

FB_EXTERN_C_END
