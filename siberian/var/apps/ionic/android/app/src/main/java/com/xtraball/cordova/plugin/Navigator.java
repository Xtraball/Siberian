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
 * Navigator plugin for handling map navigation intents
 */
public class Navigator extends CordovaPlugin {

    /**
     * Class name for logging
     */
    String CLASS_NAME = this.getClass().getName();

    /**
     * Opens a URL in an external app
     * @param urlToIntent URL to open
     */
    private void openIntent(String urlToIntent) {
        Intent intent = new Intent(Intent.ACTION_VIEW, Uri.parse(urlToIntent));
        this.cordova.getActivity().startActivity(intent);
    }

    @Override
    /**
     * Executes the requested action
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
     * Opens navigation to the specified coordinates using available map applications
     * @param toLat Destination latitude
     * @param toLng Destination longitude
     */
    private void openUrlIntentByApplication(String toLat, String toLng) {
        String wazePackage = "com.waze";
        String mapsPackage = "com.google.android.apps.maps";

        PackageManager packageManager = cordova.getActivity().getPackageManager();
        boolean isWazeInstalled = isPackageInstalled(wazePackage, packageManager);
        boolean isMapsInstalled = isPackageInstalled(mapsPackage, packageManager);

        if (isWazeInstalled || isMapsInstalled) {
            // At least one map app is installed
            Intent intentWaze = null;
            if (isWazeInstalled) {
                intentWaze = new Intent(Intent.ACTION_VIEW, Uri.parse("waze://?ll=" + toLat + "," + toLng + "&navigate=yes"));
                intentWaze.setPackage(wazePackage);
            }

            Intent intentGoogleNav = null;
            if (isMapsInstalled) {
                intentGoogleNav = new Intent(Intent.ACTION_VIEW, Uri.parse("google.navigation:q=" + toLat + "," + toLng));
                intentGoogleNav.setPackage(mapsPackage);
            }

            Intent chooserIntent;
            
            if (isWazeInstalled && isMapsInstalled) {
                // Both apps installed, create chooser
                chooserIntent = Intent.createChooser(intentGoogleNav, null);
                chooserIntent.putExtra(Intent.EXTRA_INITIAL_INTENTS, new Intent[]{intentWaze});
            } else if (isWazeInstalled) {
                // Only Waze installed, use it directly
                chooserIntent = intentWaze;
            } else {
                // Only Google Maps installed, use it directly
                chooserIntent = intentGoogleNav;
            }
            
            cordova.getActivity().startActivity(chooserIntent);
        } else {
            // No map apps installed, try generic geo intent first
            try {
                Intent geoIntent = new Intent(Intent.ACTION_VIEW, 
                    Uri.parse("geo:" + toLat + "," + toLng + "?q=" + toLat + "," + toLng));
                cordova.getActivity().startActivity(geoIntent);
            } catch (Exception e) {
                // If geo intent fails, offer to install Google Maps
                Intent installMapsIntent = new Intent(Intent.ACTION_VIEW, 
                    Uri.parse("market://details?id=" + mapsPackage));
                cordova.getActivity().startActivity(installMapsIntent);
            }
        }
    }

    /**
     * Checks if a package is installed on the device
     * @param packageName Package name to check
     * @param packageManager PackageManager instance
     * @return true if the package is installed, false otherwise
     */
    private boolean isPackageInstalled(String packageName, PackageManager packageManager) {
        try {
            packageManager.getPackageInfo(packageName, 0);
            return true;
        } catch (PackageManager.NameNotFoundException e) {
            return false;
        }
    }
}
