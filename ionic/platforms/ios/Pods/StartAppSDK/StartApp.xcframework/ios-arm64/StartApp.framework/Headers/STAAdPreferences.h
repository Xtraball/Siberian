//
//  StartAppAd.h
//  StartAppAdSDK
//
//  Copyright (c) 2013 StartApp. All rights reserved.
//  SDK version 4.10.1

@interface STAUserLocation : NSObject
@property double latitude;
@property double longitude;
@end


@interface STAAdPreferences : NSObject
/// User location
@property (nonatomic, strong) STAUserLocation *userLocation;
/// Minimal cost per impression for loaded ads
@property (nonatomic, assign) double minCPM;
/// A string tag to be sent within ad request
@property (nonatomic, strong) NSString *adTag;
/// Alternative app id
@property (nonatomic, copy) NSString* customProductId;

/// Identifier of placement of the ad.
///
/// The ad which is presented to the user under the same circumstances (at the same place of the application) should have the same and unique placementId
@property (nonatomic, copy) NSString* placementId;
/*!
 * @brief Creates STAAdPreferences with latitude and longitude
 * @discussion Call this method to create STAAdPreferences with user location: latitude and longitude.
 * @param latitude User location latitude
 * @param longitude User location longitude
 * @return STAAdPreferences instance
 */
+ (instancetype)prefrencesWithLatitude:(double)latitude andLongitude:(double)longitude;

/*!
 * @brief Creates STAAdPreferences with minCPM (minimal cost per impression)
 * @discussion Call this method to create STAAdPreferences with minCPM.
 * @param minCPM Minimal cost per impression
 * @return STAAdPreferences instance
 */
+ (instancetype)preferencesWithMinCPM:(double)minCPM;

@end
