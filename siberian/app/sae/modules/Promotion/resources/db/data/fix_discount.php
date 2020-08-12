<?php

// ALTER table.
try {
    $this->query('ALTER TABLE `promotion` CHANGE `unlock_code` `unlock_code` VARCHAR(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL;');
} catch (Exception $e) {
    // Silent
}

// Fix empty discounts
try {
    function generateQrCode ($code, $size = 200, $margin = 10) {
        $qrCode = new Endroid\QrCode\QrCode($code);
        $qrCode
            ->setSize($size)
            ->setWriterByName('png')
            ->setMargin($margin)
            ->setEncoding('UTF-8')
            ->setErrorCorrectionLevel(Endroid\QrCode\ErrorCorrectionLevel::MEDIUM)
            ->setForegroundColor(['r' => 0, 'g' => 0, 'b' => 0])
            ->setBackgroundColor(['r' => 255, 'g' => 255, 'b' => 255])
            ->setValidateResult(false)
        ;

        return $qrCode;
    }

    $promotions = (new Promotion_Model_Db_Table_Promotion())
        ->fixPromotions();

    foreach ($promotions as $promotion) {

        $unlockCode = uniqid();

        $image_name = $promotion->getId() . '-qrpromotion_qrcode.png';
        $file = Core_Model_Directory::getBasePathTo('/images/application/' .
            $promotion->getAppId() . '/application/qrpromotion/' .
            $image_name);

        if (!file_exists(dirname($file))) {
            mkdir(dirname($file), 0777, true);
        }

        $qrCode = generateQrCode($unlockCode, 200, 10);
        $qrCode->writeFile($file);

        $promotion
            ->setUnlockCode($unlockCode)
            ->save();
    }
} catch (Exception $e) {
    // Silent

}