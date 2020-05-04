package com.webview;

import java.lang.ref.WeakReference;

import android.content.Intent;
import org.apache.cordova.CordovaWebView;
import org.apache.cordova.CallbackContext;
import org.apache.cordova.CordovaPlugin;
import org.apache.cordova.CordovaInterface;
import org.apache.cordova.PluginResult;
import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;
import org.apache.cordova.LOG;
import android.net.Uri;
import android.provider.Browser;

import java.util.ArrayList;
import java.util.Map;

public class WebViewPlugin extends CordovaPlugin {

    private static final String LOG_TAG = "WebViewPlugin";
    public static WebViewPlugin webViewPlugin = null;
    public static WeakReference<WebViewActivity> webViewActivity;

    private static CallbackContext subscribeCallbackContext = null;
    private static CallbackContext subscribeExitCallbackContext = null;
    private static CallbackContext subscribeDebugCallbackContext = null;
    private static CallbackContext subscribeResumeCallbackContext = null;
    private static CallbackContext subscribePauseCallbackContext = null;
    private static CallbackContext subscribeUrlCallbackContext = null;

    public WebViewPlugin() {
    }

    /**
     * Sets the context of the Command. This can then be used to do things like
     * get file paths associated with the Activity.
     *
     * @param cordova The context of the main Activity.
     * @param webView The CordovaWebView Cordova is running in.
     */
    public void initialize(CordovaInterface cordova, CordovaWebView webView) {
        super.initialize(cordova, webView);
        webViewPlugin = this;
    }

    /**
     * Executes the request and returns PluginResult.
     *
     * @param action          The action to execute.
     * @param args            JSONArry of arguments for the plugin.
     * @param callbackContext The callback id used when calling back into JavaScript.
     * @return True if the action was valid, false if not.
     */
    public boolean execute(String action, JSONArray args, CallbackContext callbackContext) throws JSONException {
        if (action.equals("loadApp")) {
            loadApp();
            JSONObject r = new JSONObject();
            r.put("responseCode", "ok");
            callbackContext.success(r);
        } else if (action.equals("show") && args.length() > 0) {
            final String url = args.getString(0);
            Boolean showLoading = args.getBoolean(1);
            if (!"".equals(url)) {
                showWebView(url, showLoading);
                JSONObject r = new JSONObject();
                r.put("responseCode", "ok");
                callbackContext.success(r);
            } else {
                callbackContext.error("Empty Parameter url");
            }
        } else if (action.equals("hide")) {
            LOG.d(LOG_TAG, "Hide Web View");
            hideWebView();
            JSONObject r = new JSONObject();
            r.put("responseCode", "ok");
            callbackContext.success(r);
        } else if (action.equals("load")) {
            LOG.d(LOG_TAG, "Web View Load Url");
            if (webViewActivity.get() == null) {
                execute("show", args, callbackContext);
            } else {
                final String url = args.getString(0);
                webViewActivity.get().loadUrl(url);
            }
        } else if (action.equals("reload")) {
            LOG.d(LOG_TAG, "Web View Reload");
            if (webViewActivity.get() == null) {
                LOG.d(LOG_TAG, "Web View is not initialized.");
            } else {
                webViewActivity.get().runOnUiThread(new Runnable() {
                    @Override
                    public void run() {
                        final String url = webViewActivity.get().getUrl();
                        webViewActivity.get().loadUrl(url);
                    }
                });
            }
        } else if (action.equals("hideLoading")) {
            LOG.d(LOG_TAG, "Hide Web View Loading");
            try {
                webViewActivity.get().hideLoading();
            } catch (Exception e) {
                LOG.e(LOG_TAG, "Error in hideLoading");
                LOG.e(LOG_TAG, e.toString());
            }
            JSONObject r = new JSONObject();
            r.put("responseCode", "ok");
            callbackContext.success(r);
        } else if (action.equals("subscribeCallback")) {
            LOG.d(LOG_TAG, "Subscribing Cordova CallbackContext");
            subscribeCallbackContext = callbackContext;
        } else if (action.equals("subscribeDebugCallback")) {
            LOG.d(LOG_TAG, "Subscribing Cordova CallbackContext");
            subscribeDebugCallbackContext = callbackContext;
        } else if (action.equals("subscribeResumeCallback")) {
            LOG.d(LOG_TAG, "Subscribing Cordova ResumeCallbackContext");
            subscribeResumeCallbackContext = callbackContext;
        } else if (action.equals("subscribePauseCallback")) {
            LOG.d(LOG_TAG, "Subscribing Cordova PauseCallbackContext");
            subscribePauseCallbackContext = callbackContext;
        } else if (action.equals("subscribeUrlCallback")) {
            LOG.d(LOG_TAG, "Subscribing Cordova CallbackContext");
            subscribeUrlCallbackContext = callbackContext;
        } else if (action.equals("subscribeExitCallback")) {
            LOG.d(LOG_TAG, "Subscribing Cordova ExitCallbackContext");
            subscribeExitCallbackContext = callbackContext;
        } else if (action.equals("exitApp")) {
            LOG.d(LOG_TAG, "Exiting app?");
            if (subscribeExitCallbackContext != null) {
                subscribeExitCallbackContext.success();
                subscribeExitCallbackContext = null;
            }
            this.cordova.getActivity().finish();
        } else {
            return false;
        }

        return true;
    }

