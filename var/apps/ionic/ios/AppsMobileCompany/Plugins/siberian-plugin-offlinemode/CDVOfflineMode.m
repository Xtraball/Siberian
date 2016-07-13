//
//  RNCachingURLProtocol.m
//
//  Created by Robert Napier on 1/10/12.
//  Copyright (c) 2012 Rob Napier.
//
//  This code is licensed under the MIT License:
//
//  Permission is hereby granted, free of charge, to any person obtaining a
//  copy of this software and associated documentation files (the "Software"),
//  to deal in the Software without restriction, including without limitation
//  the rights to use, copy, modify, merge, publish, distribute, sublicense,
//  and/or sell copies of the Software, and to permit persons to whom the
//  Software is furnished to do so, subject to the following conditions:
//
//  The above copyright notice and this permission notice shall be included in
//  all copies or substantial portions of the Software.
//
//  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
//  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
//  FITNESS FOR A PARTICULAR PURPOSE AND NON-INFRINGEMENT. IN NO EVENT SHALL THE
//  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
//  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
//  FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER
//  DEALINGS IN THE SOFTWARE.
//

#import <Cordova/CDV.h>
#import "CDVOfflineMode.h"

#define WORKAROUND_MUTABLE_COPY_LEAK 1

#if WORKAROUND_MUTABLE_COPY_LEAK
    // required to workaround http://openradar.appspot.com/11596316
    @interface NSURLRequest(MutableCopyWorkaround)
    - (id) mutableCopyWorkaround;
    @end
#endif

@interface RNCachedData : NSObject <NSCoding>
@property (nonatomic, readwrite, strong) NSData *data;
@property (nonatomic, readwrite, strong) NSURLResponse *response;
@property (nonatomic, readwrite, strong) NSURLRequest *redirectRequest;
@end

static NSString *RNCachingURLHeader = @"X-RNCache";

@interface RNCachingURLProtocol () // <NSURLConnectionDelegate, NSURLConnectionDataDelegate> iOS5-only
@property (nonatomic, readwrite, strong) NSURLConnection *connection;
@property (nonatomic, readwrite, strong) NSMutableData *data;
@property (nonatomic, readwrite, strong) NSURLResponse *response;
- (void)appendData:(NSData *)newData;
@end

@implementation CDVOfflineMode

- (void)useCache:(CDVInvokedUrlCommand*)command
{
    useCache = [[command.arguments objectAtIndex:0] isEqualToString:@"1"];
    NSLog(@"[ios] use cache: %i", useCache);

    CDVPluginResult* result = [CDVPluginResult resultWithStatus:CDVCommandStatus_OK];
    [self.commandDelegate sendPluginResult:result callbackId:command.callbackId];
}

@end

@implementation RNCachingURLProtocol
@synthesize connection = connection_;
@synthesize data = data_;
@synthesize response = response_;


+ (BOOL)canInitWithRequest:(NSURLRequest *)request
{
    NSString *url = [NSString stringWithFormat:@"%@", [request URL]];
    BOOL isCheckingConnection = [url rangeOfString:@"check_connection.php" options:NSCaseInsensitiveSearch].location != NSNotFound;
    BOOL isChangingStatus = NO;

    if([url rangeOfString:@"/app:setIsOnline" options:NSCaseInsensitiveSearch].location != NSNotFound) {
        NSMutableArray *path = [[NSMutableArray alloc] initWithArray:[url componentsSeparatedByString: @":"]];
        NSString *value = [path lastObject];
        useCache = [value isEqualToString:@"0"];
        isChangingStatus = YES;
    }

    // Handling special $cordovaOauth facebook callback.
    if ([url rangeOfString:@"http://localhost/callback"].location != NSNotFound &&
        [url rangeOfString:@"redirect_uri=http://localhost/callback"].location == NSNotFound) {
        useCache = NO;
        return NO;
    }

    // only handle http requests we haven't marked with our header.
    if ([[[request URL] scheme] isEqualToString:@"http"] &&
        ([request valueForHTTPHeaderField:RNCachingURLHeader] == nil) &&
        (![[request HTTPMethod] isEqualToString:@"POST"]) &&
        !isCheckingConnection &&
        !isChangingStatus
    ) {
        return YES;
    }

    return NO;
}

+ (NSURLRequest *)canonicalRequestForRequest:(NSURLRequest *)request
{
    return request;
}

- (NSString *)cachePathForRequest:(NSURLRequest *)aRequest
{
    // This stores in the Caches directory, which can be deleted when space is low, but we only use it for offline access
    NSString *cachesPath = [NSSearchPathForDirectoriesInDomains(NSCachesDirectory, NSUserDomainMask, YES) lastObject];
    return [cachesPath stringByAppendingPathComponent:[NSString stringWithFormat:@"%lx", (unsigned long) [[[aRequest URL] absoluteString] hash]]];
}

