//
//  MusicControls.m
//  
//
//  Created by Juan Gonzalez on 12/16/16.
//
//

#import "MusicControls.h"
#import "MusicControlsInfo.h"

@implementation MusicControls

- (void) create: (CDVInvokedUrlCommand *) command {
    NSDictionary * musicControlsInfoDict = [command.arguments objectAtIndex:0];
    MusicControlsInfo * musicControlsInfo = [[MusicControlsInfo alloc] initWithDictionary:musicControlsInfoDict];
    
    if (!NSClassFromString(@"MPNowPlayingInfoCenter")) {
        return;
    }
    
    [self.commandDelegate runInBackground:^{
        MPNowPlayingInfoCenter * nowPlayingInfoCenter =  [MPNowPlayingInfoCenter defaultCenter];
        NSDictionary * nowPlayingInfo = nowPlayingInfoCenter.nowPlayingInfo;
        NSMutableDictionary * updatedNowPlayingInfo = [NSMutableDictionary dictionaryWithDictionary:nowPlayingInfo];
        
        MPMediaItemArtwork * mediaItemArtwork = [self createCoverArtwork:[musicControlsInfo cover]];
        NSNumber * duration = [NSNumber numberWithInt:[musicControlsInfo duration]];
        NSNumber * elapsed = [NSNumber numberWithInt:[musicControlsInfo elapsed]];
        NSNumber * playbackRate = [NSNumber numberWithBool:[musicControlsInfo isPlaying]];
        
        if (mediaItemArtwork != nil) {
            [updatedNowPlayingInfo setObject:mediaItemArtwork forKey:MPMediaItemPropertyArtwork];
        }
        
        [updatedNowPlayingInfo setObject:[musicControlsInfo artist] forKey:MPMediaItemPropertyArtist];
        [updatedNowPlayingInfo setObject:[musicControlsInfo track] forKey:MPMediaItemPropertyTitle];
        [updatedNowPlayingInfo setObject:[musicControlsInfo album] forKey:MPMediaItemPropertyAlbumTitle];
        [updatedNowPlayingInfo setObject:duration forKey:MPMediaItemPropertyPlaybackDuration];
        [updatedNowPlayingInfo setObject:elapsed forKey:MPNowPlayingInfoPropertyElapsedPlaybackTime];
        [updatedNowPlayingInfo setObject:playbackRate forKey:MPNowPlayingInfoPropertyPlaybackRate];
        
        nowPlayingInfoCenter.nowPlayingInfo = updatedNowPlayingInfo;
    }];
}

- (void) updateIsPlaying: (CDVInvokedUrlCommand *) command {
    NSDictionary * musicControlsInfoDict = [command.arguments objectAtIndex:0];
    MusicControlsInfo * musicControlsInfo = [[MusicControlsInfo alloc] initWithDictionary:musicControlsInfoDict];
    NSNumber * playbackRate = [NSNumber numberWithBool:[musicControlsInfo isPlaying]];
    
    if (!NSClassFromString(@"MPNowPlayingInfoCenter")) {
        return;
    }

    MPNowPlayingInfoCenter * nowPlayingCenter = [MPNowPlayingInfoCenter defaultCenter];
    NSMutableDictionary * updatedNowPlayingInfo = [NSMutableDictionary dictionaryWithDictionary:nowPlayingCenter.nowPlayingInfo];
    
    [updatedNowPlayingInfo setObject:playbackRate forKey:MPNowPlayingInfoPropertyPlaybackRate];
    nowPlayingCenter.nowPlayingInfo = updatedNowPlayingInfo;
}

- (void) destroy: (CDVInvokedUrlCommand *) command {
    [self deregisterMusicControlsEventListener];
}

- (void) watch: (CDVInvokedUrlCommand *) command {
    [self setLatestEventCallbackId:command.callbackId];
    [self registerMusicControlsEventListener];
}

- (MPMediaItemArtwork *) createCoverArtwork: (NSString *) coverUri {
    UIImage * coverImage = nil;
    
    if (coverUri == nil) {
        return nil;
    }
    
    if ([coverUri hasPrefix:@"http://"] || [coverUri hasPrefix:@"https://"]) {
        NSURL * coverImageUrl = [NSURL URLWithString:coverUri];
        NSData * coverImageData = [NSData dataWithContentsOfURL: coverImageUrl];
        
        coverImage = [UIImage imageWithData: coverImageData];
    }
    else if ([coverUri hasPrefix:@"file://"]) {
        NSString * fullCoverImagePath = [coverUri stringByReplacingOccurrencesOfString:@"file://" withString:@""];
        
        if ([[NSFileManager defaultManager] fileExistsAtPath: fullCoverImagePath]) {
            coverImage = [[UIImage alloc] initWithContentsOfFile: fullCoverImagePath];
        }
    }
    else if (![coverUri isEqual:@""]) {
        NSString * baseCoverImagePath = [NSSearchPathForDirectoriesInDomains(NSDocumentDirectory, NSUserDomainMask, YES) objectAtIndex:0];
        NSString * fullCoverImagePath = [NSString stringWithFormat:@"%@%@", baseCoverImagePath, coverUri];
    
        if ([[NSFileManager defaultManager] fileExistsAtPath:fullCoverImagePath]) {
            coverImage = [UIImage imageNamed:fullCoverImagePath];
        }
    }
    else {
        coverImage = [UIImage imageNamed:@"none"];
    }
    
    return [self isCoverImageValid:coverImage] ? [[MPMediaItemArtwork alloc] initWithImage:coverImage] : nil;
}

- (bool) isCoverImageValid: (UIImage *) coverImage {
    return coverImage != nil && ([coverImage CIImage] != nil || [coverImage CGImage] != nil);
}

- (void) handleMusicControlsNotification: (NSNotification *) notification {
    UIEvent * receivedEvent = notification.object;
    
    if ([self latestEventCallbackId] == nil) {
        return;
    }
    
    if (receivedEvent.type == UIEventTypeRemoteControl) {
        NSString * action;
        
        switch (receivedEvent.subtype) {
            case UIEventSubtypeRemoteControlTogglePlayPause:
                action = @"music-controls-toggle-play-pause";
                break;
                
            case UIEventSubtypeRemoteControlPlay:
                action = @"music-controls-play";
                break;
                
            case UIEventSubtypeRemoteControlPause:
                action = @"music-controls-pause";
                break;
                
            case UIEventSubtypeRemoteControlPreviousTrack:
                action = @"music-controls-previous";
                break;
                
            case UIEventSubtypeRemoteControlNextTrack:
                action = @"music-controls-next";
                break;
                
            case UIEventSubtypeRemoteControlStop:
                action = @"music-controls-destroy";
                break;
                
            default:
                break;
        }
        
        CDVPluginResult * pluginResult = [CDVPluginResult resultWithStatus:CDVCommandStatus_OK messageAsString:action];
        [self.commandDelegate sendPluginResult:pluginResult callbackId:[self latestEventCallbackId]];
    }
}

- (void) registerMusicControlsEventListener {
    [[UIApplication sharedApplication] beginReceivingRemoteControlEvents];
    [[NSNotificationCenter defaultCenter] addObserver:self selector:@selector(handleMusicControlsNotification:) name:@"musicControlsEventNotification" object:nil];
}

- (void) deregisterMusicControlsEventListener {
    [[UIApplication sharedApplication] endReceivingRemoteControlEvents];
    [[NSNotificationCenter defaultCenter] removeObserver:self name:@"receivedEvent" object:nil];
    [self setLatestEventCallbackId:nil];
}

- (void) dealloc {
    [self deregisterMusicControlsEventListener];
}

@end
