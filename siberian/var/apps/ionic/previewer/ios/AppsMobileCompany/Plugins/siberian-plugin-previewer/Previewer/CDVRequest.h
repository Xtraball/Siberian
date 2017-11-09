//
//  Request.h
//  Siberian
//
//  Created by The Tiger App Creator Team on 24/02/14.
//
//

#import <Foundation/Foundation.h>
#import "CDVUrl.h"

@protocol CDVRequest

- (void) connectionDidFinish:(NSData *)datas;

@optional

- (void) connectionDidFail;

@end

@interface CDVRequest : NSObject {
    id <NSObject, CDVRequest> delegate;
    bool isSynchronious;
    
    NSMutableData *webData;
}

@property (retain) id <NSObject, CDVRequest> delegate;
@property (readwrite) bool isSynchronious;

@property (nonatomic, retain) NSMutableData *webData;

- (void)postDatas:(NSMutableDictionary *)datas withUrl:(NSString *)url;
- (void)postWithUrl:(NSString *)withUrl;

- (void)loadImage:(NSString *)withUrl;

@end