- (void)startLoading
{
    BOOL loadData = YES;
    NSArray *cachedExtensions = [[NSArray alloc] initWithObjects:@"js", @"css", @"png", @"jpg", @"gif", nil];
    BOOL cacheIsForced = [cachedExtensions containsObject:[[[self request] URL] pathExtension]];

    if (useCache || cacheIsForced) {

        RNCachedData *cache = [NSKeyedUnarchiver unarchiveObjectWithFile:[self cachePathForRequest:[self request]]];

        if (cache) {
          NSData *data = [cache data];
          NSURLResponse *response = [cache response];
          NSURLRequest *redirectRequest = [cache redirectRequest];

          if (redirectRequest) {
              [[self client] URLProtocol:self wasRedirectedToRequest:redirectRequest redirectResponse:response];
          } else {
              [[self client] URLProtocol:self didReceiveResponse:response cacheStoragePolicy:NSURLCacheStorageNotAllowed]; // we handle caching ourselves.
              [[self client] URLProtocol:self didLoadData:data];
              [[self client] URLProtocolDidFinishLoading:self];
          }

          loadData = NO;
        } else if(useCache) {
          [[self client] URLProtocol:self didFailWithError:[NSError errorWithDomain:NSURLErrorDomain code:NSURLErrorCannotConnectToHost userInfo:nil]];
          loadData = NO;
        }
    }

    if(loadData) {
        NSMutableURLRequest *connectionRequest =
            #if WORKAROUND_MUTABLE_COPY_LEAK
                [[self request] mutableCopyWorkaround];
            #else
                [[self request] mutableCopy];
            #endif

        // we need to mark this request with our header so we know not to handle it in +[NSURLProtocol canInitWithRequest:].
        [connectionRequest setValue:@"" forHTTPHeaderField:RNCachingURLHeader];
        NSURLConnection *connection = [NSURLConnection connectionWithRequest:connectionRequest
        delegate:self];
        [self setConnection:connection];
    }
}

- (void)stopLoading
{
    [[self connection] cancel];
}

// NSURLConnection delegates (generally we pass these on to our client)

- (NSURLRequest *)connection:(NSURLConnection *)connection willSendRequest:(NSURLRequest *)request redirectResponse:(NSURLResponse *)response
{
    // NSLog(@"willSendRequest");
    // Thanks to Nick Dowell https://gist.github.com/1885821
    if (response != nil) {
        NSMutableURLRequest *redirectableRequest =
            #if WORKAROUND_MUTABLE_COPY_LEAK
                [request mutableCopyWorkaround];
            #else
                [request mutableCopy];
            #endif
        NSLog(@"Caching data 1");
        // We need to remove our header so we know to handle this request and cache it.
        // There are 3 requests in flight: the outside request, which we handled, the internal request,
        // which we marked with our header, and the redirectableRequest, which we're modifying here.
        // The redirectable request will cause a new outside request from the NSURLProtocolClient, which
        // must not be marked with our header.
        [redirectableRequest setValue:nil forHTTPHeaderField:RNCachingURLHeader];

        NSString *cachePath = [self cachePathForRequest:[self request]];
        RNCachedData *cache = [RNCachedData new];
        [cache setResponse:response];
        [cache setData:[self data]];
        [cache setRedirectRequest:redirectableRequest];
        [NSKeyedArchiver archiveRootObject:cache toFile:cachePath];
        [[self client] URLProtocol:self wasRedirectedToRequest:redirectableRequest redirectResponse:response];

        return redirectableRequest;
    } else {
        return request;
    }
}

- (void)connection:(NSURLConnection *)connection didReceiveData:(NSData *)data
{
    [[self client] URLProtocol:self didLoadData:data];
    [self appendData:data];
}

- (void)connection:(NSURLConnection *)connection didFailWithError:(NSError *)error
{
    if(!useCache) {
        useCache = YES;
        [self startLoading];
    }

    [[self client] URLProtocol:self didFailWithError:error];
    [self setConnection:nil];
    [self setData:nil];
    [self setResponse:nil];
}

- (void)connection:(NSURLConnection *)connection didReceiveResponse:(NSURLResponse *)response
{
    [self setResponse:response];
    [[self client] URLProtocol:self didReceiveResponse:response cacheStoragePolicy:NSURLCacheStorageNotAllowed];  // We cache ourselves.
}

- (void)connectionDidFinishLoading:(NSURLConnection *)connection
{
    [[self client] URLProtocolDidFinishLoading:self];

    // NSLog(@"Caching data 2");
    NSString *cachePath = [self cachePathForRequest:[self request]];
    RNCachedData *cache = [RNCachedData new];
    [cache setResponse:[self response]];
    [cache setData:[self data]];
    [NSKeyedArchiver archiveRootObject:cache toFile:cachePath];

    [self setConnection:nil];
    [self setData:nil];
    [self setResponse:nil];
}

- (void)appendData:(NSData *)newData
{
    if ([self data] == nil) {
        [self setData:[newData mutableCopy]];
    } else {
        [[self data] appendData:newData];
    }
}

@end

static NSString *const kDataKey = @"data";
static NSString *const kResponseKey = @"response";
static NSString *const kRedirectRequestKey = @"redirectRequest";

@implementation RNCachedData
@synthesize data = data_;
@synthesize response = response_;
@synthesize redirectRequest = redirectRequest_;

- (void)encodeWithCoder:(NSCoder *)aCoder
{
    [aCoder encodeObject:[self data] forKey:kDataKey];
    [aCoder encodeObject:[self response] forKey:kResponseKey];
    [aCoder encodeObject:[self redirectRequest] forKey:kRedirectRequestKey];
}

- (id)initWithCoder:(NSCoder *)aDecoder
{
    self = [super init];
    if (self != nil) {
        [self setData:[aDecoder decodeObjectForKey:kDataKey]];
        [self setResponse:[aDecoder decodeObjectForKey:kResponseKey]];
        [self setRedirectRequest:[aDecoder decodeObjectForKey:kRedirectRequestKey]];
    }

    return self;
}

@end

#if WORKAROUND_MUTABLE_COPY_LEAK
    @implementation NSURLRequest(MutableCopyWorkaround)

    - (id) mutableCopyWorkaround {
        NSMutableURLRequest *mutableURLRequest = [[NSMutableURLRequest alloc] initWithURL:[self URL]
        cachePolicy:[self cachePolicy]
        timeoutInterval:[self timeoutInterval]];
        [mutableURLRequest setAllHTTPHeaderFields:[self allHTTPHeaderFields]];
        return mutableURLRequest;
    }

    @end
#endif
