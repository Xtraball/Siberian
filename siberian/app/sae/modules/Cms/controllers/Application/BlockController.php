<?php

class Cms_Application_BlockController extends Application_Controller_Default {

    public function getblockAction() {
        $value_id = $this->getCurrentOptionValue()->getId();
        $block_id = $this->getRequest()->getParam("block_id");

        try {

            $block_model = new Cms_Model_Application_Block();
            $block_model->find($block_id);
            if(!$block_model->getId()) {
                throw new Siberian_Exception(__("This block type doesn't exist"));
            }

            $block_template = str_replace("/block/", "/block_v2/", $block_model->getTemplate());

            switch($block_model->getType()) {
                case "text":
                    $form = new Cms_Form_Block_Text();
                    break;
                case "image":
                    $form = new Cms_Form_Block_Image();
                    break;
                case "video":
                    $form = new Cms_Form_Block_Video();
                    break;
                case "address":
                    $form = new Cms_Form_Block_Address();
                    break;
                case "button":
                    $form = new Cms_Form_Block_Button();
                    break;
                case "file":
                    $form = new Cms_Form_Block_File();
                    break;
                case "slider":
                    $form = new Cms_Form_Block_Slider();
                    break;
                case "cover":
                    $form = new Cms_Form_Block_Cover();
                    break;
                default:
                    throw new Siberian_Exception(__("This block type doesn't exist"));
            }

            $form->setValueId($value_id);

            # This form is special and needs more javascript, so we made a partial
            $html = $this->getLayout()
                ->addPartial("block_template", "Core_View_Default", $block_template)
                ->setForm($form)
                ->setTitle($block_model->getTitle())
                ->toHtml();

            $data = array(
                "success"   => 1,
                "html"      => $html,
            );
        } catch (Exception $e) {
            /** Do whatever you need when form is not valid */
            $data = array(
                "error"     => 1,
                "message"   => __("An error occured while adding the block."),
            );
        }

        $this->_sendHtml($data);
    }

}
