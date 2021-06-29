package com.sunmi.utils;

import java.io.Closeable;
import java.io.File;
import java.io.FileNotFoundException;
import java.io.FileOutputStream;
import java.io.InputStream;
import java.io.OutputStream;

import android.content.Context;
import android.content.res.Resources;
import android.database.Cursor;
import android.graphics.Bitmap;
import android.graphics.Bitmap.CompressFormat;
import android.graphics.BitmapFactory;
import android.graphics.Canvas;
import android.graphics.Color;
import android.graphics.Matrix;
import android.graphics.Paint;
import android.graphics.Rect;
import android.graphics.RectF;
import android.net.Uri;
import android.provider.MediaStore.Images.ImageColumns;
import android.util.Log;

/**
 * bitmap操作的工具类
 *
 * @author longtao.li
 * 2012-10-18
 *
 */
public class BitmapUtils {

  private static final String TAG = "BitmapUtils";
  private static final int DEFAULT_COMPRESS_QUALITY = 50;
  private static final int INDEX_ORIENTATION = 0;

  private static final String[] IMAGE_PROJECTION = new String[] { ImageColumns.ORIENTATION };

  private final Context context;

  public BitmapUtils(Context context) {
    this.context = context;
  }

  /**
   * Creates a mutable bitmap from subset of source bitmap, transformed by the optional matrix.
   */
  private static Bitmap createBitmap(Bitmap source, int x, int y, int width, int height, Matrix m) {
    // Re-implement Bitmap createBitmap() to always return a mutable bitmap.
    Canvas canvas = new Canvas();

    Bitmap bitmap;
    Paint paint;
    if ((m == null) || m.isIdentity()) {
      bitmap = Bitmap.createBitmap(width, height, source.getConfig());
      paint = null;
    } else {
      RectF rect = new RectF(0, 0, width, height);
      m.mapRect(rect);
      bitmap = Bitmap.createBitmap(Math.round(rect.width()), Math.round(rect.height()), source.getConfig());

      canvas.translate(-rect.left, -rect.top);
      canvas.concat(m);

      paint = new Paint(Paint.FILTER_BITMAP_FLAG);
      if (!m.rectStaysRect()) {
        paint.setAntiAlias(true);
      }
    }
    bitmap.setDensity(source.getDensity());
    canvas.setBitmap(bitmap);

    Rect srcBounds = new Rect(x, y, x + width, y + height);
    RectF dstBounds = new RectF(0, 0, width, height);
    canvas.drawBitmap(source, srcBounds, dstBounds, paint);
    return bitmap;
  }

  private void closeStream(Closeable stream) {
    if (stream != null) {
      try {
        stream.close();
      } catch (Exception e) {
        e.printStackTrace();
      }
    }
  }

  public Rect getBitmapBounds(byte[] data) {
    Rect bounds = new Rect();
    try {
      BitmapFactory.Options options = new BitmapFactory.Options();
      options.inJustDecodeBounds = true;
      BitmapFactory.decodeByteArray(data, 0, data.length, options);
      bounds.right = options.outWidth;
      bounds.bottom = options.outHeight;
      Log.i(TAG, "options.outWidth=" + options.outWidth + " , " + "options.outHeight=" + options.outHeight);
    } catch (Exception e) {
    } finally {
    }
    return bounds;
  }

  public Rect getBitmapBounds(Uri uri) {
    Rect bounds = new Rect();
    InputStream is = null;

    try {
      is = context.getContentResolver().openInputStream(uri);
      BitmapFactory.Options options = new BitmapFactory.Options();
      options.inJustDecodeBounds = true;
      BitmapFactory.decodeStream(is, null, options);

      bounds.right = options.outWidth;
      bounds.bottom = options.outHeight;
      Log.i(TAG, "options.outWidth=" + options.outWidth + " , " + "options.outHeight=" + options.outHeight);
    } catch (Exception e) {
      e.printStackTrace();
    } finally {
      closeStream(is);
    }

    return bounds;
  }

