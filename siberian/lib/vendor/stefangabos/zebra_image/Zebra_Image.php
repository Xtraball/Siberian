<?php

/**
 *  Methods used with the {@link resize()} method.
 */
define('ZEBRA_IMAGE_BOXED', 0);
define('ZEBRA_IMAGE_NOT_BOXED', 1);
define('ZEBRA_IMAGE_CROP_TOPLEFT', 2);
define('ZEBRA_IMAGE_CROP_TOPCENTER', 3);
define('ZEBRA_IMAGE_CROP_TOPRIGHT', 4);
define('ZEBRA_IMAGE_CROP_MIDDLELEFT', 5);
define('ZEBRA_IMAGE_CROP_CENTER', 6);
define('ZEBRA_IMAGE_CROP_MIDDLERIGHT', 7);
define('ZEBRA_IMAGE_CROP_BOTTOMLEFT', 8);
define('ZEBRA_IMAGE_CROP_BOTTOMCENTER', 9);
define('ZEBRA_IMAGE_CROP_BOTTOMRIGHT', 10);

// this enables handling of partially broken JPEG files without warnings/errors
ini_set('gd.jpeg_ignore_warning', true);

/**
 *  A compact (one-file only) and lightweight PHP library for image manipulation providing methods for performing several
 *  types of image manipulation operations and applying filters to images.
 *
 *  Read more {@link https://github.com/stefangabos/Zebra_Image/ here}
 *
 *  @author     Stefan Gabos <contact@stefangabos.ro>
 *  @version    2.2.6 (last revision: May 22, 2017)
 *  @copyright  (c) 2006 - 2016 Stefan Gabos
 *  @license    http://www.gnu.org/licenses/lgpl-3.0.txt GNU LESSER GENERAL PUBLIC LICENSE
 *  @package    Zebra_Image
 */
class Zebra_Image {

    /**
     *  Indicates the file system permissions to be set for newly created images.
     *
     *  Better is to leave this setting as it is.
     *
     *  If you know what you are doing, here is how you can calculate the permission levels:
     *
     *  - 400 Owner Read
     *  - 200 Owner Write
     *  - 100 Owner Execute
     *  - 40 Group Read
     *  - 20 Group Write
     *  - 10 Group Execute
     *  - 4 Global Read
     *  - 2 Global Write
     *  - 1 Global Execute
     *
     *  Default is 0755
     *
     *  @var integer
     */
    public $chmod_value;

    /**
     *  If set to FALSE, images having both width and height smaller than the required width and height, will be left
     *  untouched ({@link jpeg_quality} and {@link png_compression} will still apply).
     *
     *  Available only for the {@link resize()} method
     *
     *  Default is TRUE
     *
     *  @var boolean
     */
    public $enlarge_smaller_images;

    /**
     *  In case of an error read this property's value to see the error's code.
     *
     *  Possible error codes are:
     *
     *  - 1:  source file could not be found
     *  - 2:  source file is not readable
     *  - 3:  could not write target file
     *  - 4:  unsupported source file format
     *  - 5:  unsupported target file format
     *  - 6:  GD library version does not support target file format
     *  - 7:  GD library is not installed!
     *  - 8:  "chmod" command is disabled via configuration
     *  - 9:  "exif_read_data" function is not available
     *
     *  Default is 0 (no error).
     *
     *  @var integer
     */
    public $error;

    /**
     *  If set to TRUE, images will be auto-rotated according to the {@link http://keyj.emphy.de/exif-orientation-rant/ Exif Orientation Tag}
     *  so that they are always shown correctly.
     *
     *  <samp>If you set this to TRUE you must also enable exif-support with --enable-exif. Windows users must enable both
     *  the php_mbstring.dll and php_exif.dll DLL's in php.ini. The php_mbstring.dll DLL must be loaded before the
     *  php_exif.dll DLL so adjust your php.ini accordingly. See {@link http://php.net/manual/en/exif.installation.php the PHP manual}</samp>
     *
     *  Default is FALSE
     *
     *  @since 2.2.4
     *
     *  @var boolean
     */
    public $auto_handle_exif_orientation;

    /**
     *  Indicates the quality of the output image (better quality means bigger file size).
     *
     *  Used only if the file at {@link target_path} is a JPG/JPEG image.
     *
     *  Range is 0 - 100
     *
     *  Default is 85
     *
     *  @var integer
     */
    public $jpeg_quality;

    /**
     *  Indicates the compression level of the output image (lower compression means bigger file size).
     *
     *  Available only if PHP version is 5.1.2+, and only if the file at {@link target_path} is a PNG image. It will be
     *  ignored otherwise.
     *
     *  Range is 0 - 9
     *
     *  Default is 9
     *
     *  @since 2.2
     *
     *  @var integer
     */
    public $png_compression;

    /**
     *  Specifies whether, upon resizing, images should preserve their aspect ratio.
     *
     *  Available only for the {@link resize()} method
     *
     *  Default is TRUE
     *
     *  @var boolean
     */
    public $preserve_aspect_ratio;

    /**
     *  Indicates whether a target files should preserve the source file's date/time.
     *
     *  Default is TRUE
     *
     *  @since 1.0.4
     *
     *  @var boolean
     */
    public $preserve_time;

    /**
     *  Indicates whether the target image should have a "sharpen" filter applied to it.
     *
     *  Can be very useful when creating thumbnails and should be used only when creating thumbnails.
     *
     *  <i>The sharpen filter relies on the "imageconvolution" PHP function which is available only for PHP version
     *  5.1.0+, and will leave the images unaltered for older versions!</i>
     *
     *  Default is FALSE
     *
     *  @since 2.2
     *
     *  @var boolean
     */
    public $sharpen_images;

    /**
     *  Path to an image file to apply the transformations to.
     *
     *  Supported file types are <b>GIF</b>, <b>PNG</b> and <b>JPEG</b>.
     *
     *  @var    string
     */
    public $source_path;

    /**
     *  Path (including file name) to where to save the transformed image.
     *
     *  <i>Can be a different than {@link source_path} - the type of the transformed image will be as indicated by the
     *  file's extension (supported file types are GIF, PNG and JPEG)</i>.
     *
     *  @var    string
     */
    public $target_path;

    /**
     *  Constructor of the class.
     *
     *  Initializes the class and the default properties
     *
     *  @return void
     */
    public function __construct() {

        // set default values for properties
        $this->chmod_value = 0755;

        $this->error = 0;

        $this->jpeg_quality = 85;

        $this->png_compression = 9;

        $this->preserve_aspect_ratio = $this->preserve_time = $this->enlarge_smaller_images = true;

        $this->sharpen_images = $this->auto_handle_exif_orientation = false;

        $this->source_path = $this->target_path = '';

    }

