package com.xtraball.musiccontrols;

import android.app.Activity;
import android.app.PendingIntent;
import android.content.*;
import android.graphics.Bitmap;
import android.graphics.BitmapFactory;
import android.media.AudioManager;
import android.net.Uri;
import android.os.Build;
import android.os.IBinder;
import android.os.PowerManager;
import android.support.v4.media.MediaMetadataCompat;
import android.support.v4.media.session.MediaSessionCompat;
import android.support.v4.media.session.PlaybackStateCompat;
import org.apache.cordova.CallbackContext;
import org.apache.cordova.CordovaInterface;
import org.apache.cordova.CordovaPlugin;
import org.apache.cordova.CordovaWebView;
import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.io.BufferedInputStream;
import java.io.File;
import java.io.FileInputStream;
import java.io.InputStream;
import java.net.HttpURLConnection;
import java.net.URL;

import static android.provider.Settings.ACTION_REQUEST_IGNORE_BATTERY_OPTIMIZATIONS;

public class MusicControls extends CordovaPlugin {
    private MusicControlsBroadcastReceiver mMessageReceiver;
    private MusicControlsNotification notification;
    private MediaSessionCompat mediaSessionCompat;
    private final int notificationID = 7824;
    private AudioManager mAudioManager;
    private PendingIntent mediaButtonPendingIntent;
    private boolean mediaButtonAccess = true;

    private Activity cordovaActivity;

    private MediaSessionCallback mMediaSessionCallback = new MediaSessionCallback();

    private void registerBroadcaster(MusicControlsBroadcastReceiver mMessageReceiver) {
        final Context context = this.cordova.getActivity().getApplicationContext();
        context.registerReceiver((BroadcastReceiver) mMessageReceiver, new IntentFilter("music-controls-previous"));
        context.registerReceiver((BroadcastReceiver) mMessageReceiver, new IntentFilter("music-controls-pause"));
        context.registerReceiver((BroadcastReceiver) mMessageReceiver, new IntentFilter("music-controls-play"));
        context.registerReceiver((BroadcastReceiver) mMessageReceiver, new IntentFilter("music-controls-next"));
        context.registerReceiver((BroadcastReceiver) mMessageReceiver, new IntentFilter("music-controls-media-button"));
        context.registerReceiver((BroadcastReceiver) mMessageReceiver, new IntentFilter("music-controls-destroy"));

        // Listen for headset plug/unplug
        context.registerReceiver((BroadcastReceiver) mMessageReceiver, new IntentFilter(Intent.ACTION_HEADSET_PLUG));
    }

    // Register pendingIntent for broacast
    public void registerMediaButtonEvent() {
        this.mediaSessionCompat.setMediaButtonReceiver(this.mediaButtonPendingIntent);
    }

    public void unregisterMediaButtonEvent() {
        this.mediaSessionCompat.setMediaButtonReceiver(null);
    }

    public void destroyPlayerNotification() {
        this.notification.destroy();
    }

    @Override
    public void initialize(CordovaInterface cordova, CordovaWebView webView) {
        super.initialize(cordova, webView);
        final Activity activity = this.cordova.getActivity();
        final Context context = activity.getApplicationContext();

        this.cordovaActivity = activity;

        this.notification = new MusicControlsNotification(activity, this.notificationID);
        this.mMessageReceiver = new MusicControlsBroadcastReceiver(this);
        this.registerBroadcaster(mMessageReceiver);


        this.mediaSessionCompat = new MediaSessionCompat(context, "cordova-music-controls-media-session", null, this.mediaButtonPendingIntent);
        this.mediaSessionCompat.setFlags(MediaSessionCompat.FLAG_HANDLES_MEDIA_BUTTONS | MediaSessionCompat.FLAG_HANDLES_TRANSPORT_CONTROLS);


        setMediaPlaybackState(PlaybackStateCompat.STATE_PAUSED);
        this.mediaSessionCompat.setActive(true);

        this.mediaSessionCompat.setCallback(this.mMediaSessionCallback);

        // Register media (headset) button event receiver
        try {
            this.mAudioManager = (AudioManager) context.getSystemService(Context.AUDIO_SERVICE);
            Intent headsetIntent = new Intent("music-controls-media-button");
            this.mediaButtonPendingIntent = PendingIntent.getBroadcast(context, 0, headsetIntent, PendingIntent.FLAG_UPDATE_CURRENT);
            this.registerMediaButtonEvent();
        } catch (Exception e) {
            this.mediaButtonAccess = false;
            e.printStackTrace();
        }

        // Notification Killer
        ServiceConnection mConnection = new ServiceConnection() {
            public void onServiceConnected(ComponentName className, IBinder binder) {
                ((KillBinder) binder).service.startService(new Intent(activity, MusicControlsNotificationKiller.class));
            }

            public void onServiceDisconnected(ComponentName className) {
            }
        };
        Intent startServiceIntent = new Intent(activity, MusicControlsNotificationKiller.class);
        startServiceIntent.putExtra("notificationID", this.notificationID);
        activity.bindService(startServiceIntent, mConnection, Context.BIND_AUTO_CREATE);
    }

