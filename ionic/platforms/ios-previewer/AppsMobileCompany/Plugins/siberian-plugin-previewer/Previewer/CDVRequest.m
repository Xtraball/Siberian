//
//  Request.m
//  Siberian
//
//  Created by The Tiger App Creator Team on 24/02/14.
//
//

#import "CDVRequest.h"

@implementation CDVRequest

@synthesize delegate, isSynchronious, webData;

- (void)postDatas:(NSMutableDictionary *)datas withUrl:(NSString *)withUrl {

//    NSLog(@"url : %@", [[CDVUrl sharedInstance] get:withUrl]);
//    NSString *appId = [[CDVUrl sharedInstance] appId];
//    [datas setObject:appId forKey:@"app_id"];
//    NSLog(@"datas : %@", datas);

    NSString *postString = [NSString string];
    for (id key in datas) {
        postString = [postString stringByAppendingFormat:@"&%@=%@", key, [datas objectForKey:key]];
    }

    // Ajoute l'identifiant du device (utilisé côté serveur)
    postString = [postString stringByAppendingFormat:@"&device_id=%@", @"1"];

    NSMutableURLRequest *request;
    
    // Prépare la requête
    request = [NSMutableURLRequest requestWithURL:[NSURL URLWithString:[[CDVUrl sharedInstance] get:withUrl]] cachePolicy:NSURLRequestUseProtocolCachePolicy timeoutInterval:20.0];
    [request setHTTPBody:[postString dataUsingEncoding:NSUTF8StringEncoding]];
    [request setHTTPMethod:@"POST"];
    
    NSURLConnection *connectionSign=[[NSURLConnection alloc] initWithRequest:request delegate:self];
    
    if (connectionSign) {
        // Prépare les données à récupérer de la requête
        webData = [NSMutableData data];
    }
    else {
        NSLog(@"Error");
    }
    
}

- (void)postWithUrl:(NSString *)withUrl {
    NSMutableDictionary *datas = [NSMutableDictionary dictionary];
    [self postDatas:datas withUrl:withUrl];
}

- (void)loadImage:(NSString *)withUrl {
    
    NSMutableURLRequest *request;

    // Prépare la requête
    request = [NSMutableURLRequest requestWithURL:[NSURL URLWithString:[[CDVUrl sharedInstance] getImage:withUrl]] cachePolicy:NSURLRequestUseProtocolCachePolicy timeoutInterval:20.0];
    [request setHTTPMethod:@"GET"];

    
    if(self.isSynchronious) {
        NSData *returnData = [NSURLConnection sendSynchronousRequest:request returningResponse:nil error:nil];
        if([delegate respondsToSelector:@selector(connectionDidFinish:)]) {
            [delegate connectionDidFinish:returnData];
        }
    }
    else {
        NSURLConnection *connection=[[NSURLConnection alloc] initWithRequest:request delegate:self];
        
        if (connection) {
            // Prépare les données à récupérer de la requête
            webData = [NSMutableData data];
        }
        else {
            NSLog(@"Error");
        }        
    }
    
}

-(void)connection:(NSURLConnection *)connection didReceiveData:(NSData *)data {
	[webData appendData:data];
}

-(void)connectionDidFinishLoading:(NSURLConnection *)connection {
//    NSString *returnString = [[NSString alloc] initWithData:webData encoding:NSUTF8StringEncoding];
//    NSLog(@"datas : %@", returnString);
    if([delegate respondsToSelector:@selector(connectionDidFinish:)]) {
        [delegate connectionDidFinish:webData];
    }
}

- (void)connection:(NSURLConnection *)connection didFailWithError:(NSError *)error {
    UIAlertView *alert = [[UIAlertView alloc] initWithTitle:@"Erreur" message:NSLocalizedString(@"An error occured while trying to connect to the server. Please, check your internet connection.", nil) delegate:nil cancelButtonTitle:@"OK" otherButtonTitles:nil];
    [alert show];

    if([delegate respondsToSelector:@selector(connectionDidFail)]) {
        [delegate connectionDidFail];
    }
}

@end
