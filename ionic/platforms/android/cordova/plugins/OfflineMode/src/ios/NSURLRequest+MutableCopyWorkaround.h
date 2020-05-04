// required to workaround http://openradar.appspot.com/11596316
@interface NSURLRequest(MutableCopyWorkaround)
- (id) mutableCopyWorkaround;
@end
