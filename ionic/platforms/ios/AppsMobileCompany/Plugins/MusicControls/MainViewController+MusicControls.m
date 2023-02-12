//
//  MainViewController+MusicControls.m
//  
//
//  Created by Juan Gonzalez on 12/17/16.
//
//

#import <Foundation/Foundation.h>


#import "MainViewController+MusicControls.h"

@implementation MainViewController (MusicControls)

- (void) remoteControlReceivedWithEvent: (UIEvent *) receivedEvent {
    [[NSNotificationCenter defaultCenter] postNotificationName:@"musicControlsEventNotification" object:receivedEvent];
}

@end
