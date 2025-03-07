package com.onesignal.cordova;

import android.util.Log;
import org.json.JSONObject;

import com.onesignal.notifications.IActionButton;
import com.onesignal.notifications.IDisplayableMutableNotification;
import com.onesignal.notifications.INotificationReceivedEvent;
import com.onesignal.notifications.INotificationServiceExtension;

//@Keep // Keep is required to prevent minification from renaming or removing your class
public class NotificationServiceExtension implements INotificationServiceExtension {

    @Override
    public void onNotificationReceived(INotificationReceivedEvent event) {
        IDisplayableMutableNotification notification = event.getNotification();
        JSONObject data = notification.getAdditionalData();

        if (notification.getActionButtons() != null) {
            for (IActionButton button : notification.getActionButtons()) {
                // you can modify your action buttons here
            }
        }

        Log.i("OneSignalServiceExtension", "Received Notification Data: " + data);

        // this is an example of how to modify the notification by changing the background color to blue
        //notification.setExtender(builder -> builder.setColor(0xFF0000FF));

        //If you need to perform an async action or stop the payload from being shown automatically,
        //use event.preventDefault(). Using event.notification.display() will show this message again.
        event.preventDefault();
    }
}