    private void loadApp() {
        Intent intent = new Intent(this.cordova.getActivity().getApplicationContext(), WebViewActivity.class);
        intent.addFlags(Intent.FLAG_ACTIVITY_NEW_TASK);
        this.cordova.getActivity().getApplicationContext().startActivity(intent);
    }

    private void showWebView(final String url, final Boolean showLoading) {
        Intent intent = new Intent(this.cordova.getActivity().getApplicationContext(), WebViewActivity.class);
        intent.putExtra("url", url);
        intent.putExtra("showLoading", showLoading);
        intent.addFlags(Intent.FLAG_ACTIVITY_NEW_TASK);
        this.cordova.getActivity().getApplicationContext().startActivity(intent);
    }

    // new one
    void hideWebView() {
        webViewActivity.get().finish();
    }

    public void callDebugCallback() {
        if (subscribeDebugCallbackContext != null) {
            LOG.d(LOG_TAG, "Calling subscribeCallbackContext success");
            PluginResult pluginResult = new PluginResult(PluginResult.Status.OK);
            pluginResult.setKeepCallback(true);
            subscribeDebugCallbackContext.sendPluginResult(pluginResult);
        }
    }

    public void callResumeCallback(String url) {
        if (subscribeResumeCallbackContext != null) {
            LOG.d(LOG_TAG, "Calling subscribeResumeCallbackContext success");
            PluginResult pluginResult = new PluginResult(PluginResult.Status.OK, url);
            pluginResult.setKeepCallback(true);
            subscribeResumeCallbackContext.sendPluginResult(pluginResult);
        }
    }

    public void callPauseCallback(String url) {
        if (subscribePauseCallbackContext != null) {
            LOG.d(LOG_TAG, "Calling subscribePauseCallbackContext success");
            PluginResult pluginResult = new PluginResult(PluginResult.Status.OK, url);
            pluginResult.setKeepCallback(true);
            subscribePauseCallbackContext.sendPluginResult(pluginResult);
        }
    }

    public void callUrlCallback(String url, boolean didNavigate) {
        if (subscribeDebugCallbackContext != null) {
            LOG.d(LOG_TAG, "Calling subscribeCallbackContext success");

            JSONObject result = new JSONObject();
            try {
                result.put("url", url);
                result.put("didNavigate", didNavigate);
            } catch (JSONException e) {
                e.printStackTrace();
            }

            PluginResult pluginResult = new PluginResult(PluginResult.Status.OK, result.toString());
            pluginResult.setKeepCallback(true);
            subscribeUrlCallbackContext.sendPluginResult(pluginResult);
        }
    }

    @Override
    public boolean onOverrideUrlLoading(String url) {
        return false;
    }

    @Override
    public Boolean shouldAllowNavigation(String url) {
        return true;
    }
}