    /**
     *  Applies one or more filters to the image given as {@link source_path} and outputs it as the file specified as
     *  {@link target_path}.
     *
     *  <samp>This method is available only if the {@link http://php.net/manual/en/function.imagefilter.php imagefilter}
     *  function is available (available from PHP 5+), and will leave images unaltered otherwise.</samp>
     *
     *  <code>
     *  // include the Zebra_Image library
     *  require 'path/to/Zebra_Image.php';
     *
     *  // instantiate the class
     *  $img = new Zebra_Image();
     *
     *  // a source image
     *  $img->source_path = 'path/to/source.ext';
     *
     *  // path to where should the resulting image be saved
     *  // note that by simply setting a different extension to the file will
     *  // instruct the script to create an image of that particular type
     *  $img->target_path = 'path/to/target.ext';
     *
     *  // apply the "grayscale" filter
     *  $img->apply_filter('grayscale');
     *
     *  // apply the "contrast" filter
     *  $img->apply_filter('contrast', -20);
     *  </code>
     *
     *  You can also apply multiple filters at once. In this case, the method requires a single argument, an array of
     *  arrays, containing the filters and associated arguments, where applicable:
     *
     *  <code>
     *  // create a sepia effect
     *  // note how we're applying multiple filters at once
     *  // each filter is in its own array
     *  $img->apply_filter(array(
     *
     *      // first we apply the "grayscale" filter
     *      array('grayscale'),
     *
     *      // then we apply the "colorize" filter with 90, 60, 40 as
     *      // the values for red, green and blue
     *      array('colorize', 90, 60, 40),
     *
     *  ));
     *  </code>
     *
     *  @param  string  $filter     The (case-insensitive) name of the filter to apply. Can be one of the following:
     *
     *                              -   <b>brightness</b>       -   changes the brightness of the image; use <b>arg1</b>
     *                                                              to set the level of brightness; the range of brightness
     *                                                              is -255 to 255;
     *                              -   <b>colorize</b>         -   adds (subtracts) specified RGB values to each pixel;
     *                                                              use <b>arg1</b>, <b>arg2</b> and <b>arg3</b> in the
     *                                                              form of red, green, blue and <b>arg4</b> for the alpha
     *                                                              channel. the range for each color is -255 to 255 and
     *                                                              0 to 127 for alpha; <i>alpha support is available only
     *                                                              for PHP 5.2.5+</i>;
     *                              -   <b>contrast</b>         -   changes the contrast of the image; use <b>arg1</b>
     *                                                              to set the level of contrast; the range of contrast
     *                                                              is -100 to 100;
     *                              -   <b>gausian_blur</b>     -   blurs the image using the Gaussian method;
     *                              -   <b>grayscale</b>        -   converts the image into grayscale;
     *                              -   <b>edgedetect</b>       -   uses edge detection to highlight the edges in the image;
     *                              -   <b>emboss</b>           -   embosses the image;
     *                              -   <b>mean_removal</b>     -   uses mean removal to achieve a "sketchy" effect;
     *                              -   <b>negate</b>           -   reverses all the colors of the image;
     *                              -   <b>pixelate</b>         -   applies pixelation effect to the image, use <b>arg1</b>
     *                                                              to set the block size and <b>arg2</b> to set the
     *                                                              pixelation effect mode; <i>this filter is available
     *                                                              only for PHP 5.3.0+</i>;
     *                              -   <b>selective_blur</b>   -   blurs the image;
     *                              -   <b>smooth</b>           -   makes the image smoother. Use <b>arg1</b> to set the
     *                                                              level of smoothness. applies a 9-cell convolution matrix
     *                                                              where center pixel has the weight of <b>arg1</b> and
     *                                                              others weight of 1.0. the result is normalized by dividing
     *                                                              the sum with <b>arg1</b> + 8.0 (sum of the matrix).
     *                                                              any float is accepted;
     *
     *  @param  mixed   $arg1       Used by the following filters:
     *                              -   <b>brightness</b>       -   sets the brightness level (-255 to 255)
     *                              -   <b>contrast</b>         -   sets the contrast level (-100 to 100)
     *                              -   <b>colorize</b>         -   sets the value of the red component (-255 to 255)
     *                              -   <b>smooth</b>           -   sets the smoothness level
     *                              -   <b>pixelate</b>         -   sets the block size, in pixels
     *
     *  @param  mixed   $arg2       Used by the following filters:
     *                              -   <b>colorize</b>         -   sets the value of the green component (-255 to 255)
     *                              -   <b>pixelate</b>         -   whether to use advanced pixelation effect or not (defaults to FALSE).
     *
     *  @param  mixed   $arg3       Used by the following filters:
     *                              -   <b>colorize</b>         -   sets the value of the blue component (-255 to 255)
     *
     *  @param  mixed   $arg4       Used by the following filters:
     *                              -   <b>colorize</b>         -   alpha channel; a value between 0 and 127. 0 indicates
     *                                                              completely opaque while 127 indicates completely
     *                                                              transparent.
     *
     *  @since 2.2.2
     *
     *  @return boolean             Returns TRUE on success or FALSE on error.
     *
     *                              If {@link http://php.net/manual/en/function.imagefilter.php imagefilter} is not
     *                              available the method will return FALSE without setting an {@link error} code.
     *
     *                              If the requested filter doesn't exist, or invalid arguments are passed, the method
     *                              will trigger a warning.
     *
     *                              If FALSE is returned and you are sure that
     *                              {@link http://php.net/manual/en/function.imagefilter.php imagefilter} exists and that
     *                              the requested filter is valid, check the {@link error} property to see the error code.
     */
    public function apply_filter($filter, $arg1 = '', $arg2 = '', $arg3 = '', $arg4 = '') {

        // if "imagefilter" function exists and the requested filter exists
        if (function_exists('imagefilter'))

            // if image resource was successfully created
            if ($this->_create_from_source()) {

                // prepare the target image
                $target_identifier = $this->_prepare_image($this->source_width, $this->source_height, -1);

                // copy the original image
                imagecopyresampled(

                    $target_identifier,
                    $this->source_identifier,
                    0,
                    0,
                    0,
                    0,
                    $this->source_width,
                    $this->source_height,
                    $this->source_width,
                    $this->source_height

                );

                // if multiple filters are to be applied at once
                if (is_array($filter)) {

                    // iterate through the filters
                    foreach ($filter as $arguments)

                        // if filter exists
                        if (defined('IMG_FILTER_' . strtoupper($arguments[0]))) {

                            // try to apply the filter...
                            if (!@call_user_func_array('imagefilter', array_merge(array($target_identifier, constant('IMG_FILTER_' . strtoupper($arguments[0]))), array_slice($arguments, 1))))

                                // ...and trigger an error if the filter could not be applied
                                trigger_error('Invalid arguments used for "' . strtoupper($arguments[0]) . '" filter', E_USER_WARNING);

                        // if filter doesn't exists, trigger an error
                        } else trigger_error('Filter "' . strtoupper($arguments[0]) . '" is not available', E_USER_WARNING);

                // if a single filter is to be applied and it is available
                } elseif (defined('IMG_FILTER_' . strtoupper($filter))) {

                    // get all the arguments passed to the method
                    $arguments = func_get_args();

                    // try to apply the filter...
                    if (!@call_user_func_array('imagefilter', array_merge(array($target_identifier, constant('IMG_FILTER_' . strtoupper($filter))), array_slice($arguments, 1))))

                        // ...and trigger an error if the filter could not be applied
                        trigger_error('Invalid arguments used for "' . strtoupper($arguments[0]) . '" filter', E_USER_WARNING);

                // if filter doesn't exists, trigger an error
                } else trigger_error('Filter "' . strtoupper($arguments[0]) . '" is not available', E_USER_WARNING);

                // write image
                return $this->_write_image($target_identifier);

            }

        // if script gets this far, return false
        // note that we do not set the error level as it has been already set
        // by the _create_from_source() method earlier, if the case
        return false;

    }

