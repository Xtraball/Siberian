package com.xtraball.musiccontrols;

import android.app.Notification;
import android.app.Service;
import android.os.Binder;

public class WakeLockBinder extends Binder {
    public final Service service;

    public WakeLockBinder(Service service) {
        this.service = service;
    }
}

