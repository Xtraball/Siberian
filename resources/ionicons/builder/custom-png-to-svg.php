<?php

$customFiles = glob(__DIR__ . '/../custom/*.png');
foreach ($customFiles as $customFile) {
    $targetPnm = str_replace(".png", ".pbm", $customFile);
    $targetSvg = str_replace(".png", ".svg", $customFile);
    $targetSvg = str_replace("/custom/", "/src/", $targetSvg);

    // PNG to PNM
    exec("convert -flatten $customFile $targetPnm");
    // PNM to SVG 512x512
    exec("potrace -W 512 -H 512 -s -o $targetSvg $targetPnm");
    // Clean-up PNM
    exec("rm $targetPnm");
}