  public Rect getBitmapBounds(InputStream is, boolean isClose) {
    Rect bounds = new Rect();

    try {
      BitmapFactory.Options options = new BitmapFactory.Options();
      options.inJustDecodeBounds = true;
      BitmapFactory.decodeStream(is, null, options);

      bounds.right = options.outWidth;
      bounds.bottom = options.outHeight;
      Log.i(TAG, "options.outWidth=" + options.outWidth + " , " + "options.outHeight=" + options.outHeight);
    } catch (Exception e) {
      e.printStackTrace();
    } finally {
      if (isClose)
        closeStream(is);
    }

    return bounds;
  }

  private int getOrientation(Uri uri) {
    int orientation = 0;
    Cursor cursor = null;
    try {
      cursor = context.getContentResolver().query(uri, IMAGE_PROJECTION, null, null, null);
      if ((cursor != null) && cursor.moveToNext()) {
        orientation = cursor.getInt(INDEX_ORIENTATION);
      }
    } catch (Exception e) {
      // Ignore error for no orientation column; just use the default orientation value 0.
    } finally {
      if (cursor != null) {
        cursor.close();
      }
    }
    return orientation;
  }

  /**
   * Decodes bitmap (maybe immutable) that keeps aspect-ratio and spans most within the bounds.
   */
  public Bitmap decodeBitmapByStream(InputStream is, Rect bounds, int width, int height) {
    Log.i(TAG, "width = " + width + " , " + "height = " + height);
    Bitmap bitmap = null;
    try {
      // TODO: Take max pixels allowed into account for calculation to avoid possible OOM.
      //    		Rect bounds = getBitmapBounds(is, false);
      int sampleSize = Math.max(bounds.width() / width, bounds.height() / height);
      sampleSize = Math.min(sampleSize, Math.max(bounds.width() / height, bounds.height() / width));

      BitmapFactory.Options options = new BitmapFactory.Options();
      options.inSampleSize = Math.max(sampleSize, 1);
      options.inPreferredConfig = Bitmap.Config.ARGB_8888;

      Log.i(TAG, "sampleSize = " + sampleSize + " , " + "options.inSampleSize = " + options.inSampleSize);
      bitmap = BitmapFactory.decodeStream(is, null, options);//!!!!溢出
    } catch (Exception e) {
      Log.e(TAG, e.getMessage());
    } finally {
      closeStream(is);
    }

    // Ensure bitmap in 8888 format, good for editing as well as GL compatible.
    if ((bitmap != null) && (bitmap.getConfig() != Bitmap.Config.ARGB_8888)) {
      Bitmap copy = bitmap.copy(Bitmap.Config.ARGB_8888, true);
      bitmap.recycle();
      bitmap = copy;
    }

    if (bitmap != null) {
      // Scale down the sampled bitmap if it's still larger than the desired dimension.
      float scale = Math.min((float) width / bitmap.getWidth(), (float) height / bitmap.getHeight());
      scale = Math.max(scale, Math.min((float) height / bitmap.getWidth(), (float) width / bitmap.getHeight()));
      if (scale < 1) {
        Matrix m = new Matrix();
        m.setScale(scale, scale);
        Bitmap transformed = createBitmap(bitmap, 0, 0, bitmap.getWidth(), bitmap.getHeight(), m);
        bitmap.recycle();
        return transformed;
      }
    }
    return bitmap;
  }

  /**
   * Decodes bitmap (maybe immutable) that keeps aspect-ratio and spans most within the bounds.
   */
  public Bitmap decodeBitmap(byte[] data, int width, int height) {
    Log.i(TAG, "width = " + width + " , " + "height = " + height);
    Bitmap bitmap = null;
    try {
      // TODO: Take max pixels allowed into account for calculation to avoid possible OOM.
      Rect bounds = getBitmapBounds(data);
      int sampleSize = Math.max(bounds.width() / width, bounds.height() / height);
      sampleSize = Math.min(sampleSize, Math.max(bounds.width() / height, bounds.height() / width));

      BitmapFactory.Options options = new BitmapFactory.Options();
      options.inSampleSize = Math.max(sampleSize, 1);
      options.inPreferredConfig = Bitmap.Config.ARGB_8888;

      Log.i(TAG, "sampleSize = " + sampleSize + " , " + "options.inSampleSize = " + options.inSampleSize);
      bitmap = BitmapFactory.decodeByteArray(data, 0, data.length, options);//!!!!溢出
    } catch (Exception e) {
      Log.e(TAG, e.getMessage());
    } finally {
      data = null;
    }

    // Ensure bitmap in 8888 format, good for editing as well as GL compatible.
    if ((bitmap != null) && (bitmap.getConfig() != Bitmap.Config.ARGB_8888)) {
      Bitmap copy = bitmap.copy(Bitmap.Config.ARGB_8888, true);
      bitmap.recycle();
      bitmap = copy;
    }

    if (bitmap != null) {
      // Scale down the sampled bitmap if it's still larger than the desired dimension.
      float scale = Math.min((float) width / bitmap.getWidth(), (float) height / bitmap.getHeight());
      scale = Math.max(scale, Math.min((float) height / bitmap.getWidth(), (float) width / bitmap.getHeight()));
      if (scale < 1) {
        Matrix m = new Matrix();
        m.setScale(scale, scale);
        Bitmap transformed = createBitmap(bitmap, 0, 0, bitmap.getWidth(), bitmap.getHeight(), m);
        bitmap.recycle();
        return transformed;
      }
    }
    return bitmap;
  }

