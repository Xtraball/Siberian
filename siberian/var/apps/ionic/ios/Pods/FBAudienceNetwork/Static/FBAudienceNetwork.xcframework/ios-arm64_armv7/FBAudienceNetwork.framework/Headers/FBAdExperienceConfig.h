// (c) Facebook, Inc. and its affiliates. Confidential and proprietary.

#import <Foundation/Foundation.h>

#import <FBAudienceNetwork/FBAdDefines.h>

NS_ASSUME_NONNULL_BEGIN

typedef NSString *FBAdExperienceType NS_STRING_ENUM;
extern FBAdExperienceType const FBAdExperienceTypeRewarded;
extern FBAdExperienceType const FBAdExperienceTypeInterstitial;
extern FBAdExperienceType const FBAdExperienceTypeRewardedInterstitial;

FB_CLASS_EXPORT
/**
 FBAdExperienceConfig is class designed to add some configuration to ad experience
 */
@interface FBAdExperienceConfig : NSObject <NSCopying>

/**
 Ad experience type to set up
 */
@property (nonatomic, strong, readwrite, nonnull) FBAdExperienceType adExperienceType;

- (instancetype)init NS_UNAVAILABLE;

+ (instancetype)new NS_UNAVAILABLE;

/**
 Creates an FBAdExperienceConfig with a specified type of experience
 */
- (instancetype)initWithAdExperienceType:(FBAdExperienceType)adExperienceType NS_DESIGNATED_INITIALIZER;

@end

NS_ASSUME_NONNULL_END