    /**
     *  Crops a portion of the image given as {@link source_path} and outputs it as the file specified as {@link target_path}.
     *
     *  <code>
     *  // include the Zebra_Image library
     *  require 'path/to/Zebra_Image.php';
     *
     *  // instantiate the class
     *  $img = new Zebra_Image();
     *
     *  // a source image
     *  $img->source_path = 'path/to/source.ext';
     *
     *  // path to where should the resulting image be saved
     *  // note that by simply setting a different extension to the file will
     *  // instruct the script to create an image of that particular type
     *  $img->target_path = 'path/to/target.ext';
     *
     *  // crop a rectangle of 100x100 pixels, starting from the top-left corner
     *  $img->crop(0, 0, 100, 100);
     *  </code>
     *
     *  @param  integer     $start_x    x coordinate to start cropping from
     *
     *  @param  integer     $start_y    y coordinate to start cropping from
     *
     *  @param  integer     $end_x      x coordinate where to end the cropping
     *
     *  @param  integer     $end_y      y coordinate where to end the cropping
     *
     *  @since  1.0.4
     *
     *  @return boolean     Returns TRUE on success or FALSE on error.
     *
     *                      If FALSE is returned, check the {@link error} property to see the error code.
     */
    public function crop($start_x, $start_y, $end_x, $end_y) {

        // this method might be also called internally
        // in this case, there's a fifth argument that points to an already existing image identifier
        $args = func_get_args();

        // if fifth argument exists
        if (isset($args[4]) && is_resource($args[4])) {

            // that it is the image identifier that we'll be using further on
            $this->source_identifier = $args[4];

            // set this to true so that the script will continue to execute at the next IF
            $result = true;

        // if method is called as usually
        // try to create an image resource from source path
        } else $result = $this->_create_from_source();

        // if image resource was successfully created
        if ($result !== false) {

            // prepare the target image
            $target_identifier = $this->_prepare_image($end_x - $start_x, $end_y - $start_y, -1);

            // crop the image
            imagecopyresampled(

                $target_identifier,
                $this->source_identifier,
                0,
                0,
                $start_x,
                $start_y,
                $end_x - $start_x,
                $end_y - $start_y,
                $end_x - $start_x,
                $end_y - $start_y

            );

            // write image
            return $this->_write_image($target_identifier);

        }

        // if script gets this far, return false
        // note that we do not set the error level as it has been already set
        // by the _create_from_source() method earlier
        return false;

    }

    /**
     *  Flips both horizontally and vertically the image given as {@link source_path} and outputs the resulted image as
     *  {@link target_path}
     *
     *  <code>
     *  // include the Zebra_Image library
     *  require 'path/to/Zebra_Image.php';
     *
     *  // instantiate the class
     *  $img = new Zebra_Image();
     *
     *  // a source image
     *  $img->source_path = 'path/to/source.ext';
     *
     *  // path to where should the resulting image be saved
     *  // note that by simply setting a different extension to the file will
     *  // instruct the script to create an image of that particular type
     *  $img->target_path = 'path/to/target.ext';
     *
     *  // flip the image both horizontally and vertically
     *  $img->flip_both();
     *  </code>
     *
     *  @since 2.1
     *
     *  @return boolean     Returns TRUE on success or FALSE on error.
     *
     *                      If FALSE is returned, check the {@link error} property to see the error code.
     */
    public function flip_both() {

        return $this->_flip('both');

    }

    /**
     *  Flips horizontally the image given as {@link source_path} and outputs the resulted image as {@link target_path}
     *
     *  <code>
     *  // include the Zebra_Image library
     *  require 'path/to/Zebra_Image.php';
     *
     *  // instantiate the class
     *  $img = new Zebra_Image();
     *
     *  // a source image
     *  $img->source_path = 'path/to/source.ext';
     *
     *  // path to where should the resulting image be saved
     *  // note that by simply setting a different extension to the file will
     *  // instruct the script to create an image of that particular type
     *  $img->target_path = 'path/to/target.ext';
     *
     *  // flip the image horizontally
     *  $img->flip_horizontal();
     *  </code>
     *
     *  @return boolean     Returns TRUE on success or FALSE on error.
     *
     *                      If FALSE is returned, check the {@link error} property to see the error code.
     */
    public function flip_horizontal() {

        return $this->_flip('horizontal');

    }

    /**
     *  Flips vertically the image given as {@link source_path} and outputs the resulted image as {@link target_path}
     *
     *  <code>
     *  // include the Zebra_Image library
     *  require 'path/to/Zebra_Image.php';
     *
     *  // instantiate the class
     *  $img = new Zebra_Image();
     *
     *  // a source image
     *  $img->source_path = 'path/to/source.ext';
     *
     *  // path to where should the resulting image be saved
     *  // note that by simply setting a different extension to the file will
     *  // instruct the script to create an image of that particular type
     *  $img->target_path = 'path/to/target.ext';
     *
     *  // flip the image vertically
     *  $img->flip_vertical();
     *  </code>
     *
     *  @return boolean     Returns TRUE on success or FALSE on error.
     *
     *                      If FALSE is returned, check the {@link error} property to see the error code.
     */
    public function flip_vertical() {

        return $this->_flip('vertical');

    }

