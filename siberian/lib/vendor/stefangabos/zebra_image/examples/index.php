<?php

// report all error messages
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// error if results folder doesn't exist or is not writable
if (!is_dir('results') || !is_writable('results')) die('<strong>ERROR</strong><br>Please create the <em>results</em> folder at <em>' . dirname(__FILE__) . '</em> and make sure it is writable!');

// include the class
require '../Zebra_Image.php';

// create a new instance of the class
$image = new Zebra_Image();

// set this to TRUE if you work on images uploaded by users
// (see http://stefangabos.ro/wp-content/docs/Zebra_Image/Zebra_Image/Zebra_Image.html#var$auto_handle_exif_orientation)
//$auto_handle_exif_orientation = true;

// indicate a source image
$original_image = $image->source_path = 'images/' . (!isset($_GET['original']) || !in_array($_GET['original'], array('image.bmp', 'image-solid.gif', 'image-transparent.gif', 'image.jpg', 'image-solid-png24.png', 'image-solid-png8.png', 'image-transparent-png24.png', 'image-transparent-png8.png', 'image-transparent-webp.webp')) ? 'image-transparent-png24.png' : $_GET['original']);

/**
 *
 *  THERE'S NO NEED TO EDIT BEYOND THIS POINT
 *
 */

$ext = substr($image->source_path, strrpos($image->source_path, '.') + 1);

// indicate a target image
$image->target_path = 'results/resize.' . $ext;

// resize
// and if there is an error, show the error message
if (!$image->resize(200, 200, ZEBRA_IMAGE_BOXED, stripos($original_image, 'bmp') !== false ? '#FFFFFF' : -1)) show_error($image->error, $image->source_path, $image->target_path);

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
if (!$image->crop(0, 0, 100, 100)) show_error($image->error, $image->source_path, $image->target_path);

// indicate a target image
$image->target_path = 'results/rotate.' . $ext;

// rotate
// and if there is an error, show the error message
// (if we are rotating a solid image, use #FFF as color of uncovered zone after the rotation)
if (!$image->rotate(45, stripos($original_image, 'solid') !== false ? '#FFFFFF' : -1)) show_error($image->error, $image->source_path, $image->target_path);

// indicate a target image
$image->target_path = 'results/filter.' . $ext;

// apply some filters
// (this combination produces the "sepia" filter)
$image->apply_filter(array(
    array('grayscale'),
    array('emboss'),
));

function show_error($error_code, $source_path, $target_path) {

    // let's see what the error is about
    switch ($error_code) {

        case 1:
            echo 'Source file "' . $source_path . '" could not be found';
            break;
        case 2:
            echo 'Source file "' . $source_path . '" is not readable';
            break;
        case 3:
            echo 'Could not write target file "' . $source_path . '"';
            break;
        case 4:
            echo '"' . $source_path . '" is of an unsupported source file format';
            break;
        case 5:
            echo '"' . $target_path . '" is of an unsupported target file format';
            break;
        case 6:
            echo 'GD library version does not support target file format';
            break;
        case 7:
            echo 'GD library is not installed';
            break;
        case 8:
            echo '"chmod" command is disabled via configuration';
            break;

    }

    die();

}

