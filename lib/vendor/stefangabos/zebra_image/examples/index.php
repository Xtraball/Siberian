<!DOCTYPE html>

<html>

    <head>

        <meta charset="utf-8">

        <title>Zebra_Image examples</title>

    </head>

    <body style="font-family: Geneva, 'Lucida Sans', 'Lucida Grande', 'Lucida Sans Unicode', Verdana, sans-serif; font-size: 13px">

    <h2>Zebra_Image, a lightweight image manipulation library written in PHP</h2>

    <?php if (!is_dir('results') || !is_writable('results')):?>

        <p>Please create the <em>results</em> folder at <em><?php echo dirname(__FILE__)?></em> and make sure it is writable!</p>

    <?php else:

    function show_error($error_code, $source_path, $target_path)
    {

        // if there was an error, let's see what the error is about
        switch ($error_code) {

            case 1:
                echo 'Source file "' . $source_path . '" could not be found!';
                break;
            case 2:
                echo 'Source file "' . $source_path . '" is not readable!';
                break;
            case 3:
                echo 'Could not write target file "' . $source_path . '"!';
                break;
            case 4:
                echo $source_path . '" is an unsupported source file format!';
                break;
            case 5:
                echo $target_path . '" is an unsupported target file format!';
                break;
            case 6:
                echo 'GD library version does not support target file format!';
                break;
            case 7:
                echo 'GD library is not installed!';
                break;
            case 8:
                echo '"chmod" command is disabled via configuration!';
                break;

        }

    }

    // include the class
    require '../Zebra_Image.php';

    // create a new instance of the class
    $image = new Zebra_Image();

    // set this to TRUE if you work on images uploaded by users
    // (see http://stefangabos.ro/wp-content/docs/Zebra_Image/Zebra_Image/Zebra_Image.html#var$auto_handle_exif_orientation)
    //$auto_handle_exif_orientation = true;

    // indicate a source image
    $image->source_path = 'images/transparent-png24.png';

    /**
     *
     *  THERE'S NO NEED TO EDIT BEYOUND THIS POINT
     *
     */

    $ext = substr($image->source_path, strrpos($image->source_path, '.') + 1);

    // indicate a target image
    $image->target_path = 'results/resize.' . $ext;

    // resize
    // and if there is an error, show the error message
    if (!$image->resize(100, 100, ZEBRA_IMAGE_BOXED, -1)) show_error($image->error, $image->source_path, $image->target_path);

    // from this moment on, work on the resized image
    $image->source_path = 'results/resize.' . $ext;

    // indicate a target image
    $image->target_path = 'results/flip-h.' . $ext;

    // flip horizontally
    // and if there is an error, show the error message
    if (!$image->flip_horizontal()) show_error($image->error, $image->source_path, $image->target_path);

    // indicate a target image
    $image->target_path = 'results/flip-v.' . $ext;

    // flip vertically
    // and if there is an error, show the error message
    if (!$image->flip_vertical()) show_error($image->error, $image->source_path, $image->target_path);

    // indicate a target image
    $image->target_path = 'results/flip-b.' . $ext;

    // flip both horizontally and vertically
    // and if there is an error, show the error message
    if (!$image->flip_both()) show_error($image->error, $image->source_path, $image->target_path);

    // indicate a target image
    $image->target_path = 'results/crop.' . $ext;

    // crop
    // and if there is an error, show the error message
    if (!$image->crop(0, 0, 50, 50)) show_error($image->error, $image->source_path, $image->target_path);

    // indicate a target image
    $image->target_path = 'results/rotate.' . $ext;

    // rotate
    // and if there is an error, show the error message
    if (!$image->rotate(45)) show_error($image->error, $image->source_path, $image->target_path);

    // indicate a target image
    $image->target_path = 'results/filter.' . $ext;

    // apply some filters
    // (this combination produces the "sepia" filter)
    $image->apply_filter(array(
        array('grayscale'),
        array('colorize', 90, 60, 40),
    ));

    ?>
    <p>Table has background so that transparency can be observed.</p>
    <table style="background:#ABCDEF; border: 2px solid #666">
    	<tr>
            <td width="100" align="center">Resized to 100x100</td>
            <td width="100" align="center">Flipped horizontally</td>
            <td width="100" align="center">Flipped vertically</td>
            <td width="100" align="center">Flipped both horizontally and vertically</td>
            <td width="100" align="center">Cropped from 0, 0 to 50, 50</td>
            <td width="100" align="center">Rotated 45 degrees clockwise</td>
            <td width="100" align="center">Sepia<br>filter</td>
        </tr>
        <tr>
            <td align="center"><img src="results/resize.<?php echo $ext?>" alt=""></td>
            <td align="center"><img src="results/flip-h.<?php echo $ext?>" alt=""></td>
            <td align="center"><img src="results/flip-v.<?php echo $ext?>" alt=""></td>
            <td align="center"><img src="results/flip-b.<?php echo $ext?>" alt=""></td>
            <td align="center"><img src="results/crop.<?php echo $ext?>" alt=""></td>
            <td align="center"><img src="results/rotate.<?php echo $ext?>" alt=""></td>
            <td align="center"><img src="results/filter.<?php echo $ext?>" alt=""></td>
        </tr>
    </table>

    <?php

    endif?>

    </body>

</html>