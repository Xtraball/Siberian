//
//

#import <Cordova/CDV.h>
#import "CDVNavigator.h"

@implementation CDVNavigator

- (void)openByUrl:(NSString *)url {
    [[UIApplication sharedApplication] openURL:[NSURL URLWithString:url]];
}

- (void)navigate:(CDVInvokedUrlCommand *)command {
    [self showAlert:command.arguments];
}

- (void)showAlert:(NSArray*) latlng {

    NSString *toLat = [NSString stringWithFormat:@"%@",[latlng objectAtIndex:0]];
    NSString *toLng = [NSString stringWithFormat:@"%@",[latlng objectAtIndex:1]];
    
    UIAlertController * alert = [UIAlertController
                                 alertControllerWithTitle:nil
                                 message:nil
                                 preferredStyle:UIAlertControllerStyleActionSheet];
    
    UIAlertAction* waze = [UIAlertAction
                           actionWithTitle:@"Waze"
                           style:UIAlertActionStyleDefault
                           handler:^(UIAlertAction * action) {
                               if ([[UIApplication sharedApplication] canOpenURL:[NSURL URLWithString:@"waze://"]]) {
                                   [self openByUrl:[NSString stringWithFormat:@"waze://?ll=%f,%f&navigate=yes", [toLat doubleValue], [toLng doubleValue]]];
                               } else {
                                   [self openByUrl:@"http://itunes.apple.com/us/app/id323229106"];
                               }
                           }];
    
    UIAlertAction* googleMaps = [UIAlertAction
                                 actionWithTitle:@"Google Maps"
                                 style:UIAlertActionStyleDefault
                                 handler:^(UIAlertAction * action) {
                                     if ([[UIApplication sharedApplication] canOpenURL:[NSURL URLWithString:@"comgooglemaps-x-callback://"]]) {
                                         [self openByUrl:[NSString stringWithFormat:@"comgooglemaps-x-callback://?daddr=%f,%f&x-success=sourceapp://?resume=true&x-source=NavigatorIntent",
                                                          [toLat doubleValue], [toLng doubleValue]]];
                                     } else {
                                         [self openByUrl:@"https://itunes.apple.com/us/app/google-maps-transit-food/id585027354?mt=8"];
                                     }
                                 }];

    UIAlertAction* appleMaps = [UIAlertAction
                                  actionWithTitle:@"Apple Maps"
                                  style:UIAlertActionStyleDefault
                                  handler:^(UIAlertAction * action) {
                                      if ([[UIApplication sharedApplication] canOpenURL:[NSURL URLWithString:@"maps://"]]) {
                                          [self openByUrl:[NSString stringWithFormat:@"maps://?q=%f,%f",
                                                           [toLat doubleValue], [toLng doubleValue]]];
                                      } else {
                                          [self openByUrl:@"https://itunes.apple.com/us/app/maps/id915056765?mt=8"];
                                      }
                                  }];
    
    UIAlertAction* cancel = [UIAlertAction
                             actionWithTitle:@"OK"
                             style:UIAlertActionStyleCancel
                             handler:^(UIAlertAction * action) {
                                 [alert dismissViewControllerAnimated:true completion:nil];
                             }];
    
    [alert addAction:waze];
    [alert addAction:googleMaps];
    [alert addAction:appleMaps];
    [alert addAction:cancel];
    
    [self.viewController presentViewController:alert animated:YES completion:nil];
}

@end
