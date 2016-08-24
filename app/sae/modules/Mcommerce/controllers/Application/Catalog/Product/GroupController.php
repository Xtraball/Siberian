<?php

class Mcommerce_Application_Catalog_Product_GroupController extends Application_Controller_Default_Ajax {

    public function editAction() {
        $group = new Catalog_Model_Product_Group();
        if($id = $this->getRequest()->getParam('group_id')) {
            $group->find($id);
        }

        $html = $this->getLayout()->addPartial('store_form', 'admin_view_default', 'mcommerce/application/edit/catalog/products/group/edit.phtml')
            ->setOptionValue($this->getCurrentOptionValue())
            ->setCurrentGroup($group)
            ->setRequireChoiceEditor($this->getRequest()->getParam('require-choice-editor') || $group->getAsCheckbox())
            ->toHtml();

        $html = array('form_html' => $html);

        $this->_sendHtml($html);

    }

    public function editpostAction() {


        if($datas = $this->getRequest()->getPost()) {

            try {
                $isNew = false;
                $group = new Catalog_Model_Product_Group();
                if(!empty($datas['group_id'])) {
                    $group->find($datas['group_id']);
                    if(!$group->getId()) {
                        throw new Exception($this->_('An error occurred while saving. Please try again later.'));
                    }
                }

                if(!$group->getId()) {
                    $isNew = true;
                    $datas["app_id"] = $this->getApplication()->getId();
                }

                $datas['is_required'] = !empty($datas['is_required']);
                $group->setData($datas)->save();
                $new_option_ids = $group->getNewOptionIds();

                $html = array(
                    'group_id' => $group->getId(),
                    'success' => '1',
                    'success_message' => $this->_("Options group successfully saved"),
                    'message_timeout' => 2,
                    'message_button' => 0,
                    'message_loader' => 0
                );

                if($isNew) {
                    $html['row_html'] = $this->getLayout()->addPartial('group_row_'.$group->getGroupId(), 'admin_view_default', 'mcommerce/application/edit/catalog/products/edit/group.phtml')
                        ->setCurrentGroup($group)
                        ->setCurrentGroupOptions($group->getOptions())
                        ->toHtml()
                    ;

                }
                else {

                    $html['group'] = array('name' => $group->getTitle(),);
                    $html['group']['options'] = array();
                    $html['group']['new_options'] = array();

                    foreach($group->getOptions() as $option) {
                        if(!in_array($option->getId(), $new_option_ids)) {
                            $html['group']['options'][$option->getId()] = array('id' => $option->getId(), 'name' => $option->getName());
                        }
                        else {
                            $html['group']['new_options'][] = array(
                                'row_html' => $this->getLayout()->addPartial('group_option_row_'.$option->getOptionId(), 'admin_view_default', 'mcommerce/application/edit/catalog/products/edit/group/row.phtml')
                                    ->setCurrentGroup($group)
                                    ->setCurrentGroupOption($option)
                                    ->toHtml(),
                                'id' => $option->getOptionId()
                            );
                        }
                    }
                }

            }
            catch(Exception $e) {
                $html = array(
                    'error' => 1,
                    'message' => $e->getMessage(),
                    'message_button' => 1,
                    'message_loader' => 1
                );
            }

            $this->_sendHtml($html);

        }

    }

    public function removeAction() {

        $store = new Mcommerce_Model_Store();

        try {
            if($id = $this->getRequest()->getParam('store_id')) {

                $mcommerce = $this->getCurrentOptionValue()->getObject();
                $store->find($id);
                if(!$store->getId() OR $mcommerce->getId() != $store->getMcommerceId()) {
                    throw new Exception($this->_('An error occurred during the process. Please try again later.'));
                }

                $store->setIsVisible(0)->save();

                $html = array(
                    'store_id' => $store->getId(),
                    'success' => '1',
                    'success_message' => $this->_("Options group successfully deleted"),
                    'message_timeout' => 2,
                    'message_button' => 0,
                    'message_loader' => 0
                );

            }
            else {
                throw new Exception($this->_('An error occurred during the process. Please try again later.'));
            }
        }
        catch(Exception $e) {
            $html = array(
                'error' => 1,
                'message' => $e->getMessage(),
                'message_button' => 1,
                'message_loader' => 1
            );
        }

        $this->_sendHtml($html);

    }

}