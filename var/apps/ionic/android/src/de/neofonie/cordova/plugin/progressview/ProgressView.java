/****************************************
 *
 *  ProgressView.java
 *  Cordova ProgressView
 *
 *  Created by Sidney Bofah on 2014-11-20.
 *
 ****************************************/

package de.neofonie.cordova.plugin.progressview;

import android.app.ProgressDialog;

import org.apache.cordova.CallbackContext;
import org.apache.cordova.CordovaInterface;
import org.apache.cordova.CordovaPlugin;
import org.apache.cordova.PluginResult;
import org.json.JSONArray;
import org.json.JSONException;

public class ProgressView extends CordovaPlugin {

    //private static String LOG_TAG = "CordovaLog";
    private static ProgressDialog progressViewObj = null;
    private CallbackContext callback = null;

    /**
     * Executes the request and returns PluginResult.
     *
     * @param action          {String}  The action to execute.
     * @param args            {String} The exec() arguments in JSON form.
     * @param callbackContext The callback context used when calling back into JavaScript.
     * @return Whether the action was valid.
     */
    @Override
    public boolean execute(String action, String args, CallbackContext callbackContext) {
        /*
         * Don't run any of these if the current activity is finishing in order
         * to avoid android.view.WindowManager$BadTokenException crashing the app.
         */
        callback = callbackContext;

        if (this.cordova.getActivity().isFinishing()) {
            return true;
        }
        if (action.equals("show")) {
            this.show(args);
        } else if (action.equals("setProgress")) {
            this.setProgress(args);
        } else if (action.equals("setLabel")) {
            this.setLabel(args);
        } else if (action.equals("hide")) {
            this.hide();
        }
        return true;
    }


    /**
     * Show Dialog
     */
    private void show(final String args) {
        //Log.v(LOG_TAG, rawArgs);
        final CordovaInterface cordova = this.cordova;
        Runnable runnable = new Runnable() {
            @Override
            public void run() {

                // Check State
                if (ProgressView.progressViewObj != null) {
                    PluginResult result = new PluginResult(PluginResult.Status.ERROR, "(Cordova ProgressView) (show) ERROR: Dialog already showing");
                    result.setKeepCallback(true);
                    callback.sendPluginResult(result);
                    return;
                }

                // Get Arguments
                JSONArray argsObj = null;
                try {
                    argsObj = new JSONArray(args);
                } catch (JSONException e) {
                    // e.printStackTrace();
                }

                // Reset
                ProgressView.progressViewObj = null;

                // Set Label
                String label = " ";
                try {
                    if(argsObj.getString(0) != null && !argsObj.getString(0).isEmpty()){
                        try {
                            label = argsObj.getString(0);
                        } catch (JSONException e) {
                            // e.printStackTrace();
                        }
                    }
                } catch (JSONException e) {
                    e.printStackTrace();
                }

                // Set Shape, Type
                int shape = ProgressDialog.STYLE_HORIZONTAL;
                try {
                    if (argsObj.get(1) != null) {
                        try {
                            if ("CIRCLE".equals(argsObj.getString(1))) {
                                shape = ProgressDialog.STYLE_SPINNER;
                            }
                        } catch (JSONException e) {
                            // e.printStackTrace();
                        }
                    }
                } catch (JSONException e) {
                    e.printStackTrace();
                }

                // Set Android Theme
                Integer theme = 5; // ProgressDialog.THEME_DEVICE_DEFAULT_LIGHT
                try {
                    if (argsObj.get(3) != null) {
                        String themeArg = null;
                        try {
                            themeArg = argsObj.getString(3);
                        } catch (JSONException e) {
                            // e.printStackTrace();
                        }
                        if ("TRADITIONAL".equals(themeArg)) {
                            theme = 1; // ProgressDialog.THEME_TRADITIONAL
                        } else if ("DEVICE_DARK".equals(themeArg)) {
                            theme = 4; // ProgressDialog.THEME_DEVICE_DEFAULT_DARK
                        }
                        if ("HOLO_DARK".equals(themeArg)) {
                            theme = 2; // ProgressDialog.THEME_HOLO_DARK
                        }
                        if ("HOLO_LIGHT".equals(themeArg)) {
                            theme = 3; // ProgressDialog.THEME_HOLO_LIGHT
                        }
                    }
                } catch (JSONException e) {
                    e.printStackTrace();
                }

                // Show
                ProgressView.progressViewObj = new ProgressDialog(cordova.getActivity(), theme);
                ProgressView.progressViewObj.setProgressStyle(shape);
                ProgressView.progressViewObj.setTitle("");
                ProgressView.progressViewObj.setMessage(label.replaceAll("^\"|\"$", ""));
                ProgressView.progressViewObj.setCancelable(false);
                ProgressView.progressViewObj.setCanceledOnTouchOutside(false);
                ProgressView.progressViewObj.show();

                // Callback
                PluginResult result = new PluginResult(PluginResult.Status.OK, "(Cordova ProgressView) (show) OK");
                result.setKeepCallback(true);
                callback.sendPluginResult(result);
            }

            ;
        };
        this.cordova.getActivity().runOnUiThread(runnable);
    }
    
    