    @Override
    public boolean execute(final String action, final JSONArray args, final CallbackContext callbackContext) throws JSONException {
        final Context context = this.cordova.getActivity().getApplicationContext();
        final Activity activity = this.cordova.getActivity();


        if (action.equals("create")) {
            final MusicControlsInfos infos = new MusicControlsInfos(args);
            final MediaMetadataCompat.Builder metadataBuilder = new MediaMetadataCompat.Builder();
            this.cordova.getThreadPool().execute(new Runnable() {
                public void run() {
                    notification.updateNotification(infos);

                    // track title
                    metadataBuilder.putString(MediaMetadataCompat.METADATA_KEY_TITLE, infos.track);
                    // artists
                    metadataBuilder.putString(MediaMetadataCompat.METADATA_KEY_ARTIST, infos.artist);
                    //album
                    metadataBuilder.putString(MediaMetadataCompat.METADATA_KEY_ALBUM, infos.album);

                    Bitmap art = getBitmapCover(infos.cover);
                    if (art != null) {
                        metadataBuilder.putBitmap(MediaMetadataCompat.METADATA_KEY_ALBUM_ART, art);
                        metadataBuilder.putBitmap(MediaMetadataCompat.METADATA_KEY_ART, art);

                    }

                    mediaSessionCompat.setMetadata(metadataBuilder.build());

                    if (infos.isPlaying) {
                        setMediaPlaybackState(PlaybackStateCompat.STATE_PLAYING);
                    } else {
                        setMediaPlaybackState(PlaybackStateCompat.STATE_PAUSED);
                    }

                    callbackContext.success("success");
                }
            });
        } else if (action.equals("updateIsPlaying")) {
            cordova.getActivity().runOnUiThread(new Runnable() {
                public void run() {
                    try {
                        final JSONObject params = args.getJSONObject(0);
                        final boolean isPlaying = params.getBoolean("isPlaying");
                        notification.updateIsPlaying(isPlaying);

                        if (isPlaying) {
                            setMediaPlaybackState(PlaybackStateCompat.STATE_PLAYING);
                        } else {
                            setMediaPlaybackState(PlaybackStateCompat.STATE_PAUSED);
                        }

                        callbackContext.success("success");
                    } catch (Exception e) {
                        callbackContext.error(e.getMessage());
                    }
                }
            });
        } else if (action.equals("updateDismissable")) {
            cordova.getActivity().runOnUiThread(new Runnable() {
                public void run() {
                    try {
                        final JSONObject params = args.getJSONObject(0);
                        final boolean dismissable = params.getBoolean("dismissable");
                        notification.updateDismissable(dismissable);
                        callbackContext.success("success");
                    } catch (Exception e) {
                        callbackContext.error(e.getMessage());
                    }
                }
            });
        } else if (action.equals("destroy")) {
            cordova.getActivity().runOnUiThread(new Runnable() {
                public void run() {
                    try {
                        notification.destroy();
                        mMessageReceiver.stopListening();
                        callbackContext.success("success");
                    } catch (Exception e) {
                        callbackContext.error(e.getMessage());
                    }
                }
            });
        } else if (action.equals("watch")) {
            this.registerMediaButtonEvent();
            this.cordova.getThreadPool().execute(new Runnable() {
                public void run() {
                    mMediaSessionCallback.setCallback(callbackContext);
                    mMessageReceiver.setCallback(callbackContext);
                }
            });
        } else if (action.equals("disableBatteryOptimization")) {
            String packageName = activity.getPackageName();
            PowerManager powerManager = (PowerManager) context.getSystemService(Context.POWER_SERVICE);
            if (powerManager.isIgnoringBatteryOptimizations(packageName)) {
                return false;
            }
            if (Build.VERSION.SDK_INT < Build.VERSION_CODES.M) {
                return false;
            }
            Intent intent = new Intent();
            intent.setAction(ACTION_REQUEST_IGNORE_BATTERY_OPTIMIZATIONS);
            intent.setData(Uri.parse("package:" + packageName));
            activity.startActivity(intent);

            callbackContext.success("success");
        }
        return true;
    }

