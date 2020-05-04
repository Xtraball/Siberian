//
//  PluginAdapterUnity.m
//  TestAdMobCombo
//
//  Created by Xie Liming on 14-10-31.
//
//

#import "PluginAdapterUnity.h"
/*
void UnityPause( bool pause );

void UnitySendMessage( const char * className, const char * methodName, const char * param );

UIViewController *UnityGetGLViewController();

@implementation PluginAdapterUnity

- (UIView*) getView
{
    return [self getViewController].view;
}

- (UIViewController*) getViewController
{
    return UnityGetGLViewController();
}

- (void) fireEvent:(NSString*)obj event:(NSString*)eventName withData:(NSString*)jsonStr
{
    const char * className = obj ? obj.UTF8String : "Cordova";
    const char * methodName = eventName ? eventName.UTF8String : "onEvent";
    const char * param = jsonStr ? jsonStr.UTF8String : "";
    
    UnitySendMessage(className, methodName, param);
}

- (void) sendPluginResult:(CDVPluginResult*)result to:(NSString*)callbackId
{
    NSMutableString* jsonData = [NSMutableString string];
    [jsonData appendFormat: @"{\"callbackId\":\"%@\",\"status\":%d, \"keepCallback\":%d", callbackId, (int)result.status, (int)result.keepCallback ];
    [jsonData appendString: @",\"data\":"];
    [jsonData appendString: [result argumentsAsJSON]];
    [jsonData appendString: @"}"];
    
    UnitySendMessage("Cordova", "onExecuteCallback", jsonData.UTF8String);
}

@end
*/