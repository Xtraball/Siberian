<?php

class Tour_ApplicationController extends Application_Controller_Default
{

    public function saveAction() {
        try {
            if($data = $this->getRequest()->getPost()) {
                if($data["step-elem-id"] AND $data["step-language-code"]) {
                    //Check if step exists
                    $step = new Tour_Model_Step();
                    $step = $step->find(array(
                        "element_id" => $data["step-elem-id"],
                        "language_code" => $data["step-language-code"],
                        "url" => $data["step-url"]
                    ));
                    $step_exists = $step->getId() ? true : false;

                    //Delete step if needed
                    if($step_exists AND $data["step-delete"]) {
                        $step->delete();
                        $step_exists = false;
                    } else {
                        $step->setTitle($data["step-title"])
                            ->setLanguageCode($data["step-language-code"])
                            ->setText($data["step-text"])
                            ->setPlacement($data["step-placement"])
                            ->setElementId($data["step-elem-id"])
                            ->setUrl($data["step-url"])
                        ;

                        if(!$step_exists) {
                            $step->setOrderIndex($data["step-order"]);
                        }

                        $step->save();
                    }

                    $html = array(
                        'success' => true,
                        'success_message' => __("Step saved successfully."),
                        'message_timeout' => 2,
                        'step_exists' => $step_exists,
                        'elem_id' => $data["step-elem-id"]
                    );
                } else {
                    throw new Siberian_Exception(__('An error occurred while saving. Please try again later.'));
                }
            }
            else {
                throw new Siberian_Exception(__('An error occurred while saving. Please try again later.'));
            }

        }
        catch(Exception $e) {
            $html = array(
                'error' => true,
                'message' => $e->getMessage(),
                'message_timeout' => 2
            );
        }

        $this->_sendHtml($html);
    }

    public function reorderAction() {
        try {
            if($data = $this->getRequest()->getPost()) {
                foreach($data["new_order"] as $step_order) {
                    $step = new Tour_Model_Step();
                    $step->find(array("element_id" => $step_order["id"]));
                    if($step->getId()) {
                        $step->setOrderIndex($step_order["order"])->save();
                    }
                }

                $html = array(
                    'success' => true,
                    'message' => "ok",
                    'message_timeout' => 2
                );
            }
            else {
                throw new Siberian_Exception(__('An error occurred while saving. Please try again later.'));
            }

        }
        catch(Exception $e) {
            $html = array(
                'error' => true,
                'message' => $e->getMessage(),
                'message_timeout' => 2
            );
        }

        $this->_sendHtml($html);
    }

    public function findforlanguageAction() {
        try {
            if($data = $this->getRequest()->getPost()) {
                $existing_steps = new Tour_Model_Step();
                $existing_steps = $existing_steps->findAllForJS($data["language-code"], $data["url"]);

                $html = array(
                    'success' => true,
                    'steps' => $existing_steps
                );
            }
            else {
                throw new Siberian_Exception(__('An error occurred. Please try again later.'));
            }
        }
        catch(Exception $e) {
            $html = array(
                'error' => true,
                'message' => $e->getMessage(),
                'message_timeout' => 2
            );
        }

        $this->_sendHtml($html);
    }

}