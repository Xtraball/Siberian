# Cordova Music Controls Plugin

<img src='https://imgur.com/fh3ACOq.png' width='564' height='342'>

Music controls for Cordova applications. Display a 'media' notification with play/pause, previous, next buttons, allowing the user to control the play. Handle also headset event (plug, unplug, headset button).

## Supported platforms
- Android (4.1+)
- Windows (10+, by [filfat](https://github.com/filfat))
- iOS 8+ (by [0505gonzalez](https://github.com/0505gonzalez))

## Installation
`cordova plugin add https://github.com/homerours/cordova-music-controls-plugin`

## Methods
- Create the media controls:
```javascript
MusicControls.create({
    track       : 'Time is Running Out',		// optional, default : ''
	artist      : 'Muse',						// optional, default : ''
    cover       : 'albums/absolution.jpg',		// optional, default : nothing
	// cover can be a local path (use fullpath 'file:///storage/emulated/...', or only 'my_image.jpg' if my_image.jpg is in the www folder of your app)
	//			 or a remote url ('http://...', 'https://...', 'ftp://...')
	isPlaying   : true,							// optional, default : true
	dismissable : true,							// optional, default : false

	// hide previous/next/close buttons:
	hasPrev   : false,		// show previous button, optional, default: true
	hasNext   : false,		// show next button, optional, default: true
	hasClose  : true,		// show close button, optional, default: false

	// iOS only, optional
	album       : 'Absolution',     // optional, default: ''
	duration : 60, // optional, default: 0
	elapsed : 10, // optional, default: 0
  	hasSkipForward : true, //optional, default: false. true value overrides hasNext.
  	hasSkipBackward : true, //optional, default: false. true value overrides hasPrev.
  	skipForwardInterval : 15, //optional. default: 0.
	skipBackwardInterval : 15, //optional. default: 0.
	hasScrubbing : false, //optional. default to false. Enable scrubbing from control center progress bar 

	// Android only, optional
	// text displayed in the status bar when the notification (and the ticker) are updated
	ticker	  : 'Now playing "Time is Running Out"',
	//All icons default to their built-in android equivalents
	//The supplied drawable name, e.g. 'media_play', is the name of a drawable found under android/res/drawable* folders
	playIcon: 'media_play',
	pauseIcon: 'media_pause',
	prevIcon: 'media_prev',
	nextIcon: 'media_next',
	closeIcon: 'media_close',
	notificationIcon: 'notification'
}, onSuccess, onError);
```

- Destroy the media controller:
```javascript
MusicControls.destroy(onSuccess, onError);
```

- Subscribe events to the media controller:
```javascript
function events(action) {

  const message = JSON.parse(action).message;
	switch(message) {
		case 'music-controls-next':
			// Do something
			break;
		case 'music-controls-previous':
			// Do something
			break;
		case 'music-controls-pause':
			// Do something
			break;
		case 'music-controls-play':
			// Do something
			break;
		case 'music-controls-destroy':
			// Do something
			break;

		// External controls (iOS only)
    	case 'music-controls-toggle-play-pause' :
			// Do something
			break;
    	case 'music-controls-seek-to':
			const seekToInSeconds = JSON.parse(action).position;
			MusicControls.updateElapsed({
				elapsed: seekToInSeconds,
				isPlaying: true
			});
			// Do something
			break;

		// Headset events (Android only)
		// All media button events are listed below
		case 'music-controls-media-button' :
			// Do something
			break;
		case 'music-controls-headset-unplugged':
			// Do something
			break;
		case 'music-controls-headset-plugged':
			// Do something
			break;
		default:
			break;
	}
}

// Register callback
MusicControls.subscribe(events);

// Start listening for events
// The plugin will run the events function each time an event is fired
MusicControls.listen();
```

- Toggle play/pause:
```javascript
MusicControls.updateIsPlaying(true); // toggle the play/pause notification button
MusicControls.updateDismissable(true);
```

- iOS Specific Events:
Allows you to listen for iOS events fired from the scrubber in control center.
```javascript
MusicControls.updateElapsed({
	elapsed: 208, // seconds
	isPlaying: true
});
```

## List of media button events 
- Default:
```javascript
'music-controls-media-button'
```

- Android only:
```javascript
'music-controls-media-button-next', 'music-controls-media-button-pause', 'music-controls-media-button-play',
'music-controls-media-button-play-pause', 'music-controls-media-button-previous', 'music-controls-media-button-stop',
'music-controls-media-button-fast-forward', 'music-controls-media-button-rewind', 'music-controls-media-button-skip-backward',
'music-controls-media-button-skip-forward', 'music-controls-media-button-step-backward', 'music-controls-media-button-step-forward',
'music-controls-media-button-meta-left', 'music-controls-media-button-meta-right', 'music-controls-media-button-music',
'music-controls-media-button-volume-up', 'music-controls-media-button-volume-down', 'music-controls-media-button-volume-mute',
'music-controls-media-button-headset-hook'
```

- iOS Only:
```javascript
'music-controls-skip-forward', 'music-controls-skip-backward'
```

## Contributing

1. Fork it
2. Create your feature branch (`git checkout -b my-new-feature`)
3. Commit your changes (`git commit -am 'Add some feature'`)
4. Push to the branch (`git push origin my-new-feature`)
5. Create new Pull Request
