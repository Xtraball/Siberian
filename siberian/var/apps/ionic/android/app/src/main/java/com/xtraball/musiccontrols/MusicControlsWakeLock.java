package com.xtraball.musiccontrols;

import android.annotation.SuppressLint;
import android.app.Notification;
import android.app.Service;
import android.content.Context;
import android.content.Intent;
import android.net.wifi.WifiManager;
import android.os.Binder;
import android.os.IBinder;
import android.os.PowerManager;
import android.support.annotation.Nullable;
import android.util.Log;

public class MusicControlsWakeLock extends Service {

    private final IBinder binder = new WakeLockBinder(this);

    @Override
    public int onStartCommand(Intent intent, int flags, int startId) {
        super.onStartCommand(intent, flags, startId);
        return Service.START_STICKY;
    }

    @Nullable
    @Override
    public IBinder onBind(Intent intent) {
        try {
            PowerManager powerManager = (PowerManager) this.getSystemService(Context.POWER_SERVICE);
            PowerManager.WakeLock wakeLock = powerManager.newWakeLock(PowerManager.PARTIAL_WAKE_LOCK, "xtraball:wakelock");
            wakeLock.acquire();

            @SuppressLint("WifiManagerLeak")
            WifiManager wMgr = (WifiManager) this.getSystemService(Context.WIFI_SERVICE);
            WifiManager.WifiLock wifiLock = wMgr.createWifiLock(WifiManager.WIFI_MODE_FULL, "xtraball:wifilock");
            wifiLock.acquire();

        } catch (Exception e) {
            e.printStackTrace();
        }

        return binder;
    }

    @Override
    public void onDestroy() {
        stopForeground(true);
    }

}
