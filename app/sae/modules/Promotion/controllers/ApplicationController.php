<?php

class Promotion_ApplicationController extends Application_Controller_Default
{

    public function formAction() {
        if($this->getRequest()->getParam("id")) {
            $id = $this->getRequest()->getParam("id");
        }
        if($this->getRequest()->getParam("option_value_id")) {
            $value_id = $this->getRequest()->getParam("option_value_id");
        }
        if($this->getRequest()->getParam("unlock_by")) {
            $unlock_type = $this->getRequest()->getParam("unlock_by");
        }
        try {
            $promotion = new Promotion_Model_Promotion();
            if(isset($id)) {
                $promotion->find($id);
            }
            $this->getLayout()->setBaseRender('form', 'promotion/application/edit/form.phtml', 'admin_view_default')->setPromotion($promotion)->setValue($value_id)->setUnlockType($unlock_type);
            $html = array(
                'form' => $this->getLayout()->render(),
                'success' => 1
            );
        } catch (Exception $e) {
            $html = array(
                'message' => $e->getMessage()
            );
        }
        $this->getLayout()->setHtml(Zend_Json::encode($html));
    }

    public function editpostAction() {

        if($data = $this->getRequest()->getPost()) {

            $html = '';

            try {
                // Test s'il y a un value_id
                if(empty($data['value_id'])) throw new Exception($this->_('An error occurred while saving. Please try again later.'));

                // Récupère l'option_value en cours
                $option_value = new Application_Model_Option_Value();
                $option_value->find($data['value_id']);

                // Instancie une nouvelle promotion
                $promotion = new Promotion_Model_Promotion();

                // Test si l'option des réductions spéciales est activée et payée
                if($id = $this->getRequest()->getParam('id')) {
                    $promotion->find($id);
                    if($promotion->getValueId() AND $promotion->getValueId() != $option_value->getId()) {
                        throw new Exception('An error occurred while saving. Please try again later.');
                    }
                }
                //Vérifs champs
                if(empty($data['title']) || empty($data['description']) || empty($data['title'])) {
                    throw new Exception($this->_('An error occurred while saving your discount. Please fill in all fields'));
                }
                if(!isset($data['is_illimited']) && empty($data['end_at'])) {
                    throw new Exception($this->_('An error occurred while saving your discount. Please fill in all fields'));
                }
                if(!empty($data['end_at'])) {
                    $date_actuelle = new Zend_Date();
                    $date_modif = new Zend_Date($data['end_at'], 'y-MM-dd');
                    if($date_modif < $date_actuelle) {
                        throw new Exception($this->_('Please select an end date greater than the current date.'));
                        die;
                    }
                }

                if(!empty($data['is_illimited']))
                {
                    $data['end_at'] = null;
                }

                $data['force_validation'] = !empty($data['force_validation']);
                $data['is_unique'] = !empty($data['is_unique']);
                $data['owner'] = 1;

                if(isset($data['available_for']) AND $data['available_for'] == 'all') $promotion->resetConditions();

                if(!empty($data['picture'])) {

                    $filename = pathinfo($data['picture'], PATHINFO_BASENAME);
                    $relative_path = $option_value->getImagePathTo();
                    $folder = Application_Model_Application::getBaseImagePath().$relative_path;
                    $img_dst = $folder.'/'.$filename;
                    $img_src = Core_Model_Directory::getTmpDirectory(true).'/'.$filename;

                    if(!is_dir($folder)) {
                        mkdir($folder, 0777, true);
                    }

                    if(!copy($img_src, $img_dst)) {
                        throw new exception($this->_('An error occurred while saving your picture. Please try again later.'));
                    } else {
                        $data['picture'] = $relative_path.'/'.$filename;
                    }
                }
                else if(!empty($data['remove_cover'])) {
                    $data['picture'] = null;
                }
                
                $promotion->setData($data);

                $promotion->save();

                if(!empty($data['unlock_code'])) {
                    $dir_image = Core_Model_Directory::getBasePathTo("/images/application/".$this->getApplication()->getId());

                    if(!is_dir($dir_image)) mkdir($dir_image, 0775, true);
                    if(!is_dir($dir_image."/application")) mkdir($dir_image."/application", 0775, true);
                    if(!is_dir($dir_image."/application/qrpromotion")) mkdir($dir_image."/application/qrpromotion", 0775, true);

                    $dir_image .= "/application/qrpromotion/";
                    $image_name = $promotion->getId()."-qrpromotion_qrcode.png";

                    copy('http://api.qrserver.com/v1/create-qr-code/?color=000000&bgcolor=FFFFFF&data=sendback%3A'.$data["unlock_code"].'&qzone=1&margin=0&size=200x200&ecc=L', $dir_image.$image_name);
                }

                $html = array(
                    'promotion_id' => $promotion->getId(),
                    'success_message' => $this->_('Discount successfully saved'),
                    'message_timeout' => 2,
                    'message_button' => 0,
                    'message_loader' => 0
                );
            }
            catch(Exception $e) {
                $html = array(
                    'message' => $e->getMessage(),
                    'url' => '/promotion/admin/list'
                );
            }

            $this->getLayout()->setHtml(Zend_Json::encode($html));

        }

    }

    public function deletepostAction() {
        $id = $this->getRequest()->getParam("id");
        $html = '';
        try {
            $promotion = new Promotion_Model_Promotion();
            $promotion->find($id);

            if($promotion->getUnlockBy() == "qrcode") {
                $image_name = $promotion->getId()."-qrpromotion_qrcode.png";
                $file = Core_Model_Directory::getBasePathTo("/images/application/".$this->getApplication()->getId()."/application/qrpromotion/".$image_name);

                if(file_exists($file)) {
                    unlink($file);
                }
            }

            $promotion->delete();

            $html = array(
                'promotion_id' => $id,
                'success' => 1,
                'success_message' => $this->_('Discount successfully deleted'),
                'message_timeout' => 2,
                'message_button' => 0,
                'message_loader' => 0
            );
        } catch (Exception $e) {
            $html = array(
                'message' => $e->getMessage(),
                'url' => '/promotion/admin/list'
            );
        }

        $this->getLayout()->setHtml(Zend_Json::encode($html));
    }
}