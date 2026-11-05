package com.siberian.insets;

import android.app.Activity;
import android.os.Build;
import android.view.View;
import android.view.ViewTreeObserver;
import android.view.WindowInsets;

import org.apache.cordova.CordovaPlugin;

import java.util.Locale;

/**
 * Edge-to-edge layout mode (application option "Layout mode", AndroidEdgeToEdge preference).
 *
 * In this mode cordova-android lets the WebView draw behind the system bars, and the app has to
 * keep its content clear of them. WebView older than Chromium 140 returns 0 for
 * env(safe-area-inset-*), so the real insets are read natively and written on the root element as
 * --sb-top/right/bottom/left in CSS pixels, plus the "edge-to-edge" class (see ionic.app.css).
 *
 * Classic mode (preference false or missing): this plugin does nothing at all.
 * iOS needs no equivalent, WKWebView is edge-to-edge and env(safe-area-inset-*) is reliable.
 */
public class SiberianInsets extends CordovaPlugin {

    private boolean enabled = false;
    private String lastScript = "";
    private ViewTreeObserver.OnGlobalLayoutListener listener;

    @Override
    protected void pluginInitialize() {
        enabled = preferences.getBoolean("AndroidEdgeToEdge", false);
        if (!enabled) {
            return;
        }

        final Activity activity = cordova.getActivity();
        activity.runOnUiThread(new Runnable() {
            @Override
            public void run() {
                listener = new ViewTreeObserver.OnGlobalLayoutListener() {
                    @Override
                    public void onGlobalLayout() {
                        apply();
                    }
                };
                activity.getWindow().getDecorView().getViewTreeObserver().addOnGlobalLayoutListener(listener);
            }
        });
    }

    @Override
    public Object onMessage(String id, Object data) {
        // A new page lost the values set on the previous one.
        if (enabled && "onPageFinished".equals(id)) {
            cordova.getActivity().runOnUiThread(new Runnable() {
                @Override
                public void run() {
                    lastScript = "";
                    apply();
                }
            });
        }
        return null;
    }

    @Override
    public void onDestroy() {
        if (listener != null) {
            cordova.getActivity().getWindow().getDecorView().getViewTreeObserver().removeOnGlobalLayoutListener(listener);
            listener = null;
        }
        super.onDestroy();
    }

    private void apply() {
        final Activity activity = cordova.getActivity();
        final View decor = activity.getWindow().getDecorView();
        final WindowInsets insets = decor.getRootWindowInsets();
        if (insets == null) {
            return;
        }

        int top;
        int left;
        int right;
        int bottom;
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.R) {
            final android.graphics.Insets bars = insets.getInsets(
                    WindowInsets.Type.systemBars() | WindowInsets.Type.displayCutout());
            top = bars.top;
            left = bars.left;
            right = bars.right;
            bottom = bars.bottom;
            // The WebView is already lifted above the keyboard, nothing to keep clear at the bottom.
            if (insets.isVisible(WindowInsets.Type.ime())) {
                bottom = 0;
            }
        } else {
            top = insets.getStableInsetTop();
            left = insets.getStableInsetLeft();
            right = insets.getStableInsetRight();
            bottom = insets.getStableInsetBottom();
            if (insets.getSystemWindowInsetBottom() > bottom) {
                // Keyboard is open.
                bottom = 0;
            }
        }

        final float density = activity.getResources().getDisplayMetrics().density;
        final String script =
                "(function(){var d=document.documentElement;if(!d){return;}var s=d.style;"
                        + "s.setProperty('--sb-top','" + px(top, density) + "');"
                        + "s.setProperty('--sb-right','" + px(right, density) + "');"
                        + "s.setProperty('--sb-bottom','" + px(bottom, density) + "');"
                        + "s.setProperty('--sb-left','" + px(left, density) + "');"
                        + "d.classList.add('edge-to-edge');})();";

        if (script.equals(lastScript)) {
            return;
        }
        lastScript = script;
        webView.getEngine().evaluateJavascript(script, null);
    }

    private static String px(final int value, final float density) {
        return String.format(Locale.US, "%.2fpx", value / density);
    }
}
