<?php

class Promotion_ApplicationController extends Application_Controller_Default {


    /**
     *
     */
    public function loadformAction() {
        $promotion_id = $this->getRequest()->getParam('promotion_id');

        $promotion = new Promotion_Model_Promotion();
        $promotion->find($promotion_id);
        if($promotion->getId()) {
            $form = new Promotion_Form_Promotion();

            $formData = $promotion->getData();
            $formData['use_only_once'] = $promotion->getIsUnique();
            $formData['unlimited'] = empty($promotion->getEndAt());

            // Fix empty QRCodes
            if (($promotion->getUnlockBy() === 'qrcode') &&
                (empty($promotion->getUnlockCode()))) {
                $promotion->setUnlockCode(uniqid());
                $promotion->save();
                $formData['unlock_code'] = $promotion->getUnlockCode();
            }

            $form->populate($formData);
            $form->setValueId($this->getCurrentOptionValue()->getId());
            $form->removeNav('promotion-nav');
            $form->addQrCode($formData['unlock_code']);
            $form->addNav('promotion-edit-nav', 'Save', false);
            $form->setPromotionId($promotion->getId());

            $payload = [
                'success' => true,
                'form' => $form->render(),
                'message' => __('Success.')
            ];
        } else {
            $payload = [
                'error' => true,
                'message' => __('The promotion you are trying to edit doesn\'t exists.'),
            ];
        }

        $this->_sendJson($payload);
    }

    /**
     * Create/Edit promotion
     *
     * @throws exception
     */
    public function editpostAction() {
        $values = $this->getRequest()->getPost();

        // Force end_at to null on unlimited
        if($values['unlimited'] === '1') {
            $values['end_at'] = null;
        }

        $form = new Promotion_Form_Promotion();
        if($form->isValid($values)) {

            $optionValue = $this->getCurrentOptionValue();
            $promotion = new Promotion_Model_Promotion();
            $promotion = $promotion->find($values['promotion_id']);

            Siberian_Form_Abstract::handlePicture(
                $optionValue, $promotion, 'picture', $values['picture']);

            Siberian_Form_Abstract::handlePicture(
                $optionValue, $promotion, 'thumbnail', $values['thumbnail']);

            $promotion
                ->setTitle($values['title'])
                ->setDescription($values['description'])
                ->setConditions($values['conditions'])
                ->setEndAt($values['end_at'])
                ->setIsActive(1)
                ->setUnlockBy($values['unlock_by'])
                ->setIsUnique($values['use_only_once'])
                ->setValueId($values['value_id'])
                ->save();

            // Write QRCode file in place!
            $image_name = $promotion->getId() . '-qrpromotion_qrcode.png';
            $file = Core_Model_Directory::getBasePathTo('/images/application/' .
                $this->getApplication()->getId() . '/application/qrpromotion/' .
                $image_name);

            if ($values['unlock_by'] === 'qrcode' && !empty($values['unlock_code'])) {
                if (!file_exists(dirname($file))) {
                    mkdir(dirname($file), 0777, true);
                }

                $qrCode = $this->generateQrCode($values['unlock_code'], 200, 10);
                $qrCode->writeFile($file);

                $promotion
                    ->setUnlockCode($values['unlock_code'])
                    ->save();
            }

            // Update touch date, then never expires (until next touch)!
            $this->getCurrentOptionValue()
                ->touch()
                ->expires(-1);

            $payload = array(
                'success' => true,
                'message' => __('Success.'),
            );
        } else {
            // Do whatever you need when form is not valid!
            $payload = array(
                'error' => true,
                'message' => $form->getTextErrors(),
                'errors' => $form->getTextErrors(true),
            );
        }

        $this->_sendJson($payload);
    }

    /**
     * Delete place
     */
    public function deletepostAction() {
        $values = $this->getRequest()->getPost();

        $form = new Promotion_Form_Promotion_Delete();
        if ($form->isValid($values)) {

            $promotion = new Promotion_Model_Promotion();
            $promotion->find($form->getValue('promotion_id'));

            if ($promotion->getUnlockBy() === 'qrcode') {
                $image_name = $promotion->getId() . '-qrpromotion_qrcode.png';
                $file = Core_Model_Directory::getBasePathTo('/images/application/' .
                    $this->getApplication()->getId() . '/application/qrpromotion/' .
                    $image_name);

                if (file_exists($file)) {
                    unlink($file);
                }
            }

            $promotion->delete();

            // Update touch date, then never expires (until next touch)!
            $this->getCurrentOptionValue()
                ->touch()
                ->expires(-1);

            $payload = [
                'success' => true,
                'success_message' => __('Promotion successfully deleted.'),
                'message_loader' => 0,
                'message_button' => 0,
                'message_timeout' => 2
            ];
        } else {
            $payload = [
                'error' => true,
                'message' => $form->getTextErrors(),
                'errors' => $form->getTextErrors(true),
            ];
        }

        $this->_sendJson($payload);
    }

    /**
     * Embed QRCode generation!
     */
    public function generateqrcodeAction() {
        try {
            $request = $this->getRequest();

            $size = $request->getParam('size', 200);
            $margin = $request->getParam('margin', 10);
            $code = $request->getParam('code', uniqid());

            $qrCode = $this->generateQrCode($code, $size, $margin);

            // Directly output the QR code!
            header('Content-Type: ' . $qrCode->getContentType());
            echo $qrCode->writeString();
            die;
        } catch (Exception $e) {
            die(__('Invalid QRCode parameters.'));
        }
    }

    /**
     * @param $code
     * @param int $size
     * @param int $margin
     * @return \Endroid\QrCode\QrCode
     */
    private function generateQrCode ($code, $size = 200, $margin = 10) {
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

    /**
     * @param $option
     * @return string
     * @throws Exception
     */
    public function exportAction() {
        if($this->getCurrentOptionValue()) {
            $promotion = new Promotion_Model_Promotion();
            $option = $this->getCurrentOptionValue();

            $result = $promotion->exportAction($option);

            $this->_download($result, $option->getCode()."-".date("Y-m-d_h-i-s").".yml", "text/x-yaml");
        }
    }
}