?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Zebra_Image examples</title>
        <link rel="stylesheet" href="style.css">
    </head>
    <body>
    <h1>Zebra_Image<br>a compact (one-file only) and lightweight<br>PHP image manipulation library</h1>
    <table cellspacing="0" cellpadding="0">
        <thead>
        <tr>
            <th colspan="2" width="1">
                Original image was <b><?php echo basename($original_image); ?></b><br>
                Multiple image types to test with are available:<br>
                <ul>
                    <li><a href="?original=image-solid.gif">solid <strong>GIF</strong></a></li>
                    <li><a href="?original=image-transparent.gif">transparent <strong>GIF</strong></a></li>
                    <li><a href="?original=image.jpg"><strong>JPEG</strong></a></li>
                    <li><a href="?original=image-solid-png24.png">solid <strong>PNG24</strong></a></li>
                    <li><a href="?original=image-solid-png8.png">solid <strong>PNG8</strong></a></li>
                    <li><a href="?original=image-transparent-png24.png">transparent <strong>PNG24</strong></a></li>
                    <li><a href="?original=image-transparent-png8.png">transparent <strong>PNG8</strong></a></li>
                    <li><a href="?original=image-transparent-webp.webp">transparent <strong>WEBP</strong></a></li>
                    <li><a href="?original=image.bmp"><strong>BMP</strong></a> (really slow)</li>
                </ul>
                <small class="text-right display-block"><em>images have background in order to observe transparency</em></small>
            </th>
        </tr>
        </thead>
        <tbody>
        <?php foreach (array(
            array(
                'title' => 'Resizing',
                'image' => 'resize',
                'usage' => '$image->resize(200, 200, ZEBRA_IMAGE_BOXED, -1)',
                'docs'  =>  'methodresize',
            ),
            array(
                'title' => 'Horizontal flip',
                'image' => 'flip-h',
                'usage' => '$image->flip_horizontal()',
                'docs'  =>  'methodflip_horizontal',
            ),
            array(
                'title' => 'Vertical flip',
                'image' => 'flip-v',
                'usage' => '$image->flip_vertical()',
                'docs'  =>  'methodflip_vertical',
            ),
            array(
                'title' => 'Flip both horizontally and vertically',
                'image' => 'flip-b',
                'usage' => '$image->flip_both()',
                'docs'  =>  'methodflip_both',
            ),
            array(
                'title' => 'Crop',
                'image' => 'crop',
                'usage' => '$image->crop(0, 0, 100, 100)',
                'docs'  =>  'methodcrop',
            ),
            array(
                'title' => 'Rotate',
                'image' => 'rotate',
                'usage' => '$image->rotate(45' . (stripos($original_image, 'solid') !== false ? ', \'#FFFFFF\'' : '') . ')',
                'docs'  =>  'methodrotate',
            ),
            array(
                'title' => 'Filters',
                'image' => 'filter',
                'usage' => '$image->apply_filter(array(' . "\n\t" . 'array(\'grayscale\'),' . "\n\t" . 'array(\'emboss\'),' . "\n" . '))',
                'docs'  =>  'methodapply_filter',
            ),
        ) as $index => $options): ?>
        <tr>
            <th colspan="2">
                <h3><?php echo $options['title']; ?></h3>
            </th>
        </tr>
    	<tr>
            <td class="text-center">
                <div class="image-container">
                    <img
                        src="results/<?php echo $options['image']; ?>.<?php echo $ext . '?t=' . time(); ?>"
                        width="<?php echo $options['image'] === 'crop' ? 100 : 200; ?>"
                        height="<?php echo $options['image'] === 'crop' ? 100 : 200; ?>"
                        alt="<?php echo $options['title']; ?>">
                </div>
            </td>
            <td>
                <?php echo highlight_string('' .
                    '<?php ' .
                        "\n\n" .    '$image = new Zebra_Image(); ' .
                        ($index > 0 ? "\n" . '// source image is the one resized at the beginning' : '') .
                        "\n" .      '$image->source_path = \'' . ($index > 0 ? 'resized' : 'input') . '_image.' . $ext . '\';' .
                        "\n" .      '$image->target_path = \'output_image.' . $ext . '\';' .
                        "\n" .      'if (!' . $options['usage'] . ')' .
                        "\n\t" .        'die($image->error);' . "\n\t"
                , true); ?>
                <small>Read more in the <a href="https://stefangabos.github.io/Zebra_Image/Zebra_Image/Zebra_Image.html#<?php echo $options['docs']; ?>" target="_bank">documentation</a></small>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </body>
</html>