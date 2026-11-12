package com.leoghynizm.fesreport;

import android.Manifest;
import android.annotation.SuppressLint;
import android.app.Activity;
import android.content.ActivityNotFoundException;
import android.content.Intent;
import android.content.pm.PackageManager;
import android.net.Uri;
import android.os.Build;
import android.os.Bundle;
import android.provider.MediaStore;
import android.webkit.GeolocationPermissions;
import android.webkit.PermissionRequest;
import android.webkit.ValueCallback;
import android.webkit.WebChromeClient;
import android.webkit.WebResourceRequest;
import android.webkit.WebResourceResponse;
import android.webkit.WebSettings;
import android.webkit.WebView;
import android.webkit.WebViewClient;

import androidx.core.content.FileProvider;
import androidx.webkit.WebViewAssetLoader;

import java.io.File;

/**
 * FES Emergency Report: the web app packaged in assets/www, served from a secure
 * https origin (appassets.androidplatform.net) so storage, GPS and camera work.
 */
public class MainActivity extends Activity {
    private static final String HOST = "appassets.androidplatform.net";
    private static final String START = "https://" + HOST + "/assets/www/app.html";
    private static final int REQ_FILE = 10, REQ_LOC = 11, REQ_CAM = 12;

    private WebView web;
    private WebViewAssetLoader assets;
    private ValueCallback<Uri[]> fileCb;
    private Uri photoUri;
    private String geoOrigin;
    private GeolocationPermissions.Callback geoCb;
    private PermissionRequest pendingMedia;

    @SuppressLint("SetJavaScriptEnabled")
    @Override
    protected void onCreate(Bundle state) {
        super.onCreate(state);
        assets = new WebViewAssetLoader.Builder()
                .addPathHandler("/assets/", new WebViewAssetLoader.AssetsPathHandler(this))
                .build();

        web = new WebView(this);
        web.setBackgroundColor(0xFF0B0D10);
        setContentView(web);

        WebSettings s = web.getSettings();
        s.setJavaScriptEnabled(true);
        s.setDomStorageEnabled(true);
        s.setDatabaseEnabled(true);
        s.setGeolocationEnabled(true);
        s.setAllowFileAccess(false);
        s.setAllowContentAccess(false);
        s.setMediaPlaybackRequiresUserGesture(false);
        s.setUserAgentString(s.getUserAgentString() + " FESReportApp/1.6");

        web.setWebViewClient(new WebViewClient() {
            @Override
            public WebResourceResponse shouldInterceptRequest(WebView v, WebResourceRequest r) {
                return assets.shouldInterceptRequest(r.getUrl());
            }

            @Override
            public boolean shouldOverrideUrlLoading(WebView v, WebResourceRequest r) {
                Uri u = r.getUrl();
                if (HOST.equals(u.getHost())) return false;
                try { startActivity(new Intent(Intent.ACTION_VIEW, u)); } catch (ActivityNotFoundException ignored) { }
                return true; // anything outside the app opens in the phone's own apps
            }
        });

        web.setWebChromeClient(new WebChromeClient() {
            @Override
            public void onGeolocationPermissionsShowPrompt(String origin, GeolocationPermissions.Callback cb) {
                if (has(Manifest.permission.ACCESS_FINE_LOCATION)) { cb.invoke(origin, true, false); return; }
                geoOrigin = origin; geoCb = cb;
                requestPermissions(new String[]{Manifest.permission.ACCESS_FINE_LOCATION, Manifest.permission.ACCESS_COARSE_LOCATION}, REQ_LOC);
            }

            @Override
            public void onPermissionRequest(PermissionRequest req) {
                boolean wantsCam = false;
                for (String r : req.getResources()) if (PermissionRequest.RESOURCE_VIDEO_CAPTURE.equals(r)) wantsCam = true;
                if (!wantsCam || !HOST.equals(req.getOrigin().getHost())) { req.deny(); return; }
                if (has(Manifest.permission.CAMERA)) { req.grant(new String[]{PermissionRequest.RESOURCE_VIDEO_CAPTURE}); return; }
                pendingMedia = req;
                requestPermissions(new String[]{Manifest.permission.CAMERA}, REQ_CAM);
            }

            @Override
            public boolean onShowFileChooser(WebView v, ValueCallback<Uri[]> cb, FileChooserParams p) {
                if (fileCb != null) fileCb.onReceiveValue(null);
                fileCb = cb;
                Intent pick = p.createIntent();
                Intent chooser = Intent.createChooser(pick, "Pilih foto");
                Intent cam = cameraIntent();
                if (cam != null) chooser.putExtra(Intent.EXTRA_INITIAL_INTENTS, new Intent[]{cam});
                try { startActivityForResult(chooser, REQ_FILE); }
                catch (ActivityNotFoundException e) { fileCb = null; return false; }
                return true;
            }
        });

        if (state != null) web.restoreState(state);
        else web.loadUrl(START);
    }