    /**
     *  Resizes the image given as {@link source_path} and outputs the resulted image as {@link target_path}.
     *
     *  <code>
     *  // include the Zebra_Image library
     *  require 'path/to/Zebra_Image.php';
     *
     *  // instantiate the class
     *  $img = new Zebra_Image();
     *
     *  // a source image
     *  $img->source_path = 'path/to/source.ext';
     *
     *  // path to where should the resulting image be saved
     *  // note that by simply setting a different extension to the file will
     *  // instruct the script to create an image of that particular type
     *  $img->target_path = 'path/to/target.ext';
     *
     *  // apply a "sharpen" filter to the resulting images
     *  $img->sharpen_images = true;
     *
     *  // resize the image to exactly 150x150 pixels, without altering aspect ratio, by using the CROP_CENTER method
     *  $img->resize(150, 150, ZEBRA_IMAGE_CROP_CENTER);
     *  </code>
     *
     *  @param  integer     $width              The width to resize the image to.
     *
     *                                          If set to <b>0</b>, the width will be automatically adjusted, depending
     *                                          on the value of the <b>height</b> argument so that the image preserves
     *                                          its aspect ratio.
     *
     *                                          If {@link preserve_aspect_ratio} is set to TRUE and both this and the
     *                                          <b>height</b> arguments are values greater than <b>0</b>, the image will
     *                                          be resized to the exact required width and height and the aspect ratio
     *                                          will be preserved - (also see the description for the <b>method</b>
     *                                          argument below on how can this be done).
     *
     *                                          If {@link preserve_aspect_ratio} is set to FALSE, the image will be
     *                                          resized to the required width and the aspect ratio will be ignored.
     *
     *                                          If both <b>width</b> and <b>height</b> are set to <b>0</b>, a copy of
     *                                          the source image will be created ({@link jpeg_quality} and
     *                                          {@link png_compression} will still apply).
     *
     *                                          If either <b>width</b> or <b>height</b> are set to <b>0</b>, the script
     *                                          will consider the value of the {@link preserve_aspect_ratio} to bet set
     *                                          to TRUE regardless of its actual value!
     *
     *  @param  integer     $height             The height to resize the image to.
     *
     *                                          If set to <b>0</b>, the height will be automatically adjusted, depending
     *                                          on the value of the <b>width</b> argument so that the image preserves
     *                                          its aspect ratio.
     *
     *                                          If {@link preserve_aspect_ratio} is set to TRUE and both this and the
     *                                          <b>width</b> arguments are values greater than <b>0</b>, the image will
     *                                          be resized to the exact required width and height and the aspect ratio
     *                                          will be preserved - (also see the description for the <b>method</b>
     *                                          argument below on how can this be done).
     *
     *                                          If {@link preserve_aspect_ratio} is set to FALSE, the image will be
     *                                          resized to the required height and the aspect ratio will be ignored.
     *
     *                                          If both <b>width</b> and <b>height</b> are set to <b>0</b>, a copy of
     *                                          the source image will be created ({@link jpeg_quality} and
     *                                          {@link png_compression} will still apply).
     *
     *                                          If either <b>height</b> or <b>width</b> are set to <b>0</b>, the script
     *                                          will consider the value of the {@link preserve_aspect_ratio} to bet set
     *                                          to TRUE regardless of its actual value!
     *
     *  @param  int     $method                 (Optional) Method to use when resizing images to exact width and height
     *                                          while preserving aspect ratio.
     *
     *                                          If the {@link preserve_aspect_ratio} property is set to TRUE and both the
     *                                          <b>width</b> and <b>height</b> arguments are values greater than <b>0</b>,
     *                                          the image will be resized to the exact given width and height and the
     *                                          aspect ratio will be preserved by using on of the following methods:
     *
     *                                          -   <b>ZEBRA_IMAGE_BOXED</b> - the image will be scaled so that it will
     *                                              fit in a box with the given width and height (both width/height will
     *                                              be smaller or equal to the required width/height) and then it will
     *                                              be centered both horizontally and vertically. The blank area will be
     *                                              filled with the color specified by the <b>bgcolor</b> argument. (the
     *                                              blank area will be filled only if the image is not transparent!)
     *
     *                                          -   <b>ZEBRA_IMAGE_NOT_BOXED</b> - the image will be scalled so that it
     *                                              <i>could</i> fit in a box with the given width and height but will
     *                                              not be enclosed in a box with given width and height. The new width/
     *                                              height will be both smaller or equal to the required width/height
     *
     *                                          -   <b>ZEBRA_IMAGE_CROP_TOPLEFT</b>
     *                                          -   <b>ZEBRA_IMAGE_CROP_TOPCENTER</b>
     *                                          -   <b>ZEBRA_IMAGE_CROP_TOPRIGHT</b>
     *                                          -   <b>ZEBRA_IMAGE_CROP_MIDDLELEFT</b>
     *                                          -   <b>ZEBRA_IMAGE_CROP_CENTER</b>
     *                                          -   <b>ZEBRA_IMAGE_CROP_MIDDLERIGHT</b>
     *                                          -   <b>ZEBRA_IMAGE_CROP_BOTTOMLEFT</b>
     *                                          -   <b>ZEBRA_IMAGE_CROP_BOTTOMCENTER</b>
     *                                          -   <b>ZEBRA_IMAGE_CROP_BOTTOMRIGHT</b>
     *
     *                                          For the methods involving crop, first the image is scaled so that both
     *                                          its sides are equal or greater than the respective sizes of the bounding
     *                                          box; next, a region of required width and height will be cropped from
     *                                          indicated region of the resulted image.
     *
     *                                          Default is ZEBRA_IMAGE_CROP_CENTER
     *
     *  @param  hexadecimal $background_color   (Optional) The hexadecimal color (like "#FFFFFF" or "#FFF") of the
     *                                          blank area. See the <b>method</b> argument.
     *
     *                                          When set to -1 the script will preserve transparency for transparent GIF
     *                                          and PNG images. For non-transparent images the background will be white
     *                                          in this case.
     *
     *                                          Default is #FFFFFF.
     *
     *  @return boolean                         Returns TRUE on success or FALSE on error.
     *
     *                                          If FALSE is returned, check the {@link error} property to see what went
     *                                          wrong
     */
    public function resize($width = 0, $height = 0, $method = ZEBRA_IMAGE_CROP_CENTER, $background_color = '#FFFFFF') {

        // if image resource was successfully created
        if ($this->_create_from_source()) {

            // if either width or height is to be adjusted automatically
            // set a flag telling the script that, even if $preserve_aspect_ratio is set to false
            // treat everything as if it was set to true
            if ($width == 0 || $height == 0) $auto_preserve_aspect_ratio = true;

            // if aspect ratio needs to be preserved
            if ($this->preserve_aspect_ratio || isset($auto_preserve_aspect_ratio)) {

                // if height is given and width is to be computed accordingly
                if ($width == 0 && $height > 0) {

                    // get the original image's aspect ratio
                    $aspect_ratio = $this->source_width / $this->source_height;

                    // the target image's height is as given as argument to the method
                    $target_height = $height;

                    // compute the target image's width, preserving the aspect ratio
                    $target_width = round($height * $aspect_ratio);

                // if width is given and height is to be computed accordingly
                } elseif ($width > 0 && $height == 0) {

                    // get the original image's aspect ratio
                    $aspect_ratio = $this->source_height / $this->source_width;

                    // the target image's width is as given as argument to the method
                    $target_width = $width;

                    // compute the target image's height, preserving the aspect ratio
                    $target_height = round($width * $aspect_ratio);

                // if both width and height are given and ZEBRA_IMAGE_BOXED or ZEBRA_IMAGE_NOT_BOXED methods are to be used
                } elseif ($width > 0 && $height > 0 && ($method == 0 || $method == 1)) {

                    // compute the horizontal and vertical aspect ratios
                    $vertical_aspect_ratio = $height / $this->source_height;
                    $horizontal_aspect_ratio = $width / $this->source_width;

                    // if the image's newly computed height would be inside the bounding box
                    if (round($horizontal_aspect_ratio * $this->source_height < $height)) {

                        // the target image's width is as given as argument to the method
                        $target_width = $width;

                        // compute the target image's height so that the image will stay inside the bounding box
                        $target_height = round($horizontal_aspect_ratio * $this->source_height);

                    // otherwise
                    } else {

                        // the target image's height is as given as argument to the method
                        $target_height = $height;

                        // compute the target image's width so that the image will stay inside the bounding box
                        $target_width = round($vertical_aspect_ratio * $this->source_width);

                    }

                // if both width and height are given and image is to be cropped in order to get to the required size
                } elseif ($width > 0 && $height > 0 && $method > 1 && $method < 11) {

                    // compute the horizontal and vertical aspect ratios
                    $vertical_aspect_ratio = $this->source_height / $height;
                    $horizontal_aspect_ratio = $this->source_width /  $width;

                    // we'll use one of the two
                    $aspect_ratio =

                        $vertical_aspect_ratio < $horizontal_aspect_ratio ?

                        $vertical_aspect_ratio :

                        $horizontal_aspect_ratio;

                    // compute the target image's width, preserving the aspect ratio
                    $target_width = round($this->source_width / $aspect_ratio);

                    // compute the target image's height, preserving the aspect ratio
                    $target_height = round($this->source_height / $aspect_ratio);

                // for any other case
                } else {

                    // we will create a copy of the source image
                    $target_width = $this->source_width;
                    $target_height = $this->source_height;

                }

            // if aspect ratio does not need to be preserved
            } else {

                // compute the target image's width
                $target_width = ($width > 0 ? $width : $this->source_width);

                // compute the target image's height
                $target_height = ($height > 0 ? $height : $this->source_height);

            }

            // if
            if (

                // all images are to be resized - including images that are smaller than the given width/height
                $this->enlarge_smaller_images ||

                // smaller images than the given width/height are to be left untouched
                // but current image has at leas one side that is larger than the required width/height
                ($width > 0 && $height > 0 ?

                    ($this->source_width > $width || $this->source_height > $height) :

                    ($this->source_width > $target_width || $this->source_height > $target_height)

                )

            ) {

                // if
                if (

                    // aspect ratio needs to be preserved AND
                    ($this->preserve_aspect_ratio || isset($auto_preserve_aspect_ratio)) &&

                    // both width and height are given
                    ($width > 0 && $height > 0) &&

                    // images are to be cropped
                    ($method > 1 && $method < 11)

                ) {

                    // prepare the target image
                    $target_identifier = $this->_prepare_image($target_width, $target_height, $background_color);

                    imagecopyresampled(

                        $target_identifier,
                        $this->source_identifier,
                        0,
                        0,
                        0,
                        0,
                        $target_width,
                        $target_height,
                        $this->source_width,
                        $this->source_height

                    );

                    // do the crop according to the required method
                    switch ($method) {

                        // if image needs to be cropped from the top-left corner
                        case ZEBRA_IMAGE_CROP_TOPLEFT:

                            // crop accordingly
                            return $this->crop(
                                0,
                                0,
                                $width,
                                $height,
                                $target_identifier // crop this resource instead
                            );

                            break;

                        // if image needs to be cropped from the top-center
                        case ZEBRA_IMAGE_CROP_TOPCENTER:

                            // crop accordingly
                            return $this->crop(
                                floor(($target_width - $width) / 2),
                                0,
                                floor(($target_width - $width) / 2) + $width,
                                $height,
                                $target_identifier // crop this resource instead
                            );

                            break;

                        // if image needs to be cropped from the top-right corner
                        case ZEBRA_IMAGE_CROP_TOPRIGHT:

                            // crop accordingly
                            return $this->crop(
                                $target_width - $width,
                                0,
                                $target_width,
                                $height,
                                $target_identifier // crop this resource instead
                            );

                            break;

                        // if image needs to be cropped from the middle-left
                        case ZEBRA_IMAGE_CROP_MIDDLELEFT:

                            // crop accordingly
                            return $this->crop(

                                0,
                                floor(($target_height - $height) / 2),
                                $width,
                                floor(($target_height - $height) / 2) + $height,
                                $target_identifier // crop this resource instead

                            );

                            break;

                        // if image needs to be cropped from the center of the image
                        case ZEBRA_IMAGE_CROP_CENTER:

                            // crop accordingly
                            return $this->crop(

                                floor(($target_width - $width) / 2),
                                floor(($target_height - $height) / 2),
                                floor(($target_width - $width) / 2) + $width,
                                floor(($target_height - $height) / 2) + $height,
                                $target_identifier // crop this resource instead

                            );

                            break;

                        // if image needs to be cropped from the middle-right
                        case ZEBRA_IMAGE_CROP_MIDDLERIGHT:

                            // crop accordingly
                            return $this->crop(

                                $target_width - $width,
                                floor(($target_height - $height) / 2),
                                $target_width,
                                floor(($target_height - $height) / 2) + $height,
                                $target_identifier // crop this resource instead

                            );

                            break;

                        // if image needs to be cropped from the bottom-left corner
                        case ZEBRA_IMAGE_CROP_BOTTOMLEFT:

                            // crop accordingly
                            return $this->crop(

                                0,
                                $target_height - $height,
                                $width,
                                $target_height,
                                $target_identifier // crop this resource instead

                            );

                            break;

                        // if image needs to be cropped from the bottom-center
                        case ZEBRA_IMAGE_CROP_BOTTOMCENTER:

                            // crop accordingly
                            return $this->crop(

                                floor(($target_width - $width) / 2),
                                $target_height - $height,
                                floor(($target_width - $width) / 2) + $width,
                                $target_height,
                                $target_identifier // crop this resource instead

                            );

                            break;

                        // if image needs to be cropped from the bottom-right corner
                        case ZEBRA_IMAGE_CROP_BOTTOMRIGHT:

                            // crop accordingly
                            return $this->crop(

                                $target_width - $width,
                                $target_height - $height,
                                $target_width,
                                $target_height,
                                $target_identifier // crop this resource instead

                            );

                            break;

                    }

                // if aspect ratio doesn't need to be preserved or
                // it needs to be preserved and method is ZEBRA_IMAGE_BOXED or ZEBRA_IMAGE_NOT_BOXED
                } else {

                    // prepare the target image
                    $target_identifier = $this->_prepare_image(
                        ($width > 0 && $height > 0 && $method != ZEBRA_IMAGE_NOT_BOXED ? $width : $target_width),
                        ($width > 0 && $height > 0 && $method != ZEBRA_IMAGE_NOT_BOXED ? $height : $target_height),
                        $background_color
                    );

                    imagecopyresampled(

                        $target_identifier,
                        $this->source_identifier,
                        ($width > 0 && $height > 0 && $method != ZEBRA_IMAGE_NOT_BOXED ? ($width - $target_width) / 2 : 0),
                        ($width > 0 && $height > 0 && $method != ZEBRA_IMAGE_NOT_BOXED ? ($height - $target_height) / 2 : 0),
                        0,
                        0,
                        $target_width,
                        $target_height,
                        $this->source_width,
                        $this->source_height

                    );

                    // if script gets this far, write the image to disk
                    return $this->_write_image($target_identifier);

                }

            // if we get here it means that
            // smaller images than the given width/height are to be left untouched
            // therefore, we save the image as it is
            } else return $this->_write_image($this->source_identifier);

        }

        // if script gets this far return false
        // note that we do not set the error level as it has been already set
        // by the _create_from_source() method earlier
        return false;

    }

