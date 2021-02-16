//
//  StartAppAd.h
//  StartAppAdSDK
//
//  Copyright (c) 2013 StartApp. All rights reserved.
//  SDK version 4.5.0

@interface STAUserLocation : NSObject
@property  double latitude;
@property  double longitude;
@end

// STAAdPreferences holds params specific to an ad
@interface STAAdPreferences : NSObject

@property (nonatomic, strong) STAUserLocation *userLocation;
@property (nonatomic, assign) double minCPM;
@property (nonatomic, strong) NSString *adTag;

+ (instancetype)prefrencesWithLatitude:(double)latitude andLongitude:(double)longitude;
+ (instancetype)preferencesWithMinCPM:(double)minCPM;

@end