    /**
     * Set Progress
     */
    private void setProgress(final String args) {
        Runnable runnable = new Runnable() {
            @Override
            public void run() {

                // Check State
                if (ProgressView.progressViewObj == null) {
                    PluginResult result = new PluginResult(PluginResult.Status.ERROR, "(Cordova ProgressView) (setProgress) ERROR: No dialog to update");
                    result.setKeepCallback(true);
                    callback.sendPluginResult(result);
                    return;
                }

                // Get Arguments
                JSONArray argsObj = null;
                try {
                    argsObj = new JSONArray(args);
                } catch (JSONException e) {
                    // e.printStackTrace();
                }

                // Convert variable number types
                Double doubleValue = 0.0;
                Integer intValue;
                try {
                    doubleValue = argsObj.getDouble(0);
                    doubleValue = doubleValue * 100;
                } catch (JSONException e) {
                    e.printStackTrace();
                }
                intValue = doubleValue.intValue();

                // Update Progress
                ProgressView.progressViewObj.setProgress(intValue);

                // Callback
                PluginResult result = new PluginResult(PluginResult.Status.OK, "(Cordova ProgressView) (setProgress) OK");
                result.setKeepCallback(true);
                callback.sendPluginResult(result);
            }
        };
        this.cordova.getActivity().runOnUiThread(runnable);
    }
    
    
    
    /**
     * Set Label
     */
    private void setLabel(final String args) {
        Runnable runnable = new Runnable() {
            @Override
            public void run() {

                // Check State
                if (ProgressView.progressViewObj == null) {
                    PluginResult result = new PluginResult(PluginResult.Status.ERROR, "(Cordova ProgressView) (setLabel) ERROR: No dialog to update");
                    result.setKeepCallback(true);
                    callback.sendPluginResult(result);
                    return;
                }
                
                // Get Arguments
                JSONArray argsObj = null;
                try {
                    argsObj = new JSONArray(args);
                } catch (JSONException e) {
                    // e.printStackTrace();
                }

                // Update Label
                String label = "";
                try {
                    if (argsObj.get(0) != null) {
                        try {
                            label = argsObj.getString(0);
                            if(label.isEmpty()){
                                label = " ";
                            }
                        } catch (JSONException e) {
                            // e.printStackTrace();
                        }
                    }
                } catch (JSONException e) {
                    e.printStackTrace();
                }

                // Update Label
                ProgressView.progressViewObj.setMessage(label.replaceAll("^\"|\"$", ""));

                // Callback
                PluginResult result = new PluginResult(PluginResult.Status.OK, "(Cordova ProgressView) (setLabel) OK");
                result.setKeepCallback(true);
                callback.sendPluginResult(result);
            }
        };
        this.cordova.getActivity().runOnUiThread(runnable);
    }
    
    
    
    /**
     * Hide
     */
    private void hide() {
        Runnable runnable = new Runnable() {
            @Override
            public void run() {

                // Check State
                if (ProgressView.progressViewObj == null) {
                    PluginResult result = new PluginResult(PluginResult.Status.ERROR, "(Cordova ProgressView) (Hide) ERROR: No dialog to hide");
                    result.setKeepCallback(true);
                    callback.sendPluginResult(result);
                    return;
                }

                // Hide
                ProgressView.progressViewObj.dismiss();
                ProgressView.progressViewObj = null;

                // Callback
                PluginResult result = new PluginResult(PluginResult.Status.OK, "(Cordova ProgressView) (Hide) OK");
                result.setKeepCallback(true);
                callback.sendPluginResult(result);
            }
        };
        this.cordova.getActivity().runOnUiThread(runnable);
    }
}