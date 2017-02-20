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

#import "NSURLRequest+MutableCopyWorkaround.h"
#import "RNCachedData.h"
#import "RNCachingURLProtocol.h"
#import "SBOfflineModeManager.h"

#define WORKAROUND_MUTABLE_COPY_LEAK 1

@implementation RNCachingURLProtocol

@synthesize connection = connection_;
@synthesize data = data_;
@synthesize response = response_;


+ (BOOL)canInitWithRequest:(NSURLRequest *)request
{
    if(![SBOfflineModeManager sharedManager].canCache) {
        return NO;
    }

    NSArray *cachedExtensions = [[NSArray alloc] initWithObjects:@"js", @"css", @"png", @"jpg", @"gif", nil];
    BOOL cache = [cachedExtensions containsObject:[[request URL] pathExtension]] || [[request valueForHTTPHeaderField:@"X-Native-Cache"] isEqualToString:@"true"];
    
    if(!cache) {
        return NO;
    }
    
    NSString *url = [NSString stringWithFormat:@"%@", [request URL]];
    BOOL isCheckingConnection = [url rangeOfString:@"check_connection.php" options:NSCaseInsensitiveSearch].location != NSNotFound;
    BOOL isChangingStatus = NO;
    
    // Handling special $cordovaOauth facebook callback.
    if ([url rangeOfString:@"http://localhost/callback"].location != NSNotFound &&
        [url rangeOfString:@"redirect_uri=http://localhost/callback"].location == NSNotFound) {
        [SBOfflineModeManager sharedManager].isOnline = NO;
        return NO;
    }

    // only handle http requests we haven't marked with our header.
    if (([[[request URL] scheme] isEqualToString:@"http"] ||
         [[[request URL] scheme] isEqualToString:@"https"]) &&
        ([request valueForHTTPHeaderField:RNCachingURLHeader] == nil) &&
        (![[request HTTPMethod] isEqualToString:@"POST"]) &&
        !isCheckingConnection &&
        !isChangingStatus && cache
        ) {
        return YES;
    }
    
    return NO;
}

+ (NSURLRequest *)canonicalRequestForRequest:(NSURLRequest *)request
{
    return request;
}

+ (NSString *)cachePathForRequest:(NSURLRequest *)aRequest
{
    // This stores in the Caches directory, which can be deleted when space is low, but we only use it for offline access
    NSString *cachesPath = [NSSearchPathForDirectoriesInDomains(NSCachesDirectory, NSUserDomainMask, YES) lastObject];
    
    // Here we remove the timestamp ?t=123456789 in request for caching. It's useful when dealing with dynamic css and such
    NSString *url = [[aRequest URL] absoluteString];
    if(url != nil && [url isKindOfClass:[NSString class]] && ![url isEqualToString:@""]) {
        NSRegularExpression *regex = [NSRegularExpression regularExpressionWithPattern:@"\\.css\\?t=[\\d]+$" options:NSRegularExpressionCaseInsensitive error:NULL];
        NSString *modifiedUrl = [regex stringByReplacingMatchesInString:url options:0 range:NSMakeRange(0, [url length]) withTemplate:@".css"];
        
        if(![url isEqualToString:modifiedUrl]) {
            NSLog(@"Storing URL %@ instead of %@", modifiedUrl, url);
        }
        
        return [cachesPath stringByAppendingPathComponent:[NSString stringWithFormat:@"%lx", (unsigned long) [modifiedUrl hash]]];
    }
    
    return nil;
}

+ (NSHTTPURLResponse *) addCacheHeaderToResponse:(NSURLResponse *)response {
    NSHTTPURLResponse *resp = (NSHTTPURLResponse *)response;
    NSMutableDictionary *headers = [resp.allHeaderFields mutableCopy];
    [headers setObject:@"true" forKey:@"X-From-Native-Cache"];
    return [[NSHTTPURLResponse alloc] initWithURL:resp.URL statusCode:resp.statusCode HTTPVersion:@"HTTP/1.1" headerFields:headers];
}

