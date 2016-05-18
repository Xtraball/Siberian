package com.appsmobilecompany.base;

import android.util.Log;
import android.webkit.WebSettings;

import org.apache.cordova.CallbackContext;
import org.apache.cordova.CordovaPlugin;
import org.json.JSONArray;
import org.json.JSONException;

public class OfflineMode extends CordovaPlugin {

    public boolean execute(String action, JSONArray args, CallbackContext callbackContext) throws JSONException {
    	if (action.equals("useCache")) {
            this.useCache(args.getString(0), callbackContext);
            return true;
        }

        return false;
    }

    //--------------------------------------------------------------------------
    // LOCAL METHODS
    //--------------------------------------------------------------------------

    private void useCache(String use_cache, CallbackContext callbackContext) {
        if(use_cache == "1") {
            webView.getSettings().setCacheMode(WebSettings.LOAD_CACHE_ONLY);
        } else {
            webView.getSettings().setCacheMode(WebSettings.LOAD_DEFAULT);
        }

        callbackContext.success();
        Log.e("OFFLINEMODE", "useCache: "+use_cache);
    }

}
