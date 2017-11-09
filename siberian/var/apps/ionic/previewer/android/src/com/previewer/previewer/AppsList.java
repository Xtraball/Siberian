package com.previewer.previewer;

import android.app.Activity;
import android.app.ProgressDialog;
import android.content.Context;
import android.content.Intent;
import android.os.Bundle;
import android.util.Log;
import android.view.View;
import android.webkit.WebView;
import android.widget.AdapterView;
import android.widget.ListView;
import android.widget.SimpleAdapter;
import android.widget.Toast;

import com.appsmobilecompany.base.MainActivity;
import com.previewer.previewer.utils.LazyImageLoadAdapter;

import org.apache.cordova.engine.SystemWebViewClient;
import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.HashMap;

import com.appsmobilecompany.base.R;

public class AppsList extends Activity {

	public static WebView Webview;
    public static Context context;
    JSONArray apps_list;
    ListView list_view;
    public SimpleAdapter mSchedule;
    public static ProgressDialog pd_list;
    public ArrayList<HashMap<String, String>> listItem;
    public static String current_app_id, current_app_name;

	@Override
	protected void onCreate(Bundle savedInstanceState) {

        overridePendingTransition(R.anim.slide_in_left, R.anim.slide_out_left);
        super.onCreate(savedInstanceState);
        setContentView(R.layout.apps_list);

        initVars();

        try {

            listItem = new ArrayList<HashMap<String, String>>();

            for(int i=0;i<apps_list.length(); i++){
                JSONObject jsonas = apps_list.getJSONObject(i);
                HashMap<String, String> map = new HashMap<String, String>();

                map.put("id", jsonas.getString("id"));
                map.put("icon", jsonas.getString("icon"));
                map.put("name", jsonas.getString("name"));
                map.put("url", jsonas.getString("url"));

                listItem.add(map);
            }

            LazyImageLoadAdapter mSchedule = new LazyImageLoadAdapter(this, listItem);

            list_view.setAdapter(mSchedule);

            list_view.setOnItemClickListener(new AdapterView.OnItemClickListener() {
                public void onItemClick(AdapterView<?> a, View v, int position, long id) {
                    current_app_id = listItem.get(position).get("id");
                    current_app_name = listItem.get(position).get("name");
                    openApplication(listItem.get(position).get("url"));
                }
            });

        } catch (JSONException e) {
        }

	}

    public void goToLogin(View v) {
        this.onBackPressed();
    }

    public void onBackPressed() {
        finish();
        overridePendingTransition(R.anim.slide_in_right, R.anim.slide_out_right);
    }

    public void initVars() {
        Login.pd.dismiss();

        Webview = (WebView) findViewById(R.id.webView);
        context = getApplicationContext();
        list_view = (ListView) findViewById(R.id.apps_list);

        try {
            apps_list = Login.json_apps_list.getJSONArray("applications");
        } catch(JSONException j) {
        }
    }

    public void openApplication(String url) {
        pd_list = ProgressDialog.show(AppsList.this, "", AppsList.this.getString(R.string.load_message), true);

        try {
            SystemWebViewClient.url_array = url.split("/");
            Intent intent = new Intent(getApplicationContext(), MainActivity.class);
            intent.addFlags(Intent.FLAG_ACTIVITY_NEW_TASK);
            startActivity(intent);

        } catch (Exception e) {
            Log.e("Exception.class", e.getMessage());
            Toast.makeText(getApplicationContext(), e.getMessage(), Toast.LENGTH_LONG).show();
        }

        pd_list.dismiss();
    }

}
