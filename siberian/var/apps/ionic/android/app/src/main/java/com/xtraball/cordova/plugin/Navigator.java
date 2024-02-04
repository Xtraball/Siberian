package com.xtraball.cordova.plugin;

import org.apache.cordova.CordovaPlugin;
import org.apache.cordova.CallbackContext;

import android.content.Intent;

import org.apache.cordova.LOG;
import org.json.JSONException;
import org.json.JSONArray;

import android.content.pm.PackageInfo;
import android.content.pm.PackageManager;
import android.net.Uri;

import java.lang.String;

/**
 *
 */
public class Navigator extends CordovaPlugin {

    /**
     *
     */
    String CLASS_NAME = this.getClass().getName();

    /**
     * @param urlToIntent
     */
    private void openIntent(String urlToIntent) {
        Intent intent = new Intent(Intent.ACTION_VIEW, Uri.parse(urlToIntent));
        this.cordova.getActivity().startActivity(intent);
    }

    @Override
    /**
     *
     */
    public boolean execute(String action, JSONArray data, CallbackContext callbackContext) throws JSONException {
        if (action.equals("navigate")) {
            String toLat = data.getString(0);
            String toLng = data.getString(1);

            openUrlIntentByApplication(toLat, toLng);

            return true;
        } else {
            return false;
        }
    }

    /**
     * @param toLat
     * @param toLng
     */
    private void openUrlIntentByApplication(String toLat, String toLng) {
        String wazePackage = "com.waze";
        String mapsPackage = "com.google.android.apps.maps";

        PackageManager packageManager = cordova.getActivity().getPackageManager();
        boolean isWazeInstalled = isPackageInstalled(wazePackage, packageManager);
        boolean isMapsInstalled = isPackageInstalled(mapsPackage, packageManager);

        Intent intentWaze = new Intent(Intent.ACTION_VIEW, Uri.parse("waze://?ll=" + toLat + "," + toLng + "&navigate=yes"));
        intentWaze.setPackage(wazePackage);

        Intent intentGoogleNav = new Intent(Intent.ACTION_VIEW, Uri.parse("google.navigation:q=" + toLat + "," + toLng));
        intentGoogleNav.setPackage(mapsPackage);

        Intent chooserIntent;

        if (isWazeInstalled && isMapsInstalled) {
            chooserIntent = Intent.createChooser(intentGoogleNav, null);
            chooserIntent.putExtra(Intent.EXTRA_INITIAL_INTENTS, new Intent[]{intentWaze});
        } else if (isWazeInstalled) {
            chooserIntent = Intent.createChooser(intentWaze, null);

            Intent installMapsIntent = new Intent(Intent.ACTION_VIEW, Uri.parse("market://details?id=" + mapsPackage));
            chooserIntent.putExtra(Intent.EXTRA_INITIAL_INTENTS, new Intent[]{installMapsIntent});
        } else if (isMapsInstalled) {
            chooserIntent = Intent.createChooser(intentGoogleNav, null);

            Intent installWazeIntent = new Intent(Intent.ACTION_VIEW, Uri.parse("market://details?id=" + wazePackage));
            chooserIntent.putExtra(Intent.EXTRA_INITIAL_INTENTS, new Intent[]{installWazeIntent});
        } else {
            Intent installWazeIntent = new Intent(Intent.ACTION_VIEW, Uri.parse("market://details?id=" + wazePackage));
            Intent installMapsIntent = new Intent(Intent.ACTION_VIEW, Uri.parse("market://details?id=" + mapsPackage));

            chooserIntent = Intent.createChooser(installWazeIntent, "Install Maps App");
            chooserIntent.putExtra(Intent.EXTRA_INITIAL_INTENTS, new Intent[]{installMapsIntent});
        }

        cordova.getActivity().startActivity(chooserIntent);
    }

    private boolean isPackageInstalled(String packageName, PackageManager packageManager) {
        try {
            packageManager.getPackageInfo(packageName, 0);
            return true;
        } catch (PackageManager.NameNotFoundException e) {
            return false;
        }
    }

}