- (void)startLoading
{
    BOOL loadData = YES;
    NSArray *cachedExtensions = [[NSArray alloc] initWithObjects:@"js", @"css", @"png", @"jpg", @"gif", nil];
    
    BOOL cache = [cachedExtensions containsObject:[[[self request] URL] pathExtension]] || [[[self request] valueForHTTPHeaderField:@"X-Native-Cache"] isEqualToString:@"true"];
    
    if (cache) {
        
        RNCachedData *cache = [NSKeyedUnarchiver unarchiveObjectWithFile:[RNCachingURLProtocol cachePathForRequest:[self request]]];
        
        if (![SBOfflineModeManager sharedManager].isOnline && cache) {
            NSData *data = [cache data];
            NSURLResponse *response = [cache response];
            NSURLRequest *redirectRequest = [cache redirectRequest];

            NSLog(@"Will cache URL: %@", [[self request] URL]);
            
            if (redirectRequest) {
                [[self client] URLProtocol:self wasRedirectedToRequest:redirectRequest redirectResponse:response];
            } else {
                [[self client] URLProtocol:self didReceiveResponse:response cacheStoragePolicy:NSURLCacheStorageNotAllowed]; // we handle caching ourselves.
                [[self client] URLProtocol:self didLoadData:data];
                [[self client] URLProtocolDidFinishLoading:self];
            }
            
            loadData = NO;
        } else if(![SBOfflineModeManager sharedManager].isOnline) {
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
        [connectionRequest setValue:@"1" forHTTPHeaderField:RNCachingURLHeader];
        [connectionRequest setTimeoutInterval:15];
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
        [redirectableRequest setValue:nil forHTTPHeaderField:@"X-Native-Cache"];
        [redirectableRequest setValue:nil forHTTPHeaderField:RNCachingURLHeader];
        [redirectableRequest setTimeoutInterval:15];
        
        NSLog(@"Caching URL: %@", [[self request] URL]);
        
        NSString *cachePath = [RNCachingURLProtocol cachePathForRequest:[self request]];
        RNCachedData *cache = [RNCachedData new];
        
        [cache setResponse: [RNCachingURLProtocol addCacheHeaderToResponse:response]];
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
    NSLog(@"Received data for request : %@", [[self request] URL]);
    [[self client] URLProtocol:self didLoadData:data];
    [self appendData:data];
}

- (void)connection:(NSURLConnection *)connection didFailWithError:(NSError *)error
{
    NSLog(@"Connection failed for error: %@", error);
    [[self client] URLProtocol:self didFailWithError:error];
    [self setConnection:nil];
    [self setData:nil];
    [self setResponse:nil];
}

- (void)connection:(NSURLConnection *)connection didReceiveResponse:(NSURLResponse *)response
{
    NSLog(@"Received response for URL: %@", [[self request] URL]);
    [self setResponse:response];
    [[self client] URLProtocol:self didReceiveResponse:response cacheStoragePolicy:NSURLCacheStorageNotAllowed];  // We cache ourselves.
}

- (void)connectionDidFinishLoading:(NSURLConnection *)connection
{
    [[self client] URLProtocolDidFinishLoading:self];
    
    // NSLog(@"Caching data 2");
    NSString *cachePath = [RNCachingURLProtocol cachePathForRequest:[self request]];
    RNCachedData *cache = [RNCachedData new];
    [cache setResponse: [RNCachingURLProtocol addCacheHeaderToResponse:[self response]]];
    [cache setData:[self data]];
    NSLog(@"Cached URL: %@", [[self request] URL]);
    [NSKeyedArchiver archiveRootObject:cache toFile:cachePath];
    
    [self setConnection:nil];
    [self setData:nil];
    [self setResponse:nil];
}

- (void)appendData:(NSData *)newData
{
    NSLog(@"Received data for request : %@", [[self request] URL]);
    if ([self data] == nil) {
        [self setData:[newData mutableCopy]];
    } else {
        [[self data] appendData:newData];
    }
}

@end
