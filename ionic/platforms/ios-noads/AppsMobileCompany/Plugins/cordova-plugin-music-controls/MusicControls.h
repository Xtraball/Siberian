//
// MusicControls.h
// Music Controls Cordova Plugin
//
// Created by Juan Gonzalez on 12/16/16.
//

#ifndef MusicControls_h
#define MusicControls_h

#import <Cordova/CDVPlugin.h>
#import <MediaPlayer/MediaPlayer.h>
#import <MediaPlayer/MPNowPlayingInfoCenter.h>
#import <MediaPlayer/MPMediaItem.h>

@interface MusicControls : CDVPlugin {}

@property NSString * latestEventCallbackId;

- (void) create: (CDVInvokedUrlCommand *) command;
- (void) updateIsPlaying: (CDVInvokedUrlCommand *) command;
- (void) destroy: (CDVInvokedUrlCommand *) command;
- (void) watch: (CDVInvokedUrlCommand *) command;
- (MPMediaItemArtwork *) createCoverArtwork: (NSString *) coverUri;
- (bool) isCoverImageValid: (UIImage *) image;
- (void) handleMusicControlsNotification:(NSNotification *) notification;
- (void) registerMusicControlsEventListener;
- (void) deregisterMusicControlsEventListener;

@end

#endif /* MusicControls_h */