    /**
     *  Rotates the image given as {@link source_path} and outputs the resulted image as {@link target_path}.
     *
     *  <code>
     *  // include the Zebra_Image library
     *  require 'path/to/Zebra_Image.php';
     *
     *  // instantiate the class
     *  $img = new Zebra_Image();
     *
     *  // a source image
     *  $img->source_path = 'path/to/source.ext';
     *
     *  // path to where should the resulting image be saved
     *  // note that by simply setting a different extension to the file will
     *  // instruct the script to create an image of that particular type
     *  $img->target_path = 'path/to/target.ext';
     *
     *  // rotate the image 45 degrees, clockwise
     *  $img->rotate(45);
     *  </code>
     *
     *  @param  double  $angle                  Angle by which to rotate the image clockwise.
     *
     *                                          Between 0 and 360.
     *
     *  @param  mixed   $background_color       (Optional) The hexadecimal color (like "#FFFFFF" or "#FFF") of the
     *                                          uncovered zone after the rotation.
     *
     *                                          When set to -1 the script will preserve transparency for transparent GIF
     *                                          and PNG images. For non-transparent images the background will be white
     *                                          in this case.
     *
     *                                          Default is -1.
     *
     *  @return boolean                         Returns TRUE on success or FALSE on error.
     *
     *                                          If FALSE is returned, check the {@link error} property to see the error
     *                                          code.
     */
    public function rotate($angle, $background_color = -1) {

        // get function arguments
        $arguments = func_get_args();

        // if a third argument exists
        $use_existing_source = (isset($arguments[2]) && $arguments[2] === false);

        // if we came here just to fix orientation or if image resource was successfully created
        if ($use_existing_source || $this->_create_from_source()) {

            // angles are given clockwise but imagerotate works counterclockwise so we need to negate our value
            $angle = -$angle;

            // if source image is PNG
            if ($this->source_type == IMAGETYPE_PNG && $background_color == -1) {

                // rotate the image
                // but if using -1 as background color didn't work (as is the case for PNG8)
                if (!($target_identifier = imagerotate($this->source_identifier, $angle, -1))) {

                    // we will be using #FFF as the color to fill the uncovered zone after the rotation
                    $background_color = imagecolorallocate($this->source_identifier, 255, 255, 255);

                    // rotate the image
                    $target_identifier = imagerotate($this->source_identifier, $angle, $background_color);

                }

            // if source image is a transparent GIF
            } elseif ($this->source_type == IMAGETYPE_GIF && $this->source_transparent_color_index >= 0) {

                // convert the background color to RGB values
                $background_color = $this->_hex2rgb($background_color);

                // allocate the color to the image identifier
                $background_color = imagecolorallocate(

                    $this->source_identifier,
                    $background_color['r'],
                    $background_color['g'],
                    $background_color['b']

                );

                // rotate the image
                $this->source_identifier = imagerotate($this->source_identifier, $angle, $background_color);

                // get the width of rotated image
                $width = imagesx($this->source_identifier);

                // get the height of rotated image
                $height = imagesy($this->source_identifier);

                // create a blank image with the new width and height
                // (this intermediary step is for preserving transparency)
                $target_identifier = $this->_prepare_image($width, $height, -1);

                // copy the rotated image on to the new one
                imagecopyresampled($target_identifier, $this->source_identifier, 0, 0, 0, 0, $width, $height, $width, $height);

            // for the other cases
            } else {

                // convert the color to RGB values
                $background_color = $this->_hex2rgb($background_color);

                // allocate the color to the image identifier
                $background_color = imagecolorallocate(

                    $this->source_identifier,
                    $background_color['r'],
                    $background_color['g'],
                    $background_color['b']

                );

                // rotate the image
                $target_identifier = imagerotate($this->source_identifier, $angle, $background_color);

            }

            // if we called this method from the _create_from_source() method
            // because we are fixing orientation
            if ($use_existing_source) {

                // make any further method work on the rotated image
                $this->source_identifier = $target_identifier;

                // update the width and height of the image to the values
                // of the rotated image
                $this->source_width = imagesx($target_identifier);
                $this->source_height = imagesy($target_identifier);

                return true;

            // write image otherwise
            } else return $this->_write_image($target_identifier);

        }

        // if script gets this far return false
        // note that we do not set the error level as it has been already set
        // by the _create_from_source() method earlier
       return false;

    }

