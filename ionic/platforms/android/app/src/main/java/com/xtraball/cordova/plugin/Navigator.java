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

        if (verifyAplicationIsInstalled(wazePackage) || verifyAplicationIsInstalled(mapsPackage)) {
            String url = "waze://?ll=" + toLat + ", " + toLng + "&navigate=yes";
            Intent intentWaze = new Intent(Intent.ACTION_VIEW, Uri.parse(url));
            intentWaze.setPackage("com.waze");

            String uriGoogle = "google.navigation:q=" + toLat + "," + toLng;
            Intent intentGoogleNav = new Intent(Intent.ACTION_VIEW, Uri.parse(uriGoogle));
            intentGoogleNav.setPackage("com.google.android.apps.maps");

            Intent chooserIntent = Intent.createChooser(intentGoogleNav, null);
            Intent[] arr = {intentWaze};
            chooserIntent.putExtra(Intent.EXTRA_INITIAL_INTENTS, arr);
            cordova.getActivity().startActivity(chooserIntent);
        } else {
            openIntent("market://details?id=com.waze");
        }
    }

    /**
     * @param stringPackage
     * @return
     */
    private Boolean verifyAplicationIsInstalled(String stringPackage) {
        try {
            PackageManager application = this.cordova.getActivity().getPackageManager();
            PackageInfo info = application.getPackageInfo(stringPackage, PackageManager.GET_ACTIVITIES);

            return info.packageName.equals(stringPackage);
        } catch (PackageManager.NameNotFoundException e) {
            LOG.e(CLASS_NAME, e.getMessage());
            return false;
        }
    }
}