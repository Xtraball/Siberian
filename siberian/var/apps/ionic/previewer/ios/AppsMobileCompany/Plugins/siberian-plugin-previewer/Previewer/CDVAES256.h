// http://www.cocos2d-iphone.org/forum/topic/6982

#import <Foundation/Foundation.h>

@interface NSData (CDVAES256)

- (NSData*) encryptedWithKey:(NSData*) key;

- (NSData*) decryptedWithKey:(NSData*) key;

@end