    /**
     *  Returns an array containing the image identifier representing the image obtained from {@link $source_path}, the
     *  image's width and height and the image's type
     *
     *  @access private
     */
    private function _create_from_source() {

        // perform some error checking first
        // if the GD library is not installed
        if (!function_exists('gd_info')) {

            // save the error level and stop the execution of the script
            $this->error = 7;

            return false;

        // if source file does not exist
        } elseif (!is_file($this->source_path)) {

            // save the error level and stop the execution of the script
            $this->error = 1;

            return false;

        // if source file is not readable
        } elseif (!is_readable($this->source_path)) {

            // save the error level and stop the execution of the script
            $this->error = 2;

            return false;

        // if target file is same as source file and source file is not writable
        } elseif ($this->target_path == $this->source_path && !is_writable($this->source_path)) {

            // save the error level and stop the execution of the script
            $this->error = 3;

            return false;

        // try to get source file width, height and type
        // and if it founds an unsupported file type
        } elseif (!list($this->source_width, $this->source_height, $this->source_type) = @getimagesize($this->source_path)) {

            // save the error level and stop the execution of the script
            $this->error = 4;

            return false;

        // if no errors so far
        } else {

            // get target file's type based on the file extension
            $this->target_type = strtolower(substr($this->target_path, strrpos($this->target_path, '.') + 1));

            // create an image from file using extension dependant function
            // checks for file extension
            switch ($this->source_type) {

                // if GIF
                case IMAGETYPE_GIF:

                    // create an image from file
                    $identifier = imagecreatefromgif($this->source_path);

                    // get the index of the transparent color (if any)
                    if (($this->source_transparent_color_index = imagecolortransparent($identifier)) >= 0)

                        // get the transparent color's RGB values
                        // we have to mute errors because there are GIF images which *are* transparent and everything
                        // works as expected, but imagecolortransparent() returns a color that is outside the range of
                        // colors in the image's pallette...
                        $this->source_transparent_color = @imagecolorsforindex($identifier, $this->source_transparent_color_index);

                    break;

                // if JPEG
                case IMAGETYPE_JPEG:

                    // create an image from file
                    $identifier = imagecreatefromjpeg($this->source_path);

                    break;

                // if PNG
                case IMAGETYPE_PNG:

                    // create an image from file
                    $identifier = imagecreatefrompng($this->source_path);

                    // disable blending
                    imagealphablending($identifier, false);

                    break;

                default:

                    // if unsupported file type
                    // note that we call this if the file is not GIF, JPG or PNG even though the getimagesize function
                    // handles more image types
                    $this->error = 4;

                    return false;

            }

        }

        // if target file has to have the same timestamp as the source image
        // save it as a global property of the class
        if ($this->preserve_time) $this->source_image_time = filemtime($this->source_path);

        // make available the source image's identifier
        $this->source_identifier = $identifier;

        // if we need to handle exif orientation automatically
        if ($this->auto_handle_exif_orientation)

            // if "exif_read_data" function is not available, return false
            if (!function_exists('exif_read_data')) {

                // save the error level and stop the execution of the script
                $this->error = 9;

                return false;

            // if "exif_read_data" function is available, EXIF information is available, orientation information is available and orientation needs fixing
            } elseif (($exif = exif_read_data($this->source_path)) && isset($exif['Orientation']) && in_array($exif['Orientation'], array(3, 6, 8))) {

                // fix the orientation
                switch ($exif['Orientation']) {

                    case 3:

                        // 180 rotate left
                        $this->rotate(180, -1, false);
                        break;

                    case 6:

                        // 90 rotate right
                        $this->rotate(90, -1, false);
                        break;

                    case 8:

                        // 90 rotate left
                        $this->rotate(-90, -1, false);
                        break;

                }

            }

        return true;

    }

