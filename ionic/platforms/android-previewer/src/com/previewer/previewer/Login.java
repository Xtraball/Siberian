package com.previewer.previewer;

import android.app.Activity;
import android.app.AlertDialog;
import android.app.ProgressDialog;
import android.content.Context;
import android.content.DialogInterface;
import android.content.Intent;
import android.content.SharedPreferences;
import android.os.AsyncTask;
import android.os.Bundle;
import android.util.Log;
import android.util.Patterns;
import android.view.View;
import android.view.animation.AnimationUtils;
import android.view.inputmethod.InputMethodManager;
import android.widget.ArrayAdapter;
import android.widget.Button;
import android.widget.EditText;
import android.widget.ImageButton;
import android.widget.ImageView;
import android.widget.RelativeLayout;
import android.widget.Toast;
import android.widget.ViewFlipper;

import com.appsmobilecompany.base.R;
import org.json.JSONException;
import org.json.JSONObject;

import java.io.DataOutputStream;
import java.io.IOException;
import java.io.InputStream;
import java.net.HttpURLConnection;
import java.net.MalformedURLException;
import java.net.URL;
import java.nio.charset.Charset;

public class Login extends Activity {

    public static Context context;
    RelativeLayout info_layout, login_layout;
    ViewFlipper flipper_layout;
    EditText input_passwd, input_email, input_url;
    Button btn_login;
    ImageButton btn_info;
    ImageView img_logo;
    public static JSONObject json_apps_list;
    public static ProgressDialog pd;
    public static String server_url;
    public SharedPreferences pref;
    public SharedPreferences.Editor editor;
    final Boolean enable_url_field = false;

