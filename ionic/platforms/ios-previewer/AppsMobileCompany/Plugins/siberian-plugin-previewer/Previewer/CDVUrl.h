//
//  url.h
//  Siberian
//
//  Created by The Tiger App Creator Team on 24/02/14.
//
//

#import <Foundation/Foundation.h>

@interface CDVUrl : NSObject {
    NSString *scheme;
    NSString *domain;
    NSString *language_code;
    NSString *path;
    NSString *key;    
    NSArray *languages;
}

@property (nonatomic, retain) NSString *appId;
@property (nonatomic, retain) NSString *scheme;
@property (nonatomic, retain) NSString *domain;
@property (nonatomic, retain) NSString *language_code;
@property (nonatomic, retain) NSString *path;
@property (nonatomic, retain) NSString *key;

+ (CDVUrl *)sharedInstance;


- (NSString *)get:(NSString *)uri;
- (NSString *)getImage:(NSString *)path;
- (NSString *)getBase:(NSString *)uri;


@end
