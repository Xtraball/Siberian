package com.xtraball.musiccontrols;

import android.app.NotificationManager;
import android.app.Service;
import android.content.Intent;
import android.os.IBinder;

public class MusicControlsNotificationKiller extends Service {

    private static int NOTIFICATION_ID;
    private NotificationManager mNM;
    private final IBinder mBinder = new KillBinder(this);

    @Override
    public IBinder onBind(Intent intent) {
        this.NOTIFICATION_ID = intent.getIntExtra("notificationID", 1);
        return mBinder;
    }

    @Override
    public int onStartCommand(Intent intent, int flags, int startId) {
        return Service.START_STICKY;
    }

    @Override
    public void onCreate() {
        mNM = (NotificationManager) getSystemService(NOTIFICATION_SERVICE);
        mNM.cancel(NOTIFICATION_ID);
    }

    @Override
    public void onDestroy() {
        mNM = (NotificationManager) getSystemService(NOTIFICATION_SERVICE);
        mNM.cancel(NOTIFICATION_ID);
    }

}
