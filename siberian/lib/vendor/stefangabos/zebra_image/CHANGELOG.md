## version 2.8.2 (January 25, 2023)

- added support for BMP; see [#27](https://github.com/stefangabos/Zebra_Image/issues/27); thanks [icret](https://github.com/icret) for suggesting
- fixed some issues with WEBP images

## version 2.8.1 (December 29, 2022)

- fixed an issue where in PHP 8 the script would break with certain GIF images; see [#26](https://github.com/stefangabos/Zebra_Image/issues/26) - thank you [Marcus Nyberg](https://github.com/mce1978) for reporting!
- fixed issue where animated WEBP images would break the script; animated WEBP images are not (yet) supported by GD; thanks to [Yani](https://github.com/yani) for reporting this one - see [#25](https://github.com/stefangabos/Zebra_Image/issues/25)

## version 2.8.0 (August 17, 2022)

- fixed potential warning when dealing with bad EXIF information; see [#24](https://github.com/stefangabos/Zebra_Image/pull/24); thank you [userlond](https://github.com/userlond) for the fix!
- fixed a bug with `WEBP` images in PHP version lower than 7.0.1
- fixed a potential bug when using `ZEBRA_IMAGE_BOXED` or `ZEBRA_IMAGE_NOT_BOXED` methods when resizing
- lots of minor bug fixes and source code formatting because we are now using [PHPStan](https://github.com/phpstan/phpstan) for static code analysis and [PHP CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer) for detecting coding standards violations, which are now [PSR12](https://www.php-fig.org/psr/psr-12/)-ish with a few of the rules excluded

## version 2.7.0 (May 29, 2022)

- fixed an issue where starting with PHP 8.0 GdImage class objects replace GD image resources; see [here](https://php.watch/versions/8.0/gdimage)
- fixed an issue where starting with PHP 8.1 sending a float value as width/height arguments instead of an interger would trigger a warning; see [here](https://php.watch/versions/8.1/deprecate-implicit-conversion-incompatible-float-string)

## version 2.6.0 (May 29, 2020)

- added sport for the WEBP format
- handle GD bug where transparency is lost when rotating at angles of 90, 180 and 270 degrees
- fixed [#22](https://github.com/stefangabos/Zebra_Image/issues/22); thanks to [Almir Neto](https://github.com/AlmirNeeto99) for reporting!

## version 2.5.0 (February 16, 2020)

- fixed bug introduced in previous release because of the new argument added to the crop method
- progressive JPEGs can now be saved by setting the newly added [jpeg_interlace](https://stefangabos.github.io/Zebra_Image/Zebra_Image/Zebra_Image.html#var$jpeg_interlace) property; thanks [tohizma](https://github.com/tohizma) for suggesting!

## version 2.4.0 (January 24, 2020)

- added the `background_color` argument to the [crop](https://stefangabos.github.io/Zebra_Image/Zebra_Image/Zebra_Image.html#methodcrop) method; used when the cropping coordinates are off-scale (negative values and/or values greater than the image's size) to fill the remaining space; see #18; thank you [Thomas Skerbis](https://github.com/skerbis)!

## version 2.3.0 (June 06, 2019)

- the default value of the "background_color" argument of the "resize" method is now -1 (for preserving transparency)
- fixed bug where saving to gif would lose transparency
- fixed transparency not being preserved when rotating PNG images
- fixed warnings shown when the target file was a gif but the source file was not
- memory is freed after images are written

## version 2.2.6 (May 22, 2017)

- minor source code tweaks
- unnecessary files are no more included when downloading from GitHub or via Composer
- documentation is now available in the repository and on GitHub
- the home of the library is now exclusively on GitHub

## version 2.2.5 (May 16, 2016)

- use the newly added "auto_handle_exif_orientation" property for auto fixing image rotation if EXIF information is available; requires PHP to be configured with exif-support via --enable-exif (or, for Windows user, by enabling the php_mbstring.dll and php_exif.dll extensions); thanks to <strong>Sebi Popa</strong>
- better integration with composer; thanks to <strong>Richard Griffith</strong>
- dropped support for PHP4; the library now requires PHP5+

## version 2.2.3 (July 14, 2013)

- removed the error muting used for imagecreatefrom{gif,jpeg,png} calls in the "_create_from_source" private method, which would cause the script to silently fail if the memory limit was exceeded; thanks to <strong>Eren Türkay</strong>
- project is now available on <a href="https://github.com/stefangabos/Zebra_Image">GitHub</a> and as a <a href="https://packagist.org/packages/stefangabos/zebra_image">package for Composer</a>

## version 2.2.2 (August 31, 2012)

- fixed a bug where if there were no disabled PHP functions (through php.ini), the library would always return error code 8; thanks to <strong>Jim Li</strong>
- filters can now be applied to images; the existing filters are those handled by PHP's <a href="http://php.net/manual/en/function.imagefilter.php">imagefilter</a> function: negate, grayscale, brightness, contrast, colorize, edgedetect, emboss, gaussian blur, selective blur, mean removal, smooth and pixelate; multiple filters can be applied at once for creating custom filters

## version 2.2.1 (September 09, 2011)

- fixed two bugs that appeared since the last version that would cause the script to throw warnings; thanks to <strong>NIXin</strong> for reporting

## version 2.2 (September 06, 2011)

- a new property is now available: <em>png_compression</em>, which determines the compression level of PNG files; this value of this property is ignored for PHP versions older than 5.1.2; thanks to <strong>Julien</strong> for suggesting
- a new property is now available: <em>sharpen_images</em> which, when enabled, will instruct the script to apply a "sharpen" filter to the resulting images; can be very useful when creating thumbnails but should be used only when creating thumbnails; the sharpen filter relies on PHP's <a href="http://docs.php.net/imageconvolution">imageconvolution</a> function which is available only for PHP version 5.1.0+, and will leave the images unaltered for older versions
- added new cropping options: TOPCENTER, TOPRIGHT, MIDDLELEFT, MIDDLERIGHT, BOTTOMLEFT, BOTTOMCENTER, BOTTOMRIGHT; thanks to <strong>flam</strong> for suggesting
- entire logic behind the resize method was rewritten
- fixed a bug where the script would generate warnings if the <em>chmod</em> function was disabled via PHP configuration options; now it will not generate the warning but instead will set a value for the script's <em>error</em> property
- fixed a bug where if one would resize a transparent image and in the process would convert it to a JPEG (no transparency) and the <em>resize</em> method's <em>background_color</em> argument was set to <em>-1</em>, the resulted image's background color would be black; now it is white, as described in the documentation; thanks to <strong>Julien</strong> for reporting

## version 2.1.2 (May 09, 2011)

- fixed a bug when resizing images having height greater than width, and using the resize() method with only the height argument; thanks to <strong>Manuweb2</strong> for reporting.

## version 2.1.1 (March 24, 2011)

- fixed a bug where the script would produce warnings on some particular transparent GIF images; thanks to <strong>Olof Fredriksson</strong> for reporting.

## version 2.1 (February 05, 2011)

- fixed a bug where the script would produce warnings on partially broken JPEG files and would not process the image; now the script will successfully handle such images
- fixed a bug where the rotate method was not working correctly on transparent PNG/GIF images
- improved overall handling of transparent images
- a new method was added: "flip_both" which flips an image both vertically and horizontally
- the code for flip_horizontal and flip_vertical methods was rewritten
- a more explicit example was added

## version 2.0 (September 27, 2010)

- entire code was audited and improved
- method names, method arguments and global properties were changed and therefore this version breaks compatibility with previous ones
- resize() method was improved and now can resize an image to exact width and height and still maintain the aspect ratio by involving the crop() method
- fixed a bug where the crop(), flip_horizontal() and flip_vertical() were not working correctly for transparent PNG files
- some documentation refinements

## version 1.0.5 (August 23, 2007)

- fixed a bug where the resize() method would produce unexpected results when the actual width of the image was smaller than the value of the resizeToWidth property and resizeIfSmaller property was set to FALSE. in this case, if the height of the image was to be adjusted upwards, the width of the image was increased indefinitely not taking in account the value of resizeToWidth property

## version 1.0.4 (October 13, 2006)

- a new method was added - crop()
- a new property was added - preserveSourceFileTime which is by default set to TRUE and which instructs the scripts to preserve the date/time of the source files and pass it on to the target files; thanks to <strong>patrick from swederland</strong>
- the flip_horizontal and flip_vertical methods were stil using the <a href="http://php.net/manual/en/function.imagecopy.php">imagecopy</a> function instead of using the <a href="http://www.php.net/manual/en/function.imagecopyresampled.php">imagecopyresampled</a> function
- the create_image_from_source_file() function was incorrectly checking for the existence of the source file: you could specify a valid path (but not a file) and the script would crash error because the path indeed existed even though it was not a file
- the result of the create_image_from_source_file() private method was poorly implemented and the script could be easily crashed by specifying bogus source files

## version 1.0.3 (September 13, 2006)

- if invalid sizes were specified for resizing (i.e. string or negative numbers) the script would crash
- resizing of transparent png24 files was not working; thanks <strong>mar251</strong>
- working with png files would always make the value of the "error" property equal to 5 even if everything went well
- when resizing, interpolation was not used and the resulting images were rough. now <a href="http://www.php.net/manual/en/function.imagecopyresampled.php">imagecopyresampled</a> function is used instead of <a href="http://www.php.net/manual/en/function.imagecopyresized.php">imagecopyresized</a>; thanks <strong>Sabri</strong>
- resizing was not working correctly in some cases

## version 1.0.2 (August 12, 2006)

- error checking for the source file was incorrectly implemented and the script would produce warnings and fatal errors if there were problems with the source file
- properties will now have default values in PHP 4

## version 1.0.1 (August 11, 2006)

- after output, the file was <a href="http://php.net/manual/en/function.chmod.php">chmod</a>-ed incorrectly
- the documentation now tells you about how to calculate the permission levels

## version 1.0 (August 04, 2006)

- initial release