    @Override
    public void onDestroy() {
        this.notification.destroy();
        this.mMessageReceiver.stopListening();
        this.unregisterMediaButtonEvent();
        super.onDestroy();
    }

    @Override
    public void onReset() {
        onDestroy();
        super.onReset();
    }

    private void setMediaPlaybackState(int state) {
        PlaybackStateCompat.Builder playbackstateBuilder = new PlaybackStateCompat.Builder();
        if (state == PlaybackStateCompat.STATE_PLAYING) {
            playbackstateBuilder.setActions(PlaybackStateCompat.ACTION_PLAY_PAUSE | PlaybackStateCompat.ACTION_PAUSE | PlaybackStateCompat.ACTION_SKIP_TO_NEXT | PlaybackStateCompat.ACTION_SKIP_TO_PREVIOUS |
                    PlaybackStateCompat.ACTION_PLAY_FROM_MEDIA_ID |
                    PlaybackStateCompat.ACTION_PLAY_FROM_SEARCH);
            playbackstateBuilder.setState(state, PlaybackStateCompat.PLAYBACK_POSITION_UNKNOWN, 1.0f);
        } else {
            playbackstateBuilder.setActions(PlaybackStateCompat.ACTION_PLAY_PAUSE | PlaybackStateCompat.ACTION_PLAY | PlaybackStateCompat.ACTION_SKIP_TO_NEXT | PlaybackStateCompat.ACTION_SKIP_TO_PREVIOUS |
                    PlaybackStateCompat.ACTION_PLAY_FROM_MEDIA_ID |
                    PlaybackStateCompat.ACTION_PLAY_FROM_SEARCH);
            playbackstateBuilder.setState(state, PlaybackStateCompat.PLAYBACK_POSITION_UNKNOWN, 0);
        }
        this.mediaSessionCompat.setPlaybackState(playbackstateBuilder.build());
    }

    // Get image from url
    private Bitmap getBitmapCover(String coverURL) {
        try {
            if (coverURL.matches("^(https?|ftp)://.*$")) {
                // Remote image
                return getBitmapFromURL(coverURL);
            } else {
                // Local image
                return getBitmapFromLocal(coverURL);
            }
        } catch (Exception ex) {
            ex.printStackTrace();
            return null;
        }
    }

    // get Local image
    private Bitmap getBitmapFromLocal(String localURL) {
        try {
            Uri uri = Uri.parse(localURL);
            File file = new File(uri.getPath());
            FileInputStream fileStream = new FileInputStream(file);
            BufferedInputStream buf = new BufferedInputStream(fileStream);
            Bitmap myBitmap = BitmapFactory.decodeStream(buf);
            buf.close();
            return myBitmap;
        } catch (Exception ex) {
            try {
                InputStream fileStream = cordovaActivity.getAssets().open("www/" + localURL);
                BufferedInputStream buf = new BufferedInputStream(fileStream);
                Bitmap myBitmap = BitmapFactory.decodeStream(buf);
                buf.close();
                return myBitmap;
            } catch (Exception ex2) {
                ex.printStackTrace();
                ex2.printStackTrace();
                return null;
            }
        }
    }

    // get Remote image
    private Bitmap getBitmapFromURL(String strURL) {
        try {
            URL url = new URL(strURL);
            HttpURLConnection connection = (HttpURLConnection) url.openConnection();
            connection.setDoInput(true);
            connection.connect();
            InputStream input = connection.getInputStream();
            Bitmap myBitmap = BitmapFactory.decodeStream(input);
            return myBitmap;
        } catch (Exception ex) {
            ex.printStackTrace();
            return null;
        }
    }
}
