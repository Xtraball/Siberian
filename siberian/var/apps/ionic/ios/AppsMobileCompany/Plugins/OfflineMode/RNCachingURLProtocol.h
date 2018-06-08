@interface RNCachingURLProtocol : NSURLProtocol
@property (nonatomic, readwrite, strong) NSURLConnection *connection;
@property (nonatomic, readwrite, strong) NSMutableData *data;
@property (nonatomic, readwrite, strong) NSURLResponse *response;

- (void)appendData:(NSData *)newData;
+ (NSString *)cachePathForRequest:(NSURLRequest *)aRequest;
+ (NSHTTPURLResponse *) addCacheHeaderToResponse:(NSURLResponse *)response;


@end
