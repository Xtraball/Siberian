/*
Licensed to the Apache Software Foundation (ASF) under one
or more contributor license agreements. See the NOTICE file
distributed with this work for additional information
regarding copyright ownership. The ASF licenses this file
to you under the Apache License, Version 2.0 (the
"License"); you may not use this file except in compliance
with the License. You may obtain a copy of the License at

http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing,
software distributed under the License is distributed on an
"AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY
KIND, either express or implied. See the License for the
specific language governing permissions and limitations
under the License.
*/

#import "CDVStream.h"

@implementation CDVStream

@synthesize objAVPlayer;

- (void)create:(CDVInvokedUrlCommand*)command
{
	[self.commandDelegate runInBackground:^{
    	CDVPluginResult* result = [CDVPluginResult resultWithStatus:CDVCommandStatus_OK];
    	[self.commandDelegate sendPluginResult:result callbackId:command.callbackId];
    }];

}

- (void)startPlayingAudio:(CDVInvokedUrlCommand*)command
{
	[self.commandDelegate runInBackground:^{
    	NSString* resourcePath = [command.arguments objectAtIndex:1];
    	NSURL* resourceURL = [NSURL URLWithString:resourcePath];
    	NSLog(@"Now Playing '%@'", resourcePath);
    	if([self objAVPlayer] == nil){
    		[self setObjAVPlayer:[[AVPlayer alloc] initWithURL:resourceURL]];
			[[self objAVPlayer] addObserver:self forKeyPath:@"status" options:0 context:nil];
		}else{
		 	[[self objAVPlayer] play];
		}
    	return;
    }];
}
- (void) observeValueForKeyPath:(NSString *)keyPath 
                                ofObject:(id)object 
                                change:(NSDictionary  *)change 
                                context:(void *)context {

    if (object == [self objAVPlayer] && [keyPath isEqualToString:@"status"]) {
        if ([self objAVPlayer].status == AVPlayerStatusReadyToPlay) {
        	//Audio session is set to allow streaming in background
            AVAudioSession *audioSession = [AVAudioSession sharedInstance];
            [audioSession setCategory:AVAudioSessionCategoryPlayback error:nil];
            [[self objAVPlayer] play];
        }
        if ([self objAVPlayer].status == AVPlayerStatusFailed) {
            NSLog(@"Something went wrong: %@", [self objAVPlayer].error);
        }
    }
}


- (void)stopPlayingAudio:(CDVInvokedUrlCommand*)command
{
	[[self objAVPlayer] pause];
}

@end