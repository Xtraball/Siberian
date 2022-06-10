package com.cowbell.cordova.geofence;

import android.app.NotificationManager;
import android.app.PendingIntent;
import android.content.Context;
import android.content.Intent;
import android.media.Ringtone;
import android.media.RingtoneManager;
import android.net.Uri;
import androidx.core.app.NotificationCompat;
import androidx.core.app.TaskStackBuilder;
import android.util.Log;
import android.os.Build;

public class GeoNotificationNotifier {
    private NotificationManager notificationManager;
    private Context context;
    private Logger logger;
    private int PendingIntentFlag = 0;

    public GeoNotificationNotifier(NotificationManager notificationManager, Context context) {
        this.notificationManager = notificationManager;
        this.context = context;
        this.logger = Logger.getLogger();
    }

    public void notify(Notification notification) {
        if (Build.VERSION.SDK_INT >= 31) {
            PendingIntentFlag = PendingIntent.FLAG_MUTABLE;
        }

        notification.setContext(context);
        Uri notificationSound = RingtoneManager.getDefaultUri(RingtoneManager.TYPE_NOTIFICATION);
        NotificationCompat.Builder mBuilder = new NotificationCompat.Builder(context)
            .setVibrate(notification.getVibrate())
            .setSmallIcon(notification.getSmallIcon())
            .setLargeIcon(notification.getLargeIcon())
            .setAutoCancel(true)
            .setContentTitle(notification.getTitle())
            .setContentText(notification.getText())
            .setSound(notificationSound);

        if (notification.openAppOnClick) {
            String packageName = context.getPackageName();
            Intent resultIntent = context.getPackageManager()
                .getLaunchIntentForPackage(packageName);

            if (notification.data != null) {
                resultIntent.putExtra("geofence.notification.data", notification.getDataJson());
            }

            TaskStackBuilder stackBuilder = TaskStackBuilder.create(context);
            stackBuilder.addNextIntent(resultIntent);
            PendingIntent resultPendingIntent = stackBuilder.getPendingIntent(
                notification.id, PendingIntentFlag | PendingIntent.FLAG_UPDATE_CURRENT);
            mBuilder.setContentIntent(resultPendingIntent);
        }
        notificationManager.notify(notification.id, mBuilder.build());
        logger.log(Log.DEBUG, notification.toString());
    }
}
