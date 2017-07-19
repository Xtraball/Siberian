<?php

class Catalog_Application_MenuController extends Application_Controller_Default
{

    /**
     * @var array
     */
    public $cache_triggers = array(
        "editpost" => array(
            "tags" => array(
                "feature_paths_valueid_#VALUE_ID#",
                "assets_paths_valueid_#VALUE_ID#",
            ),
        ),
        "deletepost" => array(
            "tags" => array(
                "feature_paths_valueid_#VALUE_ID#",
                "assets_paths_valueid_#VALUE_ID#",
            ),
        ),
    );

    public function formAction() {
        if($this->getRequest()->getParam("id")) {
            $id = $this->getRequest()->getParam("id");
        }
        if($this->getRequest()->getParam("option_value_id")) {
            $value_id = $this->getRequest()->getParam("option_value_id");
        }
        try {
            $menu = new Catalog_Model_Product();
            if(isset($id)) {
                $menu->find($id);
            }
            $this->getLayout()->setBaseRender('form', 'catalog/application/edit/menu/form.phtml', 'admin_view_default')->setMenu($menu)->setValue($value_id);
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

        if($datas = $this->getRequest()->getPost()) {

            try {
                $option_value = new Application_Model_Option_Value();
                $option_value->find($datas['value_id']);

                $datas = $datas['menus'];

                foreach($datas as $product_id => $data) {

                    $product = new Catalog_Model_Product();

                    if($id = $this->getRequest()->getParam('id')) {
                        $product->find($id);
                        if($product->getValueId() != $option_value->getId()) {
                            throw new Exception($this->_('An error occurred while saving. Please try again later.'));
                        }
                    }

                    if(!$product->getId()) {
                        $product->setValueId($option_value->getId());
                    }

                    $pos_datas = array();
                    if(!empty($data['pos'])) {
                        foreach($data['pos'] as $key => $pos_data) {
                            $pos_datas[$key] = $pos_data;
                        }
                    }
                    if(!empty($data['picture'])) {
                        if(substr($data['picture'],0,1) == '/') {
                            unset($data['picture']);
                        } else {
                            $illus_relative_path = $option_value->getImagePathTo();
                            $folder = Application_Model_Application::getBaseImagePath().$illus_relative_path;
                            $file = Core_Model_Directory::getTmpDirectory(true).'/'.$data['picture'];
                            if (!is_dir($folder))
                                mkdir($folder, 0777, true);
                            if(!copy($file, $folder.$data['picture'])) {
                                throw new exception($this->_('An error occurred while saving your picture. Please try againg later.'));
                            } else {
                                $data['picture'] = $illus_relative_path.$data['picture'];
                            }
                        }
                    }
                    $product->addData($data)->setType('menu');
                    $product->setPosDatas($pos_datas);
                    $product->save();

                }

                $html = array();
                if(!$product->getData('is_deleted')) {
                    $html['menu_id'] = $product->getId();
                }
                $html['success'] = 1;
                $html['success_message'] = $this->_('Set meal successfully saved.');
                $html['message_timeout'] = 2;
                $html['message_button'] = 0;
                $html['message_loader'] = 0;

                /** Update touch date, then never expires (until next touch) */
                $option_value
                    ->touch()
                    ->expires(-1);
            }
            catch(Exception $e) {
                $html = array(
                    'message' => $this->_('An error occurred while saving the set meal. Please try again later.')
                );
            }

            $this->getLayout()->setHtml(Zend_Json::encode($html));
        }
    }

    public function deletepostAction() {
        $id = $this->getRequest()->getParam("id");
        $html = '';
        try {
            $menu = new Catalog_Model_Product();
            $menu->find($id)->delete();
            $html = array(
                'menu_id' => $id,
                'success' => 1,
                'success_message' => $this->_('Set meal successfully deleted.'),
                'message_timeout' => 2,
                'message_button' => 0,
                'message_loader' => 0
            );
        } catch (Exception $e) {
            $html = array(
                'message' => $e->getMessage()
            );
        }

        $this->getLayout()->setHtml(Zend_Json::encode($html));
    }

    public function validatecropAction() {
        if($datas = $this->getRequest()->getPost()) {
            try {
                $uploader = new Core_Model_Lib_Uploader();
                $file = $uploader->savecrop($datas);
                $datas = array(
                    'success' => 1,
                    'file' => $file,
                    'message_success' => $this->_('Info successfully saved'),
                    'message_button' => 0,
                    'message_timeout' => 2,
                );
            } catch (Exception $e) {
                $datas = array(
                    'error' => 1,
                    'message' => $e->getMessage()
                );
            }
            $this->_sendHtml($datas);
         }
    }

}
