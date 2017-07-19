<?php

class Job_CategoryController extends Application_Controller_Default {

    /**
     *
     */
    public function loadformAction() {
        $category_id = $this->getRequest()->getParam("category_id");

        $category = new Job_Model_Category();
        $category->find($category_id);
        if($category->getId()) {
            $form = new Job_Form_Category();
            $form->populate($category->getData());
            $form->setValueId($this->getCurrentOptionValue()->getId());
            $form->removeNav("job-category-nav");
            $form->addNav("job-category-edit-nav", "Save", false);
            $form->setCategoryId($category->getId());

            $html = array(
                "success" => 1,
                "form" => $form->render(),
                "message" => __("Success."),
            );
        } else {
            /** Do whatever you need when form is not valid */
            $html = array(
                "error" => 1,
                "message" => __("The category you are trying to edit doesn't exists."),
            );
        }

        $this->_sendHtml($html);
    }

    /**
     * Create/Edit category
     *
     * @throws exception
     */
    public function editpostAction() {
        $values = $this->getRequest()->getPost();

        $form = new Job_Form_Category();
        if($form->isValid($values)) {
            /** Do whatever you need when form is valid */
            $category = new Job_Model_Category();
            $category
                ->addData($values)
                ->addData(array(
                    "is_active" => true,
                ))
            ;

            $path_icon = Siberian_Feature::moveUploadedFile($this->getCurrentOptionValue(), Core_Model_Directory::getTmpDirectory()."/".$values['icon']);
            $category->setData("icon", $path_icon);
            $category->save();

            $category->save();

            /** Update touch date, then never expires (until next touch) */
            $this->getCurrentOptionValue()
                ->touch()
                ->expires(-1);

            $html = array(
                "success" => 1,
                "message" => __("Success."),
            );
        } else {
            /** Do whatever you need when form is not valid */
            $html = array(
                "error" => 1,
                "message" => $form->getTextErrors(),
                "errors" => $form->getTextErrors(true),
            );
        }

        $this->_sendHtml($html);
    }

    /**
     * Toggle category
     */
    public function togglepostAction() {
        $values = $this->getRequest()->getPost();

        $form = new Job_Form_Category_Toggle();
        if($form->isValid($values)) {
            $category = new Job_Model_Category();
            $result = $category->find($values["category_id"])->toggle();

            /** Update touch date, then never expires (until next touch) */
            $this->getCurrentOptionValue()
                ->touch()
                ->expires(-1);

            $html = array(
                "success" => 1,
                "state" => $result,
                "message" => ($result) ? __("Category enabled") : __("Category disabled"),
            );
        } else {
            /** Do whatever you need when form is not valid */
            $html = array(
                "error" => 1,
                "message" => __("Missing category_id"),
            );
        }

        $this->_sendHtml($html);
    }

    /**
     * Delete category
     */
    public function deletepostAction() {
        $values = $this->getRequest()->getPost();

        $form = new Job_Form_Category_Delete();
        if($form->isValid($values)) {
            $category = new Job_Model_Category();
            $category->find($values["category_id"]);
            $category->delete();

            /** Update touch date, then never expires (until next touch) */
            $this->getCurrentOptionValue()
                ->touch()
                ->expires(-1);

            $html = array(
                'success' => 1,
                'success_message' => __('Category successfully deleted.'),
                'message_loader' => 0,
                'message_button' => 0,
                'message_timeout' => 2
            );
        } else {
            $html = array(
                "error" => 1,
                "message" => $form->getTextErrors(),
                "errors" => $form->getTextErrors(true),
            );
        }

        $this->_sendHtml($html);
    }

}