  /**
   * Decodes bitmap (maybe immutable) that keeps aspect-ratio and spans most within the bounds.
   */
  private Bitmap decodeBitmap(Uri uri, int width, int height) {
    Log.i(TAG, "width = " + width + " , " + "height = " + height);
    InputStream is = null;
    Bitmap bitmap = null;

    try {
      // TODO: Take max pixels allowed into account for calculation to avoid possible OOM.
      Rect bounds = getBitmapBounds(uri);
      int sampleSize = Math.max(bounds.width() / width, bounds.height() / height);
      sampleSize = Math.min(sampleSize, Math.max(bounds.width() / height, bounds.height() / width));

      BitmapFactory.Options options = new BitmapFactory.Options();
      options.inSampleSize = Math.max(sampleSize, 1);
      options.inPreferredConfig = Bitmap.Config.ARGB_8888;

      is = context.getContentResolver().openInputStream(uri);
      Log.i(TAG, "sampleSize = " + sampleSize + " , " + "options.inSampleSize = " + options.inSampleSize);
      bitmap = BitmapFactory.decodeStream(is, null, options);//!!!!溢出
    } catch (Exception e) {
    } finally {
      closeStream(is);
    }

    // Ensure bitmap in 8888 format, good for editing as well as GL compatible.
    if ((bitmap != null) && (bitmap.getConfig() != Bitmap.Config.ARGB_8888)) {
      Bitmap copy = bitmap.copy(Bitmap.Config.ARGB_8888, true);
      bitmap.recycle();
      bitmap = copy;
    }

    if (bitmap != null) {
      // Scale down the sampled bitmap if it's still larger than the desired dimension.
      float scale = Math.min((float) width / bitmap.getWidth(), (float) height / bitmap.getHeight());
      scale = Math.max(scale, Math.min((float) height / bitmap.getWidth(), (float) width / bitmap.getHeight()));
      if (scale < 1) {
        Matrix m = new Matrix();
        m.setScale(scale, scale);
        Bitmap transformed = createBitmap(bitmap, 0, 0, bitmap.getWidth(), bitmap.getHeight(), m);
        bitmap.recycle();
        return transformed;
      }
    }
    return bitmap;
  }

  public Bitmap transform(Bitmap bitmap, int width, int height) {
    // Scale down the sampled bitmap if it's still larger than the desired dimension.
    float scale = Math.min((float) width / bitmap.getWidth(), (float) height / bitmap.getHeight());
    scale = Math.max(scale, Math.min((float) height / bitmap.getWidth(), (float) width / bitmap.getHeight()));
    if (scale < 1) {
      Matrix m = new Matrix();
      m.setScale(scale, scale);
      Bitmap transformed = createBitmap(bitmap, 0, 0, bitmap.getWidth(), bitmap.getHeight(), m);
      bitmap.recycle();
      return transformed;
    }
    return bitmap;
  }