	@Override
	protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.login);

        initVars();
	}

	@Override
    public void onBackPressed() {

        if(flipper_layout.getCurrentView().getId() == R.id.info_layout) {
            toggleInfo(findViewById(R.id.info_layout));
        } else {
            DialogInterface.OnClickListener dialogClickListener = new DialogInterface.OnClickListener() {
                @Override
                public void onClick(DialogInterface dialog, int which) {
                    if (which == DialogInterface.BUTTON_POSITIVE) {
                        finish();
                    }
                }
            };

            AlertDialog.Builder builder = new AlertDialog.Builder(Login.this);
            builder.setMessage(R.string.quit_message).setPositiveButton(R.string.yes, dialogClickListener)
                    .setNegativeButton(R.string.no, dialogClickListener).show();
            return;
        }
    }

    public void initVars() {
        context = getApplicationContext();
        flipper_layout = (ViewFlipper) findViewById(R.id.flipper_layout);
        login_layout = (RelativeLayout) findViewById(R.id.login_layout);
        info_layout = (RelativeLayout) findViewById(R.id.info_layout);

        if(enable_url_field) {
            input_url = (EditText) findViewById(R.id.et_url);
            input_url.setVisibility(View.VISIBLE);

            img_logo = (ImageView) findViewById(R.id.img_logo);
            RelativeLayout.LayoutParams p = new RelativeLayout.LayoutParams ( RelativeLayout.LayoutParams.WRAP_CONTENT,
                    RelativeLayout.LayoutParams.WRAP_CONTENT );
            p.addRule(RelativeLayout.ABOVE, R.id.et_url);
            img_logo.setLayoutParams(p);
        }

        input_email = (EditText) findViewById(R.id.et_email);

        EditText input_obj = input_email;
        if(enable_url_field) {
            input_obj = input_url;
        }

        input_obj.setOnFocusChangeListener(new View.OnFocusChangeListener() {
            @Override
            public void onFocusChange(View v, boolean hasFocus) {
                if(hasFocus) {
                    showHistory();
                }
            }
        });

        input_passwd = (EditText) findViewById(R.id.et_pwd);
        btn_login = (Button) findViewById(R.id.btn_login);
        btn_info = (ImageButton) findViewById(R.id.imgbtn_info);

        if(enable_url_field) {
            pref = getApplicationContext().getSharedPreferences("UrlHistory", 0);
        } else {
            pref = getApplicationContext().getSharedPreferences("EmailHistory", 0);
        }
        editor = pref.edit();
    }

    public void toggleInfo(View v) {

        if(v.getId() != R.id.imgbtn_info) {
            flipper_layout.setInAnimation(AnimationUtils.loadAnimation(context, R.anim.slide_in_right));
            flipper_layout.setOutAnimation(AnimationUtils.loadAnimation(context, R.anim.slide_out_right));
        } else {
            flipper_layout.setInAnimation(AnimationUtils.loadAnimation(context, R.anim.slide_in_left));
            flipper_layout.setOutAnimation(AnimationUtils.loadAnimation(context, R.anim.slide_out_left));
        }

        flipper_layout.showNext();
    }

    public void showHistory() {
        final String[] history = pref.getAll().keySet().toArray(new String[0]);

        if(pref.getAll().size() > 0) {
            final ArrayAdapter<String> adapter = new ArrayAdapter<String>(this, android.R.layout.simple_dropdown_item_1line, history);

            AlertDialog.Builder builderSingle = new AlertDialog.Builder(Login.this);
            builderSingle.setTitle(getString(R.string.log_in_with));
            builderSingle.setNegativeButton(getString(R.string.another_one), new DialogInterface.OnClickListener() {

                @Override
                public void onClick(DialogInterface dialog, int which) {
                    dialog.dismiss();
                }

            });

            builderSingle.setAdapter(adapter, new DialogInterface.OnClickListener() {

                @Override
                public void onClick(DialogInterface dialog, int which) {

                    String strName = adapter.getItem(which);

                    if(enable_url_field) {
                        input_url.setText(strName);
                        String[] value = pref.getString(strName, null).split(",");
                        input_email.setText(value[0]);
                        input_passwd.setText(value[1]);
                    } else {
                        input_email.setText(strName);
                        input_passwd.setText(pref.getString(strName, null));
                    }

                    logIn(findViewById(R.id.login_layout));
                }
            });

            builderSingle.show();
        }
    }

    public void saveInHistory(String key, String value) {
        editor.putString(key, value);
        editor.commit();
    }

    public void logIn(View v) {
        //close the keyboard
        getWindow().setSoftInputMode(InputMethodManager.RESULT_HIDDEN);

        if((enable_url_field && Patterns.WEB_URL.matcher(input_url.getText().toString().trim()).matches()) || !enable_url_field) {

            if(Patterns.EMAIL_ADDRESS.matcher(input_email.getText().toString().trim()).matches() && input_passwd.length() >= 6) {

                pd = ProgressDialog.show(this, "", this.getApplicationContext().getString(R.string.load_message), true);

                String email = input_email.getText().toString().trim();
                String url = email;
                String password = input_passwd.getText().toString();
                String value = password;

                if(enable_url_field) {
                    url = input_url.getText().toString();
                    value = input_email.getText().toString() + "," + input_passwd.getText().toString();
                }

                saveInHistory(url, value);
                logInProcess(email, password, url);
            } else {
                Toast.makeText(this, getString(R.string.login_error), Toast.LENGTH_SHORT).show();
            }
        } else {
            Toast.makeText(this, getString(R.string.url_error), Toast.LENGTH_SHORT).show();
        }
    }

    private void logInProcess(final String email, final String password, final String url) {
        new AsyncTask<Void, Void, String>() {
            @Override
            protected String doInBackground(Void... params) {
                String msg = "";
                try {

                    if(enable_url_field) {
                        server_url = url + "/application/webservice_preview/login";
                        if(!server_url.startsWith("http://") && !server_url.startsWith("https://")) server_url = "http://"+server_url;
                    } else {
                        server_url = getString(R.string.url) + "/application/webservice_preview/login";
                    }
                    String parameters = "email=" + email + "&password=" + password + "&version=ionic";

                    post(server_url, parameters);

                } catch (IOException ex) {
                    msg = "Error :" + ex.getMessage();
                }
                return msg;
            }

            @Override
            protected void onPostExecute(String msg) {
                if (json_apps_list == null || json_apps_list.has("error")) {
                    pd.dismiss();

                    Login.this.runOnUiThread(new Runnable() {
                        public void run() {
                            Toast.makeText(context, context.getString(R.string.login_error), Toast.LENGTH_SHORT).show();
                        }
                    });
                } else {
                    startActivity(new Intent(context, AppsList.class));
                }
            }
        }.execute(null, null, null);
    }

    static void post(String endpoint, String params) throws IOException {

        URL url;
        try {
            url = new URL(endpoint);
        } catch (MalformedURLException e) {
            throw new IllegalArgumentException("invalid url: " + endpoint);
        }

        byte[] postData = params.getBytes( Charset.forName("UTF-8"));
        int postDataLength = postData.length;
        HttpURLConnection conn = (HttpURLConnection) url.openConnection();

        conn.setDoOutput(true);
        conn.setDoInput(true);
        conn.setInstanceFollowRedirects(false);
        conn.setRequestMethod("POST");
        conn.setRequestProperty("Content-Type", "application/x-www-form-urlencoded");
        conn.setRequestProperty("charset", "utf-8");
        conn.setRequestProperty("Content-Length", Integer.toString(postDataLength));
        conn.setUseCaches(false);

        try {

            DataOutputStream wr = new DataOutputStream( conn.getOutputStream());
            wr.write( postData );
            wr.close();

            int status = conn.getResponseCode();
            if (status != 200) {
                throw new IOException("Post failed with error code " + status);
            }

            //get the response
            String reply;
            InputStream in = conn.getInputStream();
            StringBuffer sb = new StringBuffer();
            try {
                int chr;
                while ((chr = in.read()) != -1) {
                    sb.append((char) chr);
                }
                reply = sb.toString();

                try {
                    json_apps_list = new JSONObject(reply);
                } catch(JSONException j) {
                    Log.e("JSONEXCEPTION", j.getMessage());
                }

            } finally {
                in.close();
            }

            conn.disconnect();

        } catch (IOException e) {
            Log.e("JSONEXCEPTION", e.getMessage());
        } finally {
            if (conn != null) {
                conn.disconnect();
            }
        }

    }

}