    private Intent cameraIntent() {
        if (!has(Manifest.permission.CAMERA)) return null; // asked for on first use below
        try {
            File dir = new File(getCacheDir(), "photos");
            if (!dir.exists() && !dir.mkdirs()) return null;
            File f = File.createTempFile("FES_", ".jpg", dir);
            photoUri = FileProvider.getUriForFile(this, getPackageName() + ".files", f);
            Intent i = new Intent(MediaStore.ACTION_IMAGE_CAPTURE);
            i.putExtra(MediaStore.EXTRA_OUTPUT, photoUri);
            i.addFlags(Intent.FLAG_GRANT_WRITE_URI_PERMISSION | Intent.FLAG_GRANT_READ_URI_PERMISSION);
            return i;
        } catch (Exception e) { return null; }
    }

    @Override
    protected void onActivityResult(int code, int result, Intent data) {
        if (code != REQ_FILE || fileCb == null) { super.onActivityResult(code, result, data); return; }
        Uri[] out = null;
        if (result == RESULT_OK) {
            if (data != null && (data.getData() != null || data.getClipData() != null)) {
                out = WebChromeClient.FileChooserParams.parseResult(result, data);
            } else if (photoUri != null) {
                out = new Uri[]{photoUri}; // photo taken with the camera
            }
        }
        fileCb.onReceiveValue(out);
        fileCb = null; photoUri = null;
    }

    @Override
    public void onRequestPermissionsResult(int code, String[] perms, int[] res) {
        boolean ok = res.length > 0 && res[0] == PackageManager.PERMISSION_GRANTED;
        if (code == REQ_LOC && geoCb != null) { geoCb.invoke(geoOrigin, ok, false); geoCb = null; }
        if (code == REQ_CAM && pendingMedia != null) {
            if (ok) pendingMedia.grant(new String[]{PermissionRequest.RESOURCE_VIDEO_CAPTURE}); else pendingMedia.deny();
            pendingMedia = null;
        }
    }

    private boolean has(String perm) { return checkSelfPermission(perm) == PackageManager.PERMISSION_GRANTED; }

    @Override
    protected void onResume() {
        super.onResume();
        // ask for the camera once so the photo button can offer "Ambil foto" directly
        if (Build.VERSION.SDK_INT >= 23 && !has(Manifest.permission.CAMERA) && getPreferences(MODE_PRIVATE).getBoolean("askCam", true)) {
            getPreferences(MODE_PRIVATE).edit().putBoolean("askCam", false).apply();
            requestPermissions(new String[]{Manifest.permission.CAMERA}, 99);
        }
    }

    @Override
    protected void onSaveInstanceState(Bundle out) { super.onSaveInstanceState(out); web.saveState(out); }

    @Override
    public void onBackPressed() {
        if (web.canGoBack()) web.goBack(); else super.onBackPressed();
    }

    @Override
    protected void onDestroy() { if (web != null) web.destroy(); super.onDestroy(); }
}
