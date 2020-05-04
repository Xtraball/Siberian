package com.rjfun.cordova.unity;

import org.apache.cordova.CallbackContext;
import org.apache.cordova.PluginResult;

import android.app.Activity;
import android.view.View;
import android.view.ViewGroup.LayoutParams;
import android.widget.RelativeLayout;

import com.rjfun.cordova.ext.PluginAdapterDelegate;
import com.unity3d.player.UnityPlayer;

public class PluginAdapterUnity implements PluginAdapterDelegate {

	private static RelativeLayout rootLayout = null;
	private static View	mainView = null;
	
	@Override
	public Activity getActivity() {
		return UnityPlayer.currentActivity;
	}

	@Override
	public View getView() {
		Activity act = getActivity();
		// mock Cordova's webView and the layout container
		if(rootLayout == null) {
			rootLayout = new RelativeLayout(act);
			RelativeLayout.LayoutParams params = new RelativeLayout.LayoutParams(LayoutParams.MATCH_PARENT, LayoutParams.MATCH_PARENT);
			getActivity().addContentView(rootLayout, params);
		}
		if(mainView == null) {
			mainView = new RelativeLayout(act);
			RelativeLayout.LayoutParams params = new RelativeLayout.LayoutParams(LayoutParams.MATCH_PARENT, LayoutParams.MATCH_PARENT);
			rootLayout.addView(mainView, params);
		}
		return mainView;
	}

	@Override
	public void fireEvent(String obj, String eventName, String jsonData) {
		UnityPlayer.UnitySendMessage(obj, eventName, jsonData);
	}

	@Override
	public void sendPluginResult(PluginResult result, CallbackContext context) {
		String jsonData = "{\"callbackId\":\"" + context.getCallbackId() + 
				"\",\"status\":" + result.getStatus() + 
				",\"keepCallback\":" + (result.getKeepCallback()?1:0)  + 
				",\"data\":" + result.getMessage() + 
				"}";
		UnityPlayer.UnitySendMessage("Cordova", "onExecuteCallback", jsonData);
	}
}
