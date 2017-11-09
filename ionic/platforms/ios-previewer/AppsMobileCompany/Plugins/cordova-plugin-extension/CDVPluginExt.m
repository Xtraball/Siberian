//
//  CDVPluginExt.m
//  TestAdMobCombo
//
//  Created by Xie Liming on 14-10-28.
//
//

#import "CDVPluginExt.h"

@implementation CDVPluginExt

- (UIView*) getView
{
    if(self.adapter) return [self.adapter getView];
    else return self.webView;
}

- (UIViewController*) getViewController
{
    if(self.adapter) return [self.adapter getViewController];
    else return self.viewController;
}

- (void) fireEvent:(NSString *)obj event:(NSString *)eventName withData:(NSString *)jsonStr
{
    NSLog(@"%@, %@, %@", obj, eventName, jsonStr?jsonStr:@"");
    
    if(self.adapter) [self.adapter fireEvent:obj event:eventName withData:jsonStr];
    else {
        NSString* js;
        if(obj && [obj isEqualToString:@"window"]) {
            js = [NSString stringWithFormat:@"var evt=document.createEvent(\"UIEvents\");evt.initUIEvent(\"%@\",true,false,window,0);window.dispatchEvent(evt);", eventName];
        } else if(jsonStr && [jsonStr length]>0) {
            js = [NSString stringWithFormat:@"javascript:cordova.fireDocumentEvent('%@',%@);", eventName, jsonStr];
        } else {
            js = [NSString stringWithFormat:@"javascript:cordova.fireDocumentEvent('%@');", eventName];
        }
        [self.commandDelegate evalJs:js];
    }
}

- (void) sendPluginResult:(CDVPluginResult *)result to:(NSString *)callbackId
{
    if(self.adapter) [self.adapter sendPluginResult:result to:callbackId];
    else {
        [self.commandDelegate sendPluginResult:result callbackId:callbackId];
    }
}

@end
