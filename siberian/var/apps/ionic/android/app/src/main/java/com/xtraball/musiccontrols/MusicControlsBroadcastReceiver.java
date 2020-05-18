package com.xtraball.musiccontrols;

import android.content.BroadcastReceiver;
import android.content.Context;
import android.content.Intent;
import android.view.KeyEvent;
import org.apache.cordova.CallbackContext;

public class MusicControlsBroadcastReceiver extends BroadcastReceiver {
    private CallbackContext cb;
    private MusicControls musicControls;


    public MusicControlsBroadcastReceiver(MusicControls musicControls) {
        this.musicControls = musicControls;
    }

    public void setCallback(CallbackContext cb) {
        this.cb = cb;
    }

    public void stopListening() {
        if (this.cb != null) {
            this.cb.success("{\"message\": \"music-controls-stop-listening\" }");
            this.cb = null;
        }
    }

    @Override
    public void onReceive(Context context, Intent intent) {

        if (this.cb != null) {
            String message = intent.getAction();

            if (message.equals(Intent.ACTION_HEADSET_PLUG)) {
                // Headphone plug/unplug
                int state = intent.getIntExtra("state", -1);
                switch (state) {
                    case 0:
                        this.cb.success("{\"message\": \"music-controls-headset-unplugged\"}");
                        this.cb = null;
                        this.musicControls.unregisterMediaButtonEvent();
                        break;
                    case 1:
                        this.cb.success("{\"message\": \"music-controls-headset-plugged\"}");
                        this.cb = null;
                        this.musicControls.registerMediaButtonEvent();
                        break;
                    default:
                        break;
                }
            } else if (message.equals("music-controls-media-button")) {
                // Media button
                KeyEvent event = (KeyEvent) intent.getParcelableExtra(Intent.EXTRA_KEY_EVENT);
                if (event.getAction() == KeyEvent.ACTION_DOWN) {

                    int keyCode = event.getKeyCode();
                    switch (keyCode) {
                        case KeyEvent.KEYCODE_MEDIA_NEXT:
                            this.cb.success("{\"message\": \"music-controls-media-button-next\"}");
                            break;
                        case KeyEvent.KEYCODE_MEDIA_PAUSE:
                            this.cb.success("{\"message\": \"music-controls-media-button-pause\"}");
                            break;
                        case KeyEvent.KEYCODE_MEDIA_PLAY:
                            this.cb.success("{\"message\": \"music-controls-media-button-play\"}");
                            break;
                        case KeyEvent.KEYCODE_MEDIA_PLAY_PAUSE:
                            this.cb.success("{\"message\": \"music-controls-media-button-play-pause\"}");
                            break;
                        case KeyEvent.KEYCODE_MEDIA_PREVIOUS:
                            this.cb.success("{\"message\": \"music-controls-media-button-previous\"}");
                            break;
                        case KeyEvent.KEYCODE_MEDIA_STOP:
                            this.cb.success("{\"message\": \"music-controls-media-button-stop\"}");
                            break;
                        case KeyEvent.KEYCODE_MEDIA_FAST_FORWARD:
                            this.cb.success("{\"message\": \"music-controls-media-button-fast-forward\"}");
                            break;
                        case KeyEvent.KEYCODE_MEDIA_REWIND:
                            this.cb.success("{\"message\": \"music-controls-media-button-rewind\"}");
                            break;
                        case KeyEvent.KEYCODE_MEDIA_SKIP_BACKWARD:
                            this.cb.success("{\"message\": \"music-controls-media-button-skip-backward\"}");
                            break;
                        case KeyEvent.KEYCODE_MEDIA_SKIP_FORWARD:
                            this.cb.success("{\"message\": \"music-controls-media-button-skip-forward\"}");
                            break;
                        case KeyEvent.KEYCODE_MEDIA_STEP_BACKWARD:
                            this.cb.success("{\"message\": \"music-controls-media-button-step-backward\"}");
                            break;
                        case KeyEvent.KEYCODE_MEDIA_STEP_FORWARD:
                            this.cb.success("{\"message\": \"music-controls-media-button-step-forward\"}");
                            break;
                        case KeyEvent.KEYCODE_META_LEFT:
                            this.cb.success("{\"message\": \"music-controls-media-button-meta-left\"}");
                            break;
                        case KeyEvent.KEYCODE_META_RIGHT:
                            this.cb.success("{\"message\": \"music-controls-media-button-meta-right\"}");
                            break;
                        case KeyEvent.KEYCODE_MUSIC:
                            this.cb.success("{\"message\": \"music-controls-media-button-music\"}");
                            break;
                        case KeyEvent.KEYCODE_VOLUME_UP:
                            this.cb.success("{\"message\": \"music-controls-media-button-volume-up\"}");
                            break;
                        case KeyEvent.KEYCODE_VOLUME_DOWN:
                            this.cb.success("{\"message\": \"music-controls-media-button-volume-down\"}");
                            break;
                        case KeyEvent.KEYCODE_VOLUME_MUTE:
                            this.cb.success("{\"message\": \"music-controls-media-button-volume-mute\"}");
                            break;
                        case KeyEvent.KEYCODE_HEADSETHOOK:
                            this.cb.success("{\"message\": \"music-controls-media-button-headset-hook\"}");
                            break;
                        default:
                            this.cb.success("{\"message\": \"" + message + "\"}");
                            break;
                    }
                    this.cb = null;
                }
            } else if (message.equals("music-controls-destroy")) {
                // Close Button
                this.cb.success("{\"message\": \"music-controls-destroy\"}");
                this.cb = null;
                this.musicControls.destroyPlayerNotification();
            } else {
                this.cb.success("{\"message\": \"" + message + "\"}");
                this.cb = null;
            }


        }

    }
}