    /**
     *  Flips horizontally or vertically or both ways the image given as {@link source_path}.
     *
     *  @since 2.1
     *
     *  @access private
     *
     *  @return boolean     Returns TRUE on success or FALSE on error.
     *
     *                      If FALSE is returned, check the {@link error} property to see the error code.
     */
    private function _flip($orientation) {

        // if image resource was successfully created
        if ($this->_create_from_source()) {

            // prepare the target image
            $target_identifier = $this->_prepare_image($this->source_width, $this->source_height, -1);

            // flip according to $orientation
            switch ($orientation) {

                case 'horizontal':

                    imagecopyresampled(

                        $target_identifier,
                        $this->source_identifier,
                        0,
                        0,
                        ($this->source_width - 1),
                        0,
                        $this->source_width,
                        $this->source_height,
                        -$this->source_width,
                        $this->source_height

                    );

                    break;

                case 'vertical':

                    imagecopyresampled(

                        $target_identifier,
                        $this->source_identifier,
                        0,
                        0,
                        0,
                        ($this->source_height - 1),
                        $this->source_width,
                        $this->source_height,
                        $this->source_width,
                        -$this->source_height

                    );

                    break;

                case 'both':

                    imagecopyresampled(

                        $target_identifier,
                        $this->source_identifier,
                        0,
                        0,
                        ($this->source_width - 1),
                        ($this->source_height - 1),
                        $this->source_width,
                        $this->source_height,
                        -$this->source_width,
                        -$this->source_height

                    );

                    break;

            }

            // write image
            return $this->_write_image($target_identifier);

        }

        // if script gets this far, return false
        // note that we do not set the error level as it has been already set
        // by the _create_from_source() method earlier
        return false;

    }

    /**
     *  Converts a hexadecimal representation of a color (i.e. #123456 or #AAA) to a RGB representation.
     *
     *  The RGB values will be a value between 0 and 255 each.
     *
     *  @param  string  $color              Hexadecimal representation of a color (i.e. #123456 or #AAA).
     *
     *  @param  string  $default_on_error   Hexadecimal representation of a color to be used in case $color is not
     *                                      recognized as a hexadecimal color.
     *
     *  @return array                       Returns an associative array with the values of (R)ed, (G)reen and (B)lue
     *
     *  @access private
     */
    private function _hex2rgb($color, $default_on_error = '#FFFFFF') {

        // if color is not formatted correctly
        // use the default color
        if (preg_match('/^#?([a-f]|[0-9]){3}(([a-f]|[0-9]){3})?$/i', $color) == 0) $color = $default_on_error;

        // trim off the "#" prefix from $background_color
        $color = ltrim($color, '#');

        // if color is given using the shorthand (i.e. "FFF" instead of "FFFFFF")
        if (strlen($color) == 3) {

            $tmp = '';

            // take each value
            // and duplicate it
            for ($i = 0; $i < 3; $i++) $tmp .= str_repeat($color[$i], 2);

            // the color in it's full, 6 characters length notation
            $color = $tmp;

        }

        // decimal representation of the color
        $int = hexdec($color);

        // extract and return the RGB values
        return array(

            'r' =>  0xFF & ($int >> 0x10),
            'g' =>  0xFF & ($int >> 0x8),
            'b' =>  0xFF & $int

        );

    }

