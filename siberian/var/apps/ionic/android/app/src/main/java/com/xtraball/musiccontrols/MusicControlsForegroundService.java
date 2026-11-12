package com.xtraball.musiccontrols;

import android.app.Notification;
import android.app.Service;
import android.content.Context;
import android.content.Intent;
import android.content.pm.ServiceInfo;
import android.os.IBinder;

import androidx.core.app.ServiceCompat;
import androidx.core.content.ContextCompat;

/**
 * Keeps Siberian's audio/radio playback alive in the background on modern Android.
 *
 * Without a foreground Service of type "mediaPlayback", Android throttles network I/O and can
 * freeze or kill a backgrounded process (more aggressively from Android 14 on, and already
 * unpredictably under Doze/App Standby or an OEM battery manager before that), which stops a
 * streaming MediaPlayer even though it was never told to stop. See #862 and #966.
 *
 * This service holds no state of its own and builds nothing: it only republishes the same
 * Notification that MusicControlsNotification already builds for the lock-screen/notification
 * controls, so the one piece of UI the user sees is unchanged. It is started only while
 * MusicControlsNotification is showing an "isPlaying" notification (MusicControlsNotification's
 * publish()) and stopped the moment playback pauses, stops or is destroyed - an app that never
 * creates a MusicControls notification (no Radio/Music feature) never starts it.
 */
public class MusicControlsForegroundService extends Service {

    private static final int DEFAULT_NOTIFICATION_ID = 7824;

    /**
     * Same-process hand-off only, deliberately not carried as an Intent extra: starting this
     * Service still crosses a Binder call to the system process to resolve/launch it, and a
     * Notification's cover-art Bitmap can be large enough to blow the Binder transaction size
     * limit (TransactionTooLargeException) if it travels as an Intent extra instead of a plain
     * in-process field.
     */
    private static volatile Notification pendingNotification;
    private static volatile int pendingNotificationId = DEFAULT_NOTIFICATION_ID;

    public static void start(Context context, int notificationId, Notification notification) {
        pendingNotificationId = notificationId;
        pendingNotification = notification;
        ContextCompat.startForegroundService(context, new Intent(context, MusicControlsForegroundService.class));
    }

    public static void stop(Context context) {
        pendingNotification = null;
        context.stopService(new Intent(context, MusicControlsForegroundService.class));
    }

    @Override
    public int onStartCommand(Intent intent, int flags, int startId) {
        Notification notification = pendingNotification;
        if (notification == null) {
            // stop() raced us, or the OS restarted the service with nothing queued: a foreground
            // service that never calls startForeground() is killed by the system within seconds,
            // so there is nothing useful to do but let it go.
            stopSelf();
            return START_NOT_STICKY;
        }

        ServiceCompat.startForeground(
            this,
            pendingNotificationId,
            notification,
            ServiceInfo.FOREGROUND_SERVICE_TYPE_MEDIA_PLAYBACK
        );

        // Not sticky: MusicControlsNotification is the source of truth for what should be
        // playing and calls start() again on every state change, so the OS should never
        // resurrect this service on its own after it was killed.
        return START_NOT_STICKY;
    }

    @Override
    public IBinder onBind(Intent intent) {
        return null;
    }
}
