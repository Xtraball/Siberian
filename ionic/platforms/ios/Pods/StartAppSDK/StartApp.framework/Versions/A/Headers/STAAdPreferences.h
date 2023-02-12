//
//  StartAppAd.h
//  StartAppAdSDK
//
//  Copyright (c) 2013 StartApp. All rights reserved.
//  SDK version 4.7.0

@interface STAUserLocation : NSObject
@property double latitude;
@property double longitude;
@end


@interface STAAdPreferences : NSObject
/// User location
@property (nonatomic, strong) STAUserLocation *userLocation;
/// Minimal cost per impression for loaded ads
@property (nonatomic, assign) double minCPM;
/// A string tag to be sent within impression
@property (nonatomic, strong) NSString *adTag;
/// Alternative app id
@property (nonatomic, copy) NSString* customProductId;

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