  /**
   * Gets decoded bitmap that keeps orientation as well.
   */
  public Bitmap getBitmap(Uri uri, int width, int height) {
    Bitmap bitmap = decodeBitmap(uri, width, height);

    // Rotate the decoded bitmap according to its orientation if it's necessary.
    if (bitmap != null) {
      int orientation = getOrientation(uri);
      if (orientation != 0) {
        Matrix m = new Matrix();
        m.setRotate(orientation);
        Bitmap transformed = createBitmap(bitmap, 0, 0, bitmap.getWidth(), bitmap.getHeight(), m);
        bitmap.recycle();
        return transformed;
      }
    }
    return bitmap;
  }

  /**
   * Saves the bitmap by given directory, filename, and format; if the directory is given null,
   * then saves it under the cache directory.
   */
  public File saveBitmap(Bitmap bitmap, String directory, String filename, CompressFormat format) {

    if (directory == null) {
      directory = context.getCacheDir().getAbsolutePath();
    } else {
      // Check if the given directory exists or try to create it.
      File file = new File(directory);
      if (!file.isDirectory() && !file.mkdirs()) {
        return null;
      }
    }

    File file = null;
    OutputStream os = null;

    try {
      filename = (format == CompressFormat.PNG) ? filename + ".png" : filename + ".jpg";
      file = new File(directory, filename);
      os = new FileOutputStream(file);
      bitmap.compress(format, DEFAULT_COMPRESS_QUALITY, os);
    } catch (FileNotFoundException e) {
      e.printStackTrace();
    } finally {
      closeStream(os);
    }
    return file;
  }

  /**
   * 缩放bitmap
   * @param bitmap
   * @param w
   * @param h
   * @return
   */
  public static Bitmap zoomBitmap(Bitmap bitmap, float w, float h) {
    int width = bitmap.getWidth();
    int height = bitmap.getHeight();
    Matrix matrix = new Matrix();
    float scaleW = ((float) w / width);
    float scaleH = ((float) h / height);
    matrix.postScale(scaleW, scaleH);
    Bitmap newBmp = Bitmap.createBitmap(bitmap, 0, 0, width, height, matrix, true);

    return newBmp;
  }

  public static Bitmap rotateBitmap(Bitmap bitmap, float degrees) {
    if (degrees != 0 && bitmap != null) {
      Matrix m = new Matrix();
      m.postRotate(degrees);
      //             m.setRotate(degrees,
      //                     (float) bitmap.getWidth() / 2, (float) bitmap.getHeight() / 2);
      try {
        bitmap = Bitmap.createBitmap(bitmap, 0, 0, bitmap.getWidth(), bitmap.getHeight(), m, true);
        //                 if (bitmap != b2) {
        //                	 bitmap.recycle();  //Android开发网再次提示Bitmap操作完应该显示的释放
        //                	 bitmap = b2;
        //                 }
      } catch (OutOfMemoryError ex) {
        // Android建议大家如果出现了内存不足异常，最好return 原始的bitmap对象。.
      }
    }
    return bitmap;
  }

  public static Bitmap drawTextToBitmap(Context gContext, int gResId, String gText) {
    Log.i(TAG, "drawTextToBitmap = " + gText);
    Resources resources = gContext.getResources();
    float scale = resources.getDisplayMetrics().density;
    Bitmap bitmap = BitmapFactory.decodeResource(resources, gResId);

    Bitmap.Config bitmapConfig = bitmap.getConfig();
    // set default bitmap config if none
    if (bitmapConfig == null) {
      bitmapConfig = Bitmap.Config.ARGB_8888;
    }
    // resource bitmaps are imutable,
    // so we need to convert it to mutable one
    bitmap = bitmap.copy(bitmapConfig, true);

    Canvas canvas = new Canvas(bitmap);
    // new antialised Paint
    Paint paint = new Paint(Paint.ANTI_ALIAS_FLAG);
    // text color - #3D3D3D
    paint.setColor(Color.WHITE);
    // text size in pixels
    paint.setTextSize((int) (12 * scale));
    // text shadow
    //		  paint.setShadowLayer(1f, 0f, 1f, Color.WHITE);

    // draw text to the Canvas center
    Rect bounds = new Rect();
    paint.getTextBounds(gText, 0, gText.length(), bounds);
    int x = (bitmap.getWidth() - bounds.width()) / 2;
    int y = (bitmap.getHeight()) / 2 + (int) scale * 2;

    canvas.drawText(gText, x, y, paint);

    canvas.save();
    canvas.restore();

    return bitmap;
  }

}
