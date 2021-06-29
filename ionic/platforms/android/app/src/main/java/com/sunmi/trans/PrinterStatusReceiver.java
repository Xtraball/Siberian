package com.sunmi.trans;

import org.apache.cordova.CallbackContext;
import org.apache.cordova.PluginResult;

import android.content.BroadcastReceiver;
import android.content.Intent;
import android.content.Context;

import android.util.Log;

import org.json.JSONObject;

public class PrinterStatusReceiver extends BroadcastReceiver {
  private static final String TAG = "SunmiInnerPrinterReceiver";

  private CallbackContext callbackReceive;
  private boolean isReceiving = true;

  public PrinterStatusReceiver() {

  }

  @Override
  public void onReceive(Context context, Intent data) {
    if (this.isReceiving && this.callbackReceive != null) {
      String action = data.getAction();
      String type = "PrinterStatus";

      JSONObject jsonObj = new JSONObject();
      try {
        jsonObj.put("type", type);
        jsonObj.put("action", action);

        Log.i(TAG, "RECEIVED STATUS " + action);

        PluginResult result = new PluginResult(PluginResult.Status.OK, jsonObj);
        result.setKeepCallback(true);
        callbackReceive.sendPluginResult(result);
      } catch (Exception e) {
        Log.i(TAG, "ERROR: " + e.getMessage());
      }
    }
  }

  public void startReceiving(CallbackContext ctx) {
    this.callbackReceive = ctx;
    this.isReceiving = true;

    Log.i(TAG, "Start receiving status");
  }

  public void stopReceiving() {
    this.callbackReceive = null;
    this.isReceiving = false;

    Log.i(TAG, "Stop receiving status");
  }
}
