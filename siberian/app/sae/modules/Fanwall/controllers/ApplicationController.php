<?php

use Fanwall\Form\Post as FormPost;
use Fanwall\Model\Post as Post;
use Siberian\Exception;
use Siberian\Feature;

/**
 * Class Fanwall_ApplicationController
 */
class Fanwall_ApplicationController extends Application_Controller_Default
{

    /**
     * @var array
     */
    public $cache_triggers = [
        "edit-post" => [
            "tags" => [
                //"feature_paths_valueid_#VALUE_ID#",
                //"assets_paths_valueid_#VALUE_ID#",
            ],
        ],
    ];

    /**
     *
     */
    public function editPostAction()
    {
        try {
            $optionValue = $this->getCurrentOptionValue();
            $request = $this->getRequest();
            $values = $request->getPost();

            $form = new FormPost();
            if ($form->isValid($values)) {

                $post = new Post();
                $post
                    ->addData($values)
                    ->addData([
                        "is_active" => true,
                    ])
                ;

                Feature::formImageForOption($optionValue, $post, $values, "image", true);

                $post->save();

                $optionValue
                    ->touch()
                    ->expires(-1);

                $payload = [
                    "success" => true,
                    "message" => __("Success."),
                ];
            } else {
                /** Do whatever you need when form is not valid */
                $payload = [
                    "error" => true,
                    "message" => $form->getTextErrors(),
                    "errors" => $form->getTextErrors(true),
                ];
            }
        } catch (\Exception $e) {
            $payload = [
                "error" => true,
                "message" => $e->getMessage(),
            ];
        }
        
        $this->_sendJson($payload);
    }

    /**
     *
     */
    public function loadFormAction()
    {
        try {
            $optionValue = $this->getCurrentOptionValue();
            $request = $this->getRequest();
            $postId = $request->getParam("post_id", null);
            $post = (new Post())->find($postId);

            if (!$post->getId()) {
                throw new Exception(p__("fanwall","This post entry do not exists!"));
            }

            $form = new FormPost();
            $form->removeNav("nav-fanwall-post");
            $form->populate($post->getData());
            $form->setValueId($optionValue->getId());
            $form->setPostId($post->getId());
            $form->loadFormSubmit();

            $payload = [
                "success" => true,
                "form" => $form->render(),
                "message" => __("Success."),
            ];

        } catch (\Exception $e) {
            $payload = [
                "error" => true,
                "message" => $e->getMessage(),
            ];
        }

        $this->_sendJson($payload);
    }

   /**

    public function deleteAction()
    {
        $html = '';
        if ($id = $this->getRequest()->getParam('id')) {
            try {
                $comment = new Comment_Model_Comment();
                $comment->find($id)->delete();
                $html = [
                    'success' => '1',
                    'success_message' => __('Information successfully deleted'),
                    'message_timeout' => 2,
                    'message_button' => 0,
                    'message_loader' => 0
                ];
            } catch (Exception $e) {
                $html = [
                    'error' => 1,
                    'message' => $e->getMessage()
                ];
            }
            $this->getLayout()->setHtml(Zend_Json::encode($html));
        }
    }

    public function hideAction()
    {
        $html = '';
        if ($id = $this->getRequest()->getParam('id')) {
            try {
                $comment = new Comment_Model_Comment();
                $comment->find($id)->setisVisible(0)->save();
                $html = [
                    'success' => '1',
                    'success_message' => __('Information successfully hidden'),
                    'message_timeout' => 2,
                    'message_button' => 0,
                    'message_loader' => 0
                ];
            } catch (Exception $e) {
                $html = [
                    'error' => 1,
                    'message' => $e->getMessage()
                ];
            }
            $this->getLayout()->setHtml(Zend_Json::encode($html));
        }
    }

    public function showAction()
    {
        $html = '';
        if ($id = $this->getRequest()->getParam('id')) {
            try {
                $comment = new Comment_Model_Comment();
                $comment->find($id)->setisVisible(1)->save();
                $html = [
                    'success' => '1',
                    'success_message' => __('Information successfully shown'),
                    'message_timeout' => 2,
                    'message_button' => 0,
                    'message_loader' => 0
                ];
            } catch (Exception $e) {
                $html = [
                    'error' => 1,
                    'message' => $e->getMessage()
                ];
            }
            $this->getLayout()->setHtml(Zend_Json::encode($html));
        }
    }

    public function saveradiusAction()
    {
        $html = '';
        if ($data = $this->getRequest()->getPost()) {
            try {
                if (empty($data['radius']))
                    throw new Exception(__('Radius must be provided.'));

                if (!is_numeric($data['radius']))
                    throw new Exception(__('Radius must be a valid numeric value.'));

                // Test s'il y a un value_id
                if (empty($data['value_id']))
                    throw new Exception(__('An error occurred while saving. Please try again later.'));

                // Récupère l'option_value en cours
                $option_value = new Application_Model_Option_Value();
                $option_value->find($data['value_id']);

                // Test s'il y a embrouille entre la value_id en cours de modification et l'application en session
                if (!$option_value->getId() OR $option_value->getAppId() != $this->getApplication()->getId()) {
                    throw new Exception(__('An error occurred while saving. Please try again later.'));
                }

                $radius = new Comment_Model_Radius();
                $radius->find($data['value_id'], 'value_id');

                if (!$radius->getId()) {
                    $radius->setValueId($data['value_id']);
                }

                $radius->addData($data)
                    ->save();

                $html = [
                    'success' => '1',
                    'success_message' => __('Information successfully saved'),
                    'message_timeout' => 2,
                    'message_button' => 0,
                    'message_loader' => 0
                ];
            } catch (Exception $e) {
                $html = [
                    'error' => 1,
                    'message' => $e->getMessage()
                ];
            }

            $this->getLayout()->setHtml(Zend_Json::encode($html));
        }
    }*/

}
