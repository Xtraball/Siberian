package com.webview;

import java.lang.ref.WeakReference;

import android.app.Activity;
import android.app.Dialog;
import android.content.DialogInterface;
import android.graphics.Color;
import android.os.Bundle;
import android.os.Handler;
import android.view.KeyEvent;
import android.view.MotionEvent;
import android.view.View;
import android.view.ViewConfiguration;
import android.view.ViewGroup;
import android.widget.LinearLayout;
import android.widget.ProgressBar;
import android.widget.RelativeLayout;
import org.apache.cordova.CordovaActivity;
import org.apache.cordova.PluginEntry;
import org.apache.cordova.engine.SystemWebViewClient;

public class WebViewActivity extends CordovaActivity {
    static Dialog dialog;
    static Activity activity2;
    private boolean hasPausedEver;

    @Override
    public void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);

        activity2 = this;

        // Use the default cordova launchUrl in config.xml
        String startUrl = launchUrl;
        try {
            // Or open a complete custom page inside the webview!
            Bundle b = getIntent().getExtras();
            String url = b.getString("url");
            Boolean shouldShowLoading = b.getBoolean("shouldShowLoading");
            if (!"".equals(url)) {
                startUrl = url;
            }
        } catch(Exception e) {
            // Do nothing more!
        }

        loadUrl(startUrl);

        // WeakReference to avoid looped pointers
        WebViewPlugin.webViewActivity = new WeakReference<WebViewActivity>(this);
    }

    @Override
    protected void init() {
        super.init();

        if (WebViewPlugin.webViewPlugin == null) {
            return;
        }
        appView.getPluginManager().addService(new PluginEntry("WebViewPlugin", WebViewPlugin.webViewPlugin));
    }

    public static boolean showLoading() {
        // Loading spinner
        activity2.runOnUiThread(new Runnable() {
            @Override
            public void run() {
                dialog = new Dialog(activity2, android.R.style.Theme_Translucent_NoTitleBar);
                ProgressBar progressBar = new ProgressBar(activity2, null, android.R.attr.progressBarStyle);

                LinearLayout linearLayout = new LinearLayout(activity2);
                linearLayout.setOrientation(LinearLayout.VERTICAL);
                RelativeLayout layoutPrincipal = new RelativeLayout(activity2);
                layoutPrincipal.setBackgroundColor(Color.parseColor("#d9000000"));

                RelativeLayout.LayoutParams params = new RelativeLayout.LayoutParams(ViewGroup.LayoutParams.WRAP_CONTENT, ViewGroup.LayoutParams.WRAP_CONTENT);
                params.addRule(RelativeLayout.CENTER_IN_PARENT);

                linearLayout.addView(progressBar);

                linearLayout.setLayoutParams(params);

                layoutPrincipal.addView(linearLayout);

                dialog.setContentView(layoutPrincipal);
                dialog.setOnCancelListener(new DialogInterface.OnCancelListener() {
                    @Override
                    public void onCancel(DialogInterface dialogInterface) {

                    }
                });
                dialog.setOnKeyListener(new DialogInterface.OnKeyListener() {
                    @Override
                    public boolean onKey(DialogInterface dialogInterface, int i, KeyEvent keyEvent) {
                        if (keyEvent.getKeyCode() == KeyEvent.KEYCODE_BACK)
                            return true;
                        return false;
                    }
                });

                dialog.show();
            }
        });

        return true;
    }

    public static boolean hideLoading() {
        // Loading spinner
        activity2.runOnUiThread(new Runnable() {
            @Override
            public void run() {
                dialog.hide();
            }
        });
        return true;
    }

    public String getUrl() {
        return appView.getUrl();
    }

    @Override
    protected void onResume() {
        super.onResume();
        if (hasPausedEver && WebViewPlugin.webViewPlugin != null) {
            WebViewPlugin.webViewPlugin.callResumeCallback(getUrl());
        }
    }

    @Override
    protected void onPause() {
        super.onPause();
        hasPausedEver = true;
        WebViewPlugin.webViewPlugin.callPauseCallback(getUrl());
    }
}
