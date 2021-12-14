// (c) Facebook, Inc. and its affiliates. Confidential and proprietary.

#import <UIKit/UIKit.h>

#import <FBAudienceNetwork/FBAdDefines.h>

NS_ASSUME_NONNULL_BEGIN

/**
 Represents an image creative.
 */
FB_CLASS_EXPORT
@interface FBAdImage : NSObject

/**
 Typed access to the image url.
 */
@property (nonatomic, copy, readonly) NSURL *url;
/**
 Typed access to the image width.
 */
@property (nonatomic, assign, readonly) NSInteger width;
/**
 Typed access to the image height.
 */
@property (nonatomic, assign, readonly) NSInteger height;

/**
 This is a method to initialize an FBAdImage.

 @param url the image url.
 @param width the image width.
 @param height the image height.
 */
- (instancetype)initWithURL:(NSURL *)url width:(NSInteger)width height:(NSInteger)height NS_DESIGNATED_INITIALIZER;

/**
 Loads an image from self.url over the network, or returns the cached image immediately.

 @param block Block to handle the loaded image.
 */
- (void)loadImageAsyncWithBlock:(nullable void (^)(UIImage *__nullable image))block;

@end

NS_ASSUME_NONNULL_END