    /**
     *  Creates a blank image of given width, height and background color.
     *
     *  @param  integer     $width              Width of the new image.
     *
     *  @param  integer     $height             Height of the new image.
     *
     *  @param  string      $background_color   (Optional) The hexadecimal color of the background.
     *
     *                                          Can also be -1 case in which the script will try to create a transparent
     *                                          image, if possible.
     *
     *                                          Default is "#FFFFFF".
     *
     *  @return                                 Returns the identifier of the newly created image.
     *
     *  @access private
     */
    private function _prepare_image($width, $height, $background_color = '#FFFFFF') {

        // create a blank image
        $identifier = imagecreatetruecolor((int)$width <= 0 ? 1 : (int)$width, (int)$height <= 0 ? 1 : (int)$height);

        // if we are creating a PNG image
        if ($this->target_type == 'png' && $background_color == -1) {

            // disable blending
            imagealphablending($identifier, false);

            // allocate a transparent color
            $transparent_color = imagecolorallocatealpha($identifier, 0, 0, 0, 127);

            // fill the image with the transparent color
			imagefill($identifier, 0, 0, $transparent_color);

            //save full alpha channel information
			imagesavealpha($identifier, true);

        // if source image is a transparent GIF
        } elseif ($this->target_type == 'gif' && $background_color == -1 && $this->source_transparent_color_index >= 0) {

            // allocate the source image's transparent color also to the new image resource
            $transparent_color = imagecolorallocate(
                $identifier,
                $this->source_transparent_color['red'],
                $this->source_transparent_color['green'],
                $this->source_transparent_color['blue']
            );

            // fill the background of the new image with transparent color
            imagefill($identifier, 0, 0, $transparent_color);

            // from now on, every pixel having the same RGB as the transparent color will be transparent
            imagecolortransparent($identifier, $transparent_color);

        // for other image types
        } else {

            // if transparent background color specified, revert to white
            if ($background_color == -1) $background_color = '#FFFFFF';

            // convert hex color to rgb
            $background_color = $this->_hex2rgb($background_color);

            // prepare the background color
            $background_color = imagecolorallocate($identifier, $background_color['r'], $background_color['g'], $background_color['b']);

            // fill the image with the background color
            imagefill($identifier, 0, 0, $background_color);

        }

        // return the image's identifier
        return $identifier;

    }

    /**
     *  Sharpens images. Useful when creating thumbnails.
     *
     *  Code taken from the comments at {@link http://docs.php.net/imageconvolution}.
     *
     *  <i>This function will yield a result only for PHP version 5.1.0+ and will leave the image unaltered for older
     *  versions!</i>
     *
     *  @param  $identifier identifier  An image identifier
     *
     *  @access private
     */
    private function _sharpen_image($image) {

        // if the "sharpen_images" is set to true and we're running an appropriate version of PHP
        // (the "imageconvolution" is available only for PHP 5.1.0+)
        if ($this->sharpen_images && version_compare(PHP_VERSION, '5.1.0') >= 0) {

            // the convolution matrix as an array of three arrays of three floats
            $matrix = array(
                array(-1.2, -1, -1.2),
                array(-1, 20, -1),
                array(-1.2, -1, -1.2),
            );

            // the divisor of the matrix
            $divisor = array_sum(array_map('array_sum', $matrix));

            // color offset
            $offset = 0;

            // sharpen image
            imageconvolution($image, $matrix, $divisor, $offset);

        }

        // return the image's identifier
        return $image;

    }

    /**
     *  Creates a new image from given image identifier having the extension as specified by {@link target_path}.
     *
     *  @param  $identifier identifier  An image identifier
     *
     *  @return boolean                 Returns TRUE on success or FALSE on error.
     *
     *                                  If FALSE is returned, check the {@link error} property to see the error code.
     *
     *  @access private
     */
    private function _write_image($identifier) {

        // sharpen image if it's required
        $this->_sharpen_image($identifier);

        // image saving process goes according to required extension
        switch ($this->target_type) {

            // if GIF
            case 'gif':

                // if GD support for this file type is not available
                // in version 1.6 of GD the support for GIF files was dropped see
                // http://php.net/manual/en/function.imagegif.php#function.imagegif.notes
                if (!function_exists('imagegif')) {

                    // save the error level and stop the execution of the script
                    $this->error = 6;

                    return false;

                // if, for some reason, file could not be created
                } elseif (@!imagegif($identifier, $this->target_path)) {

                    // save the error level and stop the execution of the script
                    $this->error = 3;

                    return false;

                }

                break;

            // if JPEG
            case 'jpg':
            case 'jpeg':

                // if GD support for this file type is not available
                if (!function_exists('imagejpeg')) {

                    // save the error level and stop the execution of the script
                    $this->error = 6;

                    return false;

                // if, for some reason, file could not be created
                } elseif (@!imagejpeg($identifier, $this->target_path, $this->jpeg_quality)) {

                    // save the error level and stop the execution of the script
                    $this->error = 3;

                    return false;

                }

                break;

            // if PNG
            case 'png':

                // save full alpha channel information
                imagesavealpha($identifier, true);

                // if GD support for this file type is not available
                if (!function_exists('imagepng')) {

                    // save the error level and stop the execution of the script
                    $this->error = 6;

                    return false;

                // if, for some reason, file could not be created
                } elseif (@!imagepng($identifier, $this->target_path, $this->png_compression)) {

                    // save the error level and stop the execution of the script
                    $this->error = 3;

                    return false;

                }

                break;

            // if not a supported file extension
            default:

                // save the error level and stop the execution of the script
                $this->error = 5;

                return false;

        }

        // get a list of functions disabled via configuration
        $disabled_functions = @ini_get('disable_functions');

        // if the 'chmod' function is not disabled via configuration
        if ($disabled_functions == '' || strpos('chmod', $disabled_functions) === false) {

            // chmod the file
            chmod($this->target_path, intval($this->chmod_value, 8));

        // save the error level
        } else $this->error = 8;

        // if target file has to have the same timestamp as the source image
        if ($this->preserve_time && isset($this->source_image_time)) {

            // touch the newly created file
            @touch($this->target_path, $this->source_image_time);

        }

        // return true
        return true;

    }

}
