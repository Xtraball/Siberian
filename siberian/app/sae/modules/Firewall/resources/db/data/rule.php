<?php

// Firewall default rules!

$allowedExtensions = [
    'pem',
    'apk',
    'pks',
    'png',
    'jpg',
    'jpeg',
    'gif',
    'bmp',
    'txt',
    'csv',
    'xml',
    'yml',
    'yaml',
    'xml',
    'mp3',
    'mp4',
    'avi',
    'm4v',
    'mov',
    'ogg',
    'xlsx',
    'docx',
    'pdf',
    'doc',
    'xls',
    'rtf',
    'html',
    'wma',
    'mpg',
    'ppt',
    'pptx',
    'pub',
    'm4a',
    'psd',
    'ai',
    'json',
];

// Register default allowed extensions!
foreach ($allowedExtensions as $allowedExtension) {
    try {
        $fwRule = (new Firewall_Model_Rule())
            ->setData([
                'type' => \Firewall_Model_Rule::FW_TYPE_UPLOAD,
                'value' => $allowedExtension,
            ])
            ->insertOnce([
                'type',
                'value'
            ]);
    } catch (\Exception $e) {
        // Silent!
    }
}