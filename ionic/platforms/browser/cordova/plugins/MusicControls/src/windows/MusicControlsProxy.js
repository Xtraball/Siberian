//  Copyright © 2015 filfat Studios AB

/* global Windows, cordova */
var mc;
var onUpdate = function (event) { };

var onKey = function (event) {
    var Button = Windows.Media.SystemMediaTransportControlsButton;
    switch (event.button) {
        case Button.play:
            onUpdate("{\"message\": \"music-controls-play\"}");
            break;
        case Button.pause:
            onUpdate("{\"message\": \"music-controls-pause\"}");
            break;
        case Button.stop:
            onUpdate("{\"message\": \"music-controls-stop\"}");
            break;
        case Button.next:
            onUpdate("{\"message\": \"music-controls-next\"}");
            break;
        case Button.previous:
            onUpdate("{\"message\": \"music-controls-previous\"}");
            break;
    }
};

cordova.commandProxy.add("MusicControls",{
    create: function (successCallback, errorCallback, datas) {
        var data = datas[0];
        mc = Windows.Media.SystemMediaTransportControls.getForCurrentView();

        //Handle events
        mc.addEventListener("buttonpressed", onKey, false);

        //Set data
        mc.isEnabled = true;
        mc.isPlayEnabled = true;
        mc.isPauseEnabled = true;
        mc.isNextEnabled = true;
        mc.isStopEnabled = true;
        mc.isPreviousEnabled = true;
        mc.displayUpdater.type = Windows.Media.MediaPlaybackType.music;

        //Is Playing
        if (data.isPlaying)
            mc.playbackStatus = Windows.Media.MediaPlaybackStatus.playing;
        else
            mc.playbackStatus = Windows.Media.MediaPlaybackStatus.stopped;


		if (!/^(f|ht)tps?:\/\//i.test(data.cover)) {
		    var cover = new Windows.Foundation.Uri("ms-appdata://" + data.cover);
		    mc.displayUpdater.thumbnail = cover;
		} else {
		    //TODO: Store image locally
		}
		mc.displayUpdater.musicProperties.albumArtist = data.artist;
		mc.displayUpdater.musicProperties.albumTitle = (data.album === undefined ? ' ' : data.album);
		mc.displayUpdater.musicProperties.artist = data.artist;
		mc.displayUpdater.musicProperties.title = data.track;
		mc.displayUpdater.update();
    },
    destroy: function (successCallback, errorCallback, datas) {
        //Remove events
        mc.displayUpdater.clearAll();
    },
    watch: function (_onUpdate, errorCallback, datas) {
        //Set callback
	    onUpdate = _onUpdate;
    },
    updateIsPlaying: function (successCallback, errorCallback, par) {
        if (par[0].isPlaying)
            mc.playbackStatus = Windows.Media.MediaPlaybackStatus.playing;
        else
            mc.playbackStatus = Windows.Media.MediaPlaybackStatus.stopped;
        mc.displayUpdater.update();
    }
});
