//
//  Url.m
//  Siberian
//
//  Created by The Tiger App Creator Team on 24/02/14.
//
//

#import "CDVUrl.h"

@implementation CDVUrl

@synthesize scheme, domain, language_code, key, path;

static CDVUrl *sharedInstance = nil;

- (id)init
{
    self = [super init];
    
    if (self) {
        
        NSDictionary *urlParts = [[NSBundle mainBundle] objectForInfoDictionaryKey:@"Url"];
        NSLog(@"urlParts : %@", urlParts);

        sharedInstance = self;
        scheme = [urlParts objectForKey:@"url_scheme"];
        domain = [urlParts objectForKey:@"url_domain"]; // @"192.168.31.69"; //@"192.168.0.16";
        language_code = @"en";
        path = [urlParts objectForKey:@"url_path"];
        key = [urlParts objectForKey:@"url_key"]; // @"overview";
        
        [self prepareLanguages];
        
        NSString *systemLanguageCode = [[NSLocale preferredLanguages] objectAtIndex:0];
        systemLanguageCode = [[systemLanguageCode componentsSeparatedByString:@"-"] objectAtIndex:0];
        NSLog(@"systemLanguageCode: %@", systemLanguageCode);
        NSString *currentLanguageCode = @"en";
        
        if([languages containsObject:systemLanguageCode]) {
            currentLanguageCode = systemLanguageCode;
        }
        
        language_code = currentLanguageCode;
        NSLog(@"language_code : %@", language_code);
    }

    return self;
}

- (void)prepareLanguages {
    
//    NSURL *url = [[NSURL alloc] initWithString:[self get:@"application/mobile/languages"]];
//    NSString *strLanguages = [[NSString alloc] initWithContentsOfURL:url encoding:NSUTF8StringEncoding error:nil];
//    languages = [strLanguages componentsSeparatedByString:@","];
    languages = [[NSArray alloc] initWithObjects:@"en", @"fr", nil];
}

+ (CDVUrl *)sharedInstance {

    if (nil != sharedInstance) {
        return sharedInstance;
    }

    return [[CDVUrl alloc] init];
}

- (NSString *)get:(NSString *)uri {
    
    NSString *url = [scheme stringByAppendingFormat:@"://%@", domain];
    if(path.length) {
        url = [url stringByAppendingFormat:@"/%@", path];
    }
    if(key.length) {
        url = [url stringByAppendingFormat:@"/%@", key];
    }
    if(uri.length > 0) {
        url = [url stringByAppendingFormat:@"/%@", uri];
    }

    return url;
}

- (NSString *)getBase:(NSString *)uri {
    
    NSString *url = [scheme stringByAppendingFormat:@"://%@", domain];
    if(path.length) {
        url = [url stringByAppendingFormat:@"/%@", path];
    }
    if(uri.length > 0) {
        url = [url stringByAppendingFormat:@"/%@", uri];
    }
    
    return url;
    
}

- (NSString *)getImage:(NSString *)imagePath {
    imagePath = [self sanitize:imagePath];
    return [scheme stringByAppendingFormat:@"://%@/%@", domain, imagePath];
}

- (NSString *)addPreviewTo:url {
//    if([url rangeOfString:@"?"].location == NSNotFound) {
//        url = [url stringByAppendingString:@"?"];
//    } else {
//        url = [url stringByAppendingString:@"&"];
//    }
//    url = [url stringByAppendingString:@"preview=1"];
    
    return url;
}

- (void)setScheme:(NSString *)newScheme {
    scheme = newScheme;
}
- (void)setDomain:(NSString *)newDomain {
    domain = [self sanitize:newDomain];
}
- (void)setPath:(NSString *)newPath {
    path = [self sanitize:newPath];
}
- (void)setKey:(NSString *)newKey {
    key = [self sanitize:newKey];
}


- (NSString *)sanitize:(NSString *)str {
    NSLog(@"Before: %@", str);
    if([str hasPrefix:@"/"]) {
        str = [str substringFromIndex:1];
    }
    if([str hasSuffix:@"/"]) {
        str = [str substringToIndex:str.length-1];
    }
    NSLog(@"After: %@", str);
    return str;
    
}


@end
