package com.appsmobilecompany.base;

import android.content.Context;
import android.location.Location;
import android.location.LocationListener;
import android.location.LocationManager;
import android.os.Bundle;
import android.util.Log;
import android.webkit.WebSettings;

import org.apache.cordova.CallbackContext;
import org.apache.cordova.CordovaPlugin;
import org.apache.cordova.PluginResult;
import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

public class CDVBackgroundGeoloc extends CordovaPlugin {

    String TAG = "CDVBackgroundGeoloc";
    private LocationManager locationManager;
    private LocationListener backgroundGeolocListener;
    private static final long MINIMUM_DISTANCECHANGE_FOR_UPDATE = 100; // in Meters
    private static final long MINIMUM_TIME_BETWEEN_UPDATE = 30000; // in Milliseconds

    @Override
    public boolean execute(String action, JSONArray args, CallbackContext callbackContext) throws JSONException {
        Log.i(TAG, "execute: " + action);
        JSONObject jsonResult;

        if (action.equals("getCurrentPosition")) {
            jsonResult = this.getCurrentPosition(webView.getContext());
            if (jsonResult != null) {
                callbackContext.sendPluginResult(new PluginResult(PluginResult.Status.OK, jsonResult));
            } else {
                callbackContext.error("Can't get user position, check if geolocation services is enabled or app authorized to run in background.");
            }
        } else if (action.equals("startBackgroundLocation")) {
            this.startBackgroundLocation(webView.getContext(), callbackContext);
        } else if (action.equals("stopBackgroundLocation")) {
            this.stopBackgroundLocation();
        } else {
            return false;
        }

        return true;
    }

    //--------------------------------------------------------------------------
    // LOCAL METHODS
    //--------------------------------------------------------------------------

    private void startBackgroundLocation(Context pContext, CallbackContext pCallbackContext) {
        if(locationManager == null) {
            Log.i(TAG, "Starting Background Location");
            backgroundGeolocListener = new BackgroundGeolocListener(pCallbackContext);
            locationManager = (LocationManager) pContext.getSystemService(Context.LOCATION_SERVICE);
            locationManager.requestLocationUpdates(
                    LocationManager.GPS_PROVIDER,
                    MINIMUM_TIME_BETWEEN_UPDATE,
                    MINIMUM_DISTANCECHANGE_FOR_UPDATE,
                    new BackgroundGeolocListener(pCallbackContext)
            );
        }
    }

    private void stopBackgroundLocation() {
        Log.i(TAG, "Stopping Background Location");
        locationManager.removeUpdates(backgroundGeolocListener);
        locationManager = null;
    }

    private JSONObject getCurrentPosition(Context pContext) {
        LocationManager locationManager = (LocationManager) pContext.getSystemService(Context.LOCATION_SERVICE);
        Location locationGPS = locationManager.getLastKnownLocation(LocationManager.GPS_PROVIDER);
        Location locationNet = locationManager.getLastKnownLocation(LocationManager.NETWORK_PROVIDER);
        Location current_location;

        JSONObject jsonResult = new JSONObject();

        long GPSLocationTime = 0;
        if (null != locationGPS) {
            GPSLocationTime = locationGPS.getTime();
        }

        long NetLocationTime = 0;
        if (null != locationNet) {
            NetLocationTime = locationNet.getTime();
        }

        if (0 < GPSLocationTime - NetLocationTime) {
            Log.i(TAG, "Located by GPS");
            current_location = locationGPS;
        } else {
            Log.i(TAG, "Located by network");
            current_location = locationNet;
        }

        try {
            jsonResult.put("latitude", current_location.getLatitude());
            jsonResult.put("longitude", current_location.getLongitude());
            jsonResult.put("provider", current_location.getProvider());

            return jsonResult;
        } catch (JSONException e) {
            return null;
        }
    }

    //--------------------------------------------------------------------------
    // LOCAL LISTENER
    //--------------------------------------------------------------------------

    public class BackgroundGeolocListener implements LocationListener {

        private CallbackContext callbackContext;

        public BackgroundGeolocListener(CallbackContext pCallbackContext) {
            callbackContext = pCallbackContext;
        }

        public void onLocationChanged(Location location) {
            Log.i(TAG, "Location changed: " + location.toString());

            JSONObject jsonResult = new JSONObject();
            try {
                jsonResult.put("latitude", location.getLatitude());
                jsonResult.put("longitude", location.getLongitude());
                jsonResult.put("provider", location.getProvider());
            } catch (JSONException e) {
                e.printStackTrace();
            }

            PluginResult result = new PluginResult(PluginResult.Status.OK, jsonResult);
            result.setKeepCallback(true);
            callbackContext.sendPluginResult(result);
        }

        @Override
        public void onStatusChanged(String s, int i, Bundle bundle) {

        }

        public void onProviderDisabled(String s) {
        }

        public void onProviderEnabled(String s) {
        }
    }
}
